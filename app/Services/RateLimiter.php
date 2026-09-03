<?php

namespace App\Services;

/**
 * Rate limiter simplu (fișier) — protecție brute-force pe token-uri.
 */
class RateLimiter
{
    public function __construct(private string $storageDir)
    {
        if (!is_dir($this->storageDir)) {
            mkdir($this->storageDir, 0755, true);
        }
    }

    /**
     * @return bool true dacă e permis
     */
    public function attempt(string $key, int $maxAttempts, int $windowSeconds): bool
    {
        $file = $this->fileFor($key);
        $now = time();
        $data = $this->read($file);
        $data = array_values(array_filter($data, fn($t) => ($now - (int) $t) < $windowSeconds));

        if (count($data) >= $maxAttempts) {
            $this->write($file, $data);
            return false;
        }

        $data[] = $now;
        $this->write($file, $data);
        return true;
    }

    public function remaining(string $key, int $maxAttempts, int $windowSeconds): int
    {
        $file = $this->fileFor($key);
        $now = time();
        $data = array_values(array_filter($this->read($file), fn($t) => ($now - (int) $t) < $windowSeconds));
        return max(0, $maxAttempts - count($data));
    }

    public function clear(string $key): void
    {
        $file = $this->fileFor($key);
        if (is_file($file)) {
            @unlink($file);
        }
    }

    private function fileFor(string $key): string
    {
        return $this->storageDir . '/' . hash('sha256', $key) . '.json';
    }

    private function read(string $file): array
    {
        if (!is_file($file)) {
            return [];
        }
        $json = json_decode((string) file_get_contents($file), true);
        return is_array($json) ? $json : [];
    }

    private function write(string $file, array $data): void
    {
        file_put_contents($file, json_encode(array_values($data)), LOCK_EX);
    }
}
