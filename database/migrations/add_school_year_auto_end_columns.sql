-- Migration: add auto-end tracking columns to `school_year`
-- Supports automatically archiving a school year once its end_date has passed.

ALTER TABLE `school_year`
  ADD COLUMN `ended_at` DATETIME NULL AFTER `status`,
  ADD COLUMN `auto_ended` TINYINT(1) NOT NULL DEFAULT 0 AFTER `ended_at`;
