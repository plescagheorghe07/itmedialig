<?php

namespace App\Services;

use App\Core\RedisClient;

class LiveScoreService
{
    private const QUEUE_KEY = 'live:queue';

    public function publish(array $matchPayload): void
    {
        $type = $matchPayload['type'] ?? 'match_update';
        $message = json_encode([
            'type' => $type,
            'data' => $matchPayload,
            'ts' => time(),
        ], JSON_UNESCAPED_UNICODE);

        $redis = RedisClient::get();
        if ($redis) {
            try {
                $redis->rpush(RedisClient::key(self::QUEUE_KEY), $message);
                $redis->publish(RedisClient::key('live:channel'), $message);
            } catch (\Throwable) {
            }
        }

        // Fallback fără Redis: fișier pentru WS server
        $fallback = BASE_PATH . '/storage/live_queue.jsonl';
        $dir = dirname($fallback);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        file_put_contents($fallback, $message . "\n", FILE_APPEND | LOCK_EX);
    }

    public function publishFromMatchRow(array $match): void
    {
        $this->publish([
            'id' => $match['id'],
            'echipa1_id' => $match['echipa1_id'],
            'echipa2_id' => $match['echipa2_id'],
            'echipa1_nume' => $match['echipa1_nume'] ?? null,
            'echipa2_nume' => $match['echipa2_nume'] ?? null,
            'scor_echipa1' => $match['scor_echipa1'],
            'scor_echipa2' => $match['scor_echipa2'],
            'status' => $match['status'],
            'live_link' => $match['live_link'] ?? null,
        ]);
    }

    public static function queueKey(): string
    {
        return RedisClient::key(self::QUEUE_KEY);
    }
}
