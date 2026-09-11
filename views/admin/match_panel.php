<?php
$p = $panel;
$m = $p['match'];
$isLive = $m['status'] === 'se_joaca';
$isScheduled = $m['status'] === 'programat';
$isFinished = $m['status'] === 'terminat';
$motm1 = $p['motm1'] ?? null;
$motm2 = $p['motm2'] ?? null;
$events = $p['events'] ?? $p['goals'] ?? [];
?>
<div class="page-actions">
    <a href="<?= url('/admin/meciuri') ?>" class="btn btn-secondary"><?= icon('arrow-left', 'icon icon-sm') ?> Înapoi la meciuri</a>
</div>

<div class="card match-panel-card">
    <div class="match-panel-header">
        <div class="match-panel-team-head">
            <img src="<?= upload_url($m['echipa1_logo'], 'team') ?>" class="team-logo-sm" alt="">
            <span><?= e($m['echipa1_nume']) ?></span>
        </div>
        <div class="match-panel-score-head" id="panel-score"><?= (int)($m['scor_echipa1']??0) ?> : <?= (int)($m['scor_echipa2']??0) ?></div>
        <div class="match-panel-team-head">
            <span><?= e($m['echipa2_nume']) ?></span>
            <img src="<?= upload_url($m['echipa2_logo'], 'team') ?>" class="team-logo-sm" alt="">
        </div>
    </div>
    <p class="text-muted match-panel-meta"><?= date('d.m.Y H:i', strtotime($m['data_meci'])) ?> ·
        <span class="status-badge status-<?= e($m['status']) ?>" id="panel-status"><?= e($m['status']) ?></span>
    </p>

    <div class="match-panel-layout" id="admin-match-panel"
         data-match-id="<?= e($m['id']) ?>"
         data-csrf="<?= e($csrf) ?>"
         data-api="<?= url('/admin/meciuri/' . $m['id'] . '/panou') ?>"
         data-status="<?= e($m['status']) ?>">

        <div class="panel-team panel-team-left">
            <h3><?= e($m['echipa1_nume']) ?></h3>
            <div class="panel-players" data-goal-zone="<?= $isLive ? '1' : '0' ?>">
                <?php foreach ($p['players1'] as $pl): ?>
                <button type="button" class="panel-player" data-team-id="<?= e($m['echipa1_id']) ?>" data-side="1"
                        data-player-id="<?= e($pl['id']) ?>" data-player-name="<?= e($pl['prenume'].' '.$pl['nume']) ?>"
                        <?= !$isLive ? 'disabled' : '' ?>>
                    <img src="<?= upload_url($pl['poza_path'], 'player') ?>" class="player-photo-sm" alt="">
                    <span><?= e($pl['prenume'].' '.$pl['nume']) ?></span>
                </button>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="panel-center panel-pitch">
            <div class="panel-toolbar">
                <?php if ($isScheduled): ?>
                    <button type="button" class="btn btn-primary btn-lg" data-action="start"><?= icon('play', 'icon icon-sm') ?> Începe meciul</button>
                <?php elseif ($isLive): ?>
                    <button type="button" class="btn btn-danger" data-action="finish">Termină meciul</button>
                <?php else: ?>
                    <span class="badge badge-finished">Meci terminat</span>
                <?php endif; ?>
            </div>
            <div class="panel-goals-list" id="panel-goals-list">
                <?php foreach ($events as $g):
                    $etype = $g['event_type'] ?? 'goal';
                ?>
                <div class="panel-goal-item event-<?= e($etype) ?>">
                    <span class="panel-goal-text">
                        <?= icon(match_event_icon_name($etype), 'icon icon-sm') ?>
                        <?= $g['minute'] ? e($g['minute'])."'" : '' ?>
                        <?= e(trim(($g['prenume']??'').' '.($g['nume']??''))) ?>
                        <small class="text-muted"><?= e(match_event_type_label($etype)) ?></small>
                    </span>
                    <?php if ($isLive): ?>
                    <button type="button" class="btn btn-sm btn-ghost" data-remove-goal="<?= e($g['id']) ?>">×</button>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="panel-team panel-team-right">
            <h3><?= e($m['echipa2_nume']) ?></h3>
            <div class="panel-players" data-goal-zone="<?= $isLive ? '1' : '0' ?>">
                <?php foreach ($p['players2'] as $pl): ?>
                <button type="button" class="panel-player" data-team-id="<?= e($m['echipa2_id']) ?>" data-side="2"
                        data-player-id="<?= e($pl['id']) ?>" data-player-name="<?= e($pl['prenume'].' '.$pl['nume']) ?>"
                        <?= !$isLive ? 'disabled' : '' ?>>
                    <img src="<?= upload_url($pl['poza_path'], 'player') ?>" class="player-photo-sm" alt="">
                    <span><?= e($pl['prenume'].' '.$pl['nume']) ?></span>
                </button>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="motm-panel-section <?= $isFinished ? '' : 'is-hidden' ?>" id="motm-panel-section">
        <div class="motm-panel-title">
            <span class="motm-star-lg"><?= icon('star', 'icon icon-lg') ?></span>
            <div>
                <h3>MVP</h3>
                <p class="text-muted">Selectează câte un jucător reprezentativ pentru fiecare echipă</p>
            </div>
        </div>
        <div class="motm-panel-grid">
            <div class="motm-team-block" data-motm-side="1">
                <h4><?= e($m['echipa1_nume']) ?></h4>
                <div class="motm-player-grid">
                    <?php foreach ($p['players1'] as $pl):
                        $selected = ($motm1['id'] ?? '') === $pl['id'];
                    ?>
                    <button type="button" class="motm-pick <?= $selected ? 'is-selected' : '' ?>"
                            data-side="1" data-player-id="<?= e($pl['id']) ?>">
                        <img src="<?= upload_url($pl['poza_path'], 'player') ?>" alt="">
                        <span><?= e($pl['prenume'].' '.$pl['nume']) ?></span>
                        <?php if ($selected): ?><span class="motm-badge">OM</span><?php endif; ?>
                    </button>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="motm-team-block" data-motm-side="2">
                <h4><?= e($m['echipa2_nume']) ?></h4>
                <div class="motm-player-grid">
                    <?php foreach ($p['players2'] as $pl):
                        $selected = ($motm2['id'] ?? '') === $pl['id'];
                    ?>
                    <button type="button" class="motm-pick <?= $selected ? 'is-selected' : '' ?>"
                            data-side="2" data-player-id="<?= e($pl['id']) ?>">
                        <img src="<?= upload_url($pl['poza_path'], 'player') ?>" alt="">
                        <span><?= e($pl['prenume'].' '.$pl['nume']) ?></span>
                        <?php if ($selected): ?><span class="motm-badge">OM</span><?php endif; ?>
                    </button>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <?php if ($isLive): ?>
    <p class="hint text-muted panel-hint">Apasă pe un jucător → alege gol / cartonaș → minut → confirmă.</p>
    <?php endif; ?>
