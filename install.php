<?php
/**
 * Trofeu Hub - Instalare
 * Wizard pentru configurare DB + cont admin
 */

require __DIR__ . '/app/bootstrap.php';

use App\Core\Database;
use App\Core\DbHelper;
use App\Core\Session;

$config = require BASE_PATH . '/config.php';

// Dacă e deja instalat, redirect
if ($config['is_setup']) {
    header('Location: ' . url('/'));
    exit;
}

Session::start($config['session']);

$step = (int) ($_GET['step'] ?? 1);
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $step = (int) ($_POST['step'] ?? 1);

    if ($step === 1) {
        // Test DB connection
        $dbConfig = [
            'driver' => $_POST['driver'] ?? 'sqlsrv',
            'host' => trim($_POST['host'] ?? 'localhost'),
            'port' => trim($_POST['port'] ?? '1433'),
            'name' => trim($_POST['dbname'] ?? ''),
            'username' => trim($_POST['username'] ?? ''),
            'password' => $_POST['password'] ?? '',
        ];

        if (!$dbConfig['name'] || !$dbConfig['username']) {
            $errors[] = 'Completează numele bazei de date și utilizatorul.';
        } elseif (!Database::testConnection($dbConfig)) {
            $errors[] = 'Nu s-a putut conecta la baza de date. Verifică credențialele și extensia PHP (pdo_sqlsrv sau pdo_mysql).';
        } else {
            $_SESSION['install_db'] = $dbConfig;
            $step = 2;
        }
    } elseif ($step === 2) {
        // Run schema + create admin
        $dbConfig = $_SESSION['install_db'] ?? null;
        if (!$dbConfig) {
            redirect('/install.php?step=1');
        }

        $adminUser = trim($_POST['admin_username'] ?? '');
        $adminPass = $_POST['admin_password'] ?? '';
        $adminName = trim($_POST['admin_name'] ?? 'Administrator');
        $appUrl = rtrim(trim($_POST['app_url'] ?? ''), '/');
        $tournamentName = trim($_POST['tournament_name'] ?? 'Liga de Fotbal CEITI');

        if (strlen($adminUser) < 3) {
            $errors[] = 'Utilizatorul admin trebuie să aibă minim 3 caractere.';
        }
        if (strlen($adminPass) < 6) {
            $errors[] = 'Parola trebuie să aibă minim 6 caractere.';
        }

        if (!$errors) {
            try {
                $pdo = Database::connect($dbConfig);
                $driver = $dbConfig['driver'] ?? 'sqlsrv';

                DbHelper::runSqlFile($pdo, DbHelper::schemaFile($driver, 'schema'));

                $migrationFile = DbHelper::schemaFile($driver, 'migration_v2');
                if (is_file($migrationFile)) {
                    try {
                        DbHelper::runSqlFile($pdo, $migrationFile);
                    } catch (\Throwable) {
                    }
                }
                $migrationV3 = DbHelper::schemaFile($driver, 'migration_v3');
                if (is_file($migrationV3)) {
                    try {
                        DbHelper::runSqlFile($pdo, $migrationV3);
                    } catch (\Throwable) {
                    }
                }
                $migrationV4 = DbHelper::schemaFile($driver, 'migration_v4');
                if (is_file($migrationV4)) {
                    try {
                        DbHelper::runSqlFile($pdo, $migrationV4);
                    } catch (\Throwable) {
                    }
                }
                $migrationV5 = DbHelper::schemaFile($driver, 'migration_v5');
                if (is_file($migrationV5)) {
                    try {
                        DbHelper::runSqlFile($pdo, $migrationV5);
                    } catch (\Throwable) {
                    }
                }

                // Create admin user
                $hash = password_hash($adminPass, PASSWORD_DEFAULT);
                $adminId = DbHelper::newUuid($pdo);
                $stmt = $pdo->prepare(
                    'INSERT INTO admin_users (id, username, password_hash, display_name) VALUES (?, ?, ?, ?)'
                );
                $stmt->execute([$adminId, $adminUser, $hash, $adminName]);

                // Update settings
                $pdo->prepare(
                    'UPDATE settings SET setting_value = ? WHERE setting_key = ?'
                )->execute([$tournamentName, 'tournament_name']);

                // Write config.php
                writeConfig([
                    'is_setup' => true,
                    'app' => [
                        'name' => 'Trofeu Hub',
                        'url' => $appUrl,
                        'timezone' => 'Europe/Bucharest',
                        'debug' => false,
                    ],
                    'database' => array_merge($dbConfig, [
                        'name' => $dbConfig['name'],
                        'driver' => $driver,
                        'charset' => 'UTF-8',
                    ]),
                    'upload' => $config['upload'],
                    'session' => $config['session'],
                    'redis' => $config['redis'] ?? [
                        'enabled' => false,
                        'scheme' => 'tcp',
                        'host' => '127.0.0.1',
                        'port' => 6379,
                        'password' => '',
                        'database' => 0,
                        'prefix' => 'trofeu:',
                        'cache_ttl' => 300,
                    ],
                    'websocket' => $config['websocket'] ?? [
                        'host' => '0.0.0.0',
                        'port' => 8080,
                        'public_host' => '',
                    ],
                ]);

                unset($_SESSION['install_db']);
                $success = true;
                $step = 3;
            } catch (Throwable $e) {
                $errors[] = 'Eroare la instalare: ' . $e->getMessage();
            }
        }
    }
}

