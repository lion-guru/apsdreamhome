-- Complete Database Structure for APS Dream Home
-- Generated for comprehensive real estate ERP system

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

CREATE DATABASE IF NOT EXISTS `apsdreamhome` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `apsdreamhome`;

-- Drop existing tables if they exist
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `properties`;
DROP TABLE IF EXISTS `property_types`;
DROP TABLE IF EXISTS `property_images`;
DROP TABLE IF EXISTS `bookings`;
DROP TABLE IF EXISTS `payments`;
DROP TABLE IF EXISTS `associates`;
DROP TABLE IF EXISTS `commission_transactions`;
DROP TABLE IF EXISTS `leads`;
DROP TABLE IF EXISTS `email_templates`;
DROP TABLE IF EXISTS `api_keys`;
DROP TABLE IF EXISTS `async_tasks`;
DROP TABLE IF EXISTS `task_queue`;
DROP TABLE IF EXISTS `site_settings`;
SET FOREIGN_KEY_CHECKS = 1;

-- Users table
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `role` enum('admin','agent','user','associate') DEFAULT 'user',
  `email_verified` tinyint(1) DEFAULT 0,
  `phone_verified` tinyint(1) DEFAULT 0,
  `profile_image` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive','suspended') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_role` (`role`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Property types table
CREATE TABLE `property_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Properties table
CREATE TABLE `properties` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `property_type_id` int(11) NOT NULL,
  `price` decimal(15,2) NOT NULL,
  `location` varchar(255) NOT NULL,
  `city` varchar(100) NOT NULL,
  `state` varchar(100) NOT NULL,
  `bedrooms` int(11) DEFAULT 0,
  `bathrooms` int(11) DEFAULT 0,
  `area` decimal(10,2) DEFAULT NULL,
  `area_unit` varchar(20) DEFAULT 'sqft',
  `features` json DEFAULT NULL,
  `amenities` json DEFAULT NULL,
  `agent_id` int(11) DEFAULT NULL,
  `status` enum('available','sold','rented','inactive') DEFAULT 'available',
  `featured` tinyint(1) DEFAULT 0,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `views` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `property_type_id` (`property_type_id`),
  KEY `agent_id` (`agent_id`),
  KEY `status` (`status`),
  KEY `featured` (`featured`),
  KEY `price` (`price`),
  KEY `location` (`location`),
  KEY `city` (`city`),
  FOREIGN KEY (`property_type_id`) REFERENCES `property_types` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`agent_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Property images table
