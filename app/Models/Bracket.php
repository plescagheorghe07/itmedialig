<?php

namespace App\Models;

class Bracket extends BaseModel
{
    public function all(): array
    {
        return $this->db->query(
            'SELECT b.*,
                t1.nume AS team_nume, t1.logo_path AS team_logo,
                t2.nume AS team2_nume, t2.logo_path AS team2_logo
             FROM bracket b
             LEFT JOIN teams t1 ON t1.id = b.team_id
             LEFT JOIN teams t2 ON t2.id = b.team2_id
             ORDER BY b.round_index, b.row_index'
        )->fetchAll();
    }

    public function findSlot(int $roundIndex, int $rowIndex): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM bracket WHERE round_index = ? AND row_index = ?'
        );
        $stmt->execute([$roundIndex, $rowIndex]);
        return $stmt->fetch() ?: null;
    }

    public function upsertMatch(
        int $roundIndex,
        int $rowIndex,
        ?string $team1Id,
        ?string $team2Id,
        ?int $score1,
        ?int $score2
    ): void {
        $existing = $this->findSlot($roundIndex, $rowIndex);

        if ($existing) {
            $this->db->prepare(
                'UPDATE bracket SET team_id = ?, team2_id = ?, score = ?, score2 = ?, updated_at = ' . $this->nowSql() . ' WHERE id = ?'
            )->execute([$team1Id, $team2Id, $score1, $score2, $existing['id']]);
        } else {
            $id = $this->newUuid();
            $this->db->prepare(
                'INSERT INTO bracket (id, round_index, row_index, team_id, team2_id, score, score2) VALUES (?, ?, ?, ?, ?, ?, ?)'
            )->execute([$id, $roundIndex, $rowIndex, $team1Id, $team2Id, $score1, $score2]);
        }
    }

    /** @deprecated use upsertMatch */
    public function upsert(int $roundIndex, ?int $rowIndex, ?string $teamId, ?int $score): void
    {
        $this->upsertMatch($roundIndex, $rowIndex ?? 0, $teamId, null, $score, null);
    }

    public function clear(): void
    {
        $this->db->exec('DELETE FROM bracket');
    }
}
