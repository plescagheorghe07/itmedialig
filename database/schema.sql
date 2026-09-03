-- Trofeu Hub - Schema SQL Server (îmbunătățită)
-- Compatibil cu SQL Server 2016+

IF NOT EXISTS (SELECT * FROM sys.tables WHERE name = 'settings')
CREATE TABLE settings (
    id INT IDENTITY(1,1) PRIMARY KEY,
    setting_key NVARCHAR(100) NOT NULL UNIQUE,
    setting_value NVARCHAR(MAX) NULL,
    updated_at DATETIME2 NOT NULL DEFAULT GETDATE()
);

IF NOT EXISTS (SELECT * FROM sys.tables WHERE name = 'admin_users')
CREATE TABLE admin_users (
    id UNIQUEIDENTIFIER NOT NULL DEFAULT NEWID() PRIMARY KEY,
    username NVARCHAR(100) NOT NULL UNIQUE,
    password_hash NVARCHAR(255) NOT NULL,
    display_name NVARCHAR(200) NOT NULL,
    is_active BIT NOT NULL DEFAULT 1,
    last_login_at DATETIME2 NULL,
    created_at DATETIME2 NOT NULL DEFAULT GETDATE()
);

IF NOT EXISTS (SELECT * FROM sys.tables WHERE name = 'teams')
CREATE TABLE teams (
    id UNIQUEIDENTIFIER NOT NULL DEFAULT NEWID() PRIMARY KEY,
    nume NVARCHAR(200) NOT NULL,
    logo_path NVARCHAR(500) NULL,
    grupa NVARCHAR(50) NOT NULL,
    is_active BIT NOT NULL DEFAULT 1,
    created_at DATETIME2 NOT NULL DEFAULT GETDATE(),
    updated_at DATETIME2 NOT NULL DEFAULT GETDATE()
);
CREATE INDEX idx_teams_grupa ON teams(grupa);
CREATE INDEX idx_teams_nume ON teams(nume);

IF NOT EXISTS (SELECT * FROM sys.tables WHERE name = 'players')
CREATE TABLE players (
    id UNIQUEIDENTIFIER NOT NULL DEFAULT NEWID() PRIMARY KEY,
    nume NVARCHAR(100) NOT NULL,
    prenume NVARCHAR(100) NOT NULL,
    poza_path NVARCHAR(500) NULL,
    id_echipa UNIQUEIDENTIFIER NULL REFERENCES teams(id) ON DELETE SET NULL,
    numar_tricou INT NULL,
    pozitie NVARCHAR(50) NULL,
    is_active BIT NOT NULL DEFAULT 1,
    created_at DATETIME2 NOT NULL DEFAULT GETDATE(),
    updated_at DATETIME2 NOT NULL DEFAULT GETDATE()
);
CREATE INDEX idx_players_echipa ON players(id_echipa);

IF NOT EXISTS (SELECT * FROM sys.tables WHERE name = 'matches')
CREATE TABLE matches (
    id UNIQUEIDENTIFIER NOT NULL DEFAULT NEWID() PRIMARY KEY,
    echipa1_id UNIQUEIDENTIFIER NOT NULL REFERENCES teams(id) ON DELETE NO ACTION,
    echipa2_id UNIQUEIDENTIFIER NOT NULL REFERENCES teams(id) ON DELETE NO ACTION,
    scor_echipa1 INT NULL,
    scor_echipa2 INT NULL,
    status NVARCHAR(20) NOT NULL DEFAULT 'programat'
        CHECK (status IN ('programat', 'se_joaca', 'terminat')),
    data_meci DATETIME2 NOT NULL,
    omul_meciului_echipa1_id UNIQUEIDENTIFIER NULL REFERENCES players(id) ON DELETE SET NULL,
    omul_meciului_echipa2_id UNIQUEIDENTIFIER NULL REFERENCES players(id) ON DELETE SET NULL,
    live_link NVARCHAR(500) NULL,
    match_tag NVARCHAR(50) NULL DEFAULT 'nedefinit'
        CHECK (match_tag IN ('nedefinit', 'grupa', 'optimi', 'sferturi', 'semi-finala', 'finala_mica', 'finala_mare')),
    locatie NVARCHAR(200) NULL,
    created_at DATETIME2 NOT NULL DEFAULT GETDATE(),
    updated_at DATETIME2 NOT NULL DEFAULT GETDATE()
);
CREATE INDEX idx_matches_data ON matches(data_meci);
CREATE INDEX idx_matches_status ON matches(status);
CREATE INDEX idx_matches_echipa1 ON matches(echipa1_id);
CREATE INDEX idx_matches_echipa2 ON matches(echipa2_id);

IF NOT EXISTS (SELECT * FROM sys.tables WHERE name = 'bracket')
CREATE TABLE bracket (
    id UNIQUEIDENTIFIER NOT NULL DEFAULT NEWID() PRIMARY KEY,
    round_index INT NOT NULL,
    row_index INT NULL,
    team_id UNIQUEIDENTIFIER NULL REFERENCES teams(id) ON DELETE SET NULL,
    score INT NULL,
    created_at DATETIME2 NOT NULL DEFAULT GETDATE(),
    updated_at DATETIME2 NOT NULL DEFAULT GETDATE()
);
CREATE INDEX idx_bracket_round ON bracket(round_index, row_index);

IF NOT EXISTS (SELECT * FROM sys.tables WHERE name = 'history_posts')
CREATE TABLE history_posts (
    id UNIQUEIDENTIFIER NOT NULL DEFAULT NEWID() PRIMARY KEY,
    titlu NVARCHAR(300) NOT NULL,
    descriere NVARCHAR(MAX) NULL,
    is_published BIT NOT NULL DEFAULT 1,
    created_at DATETIME2 NOT NULL DEFAULT GETDATE(),
    updated_at DATETIME2 NOT NULL DEFAULT GETDATE()
);

IF NOT EXISTS (SELECT * FROM sys.tables WHERE name = 'history_images')
CREATE TABLE history_images (
    id UNIQUEIDENTIFIER NOT NULL DEFAULT NEWID() PRIMARY KEY,
    post_id UNIQUEIDENTIFIER NOT NULL REFERENCES history_posts(id) ON DELETE CASCADE,
    image_path NVARCHAR(500) NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    created_at DATETIME2 NOT NULL DEFAULT GETDATE()
);
CREATE INDEX idx_history_images_post ON history_images(post_id);

-- Setări implicite
IF NOT EXISTS (SELECT 1 FROM settings WHERE setting_key = 'tournament_name')
INSERT INTO settings (setting_key, setting_value) VALUES ('tournament_name', 'Liga de Fotbal CEITI');

IF NOT EXISTS (SELECT 1 FROM settings WHERE setting_key = 'season')
INSERT INTO settings (setting_key, setting_value) VALUES ('season', '2025-2026');

IF NOT EXISTS (SELECT 1 FROM settings WHERE setting_key = 'points_win')
INSERT INTO settings (setting_key, setting_value) VALUES ('points_win', '3');

IF NOT EXISTS (SELECT 1 FROM settings WHERE setting_key = 'points_draw')
INSERT INTO settings (setting_key, setting_value) VALUES ('points_draw', '1');
