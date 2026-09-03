<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Admin') ?> — Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Manrope:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
</head>
<body class="admin-body">
    <div class="admin-shell" id="admin-shell">
        <div class="admin-sidebar-backdrop" id="admin-sidebar-backdrop" hidden></div>
        <aside class="admin-sidebar" id="admin-sidebar">
            <div class="sidebar-brand">
                <span class="sidebar-logo"><?= icon('trophy', 'icon icon-lg') ?></span>
                <div>
                    <strong>IT Media Lig</strong>
                    <small>Panou administrare</small>
                </div>
                <button type="button" class="admin-sidebar-close" id="admin-sidebar-close" aria-label="Închide meniul">
                    <?= icon('close', 'icon') ?>
                </button>
            </div>
            <?php
            $uri = $_SERVER['REQUEST_URI'] ?? '';
            $nav = [
                ['/admin', 'Dashboard', 'chart', $uri === url('/admin') || str_ends_with(rtrim(parse_url($uri, PHP_URL_PATH) ?: '', '/'), '/admin')],
                ['/admin/echipe', 'Echipe', 'shield', str_contains($uri, '/echipe')],
                ['/admin/jucatori', 'Jucători', 'user', str_contains($uri, '/jucatori')],
                ['/admin/meciuri', 'Meciuri', 'ball', str_contains($uri, '/meciuri')],
                ['/admin/bracket', 'Bracket', 'tree', str_contains($uri, '/bracket')],
                ['/admin/istoric', 'Istoric', 'news', str_contains($uri, '/istoric')],
                ['/admin/sezoane', 'Sezoane', 'calendar', str_contains($uri, '/sezoane')],
                ['/admin/exporturi', 'Export PDF', 'file', str_contains($uri, '/exporturi')],
                ['/admin/setari', 'Setări', 'settings', str_contains($uri, '/setari')],
            ];
            ?>
            <nav class="sidebar-nav">
                <?php foreach ($nav as [$href, $label, $iconName, $active]): ?>
                    <a href="<?= url($href) ?>" class="<?= $active ? 'active' : '' ?>">
                        <span class="nav-ico"><?= icon($iconName, 'icon') ?></span>
                        <span><?= e($label) ?></span>
                    </a>
                <?php endforeach; ?>
            </nav>
            <div class="sidebar-footer">
                <a href="<?= url('/') ?>" target="_blank" class="btn btn-sm btn-secondary btn-block">
                    <?= icon('external', 'icon icon-sm') ?> Vezi site
                </a>
                <a href="<?= url('/admin/logout') ?>" class="btn btn-sm btn-ghost btn-block">
                    <?= icon('logout', 'icon icon-sm') ?> Deconectare
                </a>
            </div>
        </aside>
        <div class="admin-content">
            <header class="admin-topbar">
                <div class="admin-topbar-left">
                    <button type="button" class="admin-menu-btn" id="admin-menu-btn" aria-label="Deschide meniul">
                        <?= icon('menu', 'icon') ?>
                    </button>
                    <div>
                        <p class="admin-eyebrow">Administrare</p>
                        <h1><?= e($title ?? '') ?></h1>
                    </div>
                </div>
                <div class="admin-topbar-right">
                    <span class="user-badge"><?= e($user['display_name'] ?? 'Admin') ?></span>
                </div>
            </header>
            <?php if ($msg = \App\Core\Session::flash('success')): ?>
                <div class="alert alert-success"><?= e($msg) ?></div>
            <?php endif; ?>
            <?php if ($msg = \App\Core\Session::flash('error')): ?>
                <div class="alert alert-error"><?= e($msg) ?></div>
            <?php endif; ?>
            <div class="admin-main-panel">
                <?= $content ?>
            </div>
        </div>
    </div>
    <script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>
