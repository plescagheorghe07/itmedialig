<?php

namespace App\Models;

class Team extends BaseModel
{
    public function all(bool $activeOnly = true): array
    {
        $sql = 'SELECT * FROM teams' . ($activeOnly ? ' WHERE is_active = 1' : '') . ' ORDER BY grupa, nume';
        return $this->db->query($sql)->fetchAll();
    }

    public function find(string $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM teams WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function create(array $data): string
    {
        $id = $this->newUuid();
        $stmt = $this->db->prepare(
            'INSERT INTO teams (id, nume, logo_path, grupa) VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$id, $data['nume'], $data['logo_path'] ?? null, $data['grupa']]);
        return $id;
    }

    public function update(string $id, array $data): void
    {
        $stmt = $this->db->prepare(
            'UPDATE teams SET nume = ?, logo_path = ?, grupa = ?, updated_at = ' . $this->nowSql() . ' WHERE id = ?'
        );
        $stmt->execute([$data['nume'], $data['logo_path'] ?? null, $data['grupa'], $id]);
    }

    public function delete(string $id): void
    {
        $this->db->prepare('UPDATE teams SET is_active = 0, updated_at = ' . $this->nowSql() . ' WHERE id = ?')->execute([$id]);
    }

    public function hardDelete(string $id): void
    {
        $this->db->prepare('DELETE FROM teams WHERE id = ?')->execute([$id]);
    }

    public function groups(): array
    {
        return $this->db->query('SELECT DISTINCT grupa FROM teams WHERE is_active = 1 ORDER BY grupa')->fetchAll(\PDO::FETCH_COLUMN);
    }
}
