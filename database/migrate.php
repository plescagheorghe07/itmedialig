<?php
require dirname(__DIR__) . '/app/bootstrap.php';

use App\Core\Database;
use App\Core\DbHelper;

if (!config('is_setup')) {
    echo "Platforma nu este instalată.\n";
    exit(1);
}

$dbConfig = config('database');
$driver = $dbConfig['driver'] ?? 'mysql';
$pdo = Database::getInstance($dbConfig);

foreach (['migration_v2', 'migration_v3', 'migration_v4'] as $name) {
    $file = DbHelper::schemaFile($driver, $name);
    if (!is_file($file)) {
        echo "Skip: {$file}\n";
        continue;
    }
    echo "Running {$file}...\n";
    try {
        DbHelper::runSqlFile($pdo, $file);
        echo "OK\n";
    } catch (Throwable $e) {
        echo "Note: " . $e->getMessage() . "\n";
    }
}

echo "Migrări complete.\n";
