<div class="admin-page-intro">
    <div>
        <p class="admin-eyebrow">Documente</p>
        <p class="text-muted">Generează PDF-uri tipărite pentru clasament și statistici.</p>
    </div>
</div>

<?php if (!$dompdfReady): ?>
<div class="alert alert-error">
    Dompdf nu este instalat. Rulează <code>composer install</code> în folderul proiectului.
</div>
<?php endif; ?>

<div class="card admin-panel-card">
    <div class="admin-panel-head">
        <span class="admin-panel-ico"><?= icon('file', 'icon icon-lg') ?></span>
        <div>
            <h2>Generează export PDF</h2>
            <p class="text-muted">Alege tipul de document și salvează-l în arhiva exporturilor.</p>
        </div>
    </div>
    <form method="post" action="<?= url('/admin/exporturi') ?>">
        <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
        <label>Tip export
            <select name="export_type" required>
                <?php foreach ($types as $key => $label): ?>
                    <option value="<?= e($key) ?>"><?= e($label) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <button type="submit" class="btn btn-primary" <?= !$dompdfReady ? 'disabled' : '' ?>><?= icon('file', 'icon icon-sm') ?> Generare PDF</button>
    </form>
</div>

<div class="card admin-panel-card" style="margin-top:1.5rem">
    <h2>Istoric exporturi</h2>
    <div class="table-scroll">
    <table class="data-table">
        <thead><tr><th>Data</th><th>Tip</th><th>Sezon</th><th>Fișier</th><th>Generat de</th><th>Acțiuni</th></tr></thead>
        <tbody>
            <?php foreach ($exports as $ex): ?>
            <tr>
                <td><?= date('d.m.Y H:i', strtotime($ex['created_at'])) ?></td>
                <td><?= e($types[$ex['export_type']] ?? $ex['export_type']) ?></td>
                <td><?= e($ex['season_label'] ?? '—') ?></td>
                <td><?= e($ex['file_name']) ?></td>
                <td><?= e($ex['created_by_name'] ?? '—') ?></td>
                <td class="actions">
                    <a href="<?= export_url($ex['file_path']) ?>" target="_blank" class="btn btn-sm btn-primary" download>Descarcă</a>
                    <form method="post" action="<?= url('/admin/exporturi/' . $ex['id'] . '/delete') ?>" style="display:inline" onsubmit="return confirm('Ștergi exportul?')">
                        <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                        <button type="submit" class="btn btn-sm btn-danger">Șterge</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($exports)): ?>
            <tr><td colspan="6" class="text-muted">Niciun export încă.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
    </div>
</div>
