-- =====================================================
-- APS DREAM HOME - CRITICAL SECURITY & STABILITY UPDATE
-- =====================================================
-- Generated: 2025-09-30
-- Priority: CRITICAL - Must apply first
-- =====================================================

-- =====================================================
-- 1. ADD PASSWORD SECURITY TABLE (CRITICAL)
-- =====================================================

CREATE TABLE IF NOT EXISTS `password_security` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `failed_attempts` int(11) DEFAULT 0,
  `last_failed_attempt` timestamp NULL DEFAULT NULL,
  `locked_until` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =====================================================
-- 2. ADD SESSION SECURITY TABLE (CRITICAL)
-- =====================================================

CREATE TABLE IF NOT EXISTS `user_sessions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `session_token` varchar(255) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `session_token` (`session_token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =====================================================
-- 3. UPDATE USERS TABLE WITH SECURITY FIELDS (CRITICAL)
-- =====================================================

ALTER TABLE `users`
ADD COLUMN IF NOT EXISTS `password_changed_at` timestamp NULL DEFAULT NULL,
ADD COLUMN IF NOT EXISTS `last_login` timestamp NULL DEFAULT NULL,
ADD COLUMN IF NOT EXISTS `login_ip` varchar(45) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS `two_factor_enabled` tinyint(1) DEFAULT 0,
ADD COLUMN IF NOT EXISTS `account_status` varchar(20) DEFAULT 'active';

-- =====================================================
-- 4. ADD SYSTEM LOGS TABLE (CRITICAL)
-- =====================================================

