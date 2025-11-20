-- 005_add_additional_foreign_keys.sql
-- Generated: 2025-11-01 20:31:10
-- Add safe additional foreign keys discovered across the schema

-- SKIP: `plots`.`customer_id` or `users` PK missing
-- SKIP: `plots`.`associate_id` or `associates` PK missing
ALTER TABLE `property_visits` MODIFY `property_id` bigint(20) unsigned NOT NULL;
-- property_visits.property_id -> properties.id
ALTER TABLE `property_visits` ADD INDEX `ix_property_visits_property_id` (`property_id`);
ALTER TABLE `property_visits` ADD CONSTRAINT `fk_property_visits_property_id` FOREIGN KEY (`property_id`) REFERENCES `properties`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE;

ALTER TABLE `property_visits` MODIFY `customer_id` bigint(20) unsigned NOT NULL;
-- property_visits.customer_id -> users.id
ALTER TABLE `property_visits` ADD INDEX `ix_property_visits_customer_id` (`customer_id`);
ALTER TABLE `property_visits` ADD CONSTRAINT `fk_property_visits_customer_id` FOREIGN KEY (`customer_id`) REFERENCES `users`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE;

ALTER TABLE `property_visits` MODIFY `created_by` bigint(20) unsigned NULL;
-- property_visits.created_by -> users.id
ALTER TABLE `property_visits` ADD INDEX `ix_property_visits_created_by` (`created_by`);
ALTER TABLE `property_visits` ADD CONSTRAINT `fk_property_visits_created_by` FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `mlm_commissions` MODIFY `associate_id` bigint(20) unsigned NOT NULL;
-- mlm_commissions.associate_id -> associates.id
ALTER TABLE `mlm_commissions` ADD INDEX `ix_mlm_commissions_associate_id` (`associate_id`);
ALTER TABLE `mlm_commissions` ADD CONSTRAINT `fk_mlm_commissions_associate_id` FOREIGN KEY (`associate_id`) REFERENCES `associates`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE;

-- SKIP: `mlm_commissions`.`booking_id` or `bookings` PK missing
ALTER TABLE `leads` MODIFY `assigned_to` bigint(20) unsigned NULL;
-- leads.assigned_to -> users.id
ALTER TABLE `leads` ADD INDEX `ix_leads_assigned_to` (`assigned_to`);
ALTER TABLE `leads` ADD CONSTRAINT `fk_leads_assigned_to` FOREIGN KEY (`assigned_to`) REFERENCES `users`(`id`) ON DELETE SET NULL ON UPDATE CASCADE;

-- SKIP: `properties`.`type_id` or `property_types` PK missing
-- associates.user_id -> users.id
ALTER TABLE `associates` ADD INDEX `ix_associates_user_id` (`user_id`);
ALTER TABLE `associates` ADD CONSTRAINT `fk_associates_user_id` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE;

-- SKIP: `documents`.`owner_user_id` or `users` PK missing
-- SKIP: `projects`.`land_purchase_id` or `land_purchases` PK missing