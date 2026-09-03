-- Migration v2 — MySQL / MariaDB

CREATE TABLE IF NOT EXISTS season_archives (
    id CHAR(36) NOT NULL PRIMARY KEY,
    season_label VARCHAR(50) NOT NULL,
    tournament_name VARCHAR(200) NULL,
    snapshot_json LONGTEXT NOT NULL,
    stats_json LONGTEXT NULL,
    archived_by CHAR(36) NULL,
    is_published TINYINT(1) NOT NULL DEFAULT 1,
    archived_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_season_archives_label (season_label),
    INDEX idx_season_archives_date (archived_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS export_files (
    id CHAR(36) NOT NULL PRIMARY KEY,
    export_type VARCHAR(50) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    season_label VARCHAR(50) NULL,
    meta_json LONGTEXT NULL,
    created_by CHAR(36) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_export_files_type (export_type),
    INDEX idx_export_files_date (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO settings (setting_key, setting_value) VALUES ('redis_enabled', '0');
INSERT IGNORE INTO settings (setting_key, setting_value) VALUES ('ws_port', '8080');
