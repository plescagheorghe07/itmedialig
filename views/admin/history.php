<div class="page-actions">
    <button type="button" class="btn btn-primary" onclick="document.getElementById('historyModal').showModal()">+ Postare nouă</button>
</div>
<div class="history-list">
    <?php foreach ($posts as $post): ?>
    <div class="card history-admin-card">
        <h3><?= e($post['titlu']) ?></h3>
        <time><?= date('d.m.Y H:i', strtotime($post['created_at'])) ?></time>
        <p><?= e(mb_substr($post['descriere'] ?? '', 0, 200)) ?></p>
        <?php if (!empty($post['images'])): ?>
            <div class="thumb-row">
                <?php foreach ($post['images'] as $img): ?>
                    <img src="<?= upload_url($img['image_path']) ?>" alt="" class="thumb">
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <form method="post" action="<?= url('/admin/istoric/' . $post['id'] . '/delete') ?>" onsubmit="return confirm('Ștergi postarea?')">
            <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
            <button type="submit" class="btn btn-sm btn-danger">Șterge</button>
        </form>
    </div>
    <?php endforeach; ?>
</div>

<dialog id="historyModal" class="modal">
    <form method="post" enctype="multipart/form-data" action="<?= url('/admin/istoric') ?>">
        <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
        <h3>Postare istoric</h3>
        <label>Titlu <input type="text" name="titlu" required></label>
        <label>Descriere <textarea name="descriere" rows="4"></textarea></label>
        <label>Imagini <input type="file" name="images[]" accept="image/*" multiple></label>
        <label><input type="checkbox" name="is_published" checked> Publicată</label>
        <div class="modal-actions">
            <button type="button" class="btn btn-ghost" onclick="this.closest('dialog').close()">Anulează</button>
            <button type="submit" class="btn btn-primary">Publică</button>
        </div>
    </form>
</dialog>
