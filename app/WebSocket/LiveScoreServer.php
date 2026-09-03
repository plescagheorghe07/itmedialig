<?php

namespace App\WebSocket;

use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;

class LiveScoreServer implements MessageComponentInterface
{
    /** @var \SplObjectStorage<ConnectionInterface, null> */
    protected \SplObjectStorage $clients;

    public function __construct()
    {
        $this->clients = new \SplObjectStorage();
    }

    public function onOpen(ConnectionInterface $conn): void
    {
        $this->clients->attach($conn);
        $conn->send(json_encode(['type' => 'connected', 'message' => 'Trofeu Hub Live']));
    }

    public function onMessage(ConnectionInterface $from, $msg): void
    {
        $data = json_decode($msg, true);
        if (($data['type'] ?? '') === 'ping') {
            $from->send(json_encode(['type' => 'pong', 'ts' => time()]));
        }
    }

    public function broadcast(string $message): void
    {
        foreach ($this->clients as $client) {
            $client->send($message);
        }
    }

    public function onClose(ConnectionInterface $conn): void
    {
        $this->clients->detach($conn);
    }

    public function onError(ConnectionInterface $conn, \Exception $e): void
    {
        $conn->close();
    }
}
