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

    public function findByManageToken(string $token): ?array
    {
        if ($token === '' || strlen($token) < 32) {
            return null;
        }
        $stmt = $this->db->prepare(
            'SELECT * FROM teams WHERE manage_token = ? AND is_active = 1 LIMIT 1'
        );
        $stmt->execute([$token]);
        return $stmt->fetch() ?: null;
    }

    public function create(array $data): string
    {
        $id = $this->newUuid();
        $token = $this->generateManageToken();
        $stmt = $this->db->prepare(
            'INSERT INTO teams (id, nume, logo_path, grupa, manage_token, manage_token_created_at)
             VALUES (?, ?, ?, ?, ?, ' . $this->nowSql() . ')'
        );
        $stmt->execute([
            $id,
            $data['nume'],
            $data['logo_path'] ?? null,
            $data['grupa'],
            $token,
        ]);
        return $id;
    }

    public function update(string $id, array $data): void
    {
        $stmt = $this->db->prepare(
            'UPDATE teams SET nume = ?, logo_path = ?, grupa = ?, updated_at = ' . $this->nowSql() . ' WHERE id = ?'
        );
        $stmt->execute([$data['nume'], $data['logo_path'] ?? null, $data['grupa'], $id]);
    }

    /** Update permis din portalul echipei (fără a schimba grupa). */
    public function updateSelfService(string $id, array $data): void
    {
        $stmt = $this->db->prepare(
            'UPDATE teams SET nume = ?, logo_path = ?, updated_at = ' . $this->nowSql() . ' WHERE id = ?'
        );
        $stmt->execute([$data['nume'], $data['logo_path'] ?? null, $id]);
    }

    public function ensureManageToken(string $id): string
    {
        $team = $this->find($id);
        if (!$team) {
            throw new \InvalidArgumentException('Echipă negăsită.');
        }
        if (!empty($team['manage_token'])) {
            return $team['manage_token'];
        }
        return $this->regenerateManageToken($id);
    }

    public function regenerateManageToken(string $id): string
    {
        $token = $this->generateManageToken();
        $stmt = $this->db->prepare(
            'UPDATE teams SET manage_token = ?, manage_token_created_at = ' . $this->nowSql() . ', updated_at = ' . $this->nowSql() . ' WHERE id = ?'
        );
        $stmt->execute([$token, $id]);
        return $token;
    }

    public function generateManageToken(): string
    {
        // 64 hex chars = 256 bits — imposibil de ghicit prin brute-force
        return bin2hex(random_bytes(32));
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
