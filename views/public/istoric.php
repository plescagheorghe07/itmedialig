<?php
$bannerTitle = 'Istoric turneu';
$bannerSubtitle = 'Momente, rezultate și amintiri din competiție';
include __DIR__ . '/../partials/page_banner.php';
?>
<div class="history-timeline">
    <?php foreach ($posts as $post): ?>
        <article class="history-card card">
            <header class="history-card-header">
                <time datetime="<?= e($post['created_at']) ?>"><?= date('d F Y', strtotime($post['created_at'])) ?></time>
                <h2><?= e($post['titlu']) ?></h2>
            </header>
            <?php if ($post['descriere']): ?>
                <div class="history-card-body"><?= nl2br(e($post['descriere'])) ?></div>
            <?php endif; ?>
            <?php if (!empty($post['images'])): ?>
                <div class="history-gallery">
                    <?php foreach ($post['images'] as $img): ?>
                        <figure class="history-gallery-item">
                            <img src="<?= upload_url($img['image_path']) ?>" alt="" loading="lazy">
                        </figure>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </article>
    <?php endforeach; ?>
    <?php if (empty($posts)): ?>
        <div class="empty-state card">
            <div class="empty-state-icon"><?= icon('news', 'icon icon-2xl') ?></div>
            <h3>Nicio postare încă</h3>
            <p class="text-muted">Istoricul turneului va fi publicat aici.</p>
        </div>
    <?php endif; ?>
</div>
