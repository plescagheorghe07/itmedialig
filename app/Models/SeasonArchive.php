<?php

namespace App\Models;

class SeasonArchive extends BaseModel
{
    public function all(bool $publishedOnly = true): array
    {
        $sql = 'SELECT id, season_label, tournament_name, stats_json, archived_at, is_published FROM season_archives';
        if ($publishedOnly) {
            $sql .= ' WHERE is_published = 1';
        }
        $sql .= ' ORDER BY archived_at DESC';
        $rows = $this->db->query($sql)->fetchAll();
        foreach ($rows as &$row) {
            $row['stats'] = json_decode($row['stats_json'] ?? '{}', true) ?: [];
            unset($row['stats_json']);
        }
        return $rows;
    }

    public function find(string $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM season_archives WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if (!$row) {
            return null;
        }
        $row['snapshot'] = json_decode($row['snapshot_json'], true) ?: [];
        $row['stats'] = json_decode($row['stats_json'] ?? '{}', true) ?: [];
        return $row;
    }

    public function create(array $data): string
    {
        $id = $this->newUuid();
        $stmt = $this->db->prepare(
            'INSERT INTO season_archives (id, season_label, tournament_name, snapshot_json, stats_json, archived_by, is_published)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $id,
            $data['season_label'],
            $data['tournament_name'],
            $data['snapshot_json'],
            $data['stats_json'],
            $data['archived_by'] ?? null,
            $data['is_published'] ?? 1,
        ]);
        return $id;
    }

    public function delete(string $id): void
    {
        $this->db->prepare('DELETE FROM season_archives WHERE id = ?')->execute([$id]);
    }
}
