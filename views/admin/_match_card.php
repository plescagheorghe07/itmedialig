<?php /** @var array $m */ ?>
<article class="admin-match-card status-<?= e($m['status']) ?> <?= $m['status'] === 'se_joaca' ? 'is-live' : '' ?>">
    <div class="admin-match-card-top">
        <span class="status-badge status-<?= e($m['status']) ?>"><?= e($m['status']) ?></span>
        <span class="admin-match-date"><?= date('d.m · H:i', strtotime($m['data_meci'])) ?></span>
    </div>
    <div class="admin-match-teams">
        <div class="admin-match-team">
            <img src="<?= upload_url($m['echipa1_logo'], 'team') ?>" alt="">
            <span><?= e($m['echipa1_nume']) ?></span>
        </div>
        <div class="admin-match-score">
            <?php if ($m['status'] === 'programat'): ?>
                <span class="vs">vs</span>
            <?php else: ?>
                <strong><?= (int)$m['scor_echipa1'] ?> : <?= (int)$m['scor_echipa2'] ?></strong>
            <?php endif; ?>
        </div>
        <div class="admin-match-team">
            <img src="<?= upload_url($m['echipa2_logo'], 'team') ?>" alt="">
            <span><?= e($m['echipa2_nume']) ?></span>
        </div>
    </div>
    <?php if ($m['locatie']): ?><p class="admin-match-venue text-muted"><?= e($m['locatie']) ?></p><?php endif; ?>
    <div class="admin-match-actions">
        <a href="<?= url('/admin/meciuri/' . $m['id'] . '/panou') ?>" class="btn btn-sm btn-primary">
            <?= $m['status'] === 'programat' ? '▶ Începe' : 'Panou' ?>
        </a>
        <button type="button" class="btn btn-sm btn-secondary" onclick="editMatch(<?= htmlspecialchars(json_encode($m), ENT_QUOTES) ?>)">Editează</button>
        <form method="post" action="<?= url('/admin/meciuri/' . $m['id'] . '/delete') ?>" onsubmit="return confirm('Ștergi meciul?')">
            <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
            <button type="submit" class="btn btn-sm btn-danger">Șterge</button>
        </form>
    </div>
</article>
