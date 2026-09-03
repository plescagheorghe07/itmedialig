<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="trofeu-api-live" content="<?= url('/api/live') ?>">
    <title><?= e($title ?? 'Acasă') ?> — <?= e($settings['tournament_name'] ?? $appName ?? 'Trofeu Hub') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
    <script>window.TROFEU_WS_URL = <?= json_encode(ws_url()) ?>;</script>
</head>
<body class="public-body">
    <header class="site-header">
        <div class="container header-inner">
            <a href="<?= url('/') ?>" class="brand">
                <span class="brand-icon" aria-hidden="true">⚽</span>
                <span class="brand-text">
                    <strong><?= e($settings['tournament_name'] ?? 'Trofeu Hub') ?></strong>
                    <small>Sezon <?= e($settings['season'] ?? '') ?> · Turneu oficial</small>
                </span>
            </a>
            <button type="button" class="nav-toggle" id="nav-toggle" aria-label="Meniu" aria-expanded="false">
                <span></span><span></span><span></span>
            </button>
            <?php
            $path = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '';
            $navItems = [
                ['/', 'Acasă', $path === url('/') || str_ends_with($path, '/index.php')],
                ['/clasament', 'Clasament', str_contains($path, 'clasament')],
                ['/meciuri', 'Meciuri', str_contains($path, 'meci')],
                ['/echipe', 'Echipe', str_contains($path, 'echip')],
                ['/bracket', 'Bracket', str_contains($path, 'bracket') || str_contains($path, 'eliminatoare')],
                ['/istoric', 'Istoric', str_contains($path, 'istoric')],
                ['/sezoane', 'Sezoane', str_contains($path, 'sezoane')],
            ];
            ?>
            <nav class="main-nav" id="main-nav">
                <?php foreach ($navItems as [$href, $label, $active]): ?>
                    <a href="<?= url($href) ?>" class="<?= $active ? 'active' : '' ?>"><?= e($label) ?></a>
                <?php endforeach; ?>
            </nav>
            <a href="<?= url('/admin') ?>" class="btn btn-secondary btn-sm header-admin-btn">Admin</a>
        </div>
    </header>
    <main class="site-main">
        <div class="container"><?= $content ?></div>
    </main>
    <footer class="site-footer">
        <div class="container footer-grid">
            <div class="footer-brand">
                <strong><?= e($settings['tournament_name'] ?? 'Trofeu Hub') ?></strong>
                <p>Platformă oficială de management și transparență pentru turnee locale.</p>
            </div>
            <div class="footer-links">
                <strong>Navigare</strong>
                <a href="<?= url('/clasament') ?>">Clasament</a>
                <a href="<?= url('/meciuri') ?>">Meciuri</a>
                <a href="<?= url('/bracket') ?>">Bracket</a>
                <a href="<?= url('/sezoane') ?>">Sezoane anterioare</a>
            </div>
            <div class="footer-meta">
                <strong>Sezon <?= e($settings['season'] ?? '') ?></strong>
                <p>&copy; <?= date('Y') ?> <?= e($settings['tournament_name'] ?? 'Trofeu Hub') ?></p>
            </div>
        </div>
    </footer>
    <script src="<?= asset('js/app.js') ?>"></script>
    <script src="<?= asset('js/live-scores.js') ?>"></script>
</body>
</html>
