-- Meciuri amicale / fără impact pe clasament
ALTER TABLE `matches` ADD COLUMN exclude_from_standings TINYINT(1) NOT NULL DEFAULT 0 AFTER locatie;
