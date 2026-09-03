-- Team self-management secure links
ALTER TABLE teams ADD COLUMN manage_token CHAR(64) NULL UNIQUE AFTER is_active;
ALTER TABLE teams ADD COLUMN manage_token_created_at DATETIME NULL AFTER manage_token;
CREATE INDEX idx_teams_manage_token ON teams(manage_token);
