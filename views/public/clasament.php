<?php
$bannerTitle = 'Clasament';
$bannerSubtitle = 'Statistici echipe și jucători — sezon ' . ($settings['season'] ?? '');
include __DIR__ . '/../partials/page_banner.php';
?>

<form method="get" action="<?= url('/clasament') ?>" class="filter-bar card filter-bar-card">
    <input type="hidden" name="tab" value="<?= e($activeTab ?? 'echipe') ?>">
    <label>Grupă
        <select name="grupa" onchange="this.form.submit()">
            <option value="">Toate grupele</option>
            <?php foreach ($groups as $g): ?>
                <option value="<?= e($g) ?>" <?= ($activeGrupa ?? '') === $g ? 'selected' : '' ?>><?= e($g) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
</form>

<div class="tabs">
    <a href="<?= url('/clasament?' . http_build_query(['tab' => 'echipe', 'grupa' => $activeGrupa ?? ''])) ?>" class="<?= ($activeTab ?? 'echipe') === 'echipe' ? 'active' : '' ?>">Echipe</a>
    <a href="<?= url('/clasament?' . http_build_query(['tab' => 'jucatori', 'grupa' => $activeGrupa ?? ''])) ?>" class="<?= ($activeTab ?? '') === 'jucatori' ? 'active' : '' ?>">Jucători</a>
</div>

<?php if (($activeTab ?? 'echipe') === 'jucatori'): ?>
<div class="card" style="padding:0; overflow:hidden">
    <table class="leaderboard-table">
        <thead>
            <tr>
                <th>#</th>
                <th class="text-left">Jucător</th>
                <th class="text-left">Echipă</th>
                <th>Grupa</th>
                <th>Goluri</th>
                <th>MOTM</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($playerStats as $i => $p): ?>
            <tr>
                <td class="rank"><?= $i + 1 ?></td>
                <td class="text-left">
                    <div class="team-cell-inner">
                        <img src="<?= upload_url($p['poza_path'], 'player') ?>" class="player-photo-sm" alt="">
                        <span><?= e($p['prenume'] . ' ' . $p['nume']) ?></span>
                    </div>
                </td>
                <td class="text-left"><?= e($p['echipa_nume']) ?></td>
                <td><?= e($p['grupa']) ?></td>
                <td><strong><?= (int) $p['goals'] ?></strong></td>
                <td><?= (int) $p['motm'] ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($playerStats)): ?>
            <tr><td colspan="6" class="text-muted">Niciun jucător.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php else: ?>
<div class="card card-flush">
    <?php include __DIR__ . '/../partials/leaderboard_table.php'; ?>
</div>
<?php endif; ?>
