<?php

namespace App\Models;

use App\Core\DbHelper;

class Settings extends BaseModel
{
    public function get(string $key, ?string $default = null): ?string
    {
        $stmt = $this->db->prepare('SELECT setting_value FROM settings WHERE setting_key = ?');
        $stmt->execute([$key]);
        $row = $stmt->fetch();
        return $row ? $row['setting_value'] : $default;
    }

    public function all(): array
    {
        $rows = $this->db->query('SELECT setting_key, setting_value FROM settings')->fetchAll();
        $out = [];
        foreach ($rows as $row) {
            $out[$row['setting_key']] = $row['setting_value'];
        }
        return $out;
    }

    public function set(string $key, string $value): void
    {
        if (DbHelper::isMysql($this->db)) {
            $stmt = $this->db->prepare(
                'INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)
                 ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW()'
            );
            $stmt->execute([$key, $value]);
            return;
        }

        $stmt = $this->db->prepare(
            'MERGE settings AS t USING (SELECT ? AS setting_key, ? AS setting_value) AS s
             ON t.setting_key = s.setting_key
             WHEN MATCHED THEN UPDATE SET setting_value = s.setting_value, updated_at = GETDATE()
             WHEN NOT MATCHED THEN INSERT (setting_key, setting_value) VALUES (s.setting_key, s.setting_value);'
        );
        $stmt->execute([$key, $value]);
    }

    public function setMany(array $pairs): void
    {
        foreach ($pairs as $key => $value) {
            $this->set($key, (string) $value);
        }
    }
}
