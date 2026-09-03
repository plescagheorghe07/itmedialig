<?php

namespace App\Core;

class Upload
{
    public function __construct(
        private array $config,
        private string $basePath
    ) {}

    public function image(array $file, string $subdir): ?string
    {
        if ($file['error'] !== UPLOAD_ERR_OK || empty($file['tmp_name'])) {
            return null;
        }

        if ($file['size'] > ($this->config['max_size'] ?? 5242880)) {
            throw new \RuntimeException('Fișierul depășește limita de ' . ($this->config['max_size'] / 1048576) . ' MB.');
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mime, $this->config['allowed_types'] ?? [], true)) {
            throw new \RuntimeException('Tip de fișier nepermis.');
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $this->config['allowed_extensions'] ?? [], true)) {
            throw new \RuntimeException('Extensie nepermisă.');
        }

        $dir = rtrim($this->basePath, '/\\') . '/uploads/' . trim($subdir, '/\\');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $filename = bin2hex(random_bytes(16)) . '.' . $ext;
        $dest = $dir . '/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            throw new \RuntimeException('Nu s-a putut salva fișierul.');
        }

        return 'uploads/' . trim($subdir, '/\\') . '/' . $filename;
    }

    public function delete(?string $relativePath): void
    {
        if (!$relativePath || str_starts_with($relativePath, 'http')) {
            return;
        }
        $full = rtrim($this->basePath, '/\\') . '/' . ltrim($relativePath, '/');
        if (is_file($full)) {
            unlink($full);
        }
    }
}
