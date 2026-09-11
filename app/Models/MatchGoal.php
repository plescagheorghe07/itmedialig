<?php

namespace App\Models;

class MatchGoal extends BaseModel
{
    public const TYPE_GOAL = 'goal';
    public const TYPE_YELLOW = 'yellow_card';
    public const TYPE_RED = 'red_card';

    public const TYPES = [
        self::TYPE_GOAL,
        self::TYPE_YELLOW,
        self::TYPE_RED,
    ];

    public function byMatch(string $matchId): array
    {
        $stmt = $this->db->prepare(
            'SELECT g.*, p.nume, p.prenume, p.poza_path, t.nume AS team_nume
             FROM match_goals g
             LEFT JOIN players p ON p.id = g.player_id
             JOIN teams t ON t.id = g.team_id
             WHERE g.match_id = ?
             ORDER BY COALESCE(g.minute, 999) ASC, g.created_at ASC'
        );
        $stmt->execute([$matchId]);
        return array_map([$this, 'normalize'], $stmt->fetchAll());
    }

    /** @return array<string, list<array>> */
    public function byMatchIds(array $matchIds): array
    {
        if ($matchIds === []) {
            return [];
        }
        $placeholders = implode(',', array_fill(0, count($matchIds), '?'));
        $stmt = $this->db->prepare(
            "SELECT g.*, p.nume, p.prenume, p.poza_path, t.nume AS team_nume
             FROM match_goals g
             LEFT JOIN players p ON p.id = g.player_id
             JOIN teams t ON t.id = g.team_id
             WHERE g.match_id IN ({$placeholders})
             ORDER BY COALESCE(g.minute, 999) ASC, g.created_at ASC"
        );
        $stmt->execute(array_values($matchIds));
        $map = [];
        foreach ($stmt->fetchAll() as $row) {
            $row = $this->normalize($row);
            $map[$row['match_id']][] = $row;
        }
        return $map;
    }

    public function add(string $matchId, string $teamId, ?string $playerId, ?int $minute, string $eventType = self::TYPE_GOAL): string
    {
        if (!in_array($eventType, self::TYPES, true)) {
            throw new \InvalidArgumentException('Tip eveniment invalid.');
        }
        $id = $this->newUuid();
        $stmt = $this->db->prepare(
            'INSERT INTO match_goals (id, match_id, team_id, player_id, minute, event_type) VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([$id, $matchId, $teamId, $playerId, $minute, $eventType]);
        return $id;
    }

    public function delete(string $id): void
    {
        $this->db->prepare('DELETE FROM match_goals WHERE id = ?')->execute([$id]);
    }

    public function countGoalsByTeam(string $matchId): array
    {
        $stmt = $this->db->prepare(
            "SELECT team_id, COUNT(*) AS cnt FROM match_goals
             WHERE match_id = ? AND COALESCE(event_type, 'goal') = 'goal'
             GROUP BY team_id"
        );
        $stmt->execute([$matchId]);
        $map = [];
        foreach ($stmt->fetchAll() as $row) {
            $map[$row['team_id']] = (int) $row['cnt'];
        }
        return $map;
    }

    /** @deprecated Use countGoalsByTeam */
    public function countByTeam(string $matchId): array
    {
        return $this->countGoalsByTeam($matchId);
    }

    public function find(string $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM match_goals WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ? $this->normalize($row) : null;
    }

    public function byPlayer(string $playerId, ?string $eventType = null): array
    {
        $mt = $this->matchesTable();
        $sql = "SELECT g.*, t.nume AS team_nume, m.data_meci, m.status,
                    t1.nume AS echipa1_nume, t2.nume AS echipa2_nume
             FROM match_goals g
             JOIN teams t ON t.id = g.team_id
             JOIN {$mt} m ON m.id = g.match_id
             JOIN teams t1 ON t1.id = m.echipa1_id
             JOIN teams t2 ON t2.id = m.echipa2_id
             WHERE g.player_id = ?";
        $params = [$playerId];
        if ($eventType) {
            $sql .= ' AND COALESCE(g.event_type, \'goal\') = ?';
            $params[] = $eventType;
        }
        $sql .= ' ORDER BY m.data_meci DESC, g.minute ASC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return array_map([$this, 'normalize'], $stmt->fetchAll());
    }

    public function countByPlayerIds(array $playerIds, string $eventType = self::TYPE_GOAL): array
    {
        if ($playerIds === []) {
            return [];
        }
        $placeholders = implode(',', array_fill(0, count($playerIds), '?'));
        $stmt = $this->db->prepare(
            "SELECT player_id, COUNT(*) AS goals FROM match_goals
             WHERE player_id IN ({$placeholders}) AND COALESCE(event_type, 'goal') = ?
             GROUP BY player_id"
        );
        $stmt->execute([...array_values($playerIds), $eventType]);
        $map = [];
        foreach ($stmt->fetchAll() as $row) {
            $map[$row['player_id']] = (int) $row['goals'];
        }
        return $map;
    }

    private function normalize(array $row): array
    {
        $row['event_type'] = $row['event_type'] ?? self::TYPE_GOAL;
        if ($row['event_type'] === '' || $row['event_type'] === null) {
            $row['event_type'] = self::TYPE_GOAL;
        }
        $row['player_name'] = trim(($row['prenume'] ?? '') . ' ' . ($row['nume'] ?? ''));
        return $row;
    }
}
