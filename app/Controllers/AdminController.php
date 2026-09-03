<?php

namespace App\Controllers;

use App\Core\Session;
use App\Core\View;
use App\Services\BracketService;

class AdminController extends BaseController
{
    public function loginForm(): void
    {
        if ($this->app->auth()->check()) {
            redirect('/admin');
        }
        View::render('admin/login', ['title' => 'Autentificare']);
    }

    public function login(): void
    {
        if (!Session::verifyCsrf($_POST['_csrf'] ?? '')) {
            Session::flash('error', 'Token invalid.');
            redirect('/admin/login');
        }

        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($this->app->auth()->attempt($username, $password)) {
            redirect('/admin');
        }

        Session::flash('error', 'Utilizator sau parolă incorectă.');
        redirect('/admin/login');
    }

    public function logout(): void
    {
        $this->app->auth()->logout();
        redirect('/admin/login');
    }

    public function dashboard(): void
    {
        $this->app->auth()->requireAdmin();
        $stats = $this->app->leaderboard()->stats();
        View::render('admin/dashboard', [
            'title' => 'Dashboard',
            'stats' => $stats,
            'settings' => $this->settings(),
            'user' => $this->app->auth()->user(),
        ], 'admin');
    }

    // --- ECHIPE ---
    public function teams(): void
    {
        $this->app->auth()->requireAdmin();
        View::render('admin/teams', [
            'title' => 'Echipe',
            'teams' => $this->app->teams()->all(false),
            'settings' => $this->settings(),
            'user' => $this->app->auth()->user(),
        ], 'admin');
    }

    public function teamStore(): void
    {
        $this->guardPost();
        $data = [
            'nume' => trim($_POST['nume'] ?? ''),
            'grupa' => trim($_POST['grupa'] ?? ''),
            'logo_path' => null,
        ];
        if (!empty($_FILES['logo']['tmp_name'])) {
            $data['logo_path'] = $this->app->upload()->image($_FILES['logo'], 'teams');
        }
        $this->app->teams()->create($data);
        $this->app->leaderboard()->invalidateCache();
        Session::flash('success', 'Echipa a fost adăugată.');
        redirect('/admin/echipe');
    }

    public function teamUpdate(string $id): void
    {
        $this->guardPost();
        $team = $this->app->teams()->find($id);
        if (!$team) redirect('/admin/echipe');

        $data = [
            'nume' => trim($_POST['nume'] ?? ''),
            'grupa' => trim($_POST['grupa'] ?? ''),
            'logo_path' => $team['logo_path'],
        ];
        if (!empty($_FILES['logo']['tmp_name'])) {
            $this->app->upload()->delete($team['logo_path']);
            $data['logo_path'] = $this->app->upload()->image($_FILES['logo'], 'teams');
        }
        $this->app->teams()->update($id, $data);
        $this->app->leaderboard()->invalidateCache();
        Session::flash('success', 'Echipa a fost actualizată.');
        redirect('/admin/echipe');
    }

    public function teamDelete(string $id): void
    {
        $this->guardPost();
        $this->app->teams()->delete($id);
        Session::flash('success', 'Echipa a fost dezactivată.');
        redirect('/admin/echipe');
    }

    // --- JUCĂTORI ---
    public function players(): void
    {
        $this->app->auth()->requireAdmin();
        View::render('admin/players', [
            'title' => 'Jucători',
            'players' => $this->app->players()->all(false),
            'teams' => $this->app->teams()->all(),
            'settings' => $this->settings(),
            'user' => $this->app->auth()->user(),
        ], 'admin');
    }

    public function playerStore(): void
    {
        $this->guardPost();
        $data = $this->playerDataFromPost();
        if (!empty($_FILES['poza']['tmp_name'])) {
            $data['poza_path'] = $this->app->upload()->image($_FILES['poza'], 'players');
        }
        $this->app->players()->create($data);
        Session::flash('success', 'Jucătorul a fost adăugat.');
        redirect('/admin/jucatori');
    }

    public function playerUpdate(string $id): void
    {
        $this->guardPost();
        $player = $this->app->players()->find($id);
        if (!$player) redirect('/admin/jucatori');

        $data = $this->playerDataFromPost();
        $data['poza_path'] = $player['poza_path'];
        if (!empty($_FILES['poza']['tmp_name'])) {
            $this->app->upload()->delete($player['poza_path']);
            $data['poza_path'] = $this->app->upload()->image($_FILES['poza'], 'players');
        }
        $this->app->players()->update($id, $data);
        Session::flash('success', 'Jucătorul a fost actualizat.');
        redirect('/admin/jucatori');
    }

