<?php
/** @var string $bannerTitle */
/** @var string|null $bannerSubtitle */
/** @var string|null $bannerBadge */
/** @var string|null $bannerBackUrl */
/** @var string|null $bannerBackLabel */
?>
<div class="page-banner">
    <div class="page-banner-inner">
        <?php if (!empty($bannerBackUrl)): ?>
            <a href="<?= e($bannerBackUrl) ?>" class="page-banner-back"><?= e($bannerBackLabel ?? '← Înapoi') ?></a>
        <?php endif; ?>
        <?php if (!empty($bannerBadge)): ?>
            <span class="page-banner-badge"><?= e($bannerBadge) ?></span>
        <?php endif; ?>
        <h1><?= e($bannerTitle) ?></h1>
        <?php if (!empty($bannerSubtitle)): ?>
            <p><?= e($bannerSubtitle) ?></p>
        <?php endif; ?>
    </div>
</div>
