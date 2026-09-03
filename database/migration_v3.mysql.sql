CREATE TABLE IF NOT EXISTS match_goals (
    id CHAR(36) NOT NULL PRIMARY KEY,
    match_id CHAR(36) NOT NULL,
    team_id CHAR(36) NOT NULL,
    player_id CHAR(36) NULL,
    minute INT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_match_goals_match (match_id),
    CONSTRAINT fk_match_goals_match FOREIGN KEY (match_id) REFERENCES `matches`(id) ON DELETE CASCADE,
    CONSTRAINT fk_match_goals_team FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE,
    CONSTRAINT fk_match_goals_player FOREIGN KEY (player_id) REFERENCES players(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