CREATE TABLE `property_images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `property_id` int(11) NOT NULL,
  `image` varchar(255) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `is_primary` tinyint(1) DEFAULT 0,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `property_id` (`property_id`),
  KEY `is_primary` (`is_primary`),
  FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Bookings table
CREATE TABLE `bookings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `property_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `booking_type` enum('visit','purchase','rental') NOT NULL,
  `visit_date` datetime DEFAULT NULL,
  `message` text DEFAULT NULL,
  `status` enum('pending','confirmed','cancelled','completed') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `property_id` (`property_id`),
  KEY `user_id` (`user_id`),
  KEY `status` (`status`),
  FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Payments table
CREATE TABLE `payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `booking_id` int(11) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `transaction_id` varchar(100) DEFAULT NULL,
  `status` enum('pending','completed','failed','refunded') DEFAULT 'pending',
  `payment_date` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `booking_id` (`booking_id`),
  KEY `user_id` (`user_id`),
  KEY `status` (`status`),
  FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE SET NULL,
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Associates table (for MLM)
CREATE TABLE `associates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `sponsor_id` int(11) DEFAULT NULL,
  `level` int(11) DEFAULT 1,
  `total_earnings` decimal(15,2) DEFAULT 0.00,
  `active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `sponsor_id` (`sponsor_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`sponsor_id`) REFERENCES `associates` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Commission transactions table
CREATE TABLE `commission_transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `associate_id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `commission_amount` decimal(15,2) NOT NULL,
  `level` int(11) NOT NULL,
  `status` enum('pending','paid','cancelled') DEFAULT 'pending',
  `paid_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `associate_id` (`associate_id`),
  KEY `booking_id` (`booking_id`),
  FOREIGN KEY (`associate_id`) REFERENCES `associates` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Leads table
CREATE TABLE `leads` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `property_interest` varchar(255) DEFAULT NULL,
  `budget_min` decimal(15,2) DEFAULT NULL,
  `budget_max` decimal(15,2) DEFAULT NULL,
  `location_preference` varchar(255) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `source` varchar(50) DEFAULT 'website',
  `status` enum('new','contacted','qualified','converted','lost') DEFAULT 'new',
  `assigned_to` int(11) DEFAULT NULL,
  `follow_up_date` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `status` (`status`),
  KEY `assigned_to` (`assigned_to`),
  KEY `created_at` (`created_at`),
  FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Email templates table
CREATE TABLE `email_templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL UNIQUE,
  `subject` varchar(255) NOT NULL,
  `body` text NOT NULL,
  `variables` json DEFAULT NULL,
  `category` varchar(50) DEFAULT 'general',
  `active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `category` (`category`),
  KEY `active` (`active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- API keys table
CREATE TABLE `api_keys` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `api_key` varchar(64) NOT NULL UNIQUE,
  `name` varchar(255) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `permissions` json DEFAULT NULL,
  `rate_limit` int(11) DEFAULT 1000,
  `daily_limit` int(11) DEFAULT 10000,
  `monthly_limit` int(11) DEFAULT 100000,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `expires_at` timestamp NULL DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `usage_count` int(11) DEFAULT 0,
  `daily_usage` int(11) DEFAULT 0,
  `monthly_usage` int(11) DEFAULT 0,
  `last_reset_date` date DEFAULT curdate(),
  PRIMARY KEY (`id`),
  KEY `api_key` (`api_key`),
  KEY `user_id` (`user_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Async tasks table
CREATE TABLE `async_tasks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `task_name` varchar(255) NOT NULL,
  `task_type` varchar(100) NOT NULL,
  `parameters` json DEFAULT NULL,
  `priority` int(11) DEFAULT 2,
  `status` varchar(20) DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `started_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `result` json DEFAULT NULL,
  `error_message` text DEFAULT NULL,
  `retry_count` int(11) DEFAULT 0,
  `max_retries` int(11) DEFAULT 3,
  `progress_percentage` int(11) DEFAULT 0,
  `assigned_worker` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `status` (`status`),
  KEY `task_type` (`task_type`),
  KEY `priority` (`priority`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Task queue table
CREATE TABLE `task_queue` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `task_id` int(11) NOT NULL,
  `queue_name` varchar(100) DEFAULT 'default',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `task_id` (`task_id`),
  FOREIGN KEY (`task_id`) REFERENCES `async_tasks` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Site settings table
CREATE TABLE `site_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_name` varchar(100) NOT NULL UNIQUE,
  `setting_value` text DEFAULT NULL,
  `setting_group` varchar(50) DEFAULT 'general',
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `setting_name` (`setting_name`),
  KEY `setting_group` (`setting_group`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default property types
INSERT INTO `property_types` (`name`, `description`, `icon`) VALUES
('Apartment', 'Modern apartment units', 'building'),
('Villa', 'Independent villa houses', 'home'),
('Plot', 'Land plots for construction', 'map-marker-alt'),
('Commercial', 'Commercial properties', 'briefcase'),
('Office', 'Office spaces', 'desktop'),
('Shop', 'Retail shops', 'shopping-cart'),
('Warehouse', 'Storage and warehouse spaces', 'warehouse');

-- Insert default site settings
INSERT INTO `site_settings` (`setting_name`, `setting_value`, `setting_group`, `description`) VALUES
('site_title', 'APS Dream Home - Find Your Dream Property', 'general', 'Main site title'),
('site_logo', '/assets/images/logo.png', 'appearance', 'Site logo URL'),
('header_background', '#1e3c72', 'appearance', 'Header background color'),
('header_text_color', '#ffffff', 'appearance', 'Header text color'),
('footer_about', 'Your trusted partner in finding your dream property. We offer the best properties at the best prices.', 'content', 'Footer about text'),
('footer_contact', '123 Property Street, City, State 12345 | Phone: +1 234 567 8900 | Email: info@apsdreamhome.com', 'contact', 'Footer contact information'),
('footer_copyright', 'APS Dream Home. All rights reserved.', 'content', 'Footer copyright text');

-- Insert default email templates
INSERT INTO `email_templates` (`name`, `subject`, `body`, `variables`, `category`) VALUES
('welcome_user', 'Welcome to APS Dream Home!', 'Dear {{name}},

Welcome to APS Dream Home! We are excited to help you find your dream property.

Your account has been successfully created. You can now:
- Browse properties
- Save your favorite properties
- Contact property agents
- Get property recommendations

If you have any questions, feel free to contact our support team.

Best regards,
APS Dream Home Team', '["name"]', 'user_management'),
('property_inquiry', 'New Property Inquiry - {{property_title}}', 'Dear Agent,

You have received a new inquiry for the property: {{property_title}}

Inquiry Details:
- Customer Name: {{customer_name}}
- Email: {{customer_email}}
- Phone: {{customer_phone}}
- Message: {{message}}

Property Details:
- Location: {{property_location}}
- Price: {{property_price}}
- Type: {{property_type}}

Please contact the customer as soon as possible to assist them.

Best regards,
APS Dream Home System', '["property_title", "customer_name", "customer_email", "customer_phone", "message", "property_location", "property_price", "property_type"]', 'property'),
('password_reset', 'Password Reset Request - APS Dream Home', 'Dear {{name}},

You have requested to reset your password for your APS Dream Home account.

Click the following link to reset your password:
{{reset_link}}

This link will expire in 24 hours for security reasons.

If you did not request this password reset, please ignore this email.

Best regards,
APS Dream Home Team', '["name", "reset_link"]', 'security');

-- Create a default admin user (password: admin123)
INSERT INTO `users` (`username`, `email`, `password`, `full_name`, `role`, `status`) VALUES
('admin', 'admin@apsdreamhome.com', '$2y$10$8K3VZvHDz5eD4vT8p4e3QeK4mN2X8yP9Q7tR2U4V6W8Y0A2C4E6G8', 'System Administrator', 'admin', 'active');

COMMIT;
