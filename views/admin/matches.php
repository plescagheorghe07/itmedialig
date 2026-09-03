<div class="page-actions">
    <button type="button" class="btn btn-primary" onclick="document.getElementById('matchModal').showModal()">+ Adaugă meci</button>
</div>

<?php
$live = array_filter($matches, fn($m) => $m['status'] === 'se_joaca');
$scheduled = array_filter($matches, fn($m) => $m['status'] === 'programat');
$finished = array_filter($matches, fn($m) => $m['status'] === 'terminat');
?>

<?php if ($live): ?>
<h2 class="admin-section-title"><span class="live-dot"></span> Live acum</h2>
<div class="admin-matches-grid admin-matches-live">
    <?php foreach ($live as $m): include __DIR__ . '/_match_card.php'; endforeach; ?>
</div>
<?php endif; ?>

<?php if ($scheduled): ?>
<h2 class="admin-section-title">Programate</h2>
<div class="admin-matches-grid">
    <?php foreach ($scheduled as $m): include __DIR__ . '/_match_card.php'; endforeach; ?>
</div>
<?php endif; ?>

<?php if ($finished): ?>
<h2 class="admin-section-title">Terminate</h2>
<div class="admin-matches-grid admin-matches-compact">
    <?php foreach ($finished as $m): include __DIR__ . '/_match_card.php'; endforeach; ?>
</div>
<?php endif; ?>

<?php if (empty($matches)): ?>
<div class="card"><p class="text-muted">Niciun meci. Adaugă primul meci.</p></div>
<?php endif; ?>

<dialog id="matchModal" class="modal modal-lg">
    <form method="post" id="matchForm" action="<?= url('/admin/meciuri') ?>">
        <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
        <h3 id="matchModalTitle">Adaugă meci</h3>
        <div class="form-row">
            <label>Echipa 1
                <select name="echipa1_id" required><?php foreach ($teams as $t): ?><option value="<?= e($t['id']) ?>"><?= e($t['nume']) ?></option><?php endforeach; ?></select>
            </label>
            <label>Echipa 2
                <select name="echipa2_id" required><?php foreach ($teams as $t): ?><option value="<?= e($t['id']) ?>"><?= e($t['nume']) ?></option><?php endforeach; ?></select>
            </label>
        </div>
        <div class="form-row">
            <label>Scor 1 <input type="number" name="scor_echipa1" min="0"></label>
            <label>Scor 2 <input type="number" name="scor_echipa2" min="0"></label>
        </div>
        <div class="form-row">
            <label>Status
                <select name="status">
                    <option value="programat">Programat</option>
                    <option value="se_joaca">Se joacă</option>
                    <option value="terminat">Terminat</option>
                </select>
            </label>
            <label>Data meci
                <input type="datetime-local" name="data_meci" required>
            </label>
        </div>
        <div class="form-row">
            <label>Tag meci
                <select name="match_tag">
                    <?php foreach (['nedefinit','grupa','optimi','sferturi','semi-finala','finala_mica','finala_mare'] as $tag): ?>
                        <option value="<?= $tag ?>"><?= $tag ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Locație <input type="text" name="locatie"></label>
        </div>
        <label>Link live <input type="url" name="live_link" placeholder="https://..."></label>
        <p class="hint text-muted">Oamenii meciului se setează din panoul meciului, după terminare.</p>
        <div class="modal-actions">
            <button type="button" class="btn btn-ghost" onclick="this.closest('dialog').close()">Anulează</button>
            <button type="submit" class="btn btn-primary">Salvează</button>
        </div>
    </form>
</dialog>
<script>
function editMatch(m) {
    const form = document.getElementById('matchForm');
    form.action = '<?= url('/admin/meciuri') ?>/' + m.id;
    form.echipa1_id.value = m.echipa1_id;
    form.echipa2_id.value = m.echipa2_id;
    form.scor_echipa1.value = m.scor_echipa1 ?? '';
    form.scor_echipa2.value = m.scor_echipa2 ?? '';
    form.status.value = m.status;
    form.data_meci.value = m.data_meci ? m.data_meci.slice(0,16) : '';
    form.match_tag.value = m.match_tag || 'nedefinit';
    form.locatie.value = m.locatie || '';
    form.live_link.value = m.live_link || '';
    document.getElementById('matchModalTitle').textContent = 'Editează meci';
    document.getElementById('matchModal').showModal();
}
</script>
