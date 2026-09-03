<?php

namespace App\Core;

class Session
{
    public static function start(array $config): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        session_name($config['name'] ?? 'trofeu_hub_session');
        session_set_cookie_params([
            'lifetime' => $config['lifetime'] ?? 7200,
            'path' => '/',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_start();
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public static function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public static function flash(string $key, mixed $value = null): mixed
    {
        if ($value !== null) {
            $_SESSION['_flash'][$key] = $value;
            return null;
        }
        $val = $_SESSION['_flash'][$key] ?? null;
        unset($_SESSION['_flash'][$key]);
        return $val;
    }

    public static function csrfToken(): string
    {
        if (!self::has('_csrf')) {
            self::set('_csrf', bin2hex(random_bytes(32)));
        }
        return self::get('_csrf');
    }

    public static function verifyCsrf(?string $token): bool
    {
        return $token && hash_equals(self::get('_csrf', ''), $token);
    }
}
