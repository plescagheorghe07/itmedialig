<?php
$snap = $archive['snapshot'];
$leaderboard = $snap['leaderboard'] ?? [];
$playerStats = $snap['player_stats'] ?? [];
$teams = $snap['teams'] ?? [];
$players = $snap['players'] ?? [];
$bracket = $snap['bracket'] ?? [];
$history = $snap['history'] ?? [];
$stats = $archive['stats'] ?? [];
$bannerTitle = $archive['tournament_name'];
$bannerSubtitle = 'Sezon arhivat · ' . date('d.m.Y', strtotime($archive['archived_at']));
$bannerBadge = $archive['season_label'];
$bannerBackUrl = url('/sezoane');
$bannerBackLabel = '← Toate sezoanele';
include __DIR__ . '/../partials/page_banner.php';

$teamMap = [];
foreach ($teams as $t) {
    $teamMap[$t['id']] = $t;
}
?>

<div class="archive-stats-row">
    <div class="stat-card"><span class="stat-value"><?= (int) ($stats['numTeams'] ?? count($teams)) ?></span><span class="stat-label">Echipe</span></div>
    <div class="stat-card"><span class="stat-value"><?= (int) ($stats['numPlayers'] ?? count($players)) ?></span><span class="stat-label">Jucători</span></div>
    <div class="stat-card"><span class="stat-value"><?= (int) ($stats['numMatches'] ?? count($snap['matches'] ?? [])) ?></span><span class="stat-label">Meciuri</span></div>
    <div class="stat-card"><span class="stat-value"><?= (int) ($stats['totalGoals'] ?? 0) ?></span><span class="stat-label">Goluri</span></div>
</div>

<?php if (!empty($leaderboard[0])): ?>
<div class="archive-champion card">
    <span class="archive-champion-icon"><?= icon('trophy', 'icon icon-xl') ?></span>
    <div>
        <small>Campioană sezon</small>
        <strong><?= e($leaderboard[0]['nume']) ?></strong>
        <span class="text-muted"><?= (int) ($leaderboard[0]['points'] ?? 0) ?> puncte · Grupa <?= e($leaderboard[0]['grupa'] ?? '') ?></span>
    </div>
</div>
<?php endif; ?>

<section class="section">
    <div class="section-header"><h2>Clasament final</h2></div>
    <div class="card card-flush">
        <div class="table-scroll table-scroll-sticky">
        <table class="leaderboard-table sticky-cols">
            <thead><tr>
                <th class="col-rank">#</th>
                <th class="col-name">Echipă</th>
                <th>Grupa</th><th>MJ</th><th>V</th><th>E</th><th>Î</th>
                <th>G+</th><th>G-</th><th>Pts</th>
            </tr></thead>
            <tbody>
                <?php foreach ($leaderboard as $i => $row): ?>
                <tr class="<?= $i === 0 ? 'row-gold' : ($i === 1 ? 'row-silver' : ($i === 2 ? 'row-bronze' : '')) ?>">
                    <td class="rank col-rank"><?= $i + 1 ?></td>
                    <td class="team-cell text-left col-name">
                        <div class="team-cell-inner">
                            <img src="<?= upload_url($row['logo_url'] ?? null, 'team') ?>" alt="" class="team-logo-xs">
                            <strong><?= e($row['nume']) ?></strong>
                        </div>
                    </td>
                    <td><?= e($row['grupa']) ?></td>
                    <td><?= (int) $row['matches_played'] ?></td>
                    <td><?= (int) $row['victories'] ?></td>
                    <td><?= (int) $row['draws'] ?></td>
                    <td><?= (int) $row['losses'] ?></td>
                    <td><?= (int) ($row['goals_scored'] ?? 0) ?></td>
                    <td><?= (int) ($row['goals_conceded'] ?? 0) ?></td>
                    <td><strong><?= (int) $row['points'] ?></strong></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($leaderboard)): ?>
                <tr><td colspan="10" class="text-muted">Fără clasament.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
        </div>
    </div>
</section>

<?php if (!empty($playerStats)): ?>
<section class="section">
    <div class="section-header"><h2>Top jucători</h2></div>
    <div class="card card-flush">
        <div class="table-scroll table-scroll-sticky">
        <table class="leaderboard-table sticky-cols sticky-cols-player">
            <thead>
                <tr>
                    <th class="col-rank">#</th>
                    <th class="col-name text-left">Jucător</th>
                    <th class="text-left">Echipă</th>
                    <th>Goluri</th>
                    <th>MVP</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach (array_slice($playerStats, 0, 30) as $i => $p): ?>
                <tr>
                    <td class="rank col-rank"><?= $i + 1 ?></td>
                    <td class="text-left col-name">
                        <div class="team-cell-inner">
                            <img src="<?= upload_url($p['poza_path'] ?? null, 'player') ?>" class="player-photo-sm" alt="">
                            <span><?= e(($p['prenume'] ?? '') . ' ' . ($p['nume'] ?? '')) ?></span>
                        </div>
                    </td>
                    <td class="text-left"><?= e($p['echipa_nume'] ?? '—') ?></td>
                    <td><strong><?= (int) ($p['goals'] ?? 0) ?></strong></td>
                    <td><?= (int) ($p['motm'] ?? 0) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if (!empty($teams)): ?>
