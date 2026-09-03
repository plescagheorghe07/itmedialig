<?php
$highlightId = \App\Core\Session::flash('manage_link_team');
?>
<div class="admin-page-intro">
    <div>
        <p class="admin-eyebrow">Gestionare</p>
        <p class="text-muted">Adaugă echipe, logo-uri și trimite fiecăreia un link privat de auto-administrare.</p>
    </div>
    <button type="button" class="btn btn-primary" onclick="document.getElementById('teamModal').showModal()"><?= icon('plus', 'icon icon-sm') ?> Adaugă echipă</button>
</div>

<div class="admin-teams-grid">
    <?php foreach ($teams as $t):
        $token = $t['manage_token'] ?? '';
        $manageUrl = $token ? url('/gestiune/' . $token) : '';
        $active = !empty($t['is_active']);
    ?>
    <article class="admin-team-card <?= $highlightId === $t['id'] ? 'is-highlight' : '' ?> <?= !$active ? 'is-inactive' : '' ?>">
        <div class="admin-team-card-top">
            <img src="<?= upload_url($t['logo_path'], 'team') ?>" alt="" class="team-logo">
            <div>
                <h3><?= e($t['nume']) ?></h3>
                <span class="badge">Grupa <?= e($t['grupa']) ?></span>
                <?php if (!$active): ?><span class="badge badge-danger">Inactivă</span><?php endif; ?>
            </div>
        </div>

        <div class="admin-team-link-box">
            <label class="admin-micro-label">Link administrare echipă</label>
            <?php if ($manageUrl): ?>
                <div class="admin-copy-row">
                    <input type="text" readonly class="manage-link-input" id="link-<?= e($t['id']) ?>"
                           value="<?= e($manageUrl) ?>"
                           data-team-name="<?= e($t['nume']) ?>"
                           data-manage-url="<?= e($manageUrl) ?>">
                    <button type="button" class="btn btn-sm btn-primary" onclick="copyManageLink('<?= e($t['id']) ?>')">
                        <?= icon('copy', 'icon icon-sm') ?> Copiază mesaj
                    </button>
                </div>
                <p class="hint text-muted">Copiază un mesaj complet (link + instrucțiuni) pe care îl poți trimite echipei.</p>
            <?php else: ?>
                <form method="post" action="<?= url('/admin/echipe/' . $t['id'] . '/link') ?>">
                    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                    <button type="submit" class="btn btn-sm btn-secondary">Generează link</button>
                </form>
            <?php endif; ?>
        </div>

        <div class="admin-team-actions">
            <button type="button" class="btn btn-sm btn-secondary" onclick="editTeam(<?= htmlspecialchars(json_encode($t), ENT_QUOTES) ?>)">Editează</button>
            <?php if ($manageUrl): ?>
            <form method="post" action="<?= url('/admin/echipe/' . $t['id'] . '/link/regenerate') ?>" onsubmit="return confirm('Regenerezi link-ul? Cel vechi nu va mai funcționa.')">
                <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                <button type="submit" class="btn btn-sm btn-ghost">Regenerează link</button>
            </form>
            <?php endif; ?>
            <form method="post" action="<?= url('/admin/echipe/' . $t['id'] . '/delete') ?>" onsubmit="return confirm('Dezactivezi echipa?')">
                <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                <button type="submit" class="btn btn-sm btn-danger">Șterge</button>
            </form>
        </div>
    </article>
    <?php endforeach; ?>
</div>

<?php if (empty($teams)): ?>
<div class="empty-state card">
    <h3>Nicio echipă</h3>
    <p class="text-muted">Adaugă prima echipă din turneu.</p>
</div>
<?php endif; ?>

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
function buildManageInviteMessage(teamName, manageUrl) {
    return [
        'Salut!',
        '',
        'Acum poți administra echipa „' + teamName + '” direct din platformă.',
        '',
        'Accesând linkul de mai jos poți edita informațiile echipei (nume, logo) și lista de jucători:',
        manageUrl,
        '',
        'ATENȚIE! Acest link oferă acces complet de editare a echipei. Nu îl transmite nimănui în afara staff-ului echipei.',
        '',
        'Mesaj generat de către platforma IT Media Lig, realizată de Plesca Gheorghe ( P-2343 ). Portofoliul personal: https://visio.md/'
    ].join('\n');
}
function copyManageLink(id) {
    const input = document.getElementById('link-' + id);
    if (!input) return;
    const text = buildManageInviteMessage(
        input.dataset.teamName || 'echipa',
        input.dataset.manageUrl || input.value
    );
    const btn = input.parentElement.querySelector('button');
    const done = () => {
        const old = btn.innerHTML;
        btn.innerHTML = 'Copiat!';
        setTimeout(() => { btn.innerHTML = old; }, 1800);
    };
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(text).then(done).catch(() => {
            input.value = text;
            input.select();
            document.execCommand('copy');
            input.value = input.dataset.manageUrl || '';
            done();
        });
    } else {
        input.value = text;
        input.select();
        document.execCommand('copy');
        input.value = input.dataset.manageUrl || '';
        done();
    }
}
document.getElementById('teamModal').addEventListener('close', () => {
    document.getElementById('teamForm').reset();
    document.getElementById('teamForm').action = '<?= url('/admin/echipe') ?>';
    document.getElementById('teamModalTitle').textContent = 'Adaugă echipă';
});
</script>
