<?php

namespace App\Controllers;

use App\Core\Session;
use App\Core\View;

class ApiController extends BaseController
{
    public function command(): void
    {
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $command = $input['command'] ?? '';

        switch ($command) {
            case 'getInfo':
                json_response(['message' => 'Server is running!', 'platform' => 'Trofeu Hub PHP']);
                break;

            case 'get_teams_leaderboard':
                $grupa = $input['grupa'] ?? null;
                json_response(['success' => true, 'data' => $this->app->leaderboard()->compute($grupa ?: null)]);
                break;

            case 'get_stats':
                json_response(['success' => true, 'data' => $this->app->leaderboard()->stats()]);
                break;

            case 'get_matches':
                $matches = $this->formatMatches($this->app->matches()->all());
                json_response(['success' => true, 'data' => $matches]);
                break;

            case 'get_team':
                $teamId = $input['team_id'] ?? '';
                $team = $this->app->teams()->find($teamId);
                if (!$team) {
                    json_response(['success' => false, 'error' => 'Echipa nu a fost găsită.'], 404);
                }
                $players = $this->app->players()->byTeam($teamId);
                $matches = $this->app->matches()->byTeam($teamId);
                $playerStats = array_map(function ($player) use ($matches) {
                    $motm = 0;
                    foreach ($matches as $m) {
                        if ($m['omul_meciului_echipa1_id'] === $player['id'] ||
                            $m['omul_meciului_echipa2_id'] === $player['id']) {
                            $motm++;
                        }
                    }
                    return [
                        'id' => $player['id'],
                        'nume' => $player['nume'],
                        'prenume' => $player['prenume'],
                        'photo_url' => $player['poza_path'],
                        'man_of_the_match' => $motm,
                    ];
                }, $players);

                json_response(['success' => true, 'data' => [
                    'id' => $team['id'],
                    'nume' => $team['nume'],
                    'grupa' => $team['grupa'],
                    'logo_url' => $team['logo_path'],
                    'players' => $playerStats,
                ]]);
                break;

            case 'get_teams':
                $teams = $this->app->teams()->all();
                json_response(['success' => true, 'data' => $teams]);
                break;

            default:
                json_response(['error' => 'Unknown command'], 404);
        }
    }

    public function live(): void
    {
        $matches = $this->app->matches()->liveMatches();
        json_response([
            'success' => true,
            'data' => array_map(fn($m) => [
                'id' => $m['id'],
                'scor_echipa1' => $m['scor_echipa1'],
                'scor_echipa2' => $m['scor_echipa2'],
                'status' => $m['status'],
                'echipa1_nume' => $m['echipa1_nume'],
                'echipa2_nume' => $m['echipa2_nume'],
            ], $matches),
            'ws_url' => ws_url(),
        ]);
    }

    public function matchLive(string $id): void
    {
        $data = $this->app->matchPanel()->getLivePayload($id);
        if (!$data) {
            json_response(['success' => false, 'error' => 'Meci negăsit'], 404);
        }
        json_response(['success' => true, 'data' => $data, 'ws_url' => ws_url()]);
    }

    private function formatMatches(array $matches): array
    {
        usort($matches, function ($a, $b) {
            $order = ['se_joaca' => 0, 'programat' => 1, 'terminat' => 2];
            $sa = $order[$a['status']] ?? 3;
            $sb = $order[$b['status']] ?? 3;
            if ($sa !== $sb) return $sa <=> $sb;
            return strtotime($a['data_meci']) <=> strtotime($b['data_meci']);
        });

        return array_map(fn($m) => [
            'id' => $m['id'],
            'team1' => [
                'id' => $m['echipa1_id'],
                'name' => $m['echipa1_nume'],
                'logo_url' => $m['echipa1_logo'],
                'group' => $m['echipa1_grupa'],
            ],
            'team2' => [
                'id' => $m['echipa2_id'],
                'name' => $m['echipa2_nume'],
                'logo_url' => $m['echipa2_logo'],
                'group' => $m['echipa2_grupa'],
            ],
            'score1' => $m['scor_echipa1'],
            'score2' => $m['scor_echipa2'],
            'status' => $m['status'],
            'date' => $m['data_meci'],
            'manOfTheMatch1' => $m['omul_meciului_echipa1_id'],
            'manOfTheMatch2' => $m['omul_meciului_echipa2_id'],
            'live_link' => $m['live_link'],
            'match_tag' => $m['match_tag'],
        ], $matches);
    }
}
