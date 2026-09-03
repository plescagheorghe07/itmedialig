<?php

namespace App\Services;

use App\Models\MatchGoal;
use App\Models\MatchModel;
use App\Models\Player;
use PDO;

class MatchPanelService
{
    public function __construct(
        private PDO $db,
        private MatchModel $matches,
        private MatchGoal $goals,
        private Player $players,
        private LiveScoreService $live
    ) {}

    public function getPanelData(string $matchId): ?array
    {
        $match = $this->matches->findEnriched($matchId);
        if (!$match) {
            return null;
        }

        $players1 = $this->players->byTeam($match['echipa1_id']);
        $players2 = $this->players->byTeam($match['echipa2_id']);
        $goalEvents = $this->goals->byMatch($matchId);

        return [
            'match' => $match,
            'players1' => $players1,
            'players2' => $players2,
            'goals' => $goalEvents,
            'motm1' => $this->playerBrief($match['omul_meciului_echipa1_id'] ?? null),
            'motm2' => $this->playerBrief($match['omul_meciului_echipa2_id'] ?? null),
        ];
    }

    public function startMatch(string $matchId): array
    {
        $match = $this->requireMatch($matchId);
        $this->matches->update($matchId, array_merge($this->matchFormFromRow($match), [
            'status' => 'se_joaca',
            'scor_echipa1' => (int) ($match['scor_echipa1'] ?? 0),
            'scor_echipa2' => (int) ($match['scor_echipa2'] ?? 0),
        ]));
        return $this->broadcast($matchId, 'match_started');
    }

    public function addGoal(string $matchId, string $teamId, ?string $playerId, ?int $minute): array
    {
        $match = $this->requireMatch($matchId);
        if ($match['status'] !== 'se_joaca') {
            throw new \InvalidArgumentException('Golurile se pot adăuga doar când meciul este în desfășurare.');
        }
        if ($teamId !== $match['echipa1_id'] && $teamId !== $match['echipa2_id']) {
            throw new \InvalidArgumentException('Echipă invalidă pentru acest meci.');
        }

        $goalId = $this->goals->add($matchId, $teamId, $playerId ?: null, $minute);
        $this->syncScores($matchId);

        $goal = $this->goals->byMatch($matchId);
        $lastGoal = end($goal) ?: null;

        $payload = $this->broadcast($matchId, 'goal_added', ['goal' => $lastGoal]);
        $payload['goal_id'] = $goalId;
        return $payload;
    }

    public function removeGoal(string $goalId): array
    {
        $goal = $this->goals->find($goalId);
        if (!$goal) {
            throw new \InvalidArgumentException('Gol negăsit.');
        }
        $matchId = $goal['match_id'];
        $this->goals->delete($goalId);
        $this->syncScores($matchId);
        return $this->broadcast($matchId, 'goal_removed', ['goal_id' => $goalId]);
    }

    public function setMotm(string $matchId, int $side, ?string $playerId): array
    {
        $match = $this->requireMatch($matchId);
        if ($match['status'] !== 'terminat') {
            throw new \InvalidArgumentException('Omul meciului se setează după terminarea meciului.');
        }
        $data = $this->matchFormFromRow($match);
        if ($side === 1) {
            $data['omul_meciului_echipa1_id'] = $playerId;
        } else {
            $data['omul_meciului_echipa2_id'] = $playerId;
        }
        $this->matches->update($matchId, $data);
        return $this->broadcast($matchId, 'motm_updated');
    }

    public function finishMatch(string $matchId): array
    {
        $match = $this->requireMatch($matchId);
        $this->syncScores($matchId);
        $match = $this->matches->findEnriched($matchId);
        $this->matches->update($matchId, array_merge($this->matchFormFromRow($match), ['status' => 'terminat']));
        return $this->broadcast($matchId, 'match_finished');
    }

    public function getLivePayload(string $matchId): ?array
    {
        $data = $this->getPanelData($matchId);
        if (!$data) {
            return null;
        }
        return [
            'match' => $this->formatMatchForLive($data['match']),
            'motm1' => $data['motm1'],
            'motm2' => $data['motm2'],
            'goals' => array_map(fn($g) => [
                'id' => $g['id'],
                'team_id' => $g['team_id'],
                'player_id' => $g['player_id'],
                'player_name' => trim(($g['prenume'] ?? '') . ' ' . ($g['nume'] ?? '')),
                'team_nume' => $g['team_nume'],
                'minute' => $g['minute'],
            ], $data['goals']),
        ];
    }

