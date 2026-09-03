<?php

namespace App\Core;

use PDO;

class Auth
{
    public function __construct(private PDO $db) {}

    public function attempt(string $username, string $password): bool
    {
        $stmt = $this->db->prepare('SELECT id, username, password_hash, display_name FROM admin_users WHERE username = ? AND is_active = 1');
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            return false;
        }

        Session::set('admin_id', $user['id']);
        Session::set('admin_username', $user['username']);
        Session::set('admin_name', $user['display_name']);

        $this->db->prepare('UPDATE admin_users SET last_login_at = ' . \App\Core\DbHelper::nowSql($this->db) . ' WHERE id = ?')->execute([$user['id']]);

        return true;
    }

    public function check(): bool
    {
        return Session::has('admin_id');
    }

    public function user(): ?array
    {
        if (!$this->check()) {
            return null;
        }
        return [
            'id' => Session::get('admin_id'),
            'username' => Session::get('admin_username'),
            'display_name' => Session::get('admin_name'),
        ];
    }

    public function logout(): void
    {
        Session::remove('admin_id');
        Session::remove('admin_username');
        Session::remove('admin_name');
    }

    public function requireAdmin(): void
    {
        if (!$this->check()) {
            header('Location: ' . url('/admin/login'));
            exit;
        }
    }
}
