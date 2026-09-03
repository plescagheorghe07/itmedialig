<div class="match-row match-card-pro home-match-card status-<?= e($m['status']) ?> <?= $m['status'] === 'se_joaca' ? 'is-live' : '' ?>" data-match-id="<?= e($m['id']) ?>">
    <div class="match-row-inner match-row-inner-compact">
        <div class="match-teams">
            <div class="team-side">
                <img src="<?= upload_url($m['echipa1_logo'], 'team') ?>" alt="" class="team-logo-sm">
                <span class="team-name"><?= e($m['echipa1_nume']) ?></span>
            </div>
            <div class="match-score-box" data-live-score>
                <?php if ($m['status'] === 'programat'): ?>
                    <span class="vs">vs</span>
                <?php else: ?>
                    <strong class="score-num"><?= (int) $m['scor_echipa1'] ?> : <?= (int) $m['scor_echipa2'] ?></strong>
                <?php endif; ?>
                <span class="status-badge status-<?= e($m['status']) ?>" data-live-status>
                    <?php if ($m['status'] === 'se_joaca'): ?><span class="live-dot"></span> LIVE
                    <?php elseif ($m['status'] === 'terminat'): ?>Terminat
                    <?php else: ?>Programat<?php endif; ?>
                </span>
            </div>
            <div class="team-side team-side-right">
                <span class="team-name"><?= e($m['echipa2_nume']) ?></span>
                <img src="<?= upload_url($m['echipa2_logo'], 'team') ?>" alt="" class="team-logo-sm">
            </div>
        </div>
        <div class="match-actions match-actions-inline">
            <?php if ($m['status'] === 'se_joaca' || $m['status'] === 'terminat'): ?>
                <a href="<?= url('/meci/' . $m['id']) ?>" class="btn btn-sm <?= $m['status'] === 'se_joaca' ? 'btn-live' : 'btn-secondary' ?>" data-watch-btn>
                    <?php if ($m['status'] === 'se_joaca'): ?><span class="live-dot"></span> Live<?php else: ?>Detalii<?php endif; ?>
                </a>
            <?php endif; ?>
        </div>
    </div>
    <div class="match-card-meta">
        <?= date('d.m · H:i', strtotime($m['data_meci'])) ?>
        <?php if ($m['locatie']): ?> · <?= e($m['locatie']) ?><?php endif; ?>
    </div>
</div>
