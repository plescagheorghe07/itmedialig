<?php

define('BASE_PATH', dirname(__DIR__));

if (is_file(BASE_PATH . '/vendor/autoload.php')) {
    require BASE_PATH . '/vendor/autoload.php';
}

spl_autoload_register(function (string $class) {
    $prefix = 'App\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }
    $relative = str_replace('\\', '/', substr($class, strlen($prefix)));
    $file = BASE_PATH . '/app/' . $relative . '.php';
    if (is_file($file)) {
        require $file;
    }
});

$config = require BASE_PATH . '/config.php';

date_default_timezone_set($config['app']['timezone'] ?? 'Europe/Bucharest');

function config(string $key, mixed $default = null): mixed
{
    global $config;
    $keys = explode('.', $key);
    $value = $config;
    foreach ($keys as $k) {
        if (!is_array($value) || !array_key_exists($k, $value)) {
            return $default;
        }
        $value = $value[$k];
    }
    return $value;
}

function url(string $path = ''): string
{
    $base = rtrim(config('app.url', ''), '/');
    if ($base === '') {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $scriptDir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
        if (basename($scriptDir) === 'public') {
            $scriptDir = dirname($scriptDir);
        }
        $base = $scheme . '://' . $host . ($scriptDir && $scriptDir !== '/' ? $scriptDir : '');
    }
    $path = '/' . ltrim($path, '/');
    if ($path === '/' . 'install.php' || str_ends_with($path, '/install.php')) {
        return $base . '/install.php';
    }
    return $base . ($path === '/' ? '' : $path);
}

function asset(string $path): string
{
    return url('/public/assets/' . ltrim($path, '/'));
}

function upload_url(?string $path, string $type = 'default'): string
{
    $placeholders = [
        'team' => asset('img/placeholder-team.svg'),
        'player' => asset('img/placeholder-player.svg'),
        'default' => asset('img/placeholder-player.svg'),
    ];
    $fallback = $placeholders[$type] ?? $placeholders['default'];

    if (!$path || trim($path) === '') {
        return $fallback;
    }
    if (str_starts_with($path, 'http')) {
        return $path;
    }
    $relative = ltrim(str_replace('\\', '/', $path), '/');
    $full = BASE_PATH . '/public/' . $relative;
    if (!is_file($full)) {
        return $fallback;
    }
    return url('/public/' . $relative);
}

function export_url(?string $path): string
{
    return upload_url($path);
}

function ws_url(): string
{
    $host = config('websocket.public_host', '');
    if ($host === '') {
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $host = preg_replace('/:\d+$/', '', $host);
    }
    $port = (int) config('websocket.port', 8080);
    if (config('is_setup')) {
        try {
            $p = \App\App::get()->settings()->get('ws_port');
            if ($p) {
                $port = (int) $p;
            }
        } catch (\Throwable) {
        }
    }
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'wss' : 'ws';
    return "{$scheme}://{$host}:{$port}";
}

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function redirect(string $path): never
{
    header('Location: ' . url($path));
    exit;
}

function json_response(mixed $data, int $code = 200): never
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function is_install_request(): bool
{
    $script = basename($_SERVER['SCRIPT_NAME'] ?? '');
    return $script === 'install.php';
}

function guard_setup(): void
{
    if (!config('is_setup') && !is_install_request()) {
        header('Location: ' . url('/install.php'));
        exit;
    }
    if (config('is_setup') && is_install_request()) {
        header('Location: ' . url('/'));
        exit;
    }
}
