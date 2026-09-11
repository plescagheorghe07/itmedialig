-- Evenimente meci: gol / cartonaș galben / cartonaș roșu
IF COL_LENGTH('match_goals', 'event_type') IS NULL
ALTER TABLE match_goals ADD event_type NVARCHAR(20) NOT NULL CONSTRAINT DF_match_goals_event_type DEFAULT 'goal';