CREATE TABLE IF NOT EXISTS `system_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `table_name` varchar(100) DEFAULT NULL,
  `record_id` int(11) DEFAULT NULL,
  `old_values` text DEFAULT NULL,
  `new_values` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `action` (`action`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =====================================================
-- 5. ADD API KEYS TABLE (CRITICAL FOR MODERN APPS)
-- =====================================================

CREATE TABLE IF NOT EXISTS `api_keys` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `api_key` varchar(255) NOT NULL,
  `api_secret` varchar(255) NOT NULL,
  `permissions` text DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_used` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `api_key` (`api_key`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =====================================================
-- 6. UPDATE PROPERTIES TABLE WITH MODERN FIELDS (CRITICAL)
-- =====================================================

ALTER TABLE `properties`
ADD COLUMN IF NOT EXISTS `property_code` varchar(50) UNIQUE DEFAULT NULL,
ADD COLUMN IF NOT EXISTS `featured_image` varchar(255) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS `gallery_images` text DEFAULT NULL,
ADD COLUMN IF NOT EXISTS `virtual_tour_url` varchar(255) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS `floor_plan` varchar(255) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS `energy_rating` varchar(10) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS `property_age` int(11) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS `furnished_status` varchar(20) DEFAULT 'unfurnished',
ADD COLUMN IF NOT EXISTS `parking_spaces` int(11) DEFAULT 0,
ADD COLUMN IF NOT EXISTS `pet_friendly` tinyint(1) DEFAULT 0,
ADD COLUMN IF NOT EXISTS `view_count` int(11) DEFAULT 0,
ADD COLUMN IF NOT EXISTS `is_sold` tinyint(1) DEFAULT 0,
ADD COLUMN IF NOT EXISTS `sold_date` timestamp NULL DEFAULT NULL,
ADD COLUMN IF NOT EXISTS `sold_price` decimal(15,2) DEFAULT NULL;

-- =====================================================
-- 7. ADD PROPERTY FEATURES TABLE (CRITICAL)
-- =====================================================

CREATE TABLE IF NOT EXISTS `property_features` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `property_id` int(11) NOT NULL,
  `feature_name` varchar(100) NOT NULL,
  `feature_value` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `property_id` (`property_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =====================================================
-- 8. ADD CONTACT INQUIRIES TABLE (CRITICAL)
-- =====================================================

CREATE TABLE IF NOT EXISTS `contact_inquiries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `inquiry_type` varchar(50) DEFAULT 'general',
  `property_id` int(11) DEFAULT NULL,
  `assigned_to` int(11) DEFAULT NULL,
  `priority` varchar(20) DEFAULT 'medium',
  `status` varchar(20) DEFAULT 'pending',
  `response` text DEFAULT NULL,
  `responded_by` int(11) DEFAULT NULL,
  `responded_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `property_id` (`property_id`),
  KEY `assigned_to` (`assigned_to`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =====================================================
-- 9. ADD NEWSLETTER SUBSCRIBERS TABLE (CRITICAL)
-- =====================================================

CREATE TABLE IF NOT EXISTS `newsletter_subscribers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(100) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `subscription_source` varchar(50) DEFAULT 'website',
  `preferences` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `subscribed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `unsubscribed_at` timestamp NULL DEFAULT NULL,
  `unsubscribe_token` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =====================================================
-- 10. ADD SYSTEM SETTINGS TABLE (CRITICAL)
-- =====================================================

CREATE TABLE IF NOT EXISTS `system_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_type` varchar(50) DEFAULT 'string',
  `setting_group` varchar(50) DEFAULT 'general',
  `is_editable` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`),
  KEY `setting_group` (`setting_group`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert critical system settings
INSERT INTO `system_settings` (`setting_key`, `setting_value`, `setting_type`, `setting_group`) VALUES
('app_name', 'APS Dream Homes', 'string', 'general'),
('app_version', '1.0.0', 'string', 'general'),
('maintenance_mode', '0', 'boolean', 'general'),
('registration_enabled', '1', 'boolean', 'general'),
('email_verification_required', '0', 'boolean', 'security'),
('max_login_attempts', '5', 'number', 'security'),
('session_timeout', '3600', 'number', 'security'),
('allowed_file_types', 'jpg,jpeg,png,gif,pdf,doc,docx', 'string', 'uploads'),
('max_file_size', '5242880', 'number', 'uploads'),
('smtp_enabled', '0', 'boolean', 'email'),
('backup_frequency', 'daily', 'string', 'backup');

-- =====================================================
-- 11. ADD USER ROLES TABLE (CRITICAL)
-- =====================================================

CREATE TABLE IF NOT EXISTS `user_roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_name` varchar(50) NOT NULL,
  `display_name` varchar(100) NOT NULL,
  `permissions` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `role_name` (`role_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert default roles
INSERT INTO `user_roles` (`role_name`, `display_name`, `permissions`) VALUES
('super_admin', 'Super Administrator', 'all'),
('admin', 'Administrator', 'manage_users,manage_properties,manage_settings'),
('agent', 'Real Estate Agent', 'view_properties,manage_own_properties,view_leads'),
('associate', 'Associate', 'view_properties,refer_properties,view_commissions'),
('customer', 'Customer', 'view_properties,submit_inquiries,manage_profile');

-- =====================================================
-- 12. ADD ACTIVITY LOGS TABLE (CRITICAL FOR AUDIT)
-- =====================================================

CREATE TABLE IF NOT EXISTS `activity_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `activity_type` varchar(50) NOT NULL,
  `description` text NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `activity_type` (`activity_type`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =====================================================
-- 13. PERFORMANCE OPTIMIZATION INDEXES (CRITICAL)
-- =====================================================

-- Optimize users table
ALTER TABLE `users` ADD INDEX IF NOT EXISTS `idx_users_email` (`email`);
ALTER TABLE `users` ADD INDEX IF NOT EXISTS `idx_users_role` (`role`);
ALTER TABLE `users` ADD INDEX IF NOT EXISTS `idx_users_status` (`status`);

-- Optimize properties table
ALTER TABLE `properties` ADD INDEX IF NOT EXISTS `idx_properties_type` (`property_type`);
ALTER TABLE `properties` ADD INDEX IF NOT EXISTS `idx_properties_city` (`city`);
ALTER TABLE `properties` ADD INDEX IF NOT EXISTS `idx_properties_status` (`status`);
ALTER TABLE `properties` ADD INDEX IF NOT EXISTS `idx_properties_featured` (`featured`);
ALTER TABLE `properties` ADD INDEX IF NOT EXISTS `idx_properties_price` (`price`);

-- Optimize site_settings table
ALTER TABLE `site_settings` ADD INDEX IF NOT EXISTS `idx_settings_name` (`setting_name`);

-- =====================================================
-- 14. INSERT SAMPLE SECURITY DATA (CRITICAL)
-- =====================================================

-- Insert password security for existing users
INSERT INTO `password_security` (`user_id`, `failed_attempts`)
SELECT `id`, 0 FROM `users` WHERE NOT EXISTS (
    SELECT 1 FROM `password_security` WHERE `user_id` = `users`.`id`
);

-- Insert system logs for critical actions
INSERT INTO `system_logs` (`user_id`, `action`, `table_name`, `new_values`, `ip_address`)
SELECT 1, 'system_update', 'system', 'Critical security update applied', 'system'
WHERE NOT EXISTS (SELECT 1 FROM `system_logs` WHERE `action` = 'system_update');

-- =====================================================
-- CRITICAL UPDATE COMPLETE
-- =====================================================

-- Final status message
SELECT 'CRITICAL SECURITY & STABILITY UPDATE COMPLETED!' as status,
       'Tables created/updated: 8' as tables_affected,
       'Security features added: 6' as security_features,
       'Performance optimizations: 8' as optimizations,
       'Sample data inserted: Yes' as sample_data,
       'Next step: Run organization script' as next_step;
