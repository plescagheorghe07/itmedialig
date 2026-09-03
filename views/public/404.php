<?php
$bannerTitle = $title === 'Prea multe încercări' ? 'Prea multe încercări' : 'Pagina nu există';
$bannerSubtitle = $message ?? 'Link invalid sau resursă mutată.';
include __DIR__ . '/../partials/page_banner.php';
?>
<div class="empty-state card" style="text-align:center">
    <div class="empty-state-icon"><?= icon('search', 'icon icon-2xl') ?></div>
    <h3><?= e($bannerTitle) ?></h3>
    <p class="text-muted"><?= e($bannerSubtitle) ?></p>
    <a href="<?= url('/') ?>" class="btn btn-primary" style="margin-top:1rem">Înapoi acasă</a>
</div>
