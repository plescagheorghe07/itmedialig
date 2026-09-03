<?php
$snap = $archive['snapshot'];
$leaderboard = $snap['leaderboard'] ?? [];
$stats = $archive['stats'] ?? [];
$bannerTitle = $archive['tournament_name'];
$bannerSubtitle = 'Sezon arhivat · ' . date('d.m.Y', strtotime($archive['archived_at']));
$bannerBadge = $archive['season_label'];
$bannerBackUrl = url('/sezoane');
$bannerBackLabel = '← Toate sezoanele';
include __DIR__ . '/../partials/page_banner.php';
?>

<div class="archive-stats-row">
    <div class="stat-card"><span class="stat-value"><?= (int) ($stats['numTeams'] ?? 0) ?></span><span class="stat-label">Echipe</span></div>
    <div class="stat-card"><span class="stat-value"><?= (int) ($stats['numPlayers'] ?? 0) ?></span><span class="stat-label">Jucători</span></div>
    <div class="stat-card"><span class="stat-value"><?= (int) ($stats['numMatches'] ?? 0) ?></span><span class="stat-label">Meciuri</span></div>
    <div class="stat-card"><span class="stat-value"><?= (int) ($stats['totalGoals'] ?? 0) ?></span><span class="stat-label">Goluri</span></div>
</div>

<?php if (!empty($leaderboard[0])): ?>
<div class="archive-champion card">
    <span class="archive-champion-icon">🏆</span>
    <div>
        <small>Campioană sezon</small>
        <strong><?= e($leaderboard[0]['nume']) ?></strong>
        <span class="text-muted"><?= (int) ($leaderboard[0]['points'] ?? 0) ?> puncte · Grupa <?= e($leaderboard[0]['grupa'] ?? '') ?></span>
    </div>
</div>
<?php endif; ?>

<section class="section">
    <div class="section-header">
        <h2>Clasament final</h2>
    </div>
    <div class="card card-flush">
        <table class="leaderboard-table">
            <thead><tr><th>#</th><th>Echipă</th><th>Grupa</th><th>MJ</th><th>V</th><th>E</th><th>Î</th><th>Pts</th></tr></thead>
            <tbody>
                <?php foreach ($leaderboard as $i => $row): ?>
                <tr class="<?= $i === 0 ? 'row-gold' : ($i === 1 ? 'row-silver' : ($i === 2 ? 'row-bronze' : '')) ?>">
                    <td class="rank"><?= $i + 1 ?></td>
                    <td class="team-cell text-left"><strong><?= e($row['nume']) ?></strong></td>
                    <td><?= e($row['grupa']) ?></td>
                    <td><?= (int) $row['matches_played'] ?></td>
                    <td><?= (int) $row['victories'] ?></td>
                    <td><?= (int) $row['draws'] ?></td>
                    <td><?= (int) $row['losses'] ?></td>
                    <td><strong><?= (int) $row['points'] ?></strong></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<section class="section">
    <div class="section-header">
        <h2>Rezultate meciuri</h2>
        <span class="text-muted"><?= count($snap['matches'] ?? []) ?> meciuri</span>
    </div>
    <div class="matches-list">
        <?php foreach ($snap['matches'] ?? [] as $m): ?>
        <div class="match-row match-card-pro status-terminat">
            <div class="match-row-inner match-row-inner-compact">
                <div class="match-meta-col">
                    <span class="match-date"><?= date('d M', strtotime($m['data_meci'])) ?></span>
                    <span class="match-time"><?= date('H:i', strtotime($m['data_meci'])) ?></span>
                </div>
                <div class="match-teams">
                    <div class="team-side"><span class="team-name"><?= e($m['echipa1_nume']) ?></span></div>
                    <div class="match-score-box">
                        <strong class="score-num"><?= (int) ($m['scor_echipa1'] ?? 0) ?> : <?= (int) ($m['scor_echipa2'] ?? 0) ?></strong>
                        <span class="status-badge status-terminat">Terminat</span>
                    </div>
                    <div class="team-side team-side-right"><span class="team-name"><?= e($m['echipa2_nume']) ?></span></div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</section>
