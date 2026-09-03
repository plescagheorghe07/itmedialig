<?php
$bannerTitle = 'Sezoane anterioare';
$bannerSubtitle = 'Arhive complete ale turneelor trecute — clasamente, statistici și rezultate.';
include __DIR__ . '/../partials/page_banner.php';
?>

<?php if (empty($archives)): ?>
<div class="empty-state card">
    <div class="empty-state-icon">📅</div>
    <h3>Niciun sezon arhivat</h3>
    <p class="text-muted">După încheierea unui sezon, arhiva va apărea aici cu toate datele.</p>
</div>
<?php else: ?>
<div class="seasons-grid">
    <?php foreach ($archives as $a):
        $stats = $a['stats'] ?? [];
        $year = preg_match('/\d{4}/', $a['season_label'], $m) ? $m[0] : $a['season_label'];
    ?>
    <a href="<?= url('/sezoane/' . $a['id']) ?>" class="season-card">
        <div class="season-card-accent"></div>
        <div class="season-card-body">
            <span class="season-year"><?= e($year) ?></span>
            <span class="season-label"><?= e($a['season_label']) ?></span>
            <h3><?= e($a['tournament_name']) ?></h3>
            <div class="season-pills">
                <span><strong><?= (int) ($stats['numTeams'] ?? 0) ?></strong> echipe</span>
                <span><strong><?= (int) ($stats['numMatches'] ?? 0) ?></strong> meciuri</span>
                <span><strong><?= (int) ($stats['totalGoals'] ?? 0) ?></strong> goluri</span>
                <?php if (!empty($stats['numPlayers'])): ?>
                <span><strong><?= (int) $stats['numPlayers'] ?></strong> jucători</span>
                <?php endif; ?>
            </div>
            <footer class="season-card-footer">
                <time>Arhivat <?= date('d.m.Y', strtotime($a['archived_at'])) ?></time>
                <span class="season-card-cta">Vezi arhiva →</span>
            </footer>
        </div>
    </a>
    <?php endforeach; ?>
</div>
<?php endif; ?>
