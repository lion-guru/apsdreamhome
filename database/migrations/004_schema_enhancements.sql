-- 004_schema_enhancements.sql
-- Generated: 2025-11-01 20:23:06
-- Create high-value auxiliary tables and relationships (safe checks)

-- addresses
-- SKIP: `addresses` already exists

-- sessions
-- SKIP: `sessions` already exists

-- password_resets
-- SKIP: `password_resets` already exists
-- SKIP: `password_resets`.`user_id` missing

-- favorites
-- SKIP: `favorites` already exists

-- saved_searches
-- SKIP: `saved_searches` already exists
ALTER TABLE `saved_searches` MODIFY `user_id` bigint(20) unsigned NOT NULL;
ALTER TABLE `saved_searches` ADD CONSTRAINT `fk_saved_searches_user_id` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- notifications
-- SKIP: `notifications` already exists
ALTER TABLE `notifications` MODIFY `user_id` bigint(20) unsigned NULL;
ALTER TABLE `notifications` ADD INDEX `ix_notifications_user_id` (`user_id`);
ALTER TABLE `notifications` ADD INDEX `ix_notifications_type` (`type`);
ALTER TABLE `notifications` ADD CONSTRAINT `fk_notifications_user_id` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL ON UPDATE CASCADE;

-- documents
-- SKIP: `documents` already exists
ALTER TABLE `documents` MODIFY `property_id` bigint(20) unsigned NULL;
ALTER TABLE `documents` ADD INDEX `ix_documents_property_id` (`property_id`);
ALTER TABLE `documents` ADD CONSTRAINT `fk_documents_property_id` FOREIGN KEY (`property_id`) REFERENCES `properties`(`id`) ON DELETE SET NULL ON UPDATE CASCADE;

-- property_features & property_feature_map
-- SKIP: `property_features` already exists
CREATE TABLE `property_feature_map` (
  `property_id` bigint(20) unsigned NOT NULL,
  `feature_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`property_id`,`feature_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
