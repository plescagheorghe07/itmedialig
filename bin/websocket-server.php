#!/usr/bin/env php
<?php
/**
 * Server WebSocket pentru scoruri live
 * Pornire: php bin/websocket-server.php
 */

require dirname(__DIR__) . '/app/bootstrap.php';

if (is_file(dirname(__DIR__) . '/vendor/autoload.php')) {
    require dirname(__DIR__) . '/vendor/autoload.php';
}

if (config('is_setup')) {
    \App\App::get();
}

use App\Core\RedisClient;
use App\Services\LiveScoreService;
use App\WebSocket\LiveScoreServer;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use React\EventLoop\Loop;
use React\Socket\SocketServer;

$port = (int) config('websocket.port', 8080);
$host = config('websocket.host', '0.0.0.0');

$liveServer = new LiveScoreServer();
$loop = Loop::get();
$fallbackFile = BASE_PATH . '/storage/live_queue.jsonl';
$lastFallbackSize = is_file($fallbackFile) ? filesize($fallbackFile) : 0;

$socket = new SocketServer("{$host}:{$port}", [], $loop);
new IoServer(
    new HttpServer(new WsServer($liveServer)),
    $socket,
    $loop
);

echo "Trofeu Hub WebSocket: ws://{$host}:{$port}\n";

$loop->addPeriodicTimer(0.05, function () use ($liveServer, &$lastFallbackSize, $fallbackFile) {
    $redis = RedisClient::get();
    if ($redis) {
        try {
            $key = LiveScoreService::queueKey();
            while ($msg = $redis->lpop($key)) {
                $liveServer->broadcast($msg);
            }
        } catch (\Throwable) {
        }
    }

    if (is_file($fallbackFile)) {
        clearstatcache(true, $fallbackFile);
        $size = filesize($fallbackFile);
        if ($size > $lastFallbackSize) {
            $fp = fopen($fallbackFile, 'r');
            if ($fp) {
                fseek($fp, $lastFallbackSize);
                while (($line = fgets($fp)) !== false) {
                    $line = trim($line);
                    if ($line !== '') {
                        $liveServer->broadcast($line);
                    }
                }
                fclose($fp);
                $lastFallbackSize = $size;
            }
        }
    }
});

$loop->run();
