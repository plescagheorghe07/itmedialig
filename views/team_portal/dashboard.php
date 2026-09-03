<div class="portal-hero card">
    <img src="<?= upload_url($team['logo_path'], 'team') ?>" alt="" class="team-logo-lg">
    <div>
        <span class="page-banner-badge" style="background:var(--primary-soft);color:var(--primary);border-color:#a7f3d0">Grupa <?= e($team['grupa']) ?></span>
        <h1><?= e($team['nume']) ?></h1>
        <p class="text-muted">Actualizează logo-ul, numele și lotul de jucători. Grupa se setează doar de organizatori.</p>
    </div>
</div>

<section class="section">
    <div class="section-header"><h2>Date echipă</h2></div>
    <form method="post" enctype="multipart/form-data" action="<?= url('/gestiune/' . $token) ?>" class="card portal-form">
        <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
        <div class="form-row">
            <label>Nume echipă
                <input type="text" name="nume" value="<?= e($team['nume']) ?>" required>
            </label>
            <label>Logo nou
                <input type="file" name="logo" accept="image/*">
            </label>
        </div>
        <button type="submit" class="btn btn-primary">Salvează echipa</button>
    </form>
</section>

<section class="section">
    <div class="section-header">
        <h2>Jucători (<?= count($players) ?>)</h2>
        <button type="button" class="btn btn-primary btn-sm" onclick="document.getElementById('playerAddModal').showModal()">+ Adaugă jucător</button>
    </div>

    <div class="portal-players">
        <?php foreach ($players as $p): ?>
        <article class="portal-player-card card">
            <img src="<?= upload_url($p['poza_path'], 'player') ?>" alt="" class="player-photo-lg">
            <div class="portal-player-info">
                <strong><?= e($p['prenume'] . ' ' . $p['nume']) ?></strong>
                <?php if ($p['numar_tricou']): ?><span class="jersey-badge">#<?= (int) $p['numar_tricou'] ?></span><?php endif; ?>
                <?php if ($p['pozitie']): ?><small class="text-muted"><?= e($p['pozitie']) ?></small><?php endif; ?>
            </div>
            <div class="portal-player-actions">
                <button type="button" class="btn btn-sm btn-secondary" onclick='editPortalPlayer(<?= json_encode([
                    'id' => $p['id'],
                    'nume' => $p['nume'],
                    'prenume' => $p['prenume'],
                    'numar_tricou' => $p['numar_tricou'],
                    'pozitie' => $p['pozitie'],
                ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP) ?>)'>Editează</button>
                <form method="post" action="<?= url('/gestiune/' . $token . '/jucatori/' . $p['id'] . '/delete') ?>" onsubmit="return confirm('Ștergi jucătorul?')">
                    <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                    <button type="submit" class="btn btn-sm btn-danger">Șterge</button>
                </form>
            </div>
        </article>
        <?php endforeach; ?>
    </div>

    <?php if (empty($players)): ?>
    <div class="empty-state card"><p class="text-muted">Nu ai jucători încă. Adaugă primul.</p></div>
    <?php endif; ?>
</section>

<dialog id="playerAddModal" class="modal">
    <form method="post" enctype="multipart/form-data" action="<?= url('/gestiune/' . $token . '/jucatori') ?>">
        <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
        <h3>Adaugă jucător</h3>
        <div class="form-row">
            <label>Prenume <input type="text" name="prenume" required></label>
            <label>Nume <input type="text" name="nume" required></label>
        </div>
        <div class="form-row">
            <label>Nr. tricou <input type="number" name="numar_tricou" min="0" max="99"></label>
            <label>Poziție <input type="text" name="pozitie" placeholder="Atacant, portar…"></label>
        </div>
        <label>Poză <input type="file" name="poza" accept="image/*"></label>
        <div class="modal-actions">
            <button type="button" class="btn btn-ghost" onclick="this.closest('dialog').close()">Anulează</button>
            <button type="submit" class="btn btn-primary">Salvează</button>
        </div>
    </form>
</dialog>

<dialog id="playerEditModal" class="modal">
    <form method="post" enctype="multipart/form-data" id="playerEditForm">
        <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
        <h3>Editează jucător</h3>
        <div class="form-row">
            <label>Prenume <input type="text" name="prenume" required></label>
            <label>Nume <input type="text" name="nume" required></label>
        </div>
        <div class="form-row">
            <label>Nr. tricou <input type="number" name="numar_tricou" min="0" max="99"></label>
            <label>Poziție <input type="text" name="pozitie"></label>
        </div>
        <label>Poză nouă <input type="file" name="poza" accept="image/*"></label>
        <div class="modal-actions">
            <button type="button" class="btn btn-ghost" onclick="this.closest('dialog').close()">Anulează</button>
            <button type="submit" class="btn btn-primary">Actualizează</button>
        </div>
    </form>
</dialog>
<script>
function editPortalPlayer(p) {
    const form = document.getElementById('playerEditForm');
    form.action = <?= json_encode(url('/gestiune/' . $token . '/jucatori/')) ?> + p.id;
    form.prenume.value = p.prenume || '';
    form.nume.value = p.nume || '';
    form.numar_tricou.value = p.numar_tricou ?? '';
    form.pozitie.value = p.pozitie || '';
    document.getElementById('playerEditModal').showModal();
}
</script>
