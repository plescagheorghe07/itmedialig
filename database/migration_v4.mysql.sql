-- Bracket: meciuri pereche (team1 vs team2)
ALTER TABLE bracket ADD COLUMN team2_id CHAR(36) NULL AFTER team_id;
ALTER TABLE bracket ADD COLUMN score2 INT NULL AFTER score;
ALTER TABLE bracket ADD CONSTRAINT fk_bracket_team2 FOREIGN KEY (team2_id) REFERENCES teams(id) ON DELETE SET NULL;