    public function playerDelete(string $id): void
    {
        $this->guardPost();
        $this->app->players()->delete($id);
        Session::flash('success', 'Jucătorul a fost dezactivat.');
        redirect('/admin/jucatori');
    }

    // --- MECIURI ---
    public function matches(): void
    {
        $this->app->auth()->requireAdmin();
        View::render('admin/matches', [
            'title' => 'Meciuri',
            'matches' => $this->app->matches()->all(),
            'teams' => $this->app->teams()->all(),
            'players' => $this->app->players()->all(),
            'settings' => $this->settings(),
            'user' => $this->app->auth()->user(),
        ], 'admin');
    }

    public function matchStore(): void
    {
        $this->guardPost();
        $id = $this->app->matches()->create($this->matchDataFromPost());
        $this->afterMatchChange($id);
        Session::flash('success', 'Meciul a fost adăugat.');
        redirect('/admin/meciuri');
    }

    public function matchUpdate(string $id): void
    {
        $this->guardPost();
        $this->app->matches()->update($id, $this->matchDataFromPost());
        $this->afterMatchChange($id);
        Session::flash('success', 'Meciul a fost actualizat.');
        redirect('/admin/meciuri');
    }

    public function matchDelete(string $id): void
    {
        $this->guardPost();
        $this->app->matches()->delete($id);
        $this->app->leaderboard()->invalidateCache();
        Session::flash('success', 'Meciul a fost șters.');
        redirect('/admin/meciuri');
    }

    // --- BRACKET ---
    public function bracketAdmin(): void
    {
        $this->app->auth()->requireAdmin();
        $svc = $this->app->bracketService();
        $size = (int) ($_GET['size'] ?? $svc->getSize());
        if (!in_array($size, BracketService::SIZES, true)) {
            $size = $svc->getSize();
        }
        if (isset($_GET['init']) && $_GET['init'] === '1') {
            $svc->ensureStructure($size);
            Session::flash('success', 'Structura bracket (' . $size . ' echipe) a fost generată.');
            redirect('/admin/bracket');
        }
        View::render('admin/bracket', [
            'title' => 'Bracket',
            'bracketTree' => $svc->buildTree($size),
            'selectedSize' => $size,
            'teams' => $this->app->teams()->all(),
            'bracketSizes' => BracketService::SIZES,
            'settings' => $this->settings(),
            'user' => $this->app->auth()->user(),
        ], 'admin');
    }

    public function bracketSave(): void
    {
        $this->guardPost();
        try {
            $this->app->bracketService()->saveFromPost($_POST);
            Session::flash('success', 'Bracket salvat.');
        } catch (\Throwable $e) {
            Session::flash('error', $e->getMessage());
        }
        $this->app->leaderboard()->invalidateCache();
        redirect('/admin/bracket');
    }

    // --- ISTORIC ---
    public function history(): void
    {
        $this->app->auth()->requireAdmin();
        View::render('admin/history', [
            'title' => 'Istoric',
            'posts' => $this->app->history()->all(false),
            'settings' => $this->settings(),
            'user' => $this->app->auth()->user(),
        ], 'admin');
    }

    public function historyStore(): void
    {
        $this->guardPost();
        $images = $this->uploadMultipleImages('images', 'history');
        $this->app->history()->create([
            'titlu' => trim($_POST['titlu'] ?? ''),
            'descriere' => trim($_POST['descriere'] ?? ''),
            'is_published' => isset($_POST['is_published']) ? 1 : 0,
        ], $images);
        Session::flash('success', 'Postarea a fost adăugată.');
        redirect('/admin/istoric');
    }

    public function historyDelete(string $id): void
    {
        $this->guardPost();
        $post = $this->app->history()->find($id);
        if ($post) {
            foreach ($post['images'] as $img) {
                $this->app->upload()->delete($img['image_path']);
            }
        }
        $this->app->history()->delete($id);
        Session::flash('success', 'Postarea a fost ștearsă.');
        redirect('/admin/istoric');
    }

