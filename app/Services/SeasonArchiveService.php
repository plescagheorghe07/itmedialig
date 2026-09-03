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

    public function archiveCurrent(string $adminId, bool $resetTeams = false, ?string $newSeasonLabel = null): string
    {
        $season = $this->settings->get('season', date('Y') . '-' . (date('Y') + 1));
        $tournamentName = $this->settings->get('tournament_name', 'Trofeu Hub');

        $snapshot = [
            'season' => $season,
            'tournament_name' => $tournamentName,
            'teams' => $this->db->query('SELECT * FROM teams')->fetchAll(),
            'players' => $this->db->query('SELECT * FROM players')->fetchAll(),
            'matches' => $this->fetchMatchesFull(),
            'bracket' => $this->db->query(
                'SELECT b.*, t.nume AS team_nume FROM bracket b LEFT JOIN teams t ON t.id = b.team_id'
            )->fetchAll(),
            'history' => $this->fetchHistoryFull(),
            'leaderboard' => $this->leaderboard->compute(null, false),
            'settings' => $this->settings->all(),
        ];

        $stats = $this->leaderboard->stats(null, false);

        $archiveId = $this->archives->create([
            'season_label' => $season,
            'tournament_name' => $tournamentName,
            'snapshot_json' => json_encode($snapshot, JSON_UNESCAPED_UNICODE),
            'stats_json' => json_encode($stats, JSON_UNESCAPED_UNICODE),
            'archived_by' => $adminId,
            'is_published' => 1,
        ]);

        $this->clearCurrentSeasonData($resetTeams);

        $nextSeason = $newSeasonLabel ?: $this->nextSeasonLabel($season);
        $this->settings->set('season', $nextSeason);
        $this->cache->invalidateLeaderboard();

        return $archiveId;
    }

    private function clearCurrentSeasonData(bool $resetTeams): void
    {
        $mt = DbHelper::isMysql($this->db) ? '`matches`' : 'matches';
        $this->db->exec('DELETE FROM history_images');
        $this->db->exec('DELETE FROM history_posts');
        $this->db->exec("DELETE FROM {$mt}");
        $this->db->exec('DELETE FROM bracket');

        if ($resetTeams) {
            $this->db->exec('DELETE FROM players');
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
            "SELECT m.*, t1.nume AS echipa1_nume, t2.nume AS echipa2_nume
             FROM {$mt} m
             JOIN teams t1 ON t1.id = m.echipa1_id
             JOIN teams t2 ON t2.id = m.echipa2_id"
        )->fetchAll();
    }

    private function fetchHistoryFull(): array
    {
        $posts = $this->db->query('SELECT * FROM history_posts')->fetchAll();
        foreach ($posts as &$post) {
            $stmt = $this->db->prepare('SELECT * FROM history_images WHERE post_id = ? ORDER BY sort_order');
            $stmt->execute([$post['id']]);
            $post['images'] = $stmt->fetchAll();
        }
        return $posts;
    }
}