<section class="section">
    <div class="section-header"><h2>Echipe</h2><span class="text-muted"><?= count($teams) ?></span></div>
    <div class="archive-teams-grid">
        <?php foreach ($teams as $t):
            $teamPlayers = array_values(array_filter($players, fn($p) => ($p['id_echipa'] ?? '') === $t['id']));
        ?>
        <article class="card archive-team-card">
            <div class="archive-team-head">
                <img src="<?= upload_url($t['logo_path'] ?? null, 'team') ?>" alt="" class="team-logo">
                <div>
                    <strong><?= e($t['nume']) ?></strong>
                    <span class="badge">Grupa <?= e($t['grupa']) ?></span>
                </div>
            </div>
            <p class="text-muted archive-team-count"><?= count($teamPlayers) ?> jucători</p>
            <?php if ($teamPlayers): ?>
            <ul class="archive-player-list">
                <?php foreach ($teamPlayers as $p): ?>
                <li>
                    <img src="<?= upload_url($p['poza_path'] ?? null, 'player') ?>" alt="" class="player-photo-sm">
                    <span><?= e(($p['prenume'] ?? '') . ' ' . ($p['nume'] ?? '')) ?></span>
                    <?php if (!empty($p['numar_tricou'])): ?><span class="jersey-badge">#<?= (int) $p['numar_tricou'] ?></span><?php endif; ?>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>
        </article>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<section class="section">
    <div class="section-header">
        <h2>Rezultate meciuri</h2>
        <span class="text-muted"><?= count($snap['matches'] ?? []) ?> meciuri</span>
    </div>
    <div class="matches-list">
        <?php
        $hideMatchActions = true;
        foreach ($snap['matches'] ?? [] as $m):
            $m['status'] = $m['status'] ?? 'terminat';
            include __DIR__ . '/../partials/match_card.php';
        endforeach;
        unset($hideMatchActions);
        ?>
        <?php if (empty($snap['matches'])): ?>
            <div class="card empty-state"><p class="text-muted">Niciun meci arhivat.</p></div>
        <?php endif; ?>
    </div>
</section>

<?php if (!empty($bracket)): ?>
<section class="section">
    <div class="section-header"><h2>PLAY-OFF</h2></div>
    <div class="bracket-arena card">
        <div class="bracket-pro-scroll">
            <div class="archive-bracket-list">
                <?php
                $byRound = [];
                foreach ($bracket as $b) {
                    $byRound[(int) $b['round_index']][] = $b;
                }
                ksort($byRound);
                foreach ($byRound as $ri => $rows):
                ?>
                <div class="archive-bracket-round">
                    <h3>Runda <?= $ri + 1 ?></h3>
                    <?php foreach ($rows as $b): ?>
                    <div class="bracket-pro-match archive-bracket-match">
                        <div class="bracket-pro-team">
                            <img src="<?= upload_url($b['team_logo'] ?? ($teamMap[$b['team_id'] ?? '']['logo_path'] ?? null), 'team') ?>" alt="" class="team-logo-xs">
                            <span class="bracket-pro-name"><?= e($b['team_nume'] ?? $teamMap[$b['team_id'] ?? '']['nume'] ?? 'TBD') ?></span>
                            <?php if ($b['score'] !== null): ?><span class="bracket-pro-score"><?= (int) $b['score'] ?></span><?php endif; ?>
                        </div>
                        <div class="bracket-pro-team">
                            <img src="<?= upload_url($b['team2_logo'] ?? ($teamMap[$b['team2_id'] ?? '']['logo_path'] ?? null), 'team') ?>" alt="" class="team-logo-xs">
                            <span class="bracket-pro-name"><?= e($b['team2_nume'] ?? $teamMap[$b['team2_id'] ?? '']['nume'] ?? 'TBD') ?></span>
                            <?php if (($b['score2'] ?? null) !== null): ?><span class="bracket-pro-score"><?= (int) $b['score2'] ?></span><?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if (!empty($history)): ?>
<section class="section">
    <div class="section-header"><h2>Istoric sezon</h2></div>
    <div class="history-list">
        <?php foreach ($history as $post): ?>
        <article class="card history-card">
            <div class="history-card-body">
                <h3><?= e($post['titlu']) ?></h3>
                <?php if (!empty($post['descriere'])): ?>
                    <p><?= nl2br(e($post['descriere'])) ?></p>
                <?php endif; ?>
                <?php if (!empty($post['images'])): ?>
                <div class="history-gallery">
                    <?php foreach ($post['images'] as $img): ?>
                        <figure class="history-gallery-item">
                            <img src="<?= upload_url($img['image_path'] ?? null, 'history') ?>" alt="">
                        </figure>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </article>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>
