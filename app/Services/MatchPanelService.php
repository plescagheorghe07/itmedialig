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
        $events = $this->goals->byMatch($matchId);

        return [
            'match' => $match,
            'players1' => $players1,
            'players2' => $players2,
            'goals' => $events,
            'events' => $events,
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

    public function addGoal(string $matchId, string $teamId, ?string $playerId, ?int $minute, string $eventType = MatchGoal::TYPE_GOAL): array
    {
        return $this->addEvent($matchId, $teamId, $playerId, $minute, $eventType);
    }

    public function addEvent(string $matchId, string $teamId, ?string $playerId, ?int $minute, string $eventType = MatchGoal::TYPE_GOAL): array
    {
        $match = $this->requireMatch($matchId);
        if ($match['status'] !== 'se_joaca') {
            throw new \InvalidArgumentException('Evenimentele se pot adăuga doar când meciul este în desfășurare.');
        }
        if ($teamId !== $match['echipa1_id'] && $teamId !== $match['echipa2_id']) {
            throw new \InvalidArgumentException('Echipă invalidă pentru acest meci.');
        }
        if (!in_array($eventType, MatchGoal::TYPES, true)) {
            throw new \InvalidArgumentException('Tip eveniment invalid.');
        }

        $eventId = $this->goals->add($matchId, $teamId, $playerId ?: null, $minute, $eventType);
        if ($eventType === MatchGoal::TYPE_GOAL) {
            $this->syncScores($matchId);
        }

        $events = $this->goals->byMatch($matchId);
        $last = null;
        foreach ($events as $e) {
            if ($e['id'] === $eventId) {
                $last = $e;
                break;
            }
        }

        $broadcastType = $eventType === MatchGoal::TYPE_GOAL ? 'goal_added' : 'event_added';
        $payload = $this->broadcast($matchId, $broadcastType, ['event' => $last, 'goal' => $last]);
        $payload['event_id'] = $eventId;
        $payload['goal_id'] = $eventId;
        return $payload;
    }

    public function removeGoal(string $goalId): array
    {
        $goal = $this->goals->find($goalId);
        if (!$goal) {
            throw new \InvalidArgumentException('Eveniment negăsit.');
        }
        $matchId = $goal['match_id'];
        $wasGoal = ($goal['event_type'] ?? MatchGoal::TYPE_GOAL) === MatchGoal::TYPE_GOAL;
        $this->goals->delete($goalId);
        if ($wasGoal) {
            $this->syncScores($matchId);
        }
        return $this->broadcast($matchId, 'goal_removed', ['goal_id' => $goalId, 'event_id' => $goalId]);
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
            'goals' => array_map([$this, 'formatEvent'], $data['events']),
            'events' => array_map([$this, 'formatEvent'], $data['events']),
        ];
    }

    private function formatEvent(array $g): array
    {
        return [
            'id' => $g['id'],
            'team_id' => $g['team_id'],
            'player_id' => $g['player_id'],
            'player_name' => trim(($g['prenume'] ?? '') . ' ' . ($g['nume'] ?? '')),
            'team_nume' => $g['team_nume'],
            'minute' => $g['minute'],
            'event_type' => $g['event_type'] ?? MatchGoal::TYPE_GOAL,
            'prenume' => $g['prenume'] ?? '',
            'nume' => $g['nume'] ?? '',
        ];
    }

    private function syncScores(string $matchId): void
    {
        $match = $this->matches->find($matchId);
        if (!$match) {
            return;
        }
        $counts = $this->goals->countGoalsByTeam($matchId);
        $this->matches->update($matchId, array_merge($this->matchFormFromRow($match), [
            'scor_echipa1' => $counts[$match['echipa1_id']] ?? 0,
            'scor_echipa2' => $counts[$match['echipa2_id']] ?? 0,
        ]));
    }

    private function broadcast(string $matchId, string $eventType, array $extra = []): array
    {
        $match = $this->matches->findEnriched($matchId);
        $events = $this->goals->byMatch($matchId);
        $formatted = array_map([$this, 'formatEvent'], $events);
        $payload = [
            'type' => $eventType,
            'match' => $this->formatMatchForLive($match),
            'goals' => $formatted,
            'events' => $formatted,
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
            'goals' => $formatted,
            'events' => $formatted,
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
            'exclude_from_standings' => !empty($match['exclude_from_standings']) ? 1 : 0,
        ];
    }
}
