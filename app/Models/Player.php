<?php

namespace App\Models;

class Player extends BaseModel
{
    public function all(bool $activeOnly = true): array
    {
        $sql = 'SELECT p.*, t.nume AS echipa_nume FROM players p LEFT JOIN teams t ON t.id = p.id_echipa';
        if ($activeOnly) {
            $sql .= ' WHERE p.is_active = 1';
        }
        $sql .= ' ORDER BY p.nume, p.prenume';
        return $this->db->query($sql)->fetchAll();
    }

    public function byTeam(string $teamId): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM players WHERE id_echipa = ? AND is_active = 1 ORDER BY nume, prenume'
        );
        $stmt->execute([$teamId]);
        return $stmt->fetchAll();
    }

    public function find(string $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM players WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function create(array $data): string
    {
        $id = $this->newUuid();
        $stmt = $this->db->prepare(
            'INSERT INTO players (id, nume, prenume, poza_path, id_echipa, numar_tricou, pozitie)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $id,
            $data['nume'],
            $data['prenume'],
            $data['poza_path'] ?? null,
            $data['id_echipa'] ?: null,
            $data['numar_tricou'] ?? null,
            $data['pozitie'] ?? null,
        ]);
        return $id;
    }

    public function update(string $id, array $data): void
    {
        $stmt = $this->db->prepare(
            'UPDATE players SET nume = ?, prenume = ?, poza_path = ?, id_echipa = ?,
             numar_tricou = ?, pozitie = ?, updated_at = ' . $this->nowSql() . ' WHERE id = ?'
        );
        $stmt->execute([
            $data['nume'],
            $data['prenume'],
            $data['poza_path'] ?? null,
            $data['id_echipa'] ?: null,
            $data['numar_tricou'] ?? null,
            $data['pozitie'] ?? null,
            $id,
        ]);
    }

    public function delete(string $id): void
    {
        $this->db->prepare('UPDATE players SET is_active = 0, updated_at = ' . $this->nowSql() . ' WHERE id = ?')->execute([$id]);
    }
}