    private function syncScores(string $matchId): void
    {
        $match = $this->matches->find($matchId);
        if (!$match) {
            return;
        }
        $counts = $this->goals->countByTeam($matchId);
        $this->matches->update($matchId, array_merge($this->matchFormFromRow($match), [
            'scor_echipa1' => $counts[$match['echipa1_id']] ?? 0,
            'scor_echipa2' => $counts[$match['echipa2_id']] ?? 0,
        ]));
    }

    private function broadcast(string $matchId, string $eventType, array $extra = []): array
    {
        $match = $this->matches->findEnriched($matchId);
        $goals = $this->goals->byMatch($matchId);
        $payload = [
            'type' => $eventType,
            'match' => $this->formatMatchForLive($match),
            'goals' => $goals,
            'motm1' => $this->playerBrief($match['omul_meciului_echipa1_id'] ?? null),
            'motm2' => $this->playerBrief($match['omul_meciului_echipa2_id'] ?? null),
        ];
        $this->live->publish([
            'type' => $eventType,
            'id' => $matchId,
            'echipa1_id' => $match['echipa1_id'],
            'echipa2_id' => $match['echipa2_id'],
            'echipa1_nume' => $match['echipa1_nume'],
            'echipa2_nume' => $match['echipa2_nume'],
            'scor_echipa1' => $match['scor_echipa1'],
            'scor_echipa2' => $match['scor_echipa2'],
            'status' => $match['status'],
            'live_link' => $match['live_link'] ?? null,
            'goals' => $goals,
        ]);
        return array_merge($payload, $extra);
    }

    private function formatMatchForLive(array $match): array
    {
        return [
            'id' => $match['id'],
            'status' => $match['status'],
            'scor_echipa1' => (int) ($match['scor_echipa1'] ?? 0),
            'scor_echipa2' => (int) ($match['scor_echipa2'] ?? 0),
            'echipa1_id' => $match['echipa1_id'],
            'echipa2_id' => $match['echipa2_id'],
            'echipa1_nume' => $match['echipa1_nume'] ?? '',
            'echipa2_nume' => $match['echipa2_nume'] ?? '',
            'echipa1_logo' => $match['echipa1_logo'] ?? null,
            'echipa2_logo' => $match['echipa2_logo'] ?? null,
            'omul_meciului_echipa1_id' => $match['omul_meciului_echipa1_id'] ?? null,
            'omul_meciului_echipa2_id' => $match['omul_meciului_echipa2_id'] ?? null,
            'data_meci' => $match['data_meci'],
            'locatie' => $match['locatie'] ?? null,
            'live_link' => $match['live_link'] ?? null,
            'motm1' => $this->playerBrief($match['omul_meciului_echipa1_id'] ?? null),
            'motm2' => $this->playerBrief($match['omul_meciului_echipa2_id'] ?? null),
        ];
    }

    private function playerBrief(?string $playerId): ?array
    {
        if (!$playerId) {
            return null;
        }
        $p = $this->players->find($playerId);
        if (!$p) {
            return null;
        }
        return [
            'id' => $p['id'],
            'name' => trim(($p['prenume'] ?? '') . ' ' . ($p['nume'] ?? '')),
            'poza_path' => $p['poza_path'] ?? null,
            'photo' => upload_url($p['poza_path'] ?? null, 'player'),
        ];
    }

    private function requireMatch(string $matchId): array
    {
        $match = $this->matches->findEnriched($matchId);
        if (!$match) {
            throw new \InvalidArgumentException('Meci negăsit.');
        }
        return $match;
    }

    private function matchFormFromRow(array $match): array
    {
        return [
            'echipa1_id' => $match['echipa1_id'],
            'echipa2_id' => $match['echipa2_id'],
            'scor_echipa1' => $match['scor_echipa1'] ?? 0,
            'scor_echipa2' => $match['scor_echipa2'] ?? 0,
            'status' => $match['status'],
            'data_meci' => $match['data_meci'],
            'omul_meciului_echipa1_id' => $match['omul_meciului_echipa1_id'] ?? null,
            'omul_meciului_echipa2_id' => $match['omul_meciului_echipa2_id'] ?? null,
            'live_link' => $match['live_link'] ?? null,
            'match_tag' => $match['match_tag'] ?? 'nedefinit',
            'locatie' => $match['locatie'] ?? null,
        ];
    }
}
