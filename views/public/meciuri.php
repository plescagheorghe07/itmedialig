<?php
$bannerTitle = 'Meciuri';
$bannerSubtitle = 'Program, transmisii live și rezultate oficiale';
include __DIR__ . '/../partials/page_banner.php';
?>

<?php if (!empty($liveMatches)): ?>
<section class="matches-section matches-section-live">
    <div class="section-label"><span class="live-dot"></span> Se joacă acum</div>
    <div class="matches-list" id="live-matches-list">
        <?php foreach ($liveMatches as $m) include __DIR__ . '/../partials/match_card.php'; ?>
    </div>
</section>
<?php endif; ?>

<?php if (!empty($upcomingMatches)): ?>
<section class="matches-section">
    <div class="section-label">Programate</div>
    <div class="matches-list">
        <?php foreach ($upcomingMatches as $m) include __DIR__ . '/../partials/match_card.php'; ?>
    </div>
</section>
<?php endif; ?>

<?php if (!empty($finishedMatches)): ?>
<section class="matches-section">
    <div class="section-label">Rezultate</div>
    <div class="matches-list matches-list-compact">
        <?php foreach ($finishedMatches as $m) include __DIR__ . '/../partials/match_card.php'; ?>
    </div>
</section>
<?php endif; ?>

<?php if (empty($matches)): ?>
<div class="card empty-state"><p class="text-muted">Nu există meciuri programate.</p></div>
<?php endif; ?>
