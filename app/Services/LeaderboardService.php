<?php

namespace App\Services;

use App\Models\MatchModel;
use App\Models\Player;
use App\Models\Settings;
use App\Models\Team;
use PDO;

class LeaderboardService
{
    public function __construct(
        private PDO $db,
        private Team $teams,
        private MatchModel $matches,
        private Player $players,
        private Settings $settings,
        private ?CacheService $cache = null
    ) {
        $this->cache ??= new CacheService();
    }

    public function compute(?string $grupa = null, bool $useCache = true): array
    {
        $cacheKey = 'leaderboard:' . ($grupa ?: 'all');
        if ($useCache) {
            $cached = $this->cache->get($cacheKey);
            if ($cached !== null) {
                return $cached;
            }
        }

        $teams = $this->teams->all();
        if ($grupa) {
            $teams = array_filter($teams, fn($t) => $t['grupa'] === $grupa);
        }

        $finished = $this->matches->finished();
        $playerCounts = $this->playerCountByTeam();
        $pointsWin = (int) ($this->settings->get('points_win', '3') ?: 3);
        $pointsDraw = (int) ($this->settings->get('points_draw', '1') ?: 1);

        $leaderboard = [];
        foreach ($teams as $team) {
            $stats = [
                'id' => $team['id'],
                'nume' => $team['nume'],
                'logo_url' => $team['logo_path'],
                'grupa' => $team['grupa'],
                'victories' => 0,
                'draws' => 0,
                'losses' => 0,
                'goals_scored' => 0,
                'goals_conceded' => 0,
                'goal_difference' => 0,
                'matches_played' => 0,
                'points' => 0,
                'players' => $playerCounts[$team['id']] ?? 0,
            ];

            foreach ($finished as $match) {
                if ($match['echipa1_id'] !== $team['id'] && $match['echipa2_id'] !== $team['id']) {
                    continue;
                }
                $stats['matches_played']++;
                $isHome = $match['echipa1_id'] === $team['id'];
                $scored = (int) ($isHome ? $match['scor_echipa1'] : $match['scor_echipa2']);
                $conceded = (int) ($isHome ? $match['scor_echipa2'] : $match['scor_echipa1']);

                $stats['goals_scored'] += $scored;
                $stats['goals_conceded'] += $conceded;

                if ($scored > $conceded) {
                    $stats['victories']++;
                    $stats['points'] += $pointsWin;
                } elseif ($scored === $conceded) {
                    $stats['draws']++;
                    $stats['points'] += $pointsDraw;
                } else {
                    $stats['losses']++;
                }
            }

            $stats['goal_difference'] = $stats['goals_scored'] - $stats['goals_conceded'];
            $leaderboard[] = $stats;
        }

        usort($leaderboard, function ($a, $b) {
            return [$b['points'], $b['goal_difference'], $b['goals_scored']]
                <=> [$a['points'], $a['goal_difference'], $a['goals_scored']];
        });

        if ($useCache) {
            $this->cache->set($cacheKey, $leaderboard);
        }

        return $leaderboard;
    }

    public function stats(?string $grupa = null, bool $useCache = true): array
    {
        if ($useCache) {
            $cached = $this->cache->get('stats:general');
            if ($cached !== null) {
                return $cached;
            }
        }

        $numTeams = (int) $this->db->query('SELECT COUNT(*) FROM teams WHERE is_active = 1')->fetchColumn();
        $numPlayers = (int) $this->db->query('SELECT COUNT(*) FROM players')->fetchColumn();
        $finished = $this->matches->finished();
        $totalGoals = 0;
        foreach ($finished as $m) {
            $totalGoals += (int) ($m['scor_echipa1'] ?? 0) + (int) ($m['scor_echipa2'] ?? 0);
        }
        $stats = [
            'numTeams' => $numTeams,
            'numPlayers' => $numPlayers,
            'numMatches' => count($finished),
            'totalGoals' => $totalGoals,
        ];

        if ($useCache) {
            $this->cache->set('stats:general', $stats);
        }

        return $stats;
    }

    public function getMatchesForExport(): array
    {
        return $this->matches->all();
    }

    public function getTeamsForExport(): array
    {
        $teams = $this->teams->all(false);
        $counts = $this->playerCountByTeam();
        foreach ($teams as &$t) {
            $t['player_count'] = $counts[$t['id']] ?? 0;
        }
        return $teams;
    }

    public function playerStats(?string $grupa = null): array
    {
        $players = $this->players->all();
        $teams = $this->teams->all();
        $teamMap = [];
        foreach ($teams as $t) {
            $teamMap[$t['id']] = $t;
        }

        if ($grupa) {
            $players = array_filter($players, function ($p) use ($teamMap, $grupa) {
                $tid = $p['id_echipa'] ?? null;
                return $tid && isset($teamMap[$tid]) && $teamMap[$tid]['grupa'] === $grupa;
            });
        }

        try {
            $goalRows = $this->db->query(
                'SELECT g.player_id, COUNT(*) AS goals FROM match_goals g GROUP BY g.player_id'
            )->fetchAll();
        } catch (\Throwable) {
            $goalRows = [];
        }
        $goalMap = [];
        foreach ($goalRows as $row) {
            if ($row['player_id']) {
                $goalMap[$row['player_id']] = (int) ($row['goals'] ?? $row['cnt'] ?? 0);
            }
        }

        $matches = $this->matches->finished();
        $stats = [];
        foreach ($players as $player) {
            $motm = 0;
            foreach ($matches as $match) {
                if ($match['omul_meciului_echipa1_id'] === $player['id'] ||
                    $match['omul_meciului_echipa2_id'] === $player['id']) {
                    $motm++;
                }
            }
            $team = $teamMap[$player['id_echipa'] ?? ''] ?? null;
            $stats[] = [
                'id' => $player['id'],
                'nume' => $player['nume'],
                'prenume' => $player['prenume'],
                'poza_path' => $player['poza_path'],
                'echipa_nume' => $team['nume'] ?? '—',
                'grupa' => $team['grupa'] ?? '—',
                'goals' => $goalMap[$player['id']] ?? 0,
                'motm' => $motm,
                'numar_tricou' => $player['numar_tricou'],
            ];
        }

        usort($stats, fn($a, $b) => [$b['goals'], $b['motm'], $a['prenume']] <=> [$a['goals'], $a['motm'], $b['prenume']]);
        return $stats;
    }

    public function invalidateCache(): void
    {
        $this->cache->invalidateLeaderboard();
    }

    private function playerCountByTeam(): array
    {
        $rows = $this->db->query(
            'SELECT id_echipa, COUNT(*) AS cnt FROM players WHERE is_active = 1 AND id_echipa IS NOT NULL GROUP BY id_echipa'
        )->fetchAll();
        $map = [];
        foreach ($rows as $row) {
            $map[$row['id_echipa']] = (int) $row['cnt'];
        }
        return $map;
    }
}
