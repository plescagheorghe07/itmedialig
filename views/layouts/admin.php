<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Admin') ?> — Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
</head>
<body class="admin-body">
    <div class="admin-shell">
        <aside class="admin-sidebar">
            <div class="sidebar-brand">
                <span>🏆</span> Trofeu Hub
            </div>
            <nav class="sidebar-nav">
                <a href="<?= url('/admin') ?>" class="<?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/admin') && !str_contains($_SERVER['REQUEST_URI'], '/admin/') ? 'active' : '' ?>">Dashboard</a>
                <a href="<?= url('/admin/echipe') ?>" class="<?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/echipe') ? 'active' : '' ?>">Echipe</a>
                <a href="<?= url('/admin/jucatori') ?>" class="<?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/jucatori') ? 'active' : '' ?>">Jucători</a>
                <a href="<?= url('/admin/meciuri') ?>" class="<?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/meciuri') ? 'active' : '' ?>">Meciuri</a>
                <a href="<?= url('/admin/bracket') ?>" class="<?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/bracket') ? 'active' : '' ?>">Bracket</a>
                <a href="<?= url('/admin/istoric') ?>" class="<?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/istoric') ? 'active' : '' ?>">Istoric</a>
                <a href="<?= url('/admin/sezoane') ?>" class="<?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/sezoane') ? 'active' : '' ?>">Sezoane</a>
                <a href="<?= url('/admin/exporturi') ?>" class="<?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/exporturi') ? 'active' : '' ?>">Export PDF</a>
                <a href="<?= url('/admin/setari') ?>" class="<?= str_contains($_SERVER['REQUEST_URI'] ?? '', '/setari') ? 'active' : '' ?>">Setări</a>
            </nav>
            <div class="sidebar-footer">
                <a href="<?= url('/') ?>" target="_blank">Vezi site →</a>
                <a href="<?= url('/admin/logout') ?>">Deconectare</a>
            </div>
        </aside>
        <div class="admin-content">
            <header class="admin-topbar">
                <h1><?= e($title ?? '') ?></h1>
                <span class="user-badge"><?= e($user['display_name'] ?? '') ?></span>
            </header>
            <?php if ($msg = \App\Core\Session::flash('success')): ?>
                <div class="alert alert-success"><?= e($msg) ?></div>
            <?php endif; ?>
            <?php if ($msg = \App\Core\Session::flash('error')): ?>
                <div class="alert alert-error"><?= e($msg) ?></div>
            <?php endif; ?>
            <?= $content ?>
        </div>
    </div>
    <script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>
