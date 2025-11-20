-- 012_add_plots_foreign_keys.sql
-- Generated: 2025-11-01 21:29:35
-- Add safe foreign keys for plots relations

-- SKIP TYPE: `plots`.`project_id` already int(11) NULL
-- SKIP ORPHANS: `plots`.`project_id` has none
ALTER TABLE `plots` ADD INDEX `ix_plots_project_id` (`project_id`);
ALTER TABLE `plots` ADD CONSTRAINT `fk_plots_project_id` FOREIGN KEY (`project_id`) REFERENCES `projects`(`id`) ON DELETE SET NULL ON UPDATE CASCADE;

-- SKIP TYPE: `plots`.`customer_id` already bigint(20) unsigned NULL
-- SKIP ORPHANS: `plots`.`customer_id` has none
ALTER TABLE `plots` ADD INDEX `ix_plots_customer_id` (`customer_id`);
ALTER TABLE `plots` ADD CONSTRAINT `fk_plots_customer_id` FOREIGN KEY (`customer_id`) REFERENCES `users`(`id`) ON DELETE SET NULL ON UPDATE CASCADE;

-- SKIP TYPE: `plots`.`associate_id` already bigint(20) unsigned NULL
-- SKIP ORPHANS: `plots`.`associate_id` has none
ALTER TABLE `plots` ADD INDEX `ix_plots_associate_id` (`associate_id`);
ALTER TABLE `plots` ADD CONSTRAINT `fk_plots_associate_id` FOREIGN KEY (`associate_id`) REFERENCES `associates`(`id`) ON DELETE SET NULL ON UPDATE CASCADE;

