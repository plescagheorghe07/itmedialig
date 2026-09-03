<?php

namespace App\Models;

class ExportFile extends BaseModel
{
    public function all(?string $type = null): array
    {
        $sql = 'SELECT e.*, a.display_name AS created_by_name
                FROM export_files e
                LEFT JOIN admin_users a ON a.id = e.created_by';
        $params = [];
        if ($type) {
            $sql .= ' WHERE e.export_type = ?';
            $params[] = $type;
        }
        $sql .= ' ORDER BY e.created_at DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();
        foreach ($rows as &$row) {
            $row['meta'] = json_decode($row['meta_json'] ?? '{}', true) ?: [];
        }
        return $rows;
    }

    public function find(string $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM export_files WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if ($row) {
            $row['meta'] = json_decode($row['meta_json'] ?? '{}', true) ?: [];
        }
        return $row ?: null;
    }

    public function create(array $data): string
    {
        $id = $this->newUuid();
        $stmt = $this->db->prepare(
            'INSERT INTO export_files (id, export_type, file_name, file_path, season_label, meta_json, created_by)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $id,
            $data['export_type'],
            $data['file_name'],
            $data['file_path'],
            $data['season_label'] ?? null,
            $data['meta_json'] ?? null,
            $data['created_by'] ?? null,
        ]);
        return $id;
    }

    public function delete(string $id): ?array
    {
        $row = $this->find($id);
        if ($row) {
            $this->db->prepare('DELETE FROM export_files WHERE id = ?')->execute([$id]);
        }
        return $row;
    }
}
