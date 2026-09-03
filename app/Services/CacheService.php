<?php

namespace App\Services;

use App\Core\RedisClient;

class CacheService
{
    private int $ttl;

    public function __construct()
    {
        $this->ttl = (int) config('redis.cache_ttl', 300);
    }

    public function get(string $key): mixed
    {
        $redis = RedisClient::get();
        if (!$redis) {
            return null;
        }
        try {
            $val = $redis->get(RedisClient::key($key));
            return $val ? json_decode($val, true) : null;
        } catch (\Throwable) {
            return null;
        }
    }

    public function set(string $key, mixed $value, ?int $ttl = null): void
    {
        $redis = RedisClient::get();
        if (!$redis) {
            return;
        }
        try {
            $redis->setex(RedisClient::key($key), $ttl ?? $this->ttl, json_encode($value, JSON_UNESCAPED_UNICODE));
        } catch (\Throwable) {
            // silent fallback
        }
    }

    public function forget(string $key): void
    {
        $redis = RedisClient::get();
        if (!$redis) {
            return;
        }
        try {
            $redis->del([RedisClient::key($key)]);
        } catch (\Throwable) {
        }
    }

    public function forgetPattern(string $pattern): void
    {
        $redis = RedisClient::get();
        if (!$redis) {
            return;
        }
        try {
            $keys = $redis->keys(RedisClient::key($pattern));
            if ($keys) {
                $redis->del($keys);
            }
        } catch (\Throwable) {
        }
    }

    public function invalidateLeaderboard(): void
    {
        $this->forgetPattern('leaderboard:*');
        $this->forget('stats:general');
    }
}
