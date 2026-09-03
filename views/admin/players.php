<div class="page-actions">
    <button type="button" class="btn btn-primary" onclick="document.getElementById('playerModal').showModal()">+ Adaugă jucător</button>
</div>
<div class="card">
    <table class="data-table">
        <thead><tr><th>Foto</th><th>Nume</th><th>Echipă</th><th>#</th><th>Poziție</th><th>Acțiuni</th></tr></thead>
        <tbody>
            <?php foreach ($players as $p): ?>
            <tr>
                <td><img src="<?= upload_url($p['poza_path'], 'player') ?>" class="player-photo-sm" alt=""></td>
                <td><?= e($p['prenume'] . ' ' . $p['nume']) ?></td>
                <td><?= e($p['echipa_nume'] ?? '—') ?></td>
                <td><?= $p['numar_tricou'] ? '#' . (int)$p['numar_tricou'] : '—' ?></td>
                <td><?= e($p['pozitie'] ?? '—') ?></td>
                <td class="actions">
                    <button type="button" class="btn btn-sm" onclick="editPlayer(<?= htmlspecialchars(json_encode($p), ENT_QUOTES) ?>)">Editează</button>
                    <form method="post" action="<?= url('/admin/jucatori/' . $p['id'] . '/delete') ?>" style="display:inline" onsubmit="return confirm('Dezactivezi jucătorul?')">
                        <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                        <button type="submit" class="btn btn-sm btn-danger">Șterge</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<dialog id="playerModal" class="modal">
    <form method="post" enctype="multipart/form-data" id="playerForm" action="<?= url('/admin/jucatori') ?>">
        <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
        <h3 id="playerModalTitle">Adaugă jucător</h3>
        <div class="form-row">
            <label>Prenume <input type="text" name="prenume" required></label>
            <label>Nume <input type="text" name="nume" required></label>
        </div>
        <label>Echipă
            <select name="id_echipa">
                <option value="">— Fără echipă —</option>
                <?php foreach ($teams as $t): ?>
                    <option value="<?= e($t['id']) ?>"><?= e($t['nume']) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <div class="form-row">
            <label>Nr. tricou <input type="number" name="numar_tricou" min="1" max="99"></label>
            <label>Poziție <input type="text" name="pozitie" placeholder="Atacant, Mijlocaș..."></label>
        </div>
        <label>Poză <input type="file" name="poza" accept="image/*"></label>
        <div class="modal-actions">
            <button type="button" class="btn btn-ghost" onclick="this.closest('dialog').close()">Anulează</button>
            <button type="submit" class="btn btn-primary">Salvează</button>
        </div>
    </form>
</dialog>
<script>
function editPlayer(p) {
    const form = document.getElementById('playerForm');
    form.action = '<?= url('/admin/jucatori') ?>/' + p.id;
    form.prenume.value = p.prenume;
    form.nume.value = p.nume;
    form.id_echipa.value = p.id_echipa || '';
    form.numar_tricou.value = p.numar_tricou || '';
    form.pozitie.value = p.pozitie || '';
    document.getElementById('playerModalTitle').textContent = 'Editează jucător';
    document.getElementById('playerModal').showModal();
}
</script>
