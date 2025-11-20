-- 011_add_missing_fk_columns.sql
-- Generated: 2025-11-01 21:25:26
-- Add missing FK columns with types aligned to referenced PKs

ALTER TABLE `plots` ADD COLUMN `project_id` int(11) NULL;

ALTER TABLE `plots` ADD COLUMN `customer_id` bigint(20) unsigned NULL;

ALTER TABLE `plots` ADD COLUMN `associate_id` bigint(20) unsigned NULL;

ALTER TABLE `transactions` ADD COLUMN `customer_id` bigint(20) unsigned NULL;

ALTER TABLE `transactions` ADD COLUMN `property_id` bigint(20) unsigned NULL;

