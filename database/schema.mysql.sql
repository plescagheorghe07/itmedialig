-- Trofeu Hub - Schema MySQL / MariaDB (XAMPP)

CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT NULL,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS admin_users (
    id CHAR(36) NOT NULL PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    display_name VARCHAR(200) NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    last_login_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS teams (
    id CHAR(36) NOT NULL PRIMARY KEY,
    nume VARCHAR(200) NOT NULL,
    logo_path VARCHAR(500) NULL,
    grupa VARCHAR(50) NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    manage_token CHAR(64) NULL,
    manage_token_created_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_teams_grupa (grupa),
    INDEX idx_teams_nume (nume),
    UNIQUE INDEX idx_teams_manage_token (manage_token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS players (
    id CHAR(36) NOT NULL PRIMARY KEY,
    nume VARCHAR(100) NOT NULL,
    prenume VARCHAR(100) NOT NULL,
    poza_path VARCHAR(500) NULL,
    id_echipa CHAR(36) NULL,
    numar_tricou INT NULL,
    pozitie VARCHAR(50) NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_players_echipa (id_echipa),
    CONSTRAINT fk_players_echipa FOREIGN KEY (id_echipa) REFERENCES teams(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `matches` (
    id CHAR(36) NOT NULL PRIMARY KEY,
    echipa1_id CHAR(36) NOT NULL,
    echipa2_id CHAR(36) NOT NULL,
    scor_echipa1 INT NULL,
    scor_echipa2 INT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'programat',
    data_meci DATETIME NOT NULL,
    omul_meciului_echipa1_id CHAR(36) NULL,
    omul_meciului_echipa2_id CHAR(36) NULL,
    live_link VARCHAR(500) NULL,
    match_tag VARCHAR(50) NULL DEFAULT 'nedefinit',
    locatie VARCHAR(200) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_matches_data (data_meci),
    INDEX idx_matches_status (status),
    INDEX idx_matches_echipa1 (echipa1_id),
    INDEX idx_matches_echipa2 (echipa2_id),
    CONSTRAINT fk_matches_echipa1 FOREIGN KEY (echipa1_id) REFERENCES teams(id),
    CONSTRAINT fk_matches_echipa2 FOREIGN KEY (echipa2_id) REFERENCES teams(id),
    CONSTRAINT fk_matches_om1 FOREIGN KEY (omul_meciului_echipa1_id) REFERENCES players(id) ON DELETE SET NULL,
    CONSTRAINT fk_matches_om2 FOREIGN KEY (omul_meciului_echipa2_id) REFERENCES players(id) ON DELETE SET NULL,
    CONSTRAINT chk_matches_status CHECK (status IN ('programat', 'se_joaca', 'terminat')),
    CONSTRAINT chk_matches_tag CHECK (match_tag IN ('nedefinit', 'grupa', 'optimi', 'sferturi', 'semi-finala', 'finala_mica', 'finala_mare'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS bracket (
    id CHAR(36) NOT NULL PRIMARY KEY,
    round_index INT NOT NULL,
    row_index INT NULL,
    team_id CHAR(36) NULL,
    score INT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_bracket_round (round_index, row_index),
    CONSTRAINT fk_bracket_team FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS history_posts (
    id CHAR(36) NOT NULL PRIMARY KEY,
    titlu VARCHAR(300) NOT NULL,
    descriere TEXT NULL,
    is_published TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS history_images (
    id CHAR(36) NOT NULL PRIMARY KEY,
    post_id CHAR(36) NOT NULL,
    image_path VARCHAR(500) NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_history_images_post (post_id),
    CONSTRAINT fk_history_images_post FOREIGN KEY (post_id) REFERENCES history_posts(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO settings (setting_key, setting_value) VALUES ('tournament_name', 'Liga de Fotbal CEITI');
INSERT IGNORE INTO settings (setting_key, setting_value) VALUES ('season', '2025-2026');
INSERT IGNORE INTO settings (setting_key, setting_value) VALUES ('points_win', '3');
INSERT IGNORE INTO settings (setting_key, setting_value) VALUES ('points_draw', '1');
