<?php

namespace App\Services;

use App\Models\ExportFile;
use App\Models\Settings;
use Dompdf\Dompdf;
use Dompdf\Options;

class PdfExportService
{
    private const TYPES = [
        'clasament' => 'Clasament',
        'meciuri' => 'Program meciuri',
        'echipe' => 'Lista echipe',
        'sezon_complet' => 'Raport sezon complet',
    ];

    public function __construct(
        private LeaderboardService $leaderboard,
        private Settings $settings,
        private ExportFile $exports,
        private string $exportDir
    ) {}

    public function types(): array
    {
        return self::TYPES;
    }

    public function generate(string $type, ?string $adminId = null): array
    {
        if (!isset(self::TYPES[$type])) {
            throw new \InvalidArgumentException('Tip export invalid.');
        }

        if (!class_exists(Dompdf::class)) {
            throw new \RuntimeException('Dompdf nu este instalat. Rulează: composer install');
        }

        $season = $this->settings->get('season', '');
        $tournament = $this->settings->get('tournament_name', 'Trofeu Hub');
        $html = $this->buildHtml($type, $season, $tournament);

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', $type === 'clasament' ? 'portrait' : 'landscape');
        $dompdf->render();

        $dir = rtrim($this->exportDir, '/\\') . '/' . date('Y/m');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $safeType = preg_replace('/[^a-z_]/', '', $type);
        $fileName = $safeType . '_' . str_replace('/', '-', $season) . '_' . date('Ymd_His') . '.pdf';
        $relativePath = 'exports/' . date('Y/m') . '/' . $fileName;
        $fullPath = rtrim($this->exportDir, '/\\') . '/' . date('Y/m') . '/' . $fileName;

        file_put_contents($fullPath, $dompdf->output());

        $id = $this->exports->create([
            'export_type' => $type,
            'file_name' => $fileName,
            'file_path' => $relativePath,
            'season_label' => $season,
            'meta_json' => json_encode([
                'tournament' => $tournament,
                'generated_at' => date('c'),
                'label' => self::TYPES[$type],
            ], JSON_UNESCAPED_UNICODE),
            'created_by' => $adminId,
        ]);

        return [
            'id' => $id,
            'file_name' => $fileName,
            'file_path' => $relativePath,
            'export_type' => $type,
        ];
    }

    private function buildHtml(string $type, string $season, string $tournament): string
    {
        $generated = date('d.m.Y H:i');
        $body = match ($type) {
            'clasament' => $this->htmlClasament(),
            'meciuri' => $this->htmlMeciuri(),
            'echipe' => $this->htmlEchipe(),
            'sezon_complet' => $this->htmlSezonComplet(),
            default => '',
        };

        return <<<HTML
<!DOCTYPE html><html><head><meta charset="utf-8">
<style>
body{font-family:DejaVu Sans,sans-serif;font-size:11px;color:#111}
h1{font-size:18px;margin:0 0 4px}h2{font-size:13px;color:#444;margin:20px 0 8px}
.meta{color:#666;font-size:10px;margin-bottom:16px}
table{width:100%;border-collapse:collapse;margin-bottom:12px}
th,td{border:1px solid #ccc;padding:5px 6px;text-align:left}
th{background:#eee;font-size:10px;text-transform:uppercase}
td.num{text-align:center}
</style></head><body>
<h1>{$this->esc($tournament)}</h1>
<div class="meta">Sezon {$this->esc($season)} · Generat {$generated}</div>
{$body}
</body></html>
HTML;
    }

    private function htmlClasament(): string
    {
        $rows = $this->leaderboard->compute(null, false);
        $tr = '';
        foreach ($rows as $i => $r) {
            $tr .= '<tr><td class="num">' . ($i + 1) . '</td><td>' . $this->esc($r['nume']) . '</td><td>' . $this->esc($r['grupa']) . '</td>'
                . '<td class="num">' . $r['matches_played'] . '</td><td class="num">' . $r['victories'] . '</td>'
                . '<td class="num">' . $r['draws'] . '</td><td class="num">' . $r['losses'] . '</td>'
                . '<td class="num">' . $r['goals_scored'] . '</td><td class="num">' . $r['goals_conceded'] . '</td>'
                . '<td class="num">' . $r['goal_difference'] . '</td><td class="num"><strong>' . $r['points'] . '</strong></td></tr>';
        }
        return '<h2>Clasament</h2><table><thead><tr><th>#</th><th>Echipă</th><th>Grupa</th><th>MJ</th><th>V</th><th>E</th><th>Î</th><th>G+</th><th>G-</th><th>Gd</th><th>Pts</th></tr></thead><tbody>' . $tr . '</tbody></table>';
    }

    private function htmlMeciuri(): string
    {
        $matches = $this->leaderboard->getMatchesForExport();
        $tr = '';
        foreach ($matches as $m) {
            $score = $m['status'] === 'programat' ? 'vs' : ((int) $m['scor_echipa1'] . ' - ' . (int) $m['scor_echipa2']);
            $tr .= '<tr><td>' . date('d.m.Y H:i', strtotime($m['data_meci'])) . '</td>'
                . '<td>' . $this->esc($m['echipa1_nume']) . '</td><td class="num">' . $score . '</td>'
                . '<td>' . $this->esc($m['echipa2_nume']) . '</td><td>' . $this->esc($m['status']) . '</td>'
                . '<td>' . $this->esc($m['match_tag'] ?? '') . '</td></tr>';
        }
        return '<h2>Program meciuri</h2><table><thead><tr><th>Data</th><th>Echipa 1</th><th>Scor</th><th>Echipa 2</th><th>Status</th><th>Tag</th></tr></thead><tbody>' . $tr . '</tbody></table>';
    }

    private function htmlEchipe(): string
    {
        $teams = $this->leaderboard->getTeamsForExport();
        $tr = '';
        foreach ($teams as $t) {
            $tr .= '<tr><td>' . $this->esc($t['nume']) . '</td><td>' . $this->esc($t['grupa']) . '</td><td class="num">' . ($t['player_count'] ?? 0) . '</td></tr>';
        }
        return '<h2>Echipe înscrise</h2><table><thead><tr><th>Echipă</th><th>Grupa</th><th>Jucători</th></tr></thead><tbody>' . $tr . '</tbody></table>';
    }

    private function htmlSezonComplet(): string
    {
        $stats = $this->leaderboard->stats(null, false);
        $summary = '<h2>Statistici sezon</h2><p>Echipe: ' . $stats['numTeams'] . ' · Meciuri: ' . $stats['numMatches'] . ' · Goluri: ' . $stats['totalGoals'] . '</p>';
        return $summary . $this->htmlClasament() . $this->htmlMeciuri() . $this->htmlEchipe();
    }

    private function esc(string $v): string
    {
        return htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
    }
}
