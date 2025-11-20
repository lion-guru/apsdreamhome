-- 003_add_core_foreign_keys.sql
-- Generated: 2025-11-01 20:11:28
-- Add safe core foreign keys for critical integrity

-- bookings.property_id -> properties.id
-- SKIP INDEX: `ix_bookings_property_id` already exists
-- SKIP FK: `fk_bookings_property_id` already exists

-- SKIP: `bookings`.`user_id` or `users` PK missing
-- bookings.customer_id -> users.id
-- SKIP INDEX: `ix_bookings_customer_id` already exists
-- SKIP FK: `fk_bookings_customer_id` already exists

ALTER TABLE `property_images` MODIFY `property_id` bigint(20) unsigned NOT NULL;
-- property_images.property_id -> properties.id
-- SKIP INDEX: `ix_property_images_property_id` already exists
ALTER TABLE `property_images` ADD CONSTRAINT `fk_property_images_property_id` FOREIGN KEY (`property_id`) REFERENCES `properties`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE;

-- SKIP: `property_inquiries` or `properties` missing
-- SKIP: `leads`.`user_id` or `users` PK missing
ALTER TABLE `payments` MODIFY `booking_id` bigint(20) unsigned NULL;
-- payments.booking_id -> bookings.id
ALTER TABLE `payments` ADD INDEX `ix_payments_booking_id` (`booking_id`);
ALTER TABLE `payments` ADD CONSTRAINT `fk_payments_booking_id` FOREIGN KEY (`booking_id`) REFERENCES `bookings`(`id`) ON DELETE SET NULL ON UPDATE CASCADE;

-- SKIP: `plots`.`project_id` or `projects` PK missing
-- SKIP: `plot_bookings` or `plots` missing
-- SKIP: `plot_bookings` or `bookings` missing