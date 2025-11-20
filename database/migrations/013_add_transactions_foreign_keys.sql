-- 013_add_transactions_foreign_keys.sql
-- Generated: 2025-11-01 21:30:02
-- Add safe foreign keys for transactions relations

-- SKIP TYPE: `transactions`.`customer_id` already bigint(20) unsigned NULL
-- SKIP ORPHANS: `transactions`.`customer_id` has none
ALTER TABLE `transactions` ADD INDEX `ix_transactions_customer_id` (`customer_id`);
ALTER TABLE `transactions` ADD CONSTRAINT `fk_transactions_customer_id` FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- SKIP TYPE: `transactions`.`property_id` already bigint(20) unsigned NULL
-- SKIP ORPHANS: `transactions`.`property_id` has none
ALTER TABLE `transactions` ADD INDEX `ix_transactions_property_id` (`property_id`);
ALTER TABLE `transactions` ADD CONSTRAINT `fk_transactions_property_id` FOREIGN KEY (`property_id`) REFERENCES `properties`(`id`) ON DELETE SET NULL ON UPDATE CASCADE;

