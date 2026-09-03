<div class="admin-page-intro">
    <div>
        <p class="admin-eyebrow">Arhivă</p>
        <p class="text-muted">Închide sezonul curent și păstrează un snapshot complet al rezultatelor.</p>
    </div>
</div>

<div class="card danger-zone admin-panel-card">
    <div class="admin-panel-head">
        <span class="admin-panel-ico"><?= icon('archive', 'icon icon-lg') ?></span>
        <div>
            <h2>Arhivează sezonul curent</h2>
            <p>Salvează un snapshot complet (echipe, jucători, meciuri, clasament, bracket, istoric) și pornește un sezon nou. Meciurile, bracket-ul și istoricul curent vor fi șterse.</p>
        </div>
    </div>
    <form method="post" action="<?= url('/admin/sezoane/arhiveaza') ?>" onsubmit="return confirm('Sigur arhivezi sezonul <?= e($settings['season'] ?? '') ?>? Această acțiune nu poate fi anulată.')">
        <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
        <div class="form-row">
            <label>Sezon nou (opțional, auto-generat dacă e gol)
                <input type="text" name="new_season" placeholder="ex: 2026-2027">
            </label>
            <label class="checkbox-label">
                <input type="checkbox" name="reset_teams"> Șterge și echipele/jucătorii
            </label>
        </div>
        <button type="submit" class="btn btn-danger"><?= icon('archive', 'icon icon-sm') ?> Arhivează sezonul curent</button>
    </form>
</div>

<div class="card admin-panel-card" style="margin-top:1.5rem">
    <div class="admin-panel-head">
        <span class="admin-panel-ico"><?= icon('calendar', 'icon icon-lg') ?></span>
        <div>
            <h2>Sezoane arhivate</h2>
            <?php if ($redisAvailable): ?>
                <p class="hint success-hint">Redis activ — clasamentul este cache-uit.</p>
            <?php else: ?>
                <p class="hint">Redis nu este conectat. Activează din Setări pentru cache clasament.</p>
            <?php endif; ?>
        </div>
    </div>
    <div class="table-scroll">
    <table class="data-table">
        <thead><tr><th>Sezon</th><th>Turneu</th><th>Echipe</th><th>Meciuri</th><th>Arhivat</th><th>Acțiuni</th></tr></thead>
        <tbody>
            <?php foreach ($archives as $a): ?>
            <tr>
                <td><strong><?= e($a['season_label']) ?></strong></td>
                <td><?= e($a['tournament_name']) ?></td>
                <td><?= (int) ($a['stats']['numTeams'] ?? 0) ?></td>
                <td><?= (int) ($a['stats']['numMatches'] ?? 0) ?></td>
                <td><?= date('d.m.Y H:i', strtotime($a['archived_at'])) ?></td>
                <td class="actions">
                    <a href="<?= url('/sezoane/' . $a['id']) ?>" target="_blank" class="btn btn-sm">Vezi public</a>
                    <form method="post" action="<?= url('/admin/sezoane/' . $a['id'] . '/delete') ?>" style="display:inline" onsubmit="return confirm('Ștergi arhiva?')">
                        <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                        <button type="submit" class="btn btn-sm btn-danger">Șterge</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($archives)): ?>
            <tr><td colspan="6" class="text-muted">Nicio arhivă încă.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
    </div>
</div>
