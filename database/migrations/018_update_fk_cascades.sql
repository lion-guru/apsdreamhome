-- Update FK cascade policies based on docs/FK_CASCADE_POLICY.md
-- Applies safer deletion rules for dependent and user-owned records

-- property_visits -> users (customer_id): recommend CASCADE
ALTER TABLE `property_visits` DROP FOREIGN KEY `fk_property_visits_customer_id`;
ALTER TABLE `property_visits` ADD CONSTRAINT `fk_property_visits_customer_id`
  FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`)
  ON DELETE CASCADE ON UPDATE CASCADE;

-- property_visits -> properties (property_id): recommend CASCADE
ALTER TABLE `property_visits` DROP FOREIGN KEY `fk_property_visits_property_id`;
ALTER TABLE `property_visits` ADD CONSTRAINT `fk_property_visits_property_id`
  FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`)
  ON DELETE CASCADE ON UPDATE CASCADE;

-- saved_searches -> users (user_id): recommend RESTRICT to avoid mass deletions
ALTER TABLE `saved_searches` DROP FOREIGN KEY `fk_saved_searches_user_id`;
ALTER TABLE `saved_searches` ADD CONSTRAINT `fk_saved_searches_user_id`
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
  ON DELETE RESTRICT ON UPDATE CASCADE;

-- sessions -> users (user_id): recommend RESTRICT (session cleanup handled by app)
ALTER TABLE `sessions` DROP FOREIGN KEY `fk_sessions_user_id`;
ALTER TABLE `sessions` ADD CONSTRAINT `fk_sessions_user_id`
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
  ON DELETE RESTRICT ON UPDATE CASCADE;

-- NOTE: transactions -> customers suggests SET NULL, but requires nullable FK column.
-- Handle separately after confirming nullability to avoid migration failure.