    // --- SETĂRI ---
    public function settingsForm(): void
    {
        $this->app->auth()->requireAdmin();
        View::render('admin/settings', [
            'title' => 'Setări',
            'settings' => $this->settings(),
            'user' => $this->app->auth()->user(),
        ], 'admin');
    }

    public function settingsSave(): void
    {
        $this->guardPost();
        $this->app->settings()->setMany([
            'tournament_name' => trim($_POST['tournament_name'] ?? ''),
            'season' => trim($_POST['season'] ?? ''),
            'points_win' => trim($_POST['points_win'] ?? '3'),
            'points_draw' => trim($_POST['points_draw'] ?? '1'),
            'redis_enabled' => isset($_POST['redis_enabled']) ? '1' : '0',
            'ws_port' => trim($_POST['ws_port'] ?? '8080'),
        ]);
        Session::flash('success', 'Setările au fost salvate.');
        redirect('/admin/setari');
    }

    // --- SEZOANE ---
    public function seasons(): void
    {
        $this->app->auth()->requireAdmin();
        View::render('admin/seasons', [
            'title' => 'Sezoane',
            'archives' => $this->app->seasonArchives()->all(false),
            'settings' => $this->settings(),
            'user' => $this->app->auth()->user(),
            'redisAvailable' => \App\Core\RedisClient::available(),
        ], 'admin');
    }

    public function seasonArchive(): void
    {
        $this->guardPost();
        $resetTeams = isset($_POST['reset_teams']);
        $newSeason = trim($_POST['new_season'] ?? '') ?: null;
        $adminId = $this->app->auth()->user()['id'];

        try {
            $archiveId = $this->app->seasonArchive()->archiveCurrent($adminId, $resetTeams, $newSeason);
            Session::flash('success', 'Sezonul curent a fost arhivat. Sezon nou activ.');
        } catch (\Throwable $e) {
            Session::flash('error', 'Eroare arhivare: ' . $e->getMessage());
        }
        redirect('/admin/sezoane');
    }

    public function seasonDelete(string $id): void
    {
        $this->guardPost();
        $this->app->seasonArchives()->delete($id);
        Session::flash('success', 'Arhiva sezonului a fost ștearsă.');
        redirect('/admin/sezoane');
    }

    // --- EXPORT PDF ---
    public function exports(): void
    {
        $this->app->auth()->requireAdmin();
        View::render('admin/exports', [
            'title' => 'Export PDF',
            'exports' => $this->app->exportFiles()->all(),
            'types' => $this->app->pdfExport()->types(),
            'settings' => $this->settings(),
            'user' => $this->app->auth()->user(),
            'dompdfReady' => class_exists(\Dompdf\Dompdf::class),
        ], 'admin');
    }

    public function exportGenerate(): void
    {
        $this->guardPost();
        $type = $_POST['export_type'] ?? '';
        try {
            $result = $this->app->pdfExport()->generate($type, $this->app->auth()->user()['id']);
            Session::flash('success', 'PDF generat: ' . $result['file_name']);
        } catch (\Throwable $e) {
            Session::flash('error', $e->getMessage());
        }
        redirect('/admin/exporturi');
    }

    public function exportDelete(string $id): void
    {
        $this->guardPost();
        $row = $this->app->exportFiles()->delete($id);
        if ($row && !empty($row['file_path'])) {
            $full = BASE_PATH . '/public/' . ltrim($row['file_path'], '/');
            if (is_file($full)) {
                unlink($full);
            }
        }
        Session::flash('success', 'Export șters.');
        redirect('/admin/exporturi');
    }

    private function afterMatchChange(string $matchId): void
    {
        $this->app->leaderboard()->invalidateCache();
        $match = $this->app->matches()->findEnriched($matchId);
        if ($match) {
            $this->app->liveScore()->publishFromMatchRow($match);
        }
    }

    public function matchPanel(string $id): void
    {
        $this->app->auth()->requireAdmin();
        $data = $this->app->matchPanel()->getPanelData($id);
        if (!$data) {
            Session::flash('error', 'Meci negăsit.');
            redirect('/admin/meciuri');
        }
        View::render('admin/match_panel', [
            'title' => 'Panou meci',
            'panel' => $data,
            'settings' => $this->settings(),
            'user' => $this->app->auth()->user(),
        ], 'admin');
    }

