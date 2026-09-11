<?php
$statusLabel = match ($m['status'] ?? '') {
    'se_joaca' => 'LIVE',
    'terminat' => 'Terminat',
    default => 'Programat',
};
$showWatch = empty($hideMatchActions) && (($m['status'] ?? '') === 'se_joaca' || ($m['status'] ?? '') === 'terminat');
$watchLabel = ($m['status'] ?? '') === 'se_joaca' ? 'Vizionează live' : 'Detalii meci';
$watchClass = ($m['status'] ?? '') === 'se_joaca' ? 'btn-live' : 'btn-secondary';
$linkTeams = empty($hideMatchActions);
?>
<div class="match-row match-card-pro match-card-v2 status-<?= e($m['status']) ?> <?= ($m['status'] ?? '') === 'se_joaca' ? 'is-live' : '' ?>" <?php if (!empty($m['id'])): ?>data-match-id="<?= e($m['id']) ?>"<?php endif; ?>>
    <div class="match-card-top">
        <span class="match-card-date"><?= date('d.m.Y · H:i', strtotime($m['data_meci'])) ?></span>
        <span class="status-badge status-<?= e($m['status']) ?>" data-live-status>
            <?php if (($m['status'] ?? '') === 'se_joaca'): ?><span class="live-dot"></span><?php endif; ?>
            <?= e($statusLabel) ?>
        </span>
        <?php if (!empty($m['locatie'])): ?>
            <span class="match-card-venue"><?= e($m['locatie']) ?></span>
        <?php endif; ?>
        <?php if (!empty($m['match_tag']) && $m['match_tag'] !== 'nedefinit'): ?>
            <span class="match-tag"><?= e(str_replace('_', ' ', $m['match_tag'])) ?></span>
        <?php endif; ?>
        <?php if (!empty($m['exclude_from_standings'])): ?>
            <span class="match-tag match-tag-friendly">Fără clasament</span>
        <?php endif; ?>
    </div>

    <div class="match-teams match-teams-v2">
        <div class="team-side">
            <img src="<?= upload_url($m['echipa1_logo'] ?? null, 'team') ?>" alt="" class="team-logo-sm">
            <?php if ($linkTeams && !empty($m['echipa1_id'])): ?>
                <a href="<?= url('/echipa/' . $m['echipa1_id']) ?>" class="team-name"><?= e($m['echipa1_nume']) ?></a>
            <?php else: ?>
                <span class="team-name"><?= e($m['echipa1_nume'] ?? '') ?></span>
            <?php endif; ?>
        </div>
        <div class="match-score-box" data-live-score>
            <?php if (($m['status'] ?? '') === 'programat'): ?>
                <span class="vs">vs</span>
            <?php else: ?>
                <strong class="score-num"><?= (int) ($m['scor_echipa1'] ?? 0) ?> : <?= (int) ($m['scor_echipa2'] ?? 0) ?></strong>
            <?php endif; ?>
        </div>
        <div class="team-side team-side-right">
            <?php if ($linkTeams && !empty($m['echipa2_id'])): ?>
                <a href="<?= url('/echipa/' . $m['echipa2_id']) ?>" class="team-name"><?= e($m['echipa2_nume']) ?></a>
            <?php else: ?>
                <span class="team-name"><?= e($m['echipa2_nume'] ?? '') ?></span>
            <?php endif; ?>
            <img src="<?= upload_url($m['echipa2_logo'] ?? null, 'team') ?>" alt="" class="team-logo-sm">
        </div>
    </div>

    <?php if ($showWatch || (!empty($m['live_link']) && ($m['status'] ?? '') === 'se_joaca')): ?>
    <div class="match-card-bottom">
        <?php if ($showWatch): ?>
        <a href="<?= url('/meci/' . $m['id']) ?>"
           class="btn btn-sm <?= $watchClass ?>"
           data-watch-btn>
            <?php if (($m['status'] ?? '') === 'se_joaca'): ?><span class="live-dot"></span><?php endif; ?>
            <?= e($watchLabel) ?>
        </a>
        <?php else: ?>
        <a href="<?= url('/meci/' . ($m['id'] ?? '')) ?>" class="btn btn-sm btn-secondary" data-watch-btn style="display:none">Detalii meci</a>
        <?php endif; ?>
        <a href="<?= e($m['live_link'] ?? '#') ?>" target="_blank" rel="noopener" class="btn btn-sm btn-ghost" data-live-link
           style="<?= (!empty($m['live_link']) && ($m['status'] ?? '') === 'se_joaca') ? '' : 'display:none' ?>">Stream</a>
    </div>
    <?php endif; ?>
</div>
