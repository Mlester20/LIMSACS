-- Migration: add an `archived` flag to `students`
-- Lets the registrar bulk-archive students (e.g. graduated/transferred/dropped)
-- out of the default Student Data list without deleting their records.

ALTER TABLE `students`
  ADD COLUMN `archived` TINYINT(1) NOT NULL DEFAULT 0 AFTER `created_at`;
