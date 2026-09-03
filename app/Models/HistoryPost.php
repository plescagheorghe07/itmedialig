<?php

namespace App\Models;

class HistoryPost extends BaseModel
{
    public function all(bool $publishedOnly = true): array
    {
        $sql = 'SELECT * FROM history_posts';
        if ($publishedOnly) {
            $sql .= ' WHERE is_published = 1';
        }
        $sql .= ' ORDER BY created_at DESC';
        $posts = $this->db->query($sql)->fetchAll();

        foreach ($posts as &$post) {
            $post['images'] = $this->images($post['id']);
        }
        return $posts;
    }

    public function find(string $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM history_posts WHERE id = ?');
        $stmt->execute([$id]);
        $post = $stmt->fetch();
        if (!$post) {
            return null;
        }
        $post['images'] = $this->images($id);
        return $post;
    }

    public function images(string $postId): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM history_images WHERE post_id = ? ORDER BY sort_order, created_at'
        );
        $stmt->execute([$postId]);
        return $stmt->fetchAll();
    }

    public function create(array $data, array $imagePaths = []): string
    {
        $id = $this->newUuid();
        $stmt = $this->db->prepare(
            'INSERT INTO history_posts (id, titlu, descriere, is_published) VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$id, $data['titlu'], $data['descriere'] ?? null, $data['is_published'] ?? 1]);
        $this->attachImages($id, $imagePaths);
        return $id;
    }

    public function update(string $id, array $data, array $newImages = []): void
    {
        $stmt = $this->db->prepare(
            'UPDATE history_posts SET titlu = ?, descriere = ?, is_published = ?, updated_at = ' . $this->nowSql() . ' WHERE id = ?'
        );
        $stmt->execute([$data['titlu'], $data['descriere'] ?? null, $data['is_published'] ?? 1, $id]);
        if ($newImages) {
            $this->attachImages($id, $newImages);
        }
    }

    public function delete(string $id): void
    {
        $this->db->prepare('DELETE FROM history_posts WHERE id = ?')->execute([$id]);
    }

    public function deleteImage(string $imageId): ?string
    {
        $stmt = $this->db->prepare('SELECT image_path FROM history_images WHERE id = ?');
        $stmt->execute([$imageId]);
        $row = $stmt->fetch();
        if ($row) {
            $this->db->prepare('DELETE FROM history_images WHERE id = ?')->execute([$imageId]);
            return $row['image_path'];
        }
        return null;
    }

    private function attachImages(string $postId, array $paths): void
    {
        $order = 0;
        foreach ($paths as $path) {
            $imgId = $this->newUuid();
            $this->db->prepare(
                'INSERT INTO history_images (id, post_id, image_path, sort_order) VALUES (?, ?, ?, ?)'
            )->execute([$imgId, $postId, $path, $order++]);
        }
    }
}
