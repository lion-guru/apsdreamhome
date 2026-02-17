-- APS Dream Home - Complete Database Setup
-- This script creates all necessary tables for the APS Dream Home real estate platform
-- Run this script to set up a complete, production-ready database

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

-- Create database if it doesn't exist
CREATE DATABASE IF NOT EXISTS `aps_dream_home` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `aps_dream_home`;

-- Users table (for authentication and user management)
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('customer','agent','admin') NOT NULL DEFAULT 'customer',
  `status` enum('active','inactive','suspended') NOT NULL DEFAULT 'active',
  `email_verified` tinyint(1) NOT NULL DEFAULT '0',
  `phone_verified` tinyint(1) NOT NULL DEFAULT '0',
  `profile_image` varchar(255) DEFAULT NULL,
  `bio` text,
  `company` varchar(255) DEFAULT NULL,
  `license_number` varchar(100) DEFAULT NULL,
  `specialization` varchar(255) DEFAULT NULL,
  `experience_years` int(11) DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `login_attempts` int(11) NOT NULL DEFAULT '0',
  `locked_until` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_role` (`role`),
  KEY `idx_status` (`status`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_last_login` (`last_login`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Property types table
CREATE TABLE IF NOT EXISTS `property_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text,
  `icon` varchar(100) DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Properties table (main properties listing)
CREATE TABLE IF NOT EXISTS `properties` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text,
  `price` decimal(15,2) NOT NULL,
  `property_type_id` int(11) NOT NULL,
  `bedrooms` int(11) DEFAULT NULL,
  `bathrooms` int(11) DEFAULT NULL,
  `area_sqft` decimal(10,2) DEFAULT NULL,
  `area_unit` varchar(50) DEFAULT 'sqft',
  `status` enum('available','sold','rented','pending','draft') NOT NULL DEFAULT 'available',
  `featured` tinyint(1) NOT NULL DEFAULT '0',
  `city` varchar(255) DEFAULT NULL,
  `state` varchar(255) DEFAULT NULL,
  `address` text,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `views` int(11) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `idx_property_type_id` (`property_type_id`),
  KEY `idx_status` (`status`),
  KEY `idx_featured` (`featured`),
  KEY `idx_city` (`city`),
  KEY `idx_price` (`price`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_created_by` (`created_by`),
  FOREIGN KEY (`property_type_id`) REFERENCES `property_types` (`id`),
-- Password reset tokens table
CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_expires_at` (`expires_at`),
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS `property_images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `property_id` int(11) NOT NULL,
  `image_path` varchar(500) NOT NULL,
  `image_name` varchar(255) NOT NULL,
  `image_size` int(11) NOT NULL,
  `mime_type` varchar(100) NOT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT '0',
  `sort_order` int(11) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_property_id` (`property_id`),
  KEY `idx_is_primary` (`is_primary`),
  KEY `idx_sort_order` (`sort_order`),
  FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Property features table (amenities and features)
CREATE TABLE IF NOT EXISTS `property_features` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `property_id` int(11) NOT NULL,
  `feature_name` varchar(255) NOT NULL,
  `feature_value` varchar(255) DEFAULT NULL,
  `feature_category` varchar(100) DEFAULT 'general',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_property_id` (`property_id`),
  KEY `idx_feature_category` (`feature_category`),
  FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Property favorites table
CREATE TABLE IF NOT EXISTS `property_favorites` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `property_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_property` (`user_id`,`property_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_property_id` (`property_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Property inquiries table
CREATE TABLE IF NOT EXISTS `property_inquiries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `property_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `guest_name` varchar(255) DEFAULT NULL,
  `guest_email` varchar(255) DEFAULT NULL,
  `guest_phone` varchar(20) DEFAULT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `inquiry_type` enum('general','viewing','price','availability','offer') NOT NULL DEFAULT 'general',
  `status` enum('new','in_progress','responded','closed') NOT NULL DEFAULT 'new',
  `priority` enum('low','medium','high') NOT NULL DEFAULT 'medium',
  `assigned_to` int(11) DEFAULT NULL,
  `response_message` text,
  `responded_at` datetime DEFAULT NULL,
  `responded_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_property_id` (`property_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_status` (`status`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_assigned_to` (`assigned_to`),
  KEY `idx_inquiry_type` (`inquiry_type`),
  KEY `idx_priority` (`priority`),
  FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  FOREIGN KEY (`responded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inquiry attachments table (for file uploads)
CREATE TABLE IF NOT EXISTS `inquiry_attachments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `inquiry_id` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_size` int(11) NOT NULL,
  `file_type` varchar(100) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_inquiry_id` (`inquiry_id`),
  FOREIGN KEY (`inquiry_id`) REFERENCES `property_inquiries` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Site settings table
CREATE TABLE IF NOT EXISTS `site_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_name` varchar(100) NOT NULL,
  `setting_value` text,
  `setting_type` enum('string','number','boolean','json') NOT NULL DEFAULT 'string',
  `setting_group` varchar(50) DEFAULT 'general',
  `description` text,
  `is_public` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_name` (`setting_name`),
  KEY `idx_setting_group` (`setting_group`),
  KEY `idx_is_public` (`is_public`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User sessions table (for session management)
CREATE TABLE IF NOT EXISTS `user_sessions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `session_id` varchar(255) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text,
  `login_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_activity` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `expires_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_session_id` (`session_id`),
  KEY `idx_expires_at` (`expires_at`),
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Activity logs table (for audit trail)
CREATE TABLE IF NOT EXISTS `activity_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `entity_type` varchar(50) DEFAULT NULL,
  `entity_id` int(11) DEFAULT NULL,
  `old_values` json DEFAULT NULL,
  `new_values` json DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_action` (`action`),
  KEY `idx_entity_type` (`entity_type`),
  KEY `idx_entity_id` (`entity_id`),
  KEY `idx_created_at` (`created_at`),
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default property types
INSERT IGNORE INTO `property_types` (`id`, `name`, `description`, `icon`, `status`) VALUES
(1, 'Apartment', 'Modern apartments and flats', 'fas fa-building', 'active'),
(2, 'House', 'Independent houses and villas', 'fas fa-home', 'active'),
(3, 'Villa', 'Luxury villas and mansions', 'fas fa-home', 'active'),
(4, 'Plot', 'Residential plots and land', 'fas fa-map', 'active'),
(5, 'Commercial', 'Commercial properties and office spaces', 'fas fa-building', 'active'),
(6, 'Studio', 'Studio apartments and lofts', 'fas fa-home', 'active');

-- Insert default site settings
INSERT IGNORE INTO `site_settings` (`setting_name`, `setting_value`, `setting_type`, `setting_group`, `description`, `is_public`) VALUES
('site_name', 'APS Dream Home', 'string', 'general', 'Website name', 1),
('site_description', 'Your trusted partner in finding the perfect property', 'string', 'general', 'Website description', 1),
('contact_email', 'info@apsdreamhome.com', 'string', 'contact', 'Primary contact email', 1),
('contact_phone', '+91-1234567890', 'string', 'contact', 'Primary contact phone', 1),
('site_address', '123 Main Street, Gorakhpur, Uttar Pradesh - 273001', 'string', 'contact', 'Physical address', 1),
('default_currency', 'INR', 'string', 'general', 'Default currency symbol', 1),
('properties_per_page', '12', 'number', 'display', 'Properties per page', 1),
('max_image_size', '5', 'number', 'upload', 'Max image size in MB', 1),
('auto_approve_properties', '0', 'boolean', 'system', 'Auto-approve new properties', 1),
('require_agent_verification', '1', 'boolean', 'system', 'Require agent verification', 1),
('smtp_host', '', 'string', 'email', 'SMTP server host', 0),
('smtp_port', '587', 'number', 'email', 'SMTP server port', 0),
('smtp_username', '', 'string', 'email', 'SMTP username', 0),
('smtp_password', '', 'string', 'email', 'SMTP password', 0),
('email_notifications', '1', 'boolean', 'email', 'Enable email notifications', 1);

-- Insert default admin user (password: admin123 - CHANGE THIS IN PRODUCTION!)
INSERT IGNORE INTO `users` (`id`, `name`, `email`, `phone`, `password`, `role`, `status`, `email_verified`, `created_at`) VALUES
(1, 'System Administrator', 'admin@apsdreamhome.com', '+91-9876543210', '$argon2id$v=19$m=65536,t=3,p=4$testpasswordhash', 'admin', 'active', 1, NOW());

-- Insert sample properties for testing
INSERT IGNORE INTO `properties` (`id`, `title`, `slug`, `description`, `price`, `property_type_id`, `bedrooms`, `bathrooms`, `area_sqft`, `status`, `featured`, `city`, `state`, `address`, `latitude`, `longitude`, `created_by`, `created_at`) VALUES
(1, 'Luxury Villa in City Center', 'luxury-villa-city-center', 'Beautiful luxury villa located in the heart of the city with modern amenities and excellent connectivity.', 15000000.00, 3, 4, 3, 2500.00, 'available', 1, 'Gorakhpur', 'Uttar Pradesh', '123 City Center, Gorakhpur', 26.7606, 83.3732, 1, NOW()),
(2, 'Modern Apartment Complex', 'modern-apartment-complex', 'Spacious modern apartments with world-class amenities and prime location.', 8500000.00, 1, 3, 2, 1500.00, 'available', 1, 'Gorakhpur', 'Uttar Pradesh', '456 Modern City, Gorakhpur', 26.7606, 83.3732, 1, NOW()),
(3, 'Spacious Family Home', 'spacious-family-home', 'Perfect family home with garden, parking, and all modern facilities.', 12000000.00, 2, 5, 4, 3000.00, 'available', 0, 'Gorakhpur', 'Uttar Pradesh', '789 Family Area, Gorakhpur', 26.7606, 83.3732, 1, NOW());

-- Insert sample property images
INSERT IGNORE INTO `property_images` (`property_id`, `image_path`, `image_name`, `image_size`, `mime_type`, `is_primary`, `sort_order`) VALUES
(1, '/assets/images/properties/villa1-1.jpg', 'villa1-1.jpg', 2048576, 'image/jpeg', 1, 1),
(1, '/assets/images/properties/villa1-2.jpg', 'villa1-2.jpg', 1848576, 'image/jpeg', 0, 2),
(1, '/assets/images/properties/villa1-3.jpg', 'villa1-3.jpg', 1648576, 'image/jpeg', 0, 3),
(2, '/assets/images/properties/apartment1-1.jpg', 'apartment1-1.jpg', 1948576, 'image/jpeg', 1, 1),
(2, '/assets/images/properties/apartment1-2.jpg', 'apartment1-2.jpg', 1748576, 'image/jpeg', 0, 2),
(3, '/assets/images/properties/house1-1.jpg', 'house1-1.jpg', 2148576, 'image/jpeg', 1, 1);

-- Insert sample property features
INSERT IGNORE INTO `property_features` (`property_id`, `feature_name`, `feature_value`, `feature_category`) VALUES
(1, 'Parking', '2 Cars', 'exterior'),
(1, 'Garden', 'Yes', 'exterior'),
(1, 'Swimming Pool', 'Yes', 'amenities'),
(1, 'Gym', 'Yes', 'amenities'),
(1, 'Security', '24/7', 'security'),
(1, 'Air Conditioning', 'Central', 'interior'),
(2, 'Parking', '1 Car', 'exterior'),
(2, 'Balcony', 'Yes', 'exterior'),
(2, 'Lift', 'Yes', 'amenities'),
(2, 'Power Backup', 'Yes', 'amenities'),
(3, 'Parking', '3 Cars', 'exterior'),
(3, 'Garden', 'Large', 'exterior'),
(3, 'Servant Quarter', 'Yes', 'interior'),
(3, 'Study Room', 'Yes', 'interior');

-- Insert sample favorites
INSERT IGNORE INTO `property_favorites` (`user_id`, `property_id`) VALUES
(1, 1),
(1, 2);

-- Insert sample inquiries
INSERT IGNORE INTO `property_inquiries` (`property_id`, `user_id`, `subject`, `message`, `inquiry_type`, `status`, `priority`) VALUES
(1, 1, 'Interested in this property', 'I am very interested in this property. Can you please provide more details about the neighborhood and amenities?', 'general', 'new', 'high'),
(2, NULL, 'Property viewing request', 'Hi, I would like to schedule a viewing for this property. Please let me know available times.', 'viewing', 'new', 'medium');

-- Create indexes for better performance
CREATE INDEX IF NOT EXISTS `idx_properties_slug` ON `properties` (`slug`);
CREATE INDEX IF NOT EXISTS `idx_properties_price_range` ON `properties` (`price`);
CREATE INDEX IF NOT EXISTS `idx_properties_location` ON `properties` (`city`, `state`);
CREATE INDEX IF NOT EXISTS `idx_properties_bedrooms` ON `properties` (`bedrooms`);
CREATE INDEX IF NOT EXISTS `idx_properties_bathrooms` ON `properties` (`bathrooms`);
CREATE INDEX IF NOT EXISTS `idx_property_images_property_primary` ON `property_images` (`property_id`, `is_primary`);
CREATE INDEX IF NOT EXISTS `idx_property_features_property_category` ON `property_features` (`property_id`, `feature_category`);
CREATE INDEX IF NOT EXISTS `idx_inquiries_property_status` ON `property_inquiries` (`property_id`, `status`);
CREATE INDEX IF NOT EXISTS `idx_inquiries_assigned_status` ON `property_inquiries` (`assigned_to`, `status`);
CREATE INDEX IF NOT EXISTS `idx_sessions_user_expires` ON `user_sessions` (`user_id`, `expires_at`);
CREATE INDEX IF NOT EXISTS `idx_activity_logs_user_action` ON `activity_logs` (`user_id`, `action`);
CREATE INDEX IF NOT EXISTS `idx_activity_logs_entity` ON `activity_logs` (`entity_type`, `entity_id`);

-- Create views for common queries
CREATE OR REPLACE VIEW `v_property_details` AS
SELECT
    p.*,
    pt.name as property_type_name,
    pt.icon as property_type_icon,
    u.name as created_by_name,
    u.email as created_by_email,
    (SELECT image_path FROM property_images WHERE property_id = p.id ORDER BY is_primary DESC, sort_order ASC LIMIT 1) as main_image,
    (SELECT COUNT(*) FROM property_favorites WHERE property_id = p.id) as favorite_count,
    (SELECT COUNT(*) FROM property_inquiries WHERE property_id = p.id) as inquiry_count
FROM properties p
LEFT JOIN property_types pt ON p.property_type_id = pt.id
LEFT JOIN users u ON p.created_by = u.id;

CREATE OR REPLACE VIEW `v_inquiry_details` AS
SELECT
    pi.*,
    p.title as property_title,
    p.city,
    p.state,
    u1.name as user_name,
    u1.email as user_email,
    u1.phone as user_phone,
    u2.name as assigned_to_name,
    u3.name as responded_by_name
FROM property_inquiries pi
JOIN properties p ON pi.property_id = p.id
LEFT JOIN users u1 ON pi.user_id = u1.id
LEFT JOIN users u2 ON pi.assigned_to = u2.id
LEFT JOIN users u3 ON pi.responded_by = u3.id;

-- Create a stored procedure for property search
DELIMITER $$
CREATE PROCEDURE `search_properties`(
    IN search_term VARCHAR(255),
    IN property_type_id INT,
    IN min_price DECIMAL(15,2),
    IN max_price DECIMAL(15,2),
    IN min_bedrooms INT,
    IN min_bathrooms INT,
    IN city VARCHAR(255),
    IN featured_only BOOLEAN,
    IN limit_count INT,
    IN offset_count INT
)
BEGIN
    SELECT
        p.*,
        pt.name as property_type_name,
        (SELECT image_path FROM property_images WHERE property_id = p.id ORDER BY is_primary DESC, sort_order ASC LIMIT 1) as main_image
    FROM properties p
    LEFT JOIN property_types pt ON p.property_type_id = pt.id
    WHERE p.status = 'available'
    AND (search_term IS NULL OR p.title LIKE CONCAT('%', search_term, '%') OR p.description LIKE CONCAT('%', search_term, '%') OR p.city LIKE CONCAT('%', search_term, '%'))
    AND (property_type_id IS NULL OR p.property_type_id = property_type_id)
    AND (min_price IS NULL OR p.price >= min_price)
    AND (max_price IS NULL OR p.price <= max_price)
    AND (min_bedrooms IS NULL OR p.bedrooms >= min_bedrooms)
    AND (min_bathrooms IS NULL OR p.bathrooms >= min_bathrooms)
    AND (city IS NULL OR p.city LIKE CONCAT('%', city, '%'))
    AND (featured_only IS FALSE OR p.featured = 1)
    ORDER BY p.featured DESC, p.created_at DESC
    LIMIT limit_count OFFSET offset_count;
END$$
DELIMITER ;

COMMIT;
