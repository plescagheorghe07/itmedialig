<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Autentificare — IT Media Lig</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Manrope:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
</head>
<body class="login-page">
    <div class="login-card card">
        <div class="login-header">
            <span class="logo-mark"><?= icon('trophy', 'icon icon-xl') ?></span>
            <h1>IT Media Lig</h1>
            <p>Panou administrativ</p>
        </div>
        <?php if ($msg = \App\Core\Session::flash('error')): ?>
            <div class="alert alert-error"><?= e($msg) ?></div>
        <?php endif; ?>
        <form method="post" action="<?= url('/admin/login') ?>">
            <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
            <label>Utilizator
                <input type="text" name="username" required autofocus>
            </label>
            <label>Parolă
                <input type="password" name="password" required>
            </label>
            <button type="submit" class="btn btn-primary btn-block">Autentificare</button>
        </form>
        <a href="<?= url('/') ?>" class="back-link">← Înapoi la site</a>
    </div>
</body>
</html>
