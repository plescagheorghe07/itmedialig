<?php

namespace App\Core;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $instance = null;

    public static function getInstance(array $config): PDO
    {
        if (self::$instance === null) {
            self::$instance = self::connect($config);
        }
        return self::$instance;
    }

    public static function connect(array $config): PDO
    {
        $driver = $config['driver'] ?? 'sqlsrv';
        $host = $config['host'];
        $port = $config['port'] ?? '';
        $name = $config['name'];
        $user = $config['username'];
        $pass = $config['password'];

        try {
            if ($driver === 'sqlsrv') {
                $server = $port ? "{$host},{$port}" : $host;
                $dsn = "sqlsrv:Server={$server};Database={$name}";
            } else {
                $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";
            }

            $pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            throw new \RuntimeException('Conexiune eșuată la baza de date: ' . $e->getMessage());
        }

        return $pdo;
    }

    public static function testConnection(array $config): bool
    {
        try {
            self::connect($config);
            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    public static function reset(): void
    {
        self::$instance = null;
    }
}
