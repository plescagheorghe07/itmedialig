<?php

namespace App\Controllers;

use App\Core\View;

class PublicController extends BaseController
{
    public function home(): void
    {
        $stats = $this->app->leaderboard()->stats();
        $leaderboard = array_slice($this->app->leaderboard()->compute(), 0, 5);
        $liveMatches = array_values(array_filter($this->app->matches()->all(), fn($m) => $m['status'] === 'se_joaca'));
        $upcoming = array_slice(
            array_filter($this->app->matches()->all(), fn($m) => $m['status'] === 'programat'),
            0, 5
        );

        View::render('public/home', [
            'title' => 'Acasă',
            'stats' => $stats,
            'leaderboard' => $leaderboard,
            'liveMatches' => $liveMatches,
            'upcoming' => $upcoming,
            'settings' => $this->settings(),
        ], 'public');
    }

    public function clasament(?string $grupa = null): void
    {
        $groups = $this->app->teams()->groups();
        $tab = $_GET['tab'] ?? 'echipe';
        if (!in_array($tab, ['echipe', 'jucatori'], true)) {
            $tab = 'echipe';
        }
        $selectedGrupa = $_GET['grupa'] ?? $grupa ?? '';

        View::render('public/clasament', [
            'title' => 'Clasament',
            'leaderboard' => $this->app->leaderboard()->compute($selectedGrupa ?: null),
            'playerStats' => $this->app->leaderboard()->playerStats($selectedGrupa ?: null),
            'groups' => $groups,
            'activeGrupa' => $selectedGrupa,
            'activeTab' => $tab,
            'settings' => $this->settings(),
        ], 'public');
    }

    public function meciuri(): void
    {
        $matches = $this->app->matches()->all();
        usort($matches, function ($a, $b) {
            $order = ['se_joaca' => 0, 'programat' => 1, 'terminat' => 2];
            $sa = $order[$a['status']] ?? 3;
            $sb = $order[$b['status']] ?? 3;
            if ($sa !== $sb) return $sa <=> $sb;
            return strtotime($a['data_meci']) <=> strtotime($b['data_meci']);
        });

        View::render('public/meciuri', [
            'title' => 'Meciuri',
            'matches' => $matches,
            'liveMatches' => array_values(array_filter($matches, fn($m) => $m['status'] === 'se_joaca')),
            'upcomingMatches' => array_values(array_filter($matches, fn($m) => $m['status'] === 'programat')),
            'finishedMatches' => array_values(array_filter($matches, fn($m) => $m['status'] === 'terminat')),
            'settings' => $this->settings(),
        ], 'public');
    }

    public function echipa(string $id): void
    {
        $team = $this->app->teams()->find($id);
        if (!$team) {
            http_response_code(404);
            View::render('public/404', ['title' => 'Echipă negăsită'], 'public');
            return;
        }

        $players = $this->app->players()->byTeam($id);
        $matches = $this->app->matches()->byTeam($id);

        $playerStats = [];
        foreach ($players as $player) {
            $motm = 0;
            foreach ($matches as $match) {
                if ($match['omul_meciului_echipa1_id'] === $player['id'] ||
                    $match['omul_meciului_echipa2_id'] === $player['id']) {
                    $motm++;
                }
            }
            $playerStats[] = array_merge($player, ['man_of_the_match' => $motm]);
        }

        View::render('public/echipa', [
            'title' => $team['nume'],
            'team' => $team,
            'players' => $playerStats,
            'settings' => $this->settings(),
        ], 'public');
    }

    public function echipe(): void
    {
        $teams = $this->app->teams()->all();
        View::render('public/echipe', [
            'title' => 'Echipe',
            'teams' => $teams,
            'settings' => $this->settings(),
        ], 'public');
    }

    public function bracket(): void
    {
        View::render('public/bracket', [
            'title' => 'Bracket',
            'bracketTree' => $this->app->bracketService()->buildTree(),
            'settings' => $this->settings(),
        ], 'public');
    }

    public function meci(string $id): void
    {
        $data = $this->app->matchPanel()->getLivePayload($id);
        if (!$data) {
            http_response_code(404);
            View::render('public/404', ['title' => 'Meci negăsit'], 'public');
            return;
        }
        View::render('public/meci', [
            'title' => $data['match']['echipa1_nume'] . ' vs ' . $data['match']['echipa2_nume'],
            'live' => $data,
            'matchId' => $id,
            'settings' => $this->settings(),
        ], 'public');
    }

    public function istoric(): void
    {
        View::render('public/istoric', [
            'title' => 'Istoric',
            'posts' => $this->app->history()->all(),
            'settings' => $this->settings(),
        ], 'public');
    }

    public function sezoane(): void
    {
        View::render('public/sezoane', [
            'title' => 'Sezoane anterioare',
            'archives' => $this->app->seasonArchives()->all(),
            'settings' => $this->settings(),
        ], 'public');
    }

    public function sezon(string $id): void
    {
        $archive = $this->app->seasonArchives()->find($id);
        if (!$archive || !$archive['is_published']) {
            http_response_code(404);
            View::render('public/404', ['title' => 'Sezon negăsit'], 'public');
            return;
        }

        View::render('public/sezon', [
            'title' => 'Sezon ' . $archive['season_label'],
            'archive' => $archive,
            'settings' => $this->settings(),
        ], 'public');
    }
}
