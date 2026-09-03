<?php

namespace App\Models;

class MatchModel extends BaseModel
{
    public function all(): array
    {
        $mt = $this->matchesTable();
        return $this->db->query(
            "SELECT m.*,
                t1.nume AS echipa1_nume, t1.logo_path AS echipa1_logo, t1.grupa AS echipa1_grupa,
                t2.nume AS echipa2_nume, t2.logo_path AS echipa2_logo, t2.grupa AS echipa2_grupa
             FROM {$mt} m
             JOIN teams t1 ON t1.id = m.echipa1_id
             JOIN teams t2 ON t2.id = m.echipa2_id
             ORDER BY m.data_meci ASC"
        )->fetchAll();
    }

    public function find(string $id): ?array
    {
        $mt = $this->matchesTable();
        $stmt = $this->db->prepare("SELECT * FROM {$mt} WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function findEnriched(string $id): ?array
    {
        $mt = $this->matchesTable();
        $stmt = $this->db->prepare(
            "SELECT m.*,
                t1.nume AS echipa1_nume, t1.logo_path AS echipa1_logo,
                t2.nume AS echipa2_nume, t2.logo_path AS echipa2_logo
             FROM {$mt} m
             JOIN teams t1 ON t1.id = m.echipa1_id
             JOIN teams t2 ON t2.id = m.echipa2_id
             WHERE m.id = ?"
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function liveMatches(): array
    {
        $mt = $this->matchesTable();
        return $this->db->query(
            "SELECT m.*, t1.nume AS echipa1_nume, t2.nume AS echipa2_nume
             FROM {$mt} m
             JOIN teams t1 ON t1.id = m.echipa1_id
             JOIN teams t2 ON t2.id = m.echipa2_id
             WHERE m.status IN ('se_joaca', 'programat', 'terminat')
             ORDER BY CASE m.status WHEN 'se_joaca' THEN 0 WHEN 'programat' THEN 1 ELSE 2 END, m.data_meci"
        )->fetchAll();
    }

    public function byTeam(string $teamId): array
    {
        $mt = $this->matchesTable();
        $stmt = $this->db->prepare(
            "SELECT omul_meciului_echipa1_id, omul_meciului_echipa2_id, echipa1_id, echipa2_id
             FROM {$mt} WHERE echipa1_id = ? OR echipa2_id = ?"
        );
        $stmt->execute([$teamId, $teamId]);
        return $stmt->fetchAll();
    }

    public function finished(): array
    {
        $mt = $this->matchesTable();
        return $this->db->query(
            "SELECT * FROM {$mt} WHERE status = 'terminat'"
        )->fetchAll();
    }

    public function create(array $data): string
    {
        $mt = $this->matchesTable();
        $id = $this->newUuid();
        $stmt = $this->db->prepare(
            "INSERT INTO {$mt} (id, echipa1_id, echipa2_id, scor_echipa1, scor_echipa2, status,
             data_meci, omul_meciului_echipa1_id, omul_meciului_echipa2_id, live_link, match_tag, locatie)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $id,
            $data['echipa1_id'],
            $data['echipa2_id'],
            $data['scor_echipa1'] ?? null,
            $data['scor_echipa2'] ?? null,
            $data['status'] ?? 'programat',
            $data['data_meci'],
            $data['omul_meciului_echipa1_id'] ?? null,
            $data['omul_meciului_echipa2_id'] ?? null,
            $data['live_link'] ?? null,
            $data['match_tag'] ?? 'nedefinit',
            $data['locatie'] ?? null,
        ]);
        return $id;
    }

    public function update(string $id, array $data): void
    {
        $mt = $this->matchesTable();
        $stmt = $this->db->prepare(
            "UPDATE {$mt} SET echipa1_id = ?, echipa2_id = ?, scor_echipa1 = ?, scor_echipa2 = ?,
             status = ?, data_meci = ?, omul_meciului_echipa1_id = ?, omul_meciului_echipa2_id = ?,
             live_link = ?, match_tag = ?, locatie = ?, updated_at = " . $this->nowSql() . ' WHERE id = ?'
        );
        $stmt->execute([
            $data['echipa1_id'],
            $data['echipa2_id'],
            $data['scor_echipa1'] ?? null,
            $data['scor_echipa2'] ?? null,
            $data['status'],
            $data['data_meci'],
            $data['omul_meciului_echipa1_id'] ?? null,
            $data['omul_meciului_echipa2_id'] ?? null,
            $data['live_link'] ?? null,
            $data['match_tag'] ?? 'nedefinit',
            $data['locatie'] ?? null,
            $id,
        ]);
    }

    public function delete(string $id): void
    {
        $mt = $this->matchesTable();
        $this->db->prepare("DELETE FROM {$mt} WHERE id = ?")->execute([$id]);
    }
}