function writeConfig(array $cfg): void
{
    $export = var_export($cfg, true);
    $export = str_replace(["\n", '  '], ["\n", '    '], $export);
    $content = "<?php\n\nreturn " . var_export($cfg, true) . ";\n";
    file_put_contents(BASE_PATH . '/config.php', $content);
}

$dbSession = $_SESSION['install_db'] ?? [];
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalare Trofeu Hub</title>
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
</head>
<body class="install-page">
    <div class="install-container">
        <div class="install-header">
            <div class="logo-mark"><?= icon('trophy', 'icon icon-xl') ?></div>
            <h1>Instalare Trofeu Hub</h1>
            <p>Platformă turnee fotbal — setup inițial</p>
        </div>

        <div class="install-steps">
            <span class="<?= $step >= 1 ? 'active' : '' ?>">1. Bază de date</span>
            <span class="<?= $step >= 2 ? 'active' : '' ?>">2. Administrator</span>
            <span class="<?= $step >= 3 ? 'active' : '' ?>">3. Finalizare</span>
        </div>

        <?php if ($errors): ?>
            <div class="alert alert-error">
                <?php foreach ($errors as $err): ?>
                    <p><?= htmlspecialchars($err) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($step === 1): ?>
            <form method="post" class="install-form card">
                <input type="hidden" name="step" value="1">
                <h2>Conexiune bază de date</h2>

                <label>Driver
                    <select name="driver">
                        <option value="sqlsrv" selected>SQL Server (sqlsrv)</option>
                        <option value="mysql">MySQL / MariaDB</option>
                    </select>
                </label>

                <div class="form-row">
                    <label>Host
                        <input type="text" name="host" value="<?= htmlspecialchars($dbSession['host'] ?? 'localhost') ?>" required>
                    </label>
                    <label>Port
                        <input type="text" name="port" value="<?= htmlspecialchars($dbSession['port'] ?? '1433') ?>">
                    </label>
                </div>

                <label>Nume bază de date
                    <input type="text" name="dbname" value="<?= htmlspecialchars($dbSession['name'] ?? 'trofeu_hub') ?>" required>
                </label>

                <div class="form-row">
                    <label>Utilizator
                        <input type="text" name="username" value="<?= htmlspecialchars($dbSession['username'] ?? '') ?>" required>
                    </label>
                    <label>Parolă
                        <input type="password" name="password" value="">
                    </label>
                </div>

                <p class="hint">Asigură-te că baza de date există și extensia PHP <code>pdo_sqlsrv</code> este activă.</p>
                <button type="submit" class="btn btn-primary">Continuă →</button>
            </form>

        <?php elseif ($step === 2 && !$success): ?>
            <form method="post" class="install-form card">
                <input type="hidden" name="step" value="2">
                <h2>Cont administrator & setări</h2>

                <label>URL site (fără slash final)
                    <input type="url" name="app_url" placeholder="https://domeniu.ro" required>
                </label>

                <label>Nume turneu
                    <input type="text" name="tournament_name" value="Liga de Fotbal CEITI">
                </label>

                <div class="form-row">
                    <label>Utilizator admin
                        <input type="text" name="admin_username" required minlength="3">
                    </label>
                    <label>Parolă admin
                        <input type="password" name="admin_password" required minlength="6">
                    </label>
                </div>

                <label>Nume afișat
                    <input type="text" name="admin_name" value="Administrator">
                </label>

                <button type="submit" class="btn btn-primary">Instalează platforma</button>
            </form>

        <?php elseif ($step === 3 || $success): ?>
            <div class="install-form card success-card">
                <h2>✅ Instalare completă!</h2>
                <p>Platforma Trofeu Hub a fost configurată cu succes.</p>
                <ul>
                    <li>Schema bazei de date a fost creată</li>
                    <li>Contul de administrator este activ</li>
                    <li><code>install.php</code> nu mai poate fi accesat</li>
                </ul>
                <div class="install-actions">
                    <a href="<?= url('/') ?>" class="btn btn-primary">Vezi site-ul public</a>
                    <a href="<?= url('/admin/login') ?>" class="btn btn-secondary">Panou admin</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
