<?php

namespace App\Core;

use PDO;

class DbHelper
{
    public static function driver(PDO $db): string
    {
        return $db->getAttribute(PDO::ATTR_DRIVER_NAME);
    }

    public static function isMysql(PDO $db): bool
    {
        return self::driver($db) === 'mysql';
    }

    public static function newUuid(PDO $db): string
    {
        if (self::isMysql($db)) {
            return self::generateUuid();
        }
        return $db->query('SELECT NEWID() AS id')->fetch()['id'];
    }

    public static function generateUuid(): string
    {
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    public static function nowSql(PDO $db): string
    {
        return self::isMysql($db) ? 'NOW()' : 'GETDATE()';
    }

    public static function schemaFile(string $driver, string $baseName = 'schema'): string
    {
        $suffix = ($driver === 'mysql') ? '.mysql.sql' : '.sql';
        $mysqlPath = BASE_PATH . "/database/{$baseName}.mysql.sql";
        $defaultPath = BASE_PATH . "/database/{$baseName}.sql";

        if ($driver === 'mysql' && is_file($mysqlPath)) {
            return $mysqlPath;
        }
        return $defaultPath;
    }

    public static function runSqlFile(PDO $pdo, string $filePath): void
    {
        $sql = file_get_contents($filePath);
        $sql = preg_replace('/--[^\n]*\n/', "\n", $sql);
        $statements = array_filter(array_map('trim', explode(';', $sql)));

        foreach ($statements as $statement) {
            if ($statement !== '') {
                $pdo->exec($statement);
            }
        }
    }
}
