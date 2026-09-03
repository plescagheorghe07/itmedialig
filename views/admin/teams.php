<div class="page-actions">
    <button type="button" class="btn btn-primary" onclick="document.getElementById('teamModal').showModal()">+ Adaugă echipă</button>
</div>

<div class="card">
    <table class="data-table">
        <thead><tr><th>Logo</th><th>Nume</th><th>Grupa</th><th>Acțiuni</th></tr></thead>
        <tbody>
            <?php foreach ($teams as $t): ?>
            <tr>
                <td><img src="<?= upload_url($t['logo_path'], 'team') ?>" class="team-logo-xs" alt=""></td>
                <td><?= e($t['nume']) ?></td>
                <td><span class="badge"><?= e($t['grupa']) ?></span></td>
                <td class="actions">
                    <button type="button" class="btn btn-sm" onclick="editTeam(<?= htmlspecialchars(json_encode($t), ENT_QUOTES) ?>)">Editează</button>
                    <form method="post" action="<?= url('/admin/echipe/' . $t['id'] . '/delete') ?>" style="display:inline" onsubmit="return confirm('Dezactivezi echipa?')">
                        <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                        <button type="submit" class="btn btn-sm btn-danger">Șterge</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<dialog id="teamModal" class="modal">
    <form method="post" enctype="multipart/form-data" id="teamForm" action="<?= url('/admin/echipe') ?>">
        <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
        <h3 id="teamModalTitle">Adaugă echipă</h3>
        <label>Nume <input type="text" name="nume" required></label>
        <label>Grupa <input type="text" name="grupa" required placeholder="A, B, C..."></label>
        <label>Logo <input type="file" name="logo" accept="image/*"></label>
        <div class="modal-actions">
            <button type="button" class="btn btn-ghost" onclick="this.closest('dialog').close()">Anulează</button>
            <button type="submit" class="btn btn-primary">Salvează</button>
        </div>
    </form>
</dialog>
<script>
function editTeam(team) {
    const form = document.getElementById('teamForm');
    form.action = '<?= url('/admin/echipe') ?>/' + team.id;
    form.nume.value = team.nume;
    form.grupa.value = team.grupa;
    document.getElementById('teamModalTitle').textContent = 'Editează echipă';
    document.getElementById('teamModal').showModal();
}
document.getElementById('teamModal').addEventListener('close', () => {
    document.getElementById('teamForm').reset();
    document.getElementById('teamForm').action = '<?= url('/admin/echipe') ?>';
    document.getElementById('teamModalTitle').textContent = 'Adaugă echipă';
});
</script>
