-- Append to schema.sql (v2 tables)

IF NOT EXISTS (SELECT * FROM sys.tables WHERE name = 'season_archives')
CREATE TABLE season_archives (
    id UNIQUEIDENTIFIER NOT NULL DEFAULT NEWID() PRIMARY KEY,
    season_label NVARCHAR(50) NOT NULL,
    tournament_name NVARCHAR(200) NULL,
    snapshot_json NVARCHAR(MAX) NOT NULL,
    stats_json NVARCHAR(MAX) NULL,
    archived_by UNIQUEIDENTIFIER NULL,
    is_published BIT NOT NULL DEFAULT 1,
    archived_at DATETIME2 NOT NULL DEFAULT GETDATE()
);

IF NOT EXISTS (SELECT * FROM sys.tables WHERE name = 'export_files')
CREATE TABLE export_files (
    id UNIQUEIDENTIFIER NOT NULL DEFAULT NEWID() PRIMARY KEY,
    export_type NVARCHAR(50) NOT NULL,
    file_name NVARCHAR(255) NOT NULL,
    file_path NVARCHAR(500) NOT NULL,
    season_label NVARCHAR(50) NULL,
    meta_json NVARCHAR(MAX) NULL,
    created_by UNIQUEIDENTIFIER NULL,
    created_at DATETIME2 NOT NULL DEFAULT GETDATE()
);

IF NOT EXISTS (SELECT 1 FROM settings WHERE setting_key = 'redis_enabled')
INSERT INTO settings (setting_key, setting_value) VALUES ('redis_enabled', '0');

IF NOT EXISTS (SELECT 1 FROM settings WHERE setting_key = 'ws_port')
INSERT INTO settings (setting_key, setting_value) VALUES ('ws_port', '8080');
