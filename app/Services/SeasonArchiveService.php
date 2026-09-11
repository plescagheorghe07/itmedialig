<?php

namespace App\Services;

use App\Models\SeasonArchive;
use App\Models\Settings;
use App\Core\DbHelper;
use PDO;

class SeasonArchiveService
{
    public function __construct(
        private PDO $db,
        private Settings $settings,
        private LeaderboardService $leaderboard,
        private SeasonArchive $archives,
        private CacheService $cache
    ) {}

    public function archiveCurrent(
        string $adminId,
        bool $resetTeams = false,
        bool $resetPlayers = false,
        bool $resetMatches = true,
        bool $resetBracket = true,
        ?string $newSeasonLabel = null
    ): string {
        $season = $this->settings->get('season', date('Y') . '-' . (date('Y') + 1));
        $tournamentName = $this->settings->get('tournament_name', 'Trofeu Hub');

        // Snapshot BEFORE any deletes
        $snapshot = [
            'season' => $season,
            'tournament_name' => $tournamentName,
            'teams' => $this->db->query('SELECT * FROM teams')->fetchAll(),
            'players' => $this->db->query('SELECT * FROM players')->fetchAll(),
            'matches' => $this->fetchMatchesFull(),
            'match_goals' => $this->fetchGoalsFull(),
            'bracket' => $this->fetchBracketFull(),
            'history' => $this->fetchHistoryFull(),
            'leaderboard' => $this->leaderboard->compute(null, false),
            'player_stats' => $this->leaderboard->playerStats(null),
            'settings' => $this->settings->all(),
        ];

        $stats = $this->leaderboard->stats(null, false);
        $stats['numGoalsEvents'] = count($snapshot['match_goals']);
        $stats['numHistoryPosts'] = count($snapshot['history']);

        $archiveId = $this->archives->create([
            'season_label' => $season,
            'tournament_name' => $tournamentName,
            'snapshot_json' => json_encode($snapshot, JSON_UNESCAPED_UNICODE),
            'stats_json' => json_encode($stats, JSON_UNESCAPED_UNICODE),
            'archived_by' => $adminId,
            'is_published' => 1,
        ]);

        $this->clearCurrentSeasonData($resetTeams, $resetPlayers, $resetMatches, $resetBracket);

        $nextSeason = $newSeasonLabel ?: $this->nextSeasonLabel($season);
        $this->settings->set('season', $nextSeason);
        $this->cache->invalidateLeaderboard();

        return $archiveId;
    }

    private function clearCurrentSeasonData(
        bool $resetTeams,
        bool $resetPlayers,
        bool $resetMatches,
        bool $resetBracket
    ): void {
        $mt = DbHelper::isMysql($this->db) ? '`matches`' : 'matches';

        $this->db->exec('DELETE FROM history_images');
        $this->db->exec('DELETE FROM history_posts');

        if ($resetMatches) {
            try {
                $this->db->exec('DELETE FROM match_goals');
            } catch (\Throwable) {
                // table may not exist on very old installs
            }
            $this->db->exec("DELETE FROM {$mt}");
        }
        if ($resetBracket) {
            $this->db->exec('DELETE FROM bracket');
        }
        if ($resetPlayers || $resetTeams) {
            $this->db->exec('DELETE FROM players');
        }
        if ($resetTeams) {
            $this->db->exec('DELETE FROM teams');
        }
    }

    private function nextSeasonLabel(string $current): string
    {
        if (preg_match('/^(\d{4})-(\d{4})$/', $current, $m)) {
            return ((int) $m[1] + 1) . '-' . ((int) $m[2] + 1);
        }
        $y = (int) date('Y');
        return $y . '-' . ($y + 1);
    }

    private function fetchMatchesFull(): array
    {
        $mt = DbHelper::isMysql($this->db) ? '`matches`' : 'matches';
        return $this->db->query(
            "SELECT m.*, t1.nume AS echipa1_nume, t1.logo_path AS echipa1_logo,
                    t2.nume AS echipa2_nume, t2.logo_path AS echipa2_logo
             FROM {$mt} m
             JOIN teams t1 ON t1.id = m.echipa1_id
             JOIN teams t2 ON t2.id = m.echipa2_id
             ORDER BY m.data_meci ASC"
        )->fetchAll();
    }

    private function fetchGoalsFull(): array
    {
        try {
            $mt = DbHelper::isMysql($this->db) ? '`matches`' : 'matches';
            return $this->db->query(
                "SELECT g.*, p.nume AS player_nume, p.prenume AS player_prenume, p.poza_path AS player_poza,
                        t.nume AS team_nume, m.data_meci
                 FROM match_goals g
                 LEFT JOIN players p ON p.id = g.player_id
                 JOIN teams t ON t.id = g.team_id
                 JOIN {$mt} m ON m.id = g.match_id
                 ORDER BY m.data_meci, g.minute, g.created_at"
            )->fetchAll();
        } catch (\Throwable) {
            return [];
        }
    }

    private function fetchBracketFull(): array
    {
        return $this->db->query(
            'SELECT b.*,
                    t1.nume AS team_nume, t1.logo_path AS team_logo,
                    t2.nume AS team2_nume, t2.logo_path AS team2_logo
             FROM bracket b
             LEFT JOIN teams t1 ON t1.id = b.team_id
             LEFT JOIN teams t2 ON t2.id = b.team2_id
             ORDER BY b.round_index, b.row_index'
        )->fetchAll();
    }

    private function fetchHistoryFull(): array
    {
        $posts = $this->db->query('SELECT * FROM history_posts ORDER BY created_at DESC')->fetchAll();
        foreach ($posts as &$post) {
            $stmt = $this->db->prepare('SELECT * FROM history_images WHERE post_id = ? ORDER BY sort_order');
            $stmt->execute([$post['id']]);
            $post['images'] = $stmt->fetchAll();
        }
        return $posts;
    }
}
