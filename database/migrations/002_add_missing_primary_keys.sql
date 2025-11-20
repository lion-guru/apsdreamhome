-- 002_add_missing_primary_keys.sql
-- Generated: 2025-11-01 20:03:15
-- Safely add primary keys to critical tables

-- SKIP: `users` already has PRIMARY KEY
-- SKIP: `properties` already has PRIMARY KEY
-- SKIP: `bookings` already has PRIMARY KEY
-- SKIP: `leads` already has PRIMARY KEY
-- SKIP: `payments` already has PRIMARY KEY
-- SKIP: `projects` already has PRIMARY KEY
ALTER TABLE `plots` ADD PRIMARY KEY (`id`);
ALTER TABLE `plots` MODIFY `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT;
-- Applied PK on `plots`.`id`

-- SKIP: `property_images` already has PRIMARY KEY
-- SKIP: `property_types` already has PRIMARY KEY
-- SKIP: `customers` already has PRIMARY KEY