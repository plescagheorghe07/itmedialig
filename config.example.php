<?php
/**
 * Exemplu config — copiază ca config.php înainte de instalare
 */
return [
    'is_setup' => false,
    'app' => [
        'name' => 'Trofeu Hub',
        'url' => '',
        'timezone' => 'Europe/Bucharest',
        'debug' => false,
    ],
    'database' => [
        'driver' => 'sqlsrv',
        'host' => 'localhost',
        'port' => '1433',
        'name' => 'trofeu_hub',
        'username' => '',
        'password' => '',
        'charset' => 'UTF-8',
    ],
    'upload' => [
        'max_size' => 5242880,
        'allowed_types' => ['image/jpeg', 'image/png', 'image/webp', 'image/gif'],
        'allowed_extensions' => ['jpg', 'jpeg', 'png', 'webp', 'gif'],
    ],
    'session' => [
        'name' => 'trofeu_hub_session',
        'lifetime' => 7200,
    ],
    'redis' => [
        'enabled' => false,
        'scheme' => 'tcp',
        'host' => '127.0.0.1',
        'port' => 6379,
        'password' => '',
        'database' => 0,
        'prefix' => 'trofeu:',
        'cache_ttl' => 300,
    ],
    'websocket' => [
        'host' => '0.0.0.0',
        'port' => 8080,
        'public_host' => '',
    ],
];
