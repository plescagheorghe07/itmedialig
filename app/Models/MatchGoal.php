<?php

namespace App\Models;

class MatchGoal extends BaseModel
{
    public function byMatch(string $matchId): array
    {
        $stmt = $this->db->prepare(
            'SELECT g.*, p.nume, p.prenume, p.poza_path, t.nume AS team_nume
             FROM match_goals g
             LEFT JOIN players p ON p.id = g.player_id
             JOIN teams t ON t.id = g.team_id
             WHERE g.match_id = ?
             ORDER BY g.minute ASC, g.created_at ASC'
        );
        $stmt->execute([$matchId]);
        return $stmt->fetchAll();
    }

    public function add(string $matchId, string $teamId, ?string $playerId, ?int $minute): string
    {
        $id = $this->newUuid();
        $stmt = $this->db->prepare(
            'INSERT INTO match_goals (id, match_id, team_id, player_id, minute) VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([$id, $matchId, $teamId, $playerId, $minute]);
        return $id;
    }

    public function delete(string $id): void
    {
        $this->db->prepare('DELETE FROM match_goals WHERE id = ?')->execute([$id]);
    }

    public function countByTeam(string $matchId): array
    {
        $stmt = $this->db->prepare(
            'SELECT team_id, COUNT(*) AS cnt FROM match_goals WHERE match_id = ? GROUP BY team_id'
        );
        $stmt->execute([$matchId]);
        $map = [];
        foreach ($stmt->fetchAll() as $row) {
            $map[$row['team_id']] = (int) $row['cnt'];
        }
        return $map;
    }

    public function find(string $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM match_goals WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }
}
