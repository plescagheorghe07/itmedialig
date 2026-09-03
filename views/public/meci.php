<?php
$m = $live['match'];
$isLive = $m['status'] === 'se_joaca';
$isFinished = $m['status'] === 'terminat';
$motm1 = $live['motm1'] ?? null;
$motm2 = $live['motm2'] ?? null;
?>
<div class="live-match-page" id="match-live-page" data-match-id="<?= e($matchId) ?>">
    <div class="live-match-top">
        <a href="<?= url('/meciuri') ?>" class="back-link">← Meciuri</a>
        <span class="conn-badge conn-poll" id="ws-conn-badge">○ Conectare…</span>
    </div>

    <div class="live-scoreboard <?= $isLive ? 'is-live' : '' ?> <?= $isFinished ? 'is-finished' : '' ?>" id="live-scoreboard">
        <div class="live-team live-team-home">
            <img src="<?= upload_url($m['echipa1_logo'], 'team') ?>" alt="">
            <h2><?= e($m['echipa1_nume']) ?></h2>
        </div>
        <div class="live-center">
            <div class="score-display" id="live-score-text"><?= (int) $m['scor_echipa1'] ?> : <?= (int) $m['scor_echipa2'] ?></div>
            <div class="match-state">
                <span class="status-badge status-<?= e($m['status']) ?>" id="live-status-badge">
                    <?php if ($isLive): ?><span class="live-dot"></span> LIVE
                    <?php elseif ($isFinished): ?>Terminat
                    <?php else: ?>Programat<?php endif; ?>
                </span>
            </div>
            <?php if ($m['locatie']): ?><p class="live-venue text-muted"><?= e($m['locatie']) ?> · <?= date('d.m.Y H:i', strtotime($m['data_meci'])) ?></p><?php endif; ?>
        </div>
        <div class="live-team live-team-away">
            <img src="<?= upload_url($m['echipa2_logo'], 'team') ?>" alt="">
            <h2><?= e($m['echipa2_nume']) ?></h2>
        </div>
    </div>

    <div class="live-grid">
        <div class="card live-events-card">
            <h2 class="card-title">Evenimente</h2>
            <div class="goals-timeline" id="goals-timeline">
                <?php foreach ($live['goals'] as $g):
                    $isHome = $g['team_id'] === $m['echipa1_id'];
                ?>
                <div class="goal-event <?= $isHome ? 'team-home' : 'team-away' ?>" data-goal-id="<?= e($g['id']) ?>">
                    <span class="goal-minute"><?= $g['minute'] ? e($g['minute']) . "'" : icon('ball', 'icon icon-sm') ?></span>
                    <div class="goal-body">
                        <strong><?= e($g['player_name'] ?: 'Jucător necunoscut') ?></strong>
                        <span class="text-muted"><?= e($g['team_nume']) ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php if (empty($live['goals'])): ?>
                    <p class="text-muted" id="no-goals-msg">Niciun gol înregistrat încă.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="card motm-card-wrap">
            <h2 class="card-title">Oamenii meciului</h2>
            <div class="motm-display" id="motm-display">
                <?php if ($motm1 || $motm2): ?>
                    <?php if ($motm1): ?>
                    <div class="motm-card">
                        <span class="motm-star"><?= icon('star', 'icon icon-sm') ?></span>
                        <img src="<?= upload_url($motm1['poza_path'] ?? null, 'player') ?>" alt="">
                        <strong><?= e($motm1['name']) ?></strong>
                        <small><?= e($m['echipa1_nume']) ?></small>
                    </div>
                    <?php endif; ?>
                    <?php if ($motm2): ?>
                    <div class="motm-card">
                        <span class="motm-star"><?= icon('star', 'icon icon-sm') ?></span>
                        <img src="<?= upload_url($motm2['poza_path'] ?? null, 'player') ?>" alt="">
                        <strong><?= e($motm2['name']) ?></strong>
                        <small><?= e($m['echipa2_nume']) ?></small>
                    </div>
                    <?php endif; ?>
                <?php else: ?>
                    <p class="text-muted">Vor fi anunțați după terminarea meciului.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<script>
window.TROFEU_WS_URL = window.TROFEU_WS_URL || <?= json_encode(ws_url()) ?>;
window.TROFEU_MATCH_ID = <?= json_encode($matchId) ?>;
window.TROFEU_MATCH_API = <?= json_encode(url('/api/meci/' . $matchId)) ?>;
window.TROFEU_TEAM1 = <?= json_encode($m['echipa1_id']) ?>;
</script>
<script src="<?= asset('js/match-live.js') ?>"></script>
