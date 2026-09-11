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
$events = $m['events'] ?? [];
$events1 = array_values(array_filter($events, fn($e) => ($e['team_id'] ?? '') === ($m['echipa1_id'] ?? '')));
$events2 = array_values(array_filter($events, fn($e) => ($e['team_id'] ?? '') === ($m['echipa2_id'] ?? '')));
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
        <div class="team-side team-side-col">
            <div class="team-side-head">
                <img src="<?= upload_url($m['echipa1_logo'] ?? null, 'team') ?>" alt="" class="team-logo-sm">
                <?php if ($linkTeams && !empty($m['echipa1_id'])): ?>
                    <a href="<?= url('/echipa/' . $m['echipa1_id']) ?>" class="team-name"><?= e($m['echipa1_nume']) ?></a>
                <?php else: ?>
                    <span class="team-name"><?= e($m['echipa1_nume'] ?? '') ?></span>
                <?php endif; ?>
            </div>
            <?php if ($events1): ?>
            <ul class="match-team-events" data-team-events="1">
                <?php foreach ($events1 as $ev):
                    $etype = $ev['event_type'] ?? 'goal';
                    $pname = trim(($ev['prenume'] ?? '') . ' ' . ($ev['nume'] ?? '')) ?: ($ev['player_name'] ?? '—');
                ?>
                <li class="match-event-line event-<?= e($etype) ?>">
                    <?= icon(match_event_icon_name($etype), 'icon icon-xs') ?>
                    <span class="match-event-min"><?= $ev['minute'] !== null && $ev['minute'] !== '' ? (int) $ev['minute'] . "'" : '' ?></span>
                    <span class="match-event-player"><?= e($pname) ?></span>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>
        </div>
        <div class="match-score-box" data-live-score>
            <?php if (($m['status'] ?? '') === 'programat'): ?>
                <span class="vs">vs</span>
            <?php else: ?>
                <strong class="score-num"><?= (int) ($m['scor_echipa1'] ?? 0) ?> : <?= (int) ($m['scor_echipa2'] ?? 0) ?></strong>
            <?php endif; ?>
        </div>
        <div class="team-side team-side-right team-side-col">
            <div class="team-side-head team-side-head-right">
                <?php if ($linkTeams && !empty($m['echipa2_id'])): ?>
                    <a href="<?= url('/echipa/' . $m['echipa2_id']) ?>" class="team-name"><?= e($m['echipa2_nume']) ?></a>
                <?php else: ?>
                    <span class="team-name"><?= e($m['echipa2_nume'] ?? '') ?></span>
                <?php endif; ?>
                <img src="<?= upload_url($m['echipa2_logo'] ?? null, 'team') ?>" alt="" class="team-logo-sm">
            </div>
            <?php if ($events2): ?>
            <ul class="match-team-events match-team-events-right" data-team-events="2">
                <?php foreach ($events2 as $ev):
                    $etype = $ev['event_type'] ?? 'goal';
                    $pname = trim(($ev['prenume'] ?? '') . ' ' . ($ev['nume'] ?? '')) ?: ($ev['player_name'] ?? '—');
                ?>
                <li class="match-event-line event-<?= e($etype) ?>">
                    <?= icon(match_event_icon_name($etype), 'icon icon-xs') ?>
                    <span class="match-event-min"><?= $ev['minute'] !== null && $ev['minute'] !== '' ? (int) $ev['minute'] . "'" : '' ?></span>
                    <span class="match-event-player"><?= e($pname) ?></span>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>
        </div>
    </div>

    <?php if (empty($hideMatchActions)): ?>
    <div class="match-card-bottom">
        <a href="<?= url('/meci/' . $m['id']) ?>"
           class="btn btn-sm <?= $watchClass ?>"
           data-watch-btn
           style="<?= $showWatch ? '' : 'display:none' ?>">
            <?php if (($m['status'] ?? '') === 'se_joaca'): ?><span class="live-dot"></span><?php endif; ?>
            <?= e($watchLabel) ?>
        </a>
        <a href="<?= e($m['live_link'] ?? '#') ?>" target="_blank" rel="noopener" class="btn btn-sm btn-ghost" data-live-link
           style="<?= (!empty($m['live_link']) && ($m['status'] ?? '') === 'se_joaca') ? '' : 'display:none' ?>">Stream</a>
    </div>
    <?php endif; ?>
</div>
