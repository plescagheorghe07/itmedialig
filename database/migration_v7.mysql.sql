-- Evenimente meci: gol / cartonaș galben / cartonaș roșu
ALTER TABLE match_goals ADD COLUMN event_type VARCHAR(20) NOT NULL DEFAULT 'goal' AFTER minute;
-- MySQL may not support CHECK on older versions; app enforces values
