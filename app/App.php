<?php

namespace App;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Upload;
use App\Models\Bracket;
use App\Models\ExportFile;
use App\Models\HistoryPost;
use App\Models\MatchGoal;
use App\Models\MatchModel;
use App\Models\Player;
use App\Models\SeasonArchive;
use App\Models\Settings;
use App\Models\Team;
use App\Services\BracketService;
use App\Services\CacheService;
use App\Services\LeaderboardService;
use App\Services\LiveScoreService;
use App\Services\MatchPanelService;
use App\Services\PdfExportService;
use App\Services\SeasonArchiveService;
use PDO;

class App
{
    private static ?self $instance = null;
    private PDO $db;

    private function __construct()
    {
        $this->db = Database::getInstance(config('database'));
    }

    public static function get(): self
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function db(): PDO
    {
        return $this->db;
    }

    public function auth(): Auth
    {
        return new Auth($this->db);
    }

    public function upload(): Upload
    {
        return new Upload(config('upload'), BASE_PATH . '/public');
    }

    public function cache(): CacheService
    {
        return new CacheService();
    }

    public function teams(): Team
    {
        return new Team($this->db);
    }

    public function players(): Player
    {
        return new Player($this->db);
    }

    public function matches(): MatchModel
    {
        return new MatchModel($this->db);
    }

    public function bracket(): Bracket
    {
        return new Bracket($this->db);
    }

    public function bracketService(): BracketService
    {
        return new BracketService($this->bracket(), $this->settings());
    }

    public function history(): HistoryPost
    {
        return new HistoryPost($this->db);
    }

    public function settings(): Settings
    {
        return new Settings($this->db);
    }

    public function seasonArchives(): SeasonArchive
    {
        return new SeasonArchive($this->db);
    }

    public function exportFiles(): ExportFile
    {
        return new ExportFile($this->db);
    }

    public function leaderboard(): LeaderboardService
    {
        return new LeaderboardService(
            $this->db,
            $this->teams(),
            $this->matches(),
            $this->players(),
            $this->settings(),
            $this->cache()
        );
    }

    public function liveScore(): LiveScoreService
    {
        return new LiveScoreService();
    }

    public function seasonArchive(): SeasonArchiveService
    {
        return new SeasonArchiveService(
            $this->db,
            $this->settings(),
            $this->leaderboard(),
            $this->seasonArchives(),
            $this->cache()
        );
    }

    public function matchGoals(): MatchGoal
    {
        return new MatchGoal($this->db);
    }

    public function matchPanel(): MatchPanelService
    {
        return new MatchPanelService(
            $this->db,
            $this->matches(),
            $this->matchGoals(),
            $this->players(),
            $this->liveScore()
        );
    }

    public function pdfExport(): PdfExportService
    {
        return new PdfExportService(
            $this->leaderboard(),
            $this->settings(),
            $this->exportFiles(),
            BASE_PATH . '/public/exports'
        );
    }
}
