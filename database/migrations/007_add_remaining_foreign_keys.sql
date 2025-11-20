-- 007_add_remaining_foreign_keys.sql
-- Generated: 2025-11-01 20:44:23
-- Add safe remaining foreign keys across bookings, payments, plots, and transactions

-- SKIP FK: `fk_bookings_customer_id` has 16 orphan rows and delete rule is CASCADE
-- SKIP IDX: `ix_bookings_property_id` exists
-- SKIP FK: `fk_bookings_property_id` exists
-- SKIP IDX: `ix_payments_booking_id` exists
-- SKIP FK: `fk_payments_booking_id` exists
UPDATE `payments` t LEFT JOIN `customers` r ON t.`customer_id` = r.`id` SET t.`customer_id` = NULL WHERE r.`id` IS NULL;
-- SKIP IDX: `ix_payments_customer_id` exists
ALTER TABLE `payments` ADD CONSTRAINT `fk_payments_customer_id` FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE SET NULL ON UPDATE CASCADE;
-- SKIP: `plots`.`project_id` or `projects` PK missing
-- SKIP: `plots`.`customer_id` or `users` PK missing
-- SKIP: `plots`.`associate_id` or `associates` PK missing
-- SKIP: `transactions`.`customer_id` or `customers` PK missing
-- SKIP: `transactions`.`property_id` or `properties` PK missing