<?php

namespace App\Controllers;

use App\Core\Session;
use App\Core\View;
use App\Services\RateLimiter;

class TeamPortalController extends BaseController
{
    private function limiter(): RateLimiter
    {
        return new RateLimiter(BASE_PATH . '/storage/rate_limit');
    }

    private function clientIp(): string
    {
        return $_SERVER['HTTP_CF_CONNECTING_IP']
            ?? $_SERVER['HTTP_X_FORWARDED_FOR']
            ?? $_SERVER['REMOTE_ADDR']
            ?? '0.0.0.0';
    }

    private function resolveTeam(string $token): ?array
    {
        $token = preg_replace('/[^a-f0-9]/i', '', $token) ?? '';
        if (strlen($token) !== 64) {
            return null;
        }

        $ip = $this->clientIp();
        // Max 30 încercări / oră / IP pe lookup-uri eșuate (anti brute-force)
        $limiter = $this->limiter();
        $key = 'team_portal:' . $ip;

        $team = $this->app->teams()->findByManageToken($token);
        if (!$team) {
            if (!$limiter->attempt($key, 30, 3600)) {
                http_response_code(429);
                View::render('public/404', [
                    'title' => 'Prea multe încercări',
                    'message' => 'Ai încercat prea des. Reîncearcă peste o oră.',
                    'settings' => $this->settings(),
                ], 'public');
                exit;
            }
            return null;
        }

        // Succes: nu penalizăm (clear partial optional)
        return $team;
    }

    public function show(string $token): void
    {
        $team = $this->resolveTeam($token);
        if (!$team) {
            http_response_code(404);
            View::render('public/404', [
                'title' => 'Link invalid',
                'settings' => $this->settings(),
            ], 'public');
            return;
        }

        View::render('team_portal/dashboard', [
            'title' => 'Administrare ' . $team['nume'],
            'team' => $team,
            'players' => $this->app->players()->byTeam($team['id']),
            'token' => $token,
            'settings' => $this->settings(),
        ], 'team_portal');
    }

    public function updateTeam(string $token): void
    {
        $team = $this->requireTokenTeam($token);
        if (!Session::verifyCsrf($_POST['_csrf'] ?? '')) {
            Session::flash('error', 'Token invalid. Reîncearcă.');
            redirect('/gestiune/' . $token);
        }

        $nume = trim($_POST['nume'] ?? '');
        if ($nume === '') {
            Session::flash('error', 'Numele echipei este obligatoriu.');
            redirect('/gestiune/' . $token);
        }

        $logo = $team['logo_path'];
        if (!empty($_FILES['logo']['tmp_name'])) {
            $uploaded = $this->app->upload()->image($_FILES['logo'], 'teams');
            if ($uploaded) {
                if ($logo) {
                    $this->app->upload()->delete($logo);
                }
                $logo = $uploaded;
            }
        }

        $this->app->teams()->updateSelfService($team['id'], [
            'nume' => $nume,
            'logo_path' => $logo,
        ]);
        $this->app->leaderboard()->invalidateCache();
        Session::flash('success', 'Datele echipei au fost actualizate.');
        redirect('/gestiune/' . $token);
    }

    public function storePlayer(string $token): void
    {
        $team = $this->requireTokenTeam($token);
        if (!Session::verifyCsrf($_POST['_csrf'] ?? '')) {
            Session::flash('error', 'Token invalid.');
            redirect('/gestiune/' . $token);
        }

        $data = $this->playerFromPost($team['id']);
        if ($data['nume'] === '' || $data['prenume'] === '') {
            Session::flash('error', 'Numele și prenumele sunt obligatorii.');
            redirect('/gestiune/' . $token);
        }

        if (!empty($_FILES['poza']['tmp_name'])) {
            $data['poza_path'] = $this->app->upload()->image($_FILES['poza'], 'players');
        }

        $this->app->players()->create($data);
        $this->app->leaderboard()->invalidateCache();
        Session::flash('success', 'Jucător adăugat.');
        redirect('/gestiune/' . $token);
    }

    public function updatePlayer(string $token, string $playerId): void
    {
        $team = $this->requireTokenTeam($token);
        if (!Session::verifyCsrf($_POST['_csrf'] ?? '')) {
            Session::flash('error', 'Token invalid.');
            redirect('/gestiune/' . $token);
        }

        $player = $this->app->players()->find($playerId);
        if (!$player || $player['id_echipa'] !== $team['id']) {
            Session::flash('error', 'Jucător invalid.');
            redirect('/gestiune/' . $token);
        }

        $data = $this->playerFromPost($team['id']);
        $data['poza_path'] = $player['poza_path'];
        if (!empty($_FILES['poza']['tmp_name'])) {
            $uploaded = $this->app->upload()->image($_FILES['poza'], 'players');
            if ($uploaded) {
                if ($player['poza_path']) {
                    $this->app->upload()->delete($player['poza_path']);
                }
                $data['poza_path'] = $uploaded;
            }
        }

        $this->app->players()->update($playerId, $data);
        $this->app->leaderboard()->invalidateCache();
        Session::flash('success', 'Jucător actualizat.');
        redirect('/gestiune/' . $token);
    }

    public function deletePlayer(string $token, string $playerId): void
    {
        $team = $this->requireTokenTeam($token);
        if (!Session::verifyCsrf($_POST['_csrf'] ?? '')) {
            Session::flash('error', 'Token invalid.');
            redirect('/gestiune/' . $token);
        }

        $player = $this->app->players()->find($playerId);
        if (!$player || $player['id_echipa'] !== $team['id']) {
            Session::flash('error', 'Jucător invalid.');
            redirect('/gestiune/' . $token);
        }

        $this->app->players()->delete($playerId);
        $this->app->leaderboard()->invalidateCache();
        Session::flash('success', 'Jucător dezactivat.');
        redirect('/gestiune/' . $token);
    }

    private function requireTokenTeam(string $token): array
    {
        $team = $this->resolveTeam($token);
        if (!$team) {
            http_response_code(404);
            View::render('public/404', ['title' => 'Link invalid', 'settings' => $this->settings()], 'public');
            exit;
        }
        return $team;
    }

    private function playerFromPost(string $teamId): array
    {
        return [
            'nume' => trim($_POST['nume'] ?? ''),
            'prenume' => trim($_POST['prenume'] ?? ''),
            'id_echipa' => $teamId,
            'numar_tricou' => ($_POST['numar_tricou'] ?? '') !== '' ? (int) $_POST['numar_tricou'] : null,
            'pozitie' => trim($_POST['pozitie'] ?? '') ?: null,
            'poza_path' => null,
        ];
    }
}
