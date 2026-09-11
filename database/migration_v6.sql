-- Meciuri amicale / fără impact pe clasament
IF COL_LENGTH('matches', 'exclude_from_standings') IS NULL
ALTER TABLE matches ADD exclude_from_standings BIT NOT NULL DEFAULT 0;
