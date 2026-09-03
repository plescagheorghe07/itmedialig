<?php

namespace App\Core;

use Predis\Client;

class RedisClient
{
    private static ?Client $client = null;
    private static bool $available = false;

    public static function isEnabled(): bool
    {
        if ((bool) config('redis.enabled', false)) {
            return true;
        }
        if (config('is_setup')) {
            try {
                $val = \App\App::get()->settings()->get('redis_enabled');
                return $val === '1' || $val === 'true';
            } catch (\Throwable) {
            }
        }
        return false;
    }

    public static function get(): ?Client
    {
        if (!self::isEnabled()) {
            return null;
        }
        if (self::$client === null) {
            try {
                self::$client = new Client([
                    'scheme' => config('redis.scheme', 'tcp'),
                    'host' => config('redis.host', '127.0.0.1'),
                    'port' => (int) config('redis.port', 6379),
                    'password' => config('redis.password') ?: null,
                    'database' => (int) config('redis.database', 0),
                ]);
                self::$client->ping();
                self::$available = true;
            } catch (\Throwable) {
                self::$client = null;
                self::$available = false;
            }
        }
        return self::$available ? self::$client : null;
    }

    public static function available(): bool
    {
        return self::get() !== null;
    }

    public static function prefix(): string
    {
        return config('redis.prefix', 'trofeu:');
    }

    public static function key(string $suffix): string
    {
        return self::prefix() . $suffix;
    }
}
