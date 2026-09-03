<?php
function renderMatchRow(array $m): void { ?>
<div class="match-row match-card-pro status-<?= e($m['status']) ?> <?= $m['status'] === 'se_joaca' ? 'is-live' : '' ?>" data-match-id="<?= e($m['id']) ?>">
    <div class="match-row-inner">
        <div class="match-meta-col">
            <span class="match-date"><?= date('d M', strtotime($m['data_meci'])) ?></span>
            <span class="match-time"><?= date('H:i', strtotime($m['data_meci'])) ?></span>
            <?php if ($m['locatie']): ?><span class="match-venue"><?= e($m['locatie']) ?></span><?php endif; ?>
            <?php if ($m['match_tag'] && $m['match_tag'] !== 'nedefinit'): ?>
                <span class="match-tag"><?= e(str_replace('_', ' ', $m['match_tag'])) ?></span>
            <?php endif; ?>
        </div>
        <div class="match-teams">
            <div class="team-side">
                <img src="<?= upload_url($m['echipa1_logo'], 'team') ?>" alt="" class="team-logo-sm">
                <a href="<?= url('/echipa/' . $m['echipa1_id']) ?>" class="team-name"><?= e($m['echipa1_nume']) ?></a>
            </div>
            <div class="match-score-box" data-live-score>
                <?php if ($m['status'] === 'programat'): ?>
                    <span class="vs">vs</span>
                <?php else: ?>
                    <strong class="score-num"><?= (int) $m['scor_echipa1'] ?> : <?= (int) $m['scor_echipa2'] ?></strong>
                <?php endif; ?>
                <span class="status-badge status-<?= e($m['status']) ?>" data-live-status><?= matchStatusLabel($m['status']) ?></span>
            </div>
            <div class="team-side team-side-right">
                <a href="<?= url('/echipa/' . $m['echipa2_id']) ?>" class="team-name"><?= e($m['echipa2_nume']) ?></a>
                <img src="<?= upload_url($m['echipa2_logo'], 'team') ?>" alt="" class="team-logo-sm">
            </div>
        </div>
        <div class="match-actions">
            <a href="<?= url('/meci/' . $m['id']) ?>" class="btn btn-sm <?= $m['status'] === 'se_joaca' ? 'btn-live' : 'btn-secondary' ?>" data-watch-btn style="<?= $m['status'] === 'programat' ? 'display:none' : '' ?>">
                <?php if ($m['status'] === 'se_joaca'): ?><span class="live-dot"></span> Vizionează live<?php else: ?>Detalii meci<?php endif; ?>
            </a>
            <?php if ($m['live_link'] && $m['status'] === 'se_joaca'): ?>
                <a href="<?= e($m['live_link']) ?>" target="_blank" class="btn btn-sm btn-ghost" data-live-link>Stream</a>
            <?php else: ?>
                <a href="<?= e($m['live_link'] ?? '#') ?>" target="_blank" class="btn btn-sm btn-ghost" data-live-link style="display:none">Stream</a>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php }

function matchStatusLabel(string $s): string {
    return match($s) {
        'se_joaca' => '● LIVE',
        'terminat' => 'Terminat',
        default => 'Programat',
    };
}
?>
<?php
$bannerTitle = 'Meciuri';
$bannerSubtitle = 'Program, transmisii live și rezultate oficiale';
include __DIR__ . '/../partials/page_banner.php';
?>

<?php if (!empty($liveMatches)): ?>
<section class="matches-section matches-section-live">
    <div class="section-label"><span class="live-dot"></span> Se joacă acum</div>
    <div class="matches-list" id="live-matches-list">
        <?php foreach ($liveMatches as $m) renderMatchRow($m); ?>
    </div>
</section>
<?php endif; ?>

<?php if (!empty($upcomingMatches)): ?>
<section class="matches-section">
    <div class="section-label">Programate</div>
    <div class="matches-list">
        <?php foreach ($upcomingMatches as $m) renderMatchRow($m); ?>
    </div>
</section>
<?php endif; ?>

<?php if (!empty($finishedMatches)): ?>
<section class="matches-section">
    <div class="section-label">Rezultate</div>
    <div class="matches-list matches-list-compact">
        <?php foreach ($finishedMatches as $m) renderMatchRow($m); ?>
    </div>
</section>
<?php endif; ?>

<?php if (empty($matches)): ?>
<div class="card empty-state"><p class="text-muted">Nu există meciuri programate.</p></div>
<?php endif; ?>
