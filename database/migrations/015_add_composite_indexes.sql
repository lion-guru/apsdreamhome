-- Auto-generated composite index migration
-- Adds multi-column indexes for common query patterns, skipping existing ones.

ALTER TABLE `bookings` ADD INDEX `idx_bookings_status_date` (`status`, `booking_date` DESC);

ALTER TABLE `bookings` ADD INDEX `idx_bookings_property_date` (`property_id`, `booking_date` DESC);

ALTER TABLE `bookings` ADD INDEX `idx_bookings_customer_created` (`customer_id`, `created_at` DESC);

ALTER TABLE `commission_transactions` ADD INDEX `idx_commission_transactions_associate_date` (`associate_id`, `transaction_date` DESC);

ALTER TABLE `commission_transactions` ADD INDEX `idx_commission_transactions_status_date` (`status`, `transaction_date` DESC);

ALTER TABLE `properties` ADD INDEX `idx_properties_status_type` (`status`, `type`);

ALTER TABLE `property_visits` ADD INDEX `idx_property_visits_property_date` (`property_id`, `visit_date` DESC);

ALTER TABLE `property_visits` ADD INDEX `idx_property_visits_customer_date` (`customer_id`, `visit_date` DESC);

ALTER TABLE `property_visits` ADD INDEX `idx_property_visits_status_date` (`status`, `visit_date` DESC);

ALTER TABLE `users` ADD INDEX `idx_users_email_status` (`email`, `status`);