    public function matchPanelApi(string $id): void
    {
        $this->app->auth()->requireAdmin();
        if (!Session::verifyCsrf($_POST['_csrf'] ?? '')) {
            json_response(['success' => false, 'error' => 'Token invalid'], 403);
        }
        $action = $_POST['action'] ?? '';
        try {
            $panel = $this->app->matchPanel();
            $result = match ($action) {
                'start' => $panel->startMatch($id),
                'goal' => $panel->addGoal(
                    $id,
                    $_POST['team_id'] ?? '',
                    $_POST['player_id'] ?? null ?: null,
                    $_POST['minute'] !== '' ? (int) $_POST['minute'] : null
                ),
                'remove_goal' => $panel->removeGoal($_POST['goal_id'] ?? ''),
                'motm' => $panel->setMotm($id, (int) ($_POST['side'] ?? 1), $_POST['player_id'] ?? null ?: null),
                'finish' => $panel->finishMatch($id),
                default => throw new \InvalidArgumentException('Acțiune necunoscută'),
            };
            $this->app->leaderboard()->invalidateCache();
            json_response(['success' => true, 'data' => $result]);
        } catch (\Throwable $e) {
            json_response(['success' => false, 'error' => $e->getMessage()], 400);
        }
    }

    private function guardPost(): void
    {
        $this->app->auth()->requireAdmin();
        if (!Session::verifyCsrf($_POST['_csrf'] ?? '')) {
            Session::flash('error', 'Token invalid.');
            redirect('/admin');
        }
    }

    private function playerDataFromPost(): array
    {
        return [
            'nume' => trim($_POST['nume'] ?? ''),
            'prenume' => trim($_POST['prenume'] ?? ''),
            'id_echipa' => $_POST['id_echipa'] ?? null,
            'numar_tricou' => $_POST['numar_tricou'] !== '' ? (int) $_POST['numar_tricou'] : null,
            'pozitie' => trim($_POST['pozitie'] ?? '') ?: null,
            'poza_path' => null,
        ];
    }

    private function matchDataFromPost(): array
    {
        $motm1 = $_POST['omul_meciului_echipa1_id'] ?? '';
        $motm2 = $_POST['omul_meciului_echipa2_id'] ?? '';
        return [
            'echipa1_id' => $_POST['echipa1_id'] ?? '',
            'echipa2_id' => $_POST['echipa2_id'] ?? '',
            'scor_echipa1' => $_POST['scor_echipa1'] !== '' ? (int) $_POST['scor_echipa1'] : null,
            'scor_echipa2' => $_POST['scor_echipa2'] !== '' ? (int) $_POST['scor_echipa2'] : null,
            'status' => $_POST['status'] ?? 'programat',
            'data_meci' => $_POST['data_meci'] ?? date('Y-m-d H:i:s'),
            'omul_meciului_echipa1_id' => $motm1 && $motm1 !== 'none' ? $motm1 : null,
            'omul_meciului_echipa2_id' => $motm2 && $motm2 !== 'none' ? $motm2 : null,
            'live_link' => trim($_POST['live_link'] ?? '') ?: null,
            'match_tag' => $_POST['match_tag'] ?? 'nedefinit',
            'locatie' => trim($_POST['locatie'] ?? '') ?: null,
        ];
    }

    private function uploadMultipleImages(string $field, string $subdir): array
    {
        $paths = [];
        if (empty($_FILES[$field]['tmp_name'])) {
            return $paths;
        }
        $files = $_FILES[$field];
        $count = is_array($files['tmp_name']) ? count($files['tmp_name']) : 1;

        for ($i = 0; $i < $count; $i++) {
            $file = [
                'name' => is_array($files['name']) ? $files['name'][$i] : $files['name'],
                'type' => is_array($files['type']) ? $files['type'][$i] : $files['type'],
                'tmp_name' => is_array($files['tmp_name']) ? $files['tmp_name'][$i] : $files['tmp_name'],
                'error' => is_array($files['error']) ? $files['error'][$i] : $files['error'],
                'size' => is_array($files['size']) ? $files['size'][$i] : $files['size'],
            ];
            if ($file['error'] === UPLOAD_ERR_OK && $file['tmp_name']) {
                $paths[] = $this->app->upload()->image($file, $subdir);
            }
        }
        return $paths;
    }
}
