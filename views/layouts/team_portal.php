<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Gestiune echipă') ?> — <?= e($settings['tournament_name'] ?? 'Trofeu Hub') ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Manrope:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
</head>
<body class="portal-body">
    <header class="portal-header">
        <div class="container portal-header-inner">
            <div class="portal-brand">
                <span class="brand-icon" aria-hidden="true"><?= icon('ball', 'icon icon-lg') ?></span>
                <div>
                    <strong><?= e($settings['tournament_name'] ?? 'Trofeu Hub') ?></strong>
                    <small>Portal echipă · acces privat</small>
                </div>
            </div>
            <a href="<?= url('/') ?>" class="btn btn-sm btn-secondary">Vezi site-ul</a>
        </div>
    </header>
    <main class="site-main">
        <div class="container portal-wrap">
            <?php if ($msg = \App\Core\Session::flash('success')): ?>
                <div class="alert alert-success"><?= e($msg) ?></div>
            <?php endif; ?>
            <?php if ($msg = \App\Core\Session::flash('error')): ?>
                <div class="alert alert-error"><?= e($msg) ?></div>
            <?php endif; ?>
            <?= $content ?>
        </div>
    </main>
    <footer class="site-footer portal-footer">
        <div class="container">
            <p>Link privat — nu-l partaja public. Poți modifica doar datele echipei tale.</p>
        </div>
    </footer>
    <script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>
