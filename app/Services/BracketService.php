<?php

namespace App\Services;

use App\Models\Bracket;
use App\Models\Settings;

class BracketService
{
    public const SIZES = [4, 8, 16];

    public function __construct(
        private Bracket $bracket,
        private Settings $settings
    ) {}

    public function getSize(): int
    {
        $size = (int) $this->settings->get('bracket_size', '8');
        return in_array($size, self::SIZES, true) ? $size : 8;
    }

    public function setSize(int $size): void
    {
        if (!in_array($size, self::SIZES, true)) {
            throw new \InvalidArgumentException('Dimensiune bracket invalidă.');
        }
        $this->settings->set('bracket_size', (string) $size);
    }

    public function roundCount(int $size): int
    {
        return (int) log($size, 2);
    }

    public function matchesInRound(int $size, int $roundIndex): int
    {
        return (int) ($size / (2 ** ($roundIndex + 1)));
    }

    /** @return list<string> */
    public function roundLabels(int $size): array
    {
        $count = $this->roundCount($size);
        $all = [
            16 => 'Optimii de finală',
            8 => 'Sferturi de finală',
            4 => 'Semifinale',
            2 => 'Finală',
            1 => 'Campioană',
        ];
        $labels = [];
        $teamsAtRound = $size;
        for ($r = 0; $r < $count; $r++) {
            $matches = $teamsAtRound / 2;
            $labels[] = $all[$matches] ?? ('Runda ' . ($r + 1));
            $teamsAtRound = $matches;
        }
        return $labels;
    }

    public function buildTree(?int $size = null): array
    {
        $size = $size ?? $this->getSize();
        if (!in_array($size, self::SIZES, true)) {
            $size = 8;
        }
        $rounds = $this->roundCount($size);
        $labels = $this->roundLabels($size);
        $rows = $this->bracket->all();

        $indexed = [];
        foreach ($rows as $row) {
            $indexed[$row['round_index']][$row['row_index']] = $row;
        }

        $tree = [];
        for ($r = 0; $r < $rounds; $r++) {
            $matchCount = $this->matchesInRound($size, $r);
            $matches = [];
            for ($m = 0; $m < $matchCount; $m++) {
                $slot = $indexed[$r][$m] ?? null;
                $matches[] = [
                    'round_index' => $r,
                    'match_index' => $m,
                    'team1_id' => $slot['team_id'] ?? null,
                    'team2_id' => $slot['team2_id'] ?? null,
                    'team1_nume' => $slot['team_nume'] ?? null,
                    'team2_nume' => $slot['team2_nume'] ?? null,
                    'team1_logo' => $slot['team_logo'] ?? null,
                    'team2_logo' => $slot['team2_logo'] ?? null,
                    'score1' => $slot['score'] ?? null,
                    'score2' => $slot['score2'] ?? null,
                    'winner_id' => $this->winnerFromSlot($slot),
                ];
            }
            $tree[] = [
                'index' => $r,
                'label' => $labels[$r] ?? ('Runda ' . ($r + 1)),
                'matches' => $matches,
            ];
        }

        return ['size' => $size, 'rounds' => $tree];
    }

    public function saveFromPost(array $post): void
    {
        $size = (int) ($post['bracket_size'] ?? $this->getSize());
        $this->setSize($size);

        $this->bracket->clear();
        $matches = $post['matches'] ?? [];
        foreach ($matches as $roundIndex => $roundMatches) {
            foreach ($roundMatches as $matchIndex => $match) {
                $team1 = ($match['team1_id'] ?? '') ?: null;
                $team2 = ($match['team2_id'] ?? '') ?: null;
                $score1 = ($match['score1'] ?? '') !== '' ? (int) $match['score1'] : null;
                $score2 = ($match['score2'] ?? '') !== '' ? (int) $match['score2'] : null;
                $this->bracket->upsertMatch(
                    (int) $roundIndex,
                    (int) $matchIndex,
                    $team1,
                    $team2,
                    $score1,
                    $score2
                );
            }
        }
    }

    public function ensureStructure(int $size): void
    {
        if (!in_array($size, self::SIZES, true)) {
            throw new \InvalidArgumentException('Dimensiune bracket invalidă.');
        }
        $this->setSize($size);
        $this->bracket->clear();
        $rounds = $this->roundCount($size);
        for ($r = 0; $r < $rounds; $r++) {
            $count = $this->matchesInRound($size, $r);
            for ($m = 0; $m < $count; $m++) {
                $this->bracket->upsertMatch($r, $m, null, null, null, null);
            }
        }
    }

    private function winnerFromSlot(?array $slot): ?string
    {
        if (!$slot || $slot['score'] === null || $slot['score2'] === null) {
            return null;
        }
        $s1 = (int) $slot['score'];
        $s2 = (int) $slot['score2'];
        if ($s1 === $s2) {
            return null;
        }
        return $s1 > $s2 ? ($slot['team_id'] ?: null) : ($slot['team2_id'] ?: null);
    }
}
