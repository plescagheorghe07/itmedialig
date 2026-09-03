<?php
$bannerTitle = 'Echipe';
$bannerSubtitle = 'Toate echipele înscrise în turneul curent';
include __DIR__ . '/../partials/page_banner.php';
?>
<div class="teams-grid">
    <?php foreach ($teams as $team): ?>
        <a href="<?= url('/echipa/' . $team['id']) ?>" class="team-card">
            <div class="team-card-logo-wrap">
                <img src="<?= upload_url($team['logo_path'], 'team') ?>" alt="<?= e($team['nume']) ?>" class="team-logo">
            </div>
            <h3><?= e($team['nume']) ?></h3>
            <span class="team-card-grupa"><?= e($team['grupa']) ?></span>
        </a>
    <?php endforeach; ?>
</div>
<?php if (empty($teams)): ?>
<div class="empty-state card"><p class="text-muted">Nicio echipă înscrisă.</p></div>
<?php endif; ?>
