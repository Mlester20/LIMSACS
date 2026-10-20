-- Migration: create `school_settings` table
-- Simple key/value store for school-wide report settings (e.g. who signs
-- official reports like the Graduates Master List) -- editable in one place
-- instead of hardcoded in every report template.

CREATE TABLE `school_settings` (
  `setting_key` varchar(100) NOT NULL,
  `setting_value` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `school_settings` (`setting_key`, `setting_value`) VALUES
  ('registrar_name', ''),
  ('registrar_title', 'Registrar'),
  ('principal_name', ''),
  ('principal_title', 'School Principal');
