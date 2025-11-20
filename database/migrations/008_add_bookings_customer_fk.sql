-- generate_bookings_customer_fk_migration.php
-- Generated: 2025-11-01 19:49:46
-- Add FK for bookings.customer_id -> customers.id with SET NULL and cleanup
ALTER TABLE `bookings` MODIFY `customer_id` bigint(20) unsigned NULL;
UPDATE `bookings` b LEFT JOIN `customers` c ON b.`customer_id` = c.`id` SET b.`customer_id` = NULL WHERE c.`id` IS NULL;
-- SKIP IDX: ix_bookings_customer_id exists
-- SKIP FK: fk_bookings_customer_id exists