</div>

<dialog id="eventOverlay" class="event-overlay" aria-label="Adaugă eveniment">
    <form method="dialog" class="event-overlay-card" id="eventOverlayForm">
        <button type="button" class="event-overlay-close" data-event-cancel aria-label="Închide">&times;</button>
        <h3 id="event-player-name">Jucător</h3>
        <p class="text-muted event-overlay-sub">Alege tipul evenimentului</p>
        <div class="event-type-grid" role="radiogroup" aria-label="Tip eveniment">
            <label class="event-type-option">
                <input type="radio" name="event_type" value="goal" checked>
                <span><?= icon('ball', 'icon icon-lg') ?> Gol</span>
            </label>
            <label class="event-type-option">
                <input type="radio" name="event_type" value="yellow_card">
                <span><?= icon('card-yellow', 'icon icon-lg') ?> Galben</span>
            </label>
            <label class="event-type-option">
                <input type="radio" name="event_type" value="red_card">
                <span><?= icon('card-red', 'icon icon-lg') ?> Roșu</span>
            </label>
        </div>
        <label class="event-minute-label">Minut
            <input type="number" id="event-minute" name="minute" min="1" max="120" placeholder="ex: 23" required>
        </label>
        <div class="event-overlay-actions">
            <button type="button" class="btn btn-ghost" data-event-cancel>Anulează</button>
            <button type="submit" class="btn btn-primary" id="event-confirm-btn">Confirmă</button>
        </div>
    </form>
</dialog>
<script src="<?= asset('js/match-panel.js') ?>"></script>
