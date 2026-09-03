<div class="team-profile card">
    <div class="team-profile-header">
        <div class="team-profile-logo">
            <img src="<?= upload_url($team['logo_path'], 'team') ?>" alt="" class="team-logo-lg">
        </div>
        <div class="team-profile-info">
            <span class="page-banner-badge"><?= e($team['grupa']) ?></span>
            <h1><?= e($team['nume']) ?></h1>
            <p class="text-muted"><?= count($players) ?> jucători în lot</p>
        </div>
    </div>
</div>

<section class="section">
    <div class="section-header"><h2>Lot jucători</h2></div>
    <div class="players-grid">
        <?php foreach ($players as $p): ?>
            <div class="player-card card">
                <img src="<?= upload_url($p['poza_path'], 'player') ?>" alt="" class="player-photo-lg">
                <div class="player-card-info">
                    <strong><?= e($p['prenume'] . ' ' . $p['nume']) ?></strong>
                    <?php if ($p['numar_tricou']): ?><span class="jersey-badge">#<?= (int) $p['numar_tricou'] ?></span><?php endif; ?>
                    <?php if ($p['pozitie']): ?><small class="text-muted"><?= e($p['pozitie']) ?></small><?php endif; ?>
                    <?php if ($p['man_of_the_match'] > 0): ?>
                        <span class="player-motm-tag"><?= icon('star', 'icon icon-sm') ?> OM ×<?= (int) $p['man_of_the_match'] ?></span>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>
