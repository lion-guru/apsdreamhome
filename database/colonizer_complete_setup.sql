-- Complete Database Setup for APS Dream Home - Colonizer Management System
-- This includes all tables for real estate, plotting, farmer management, MLM, and salary systems

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

CREATE DATABASE IF NOT EXISTS `apsdreamhome` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `apsdreamhome`;

-- Drop existing tables if they exist (for fresh setup)
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
DROP TABLE IF EXISTS `farmer_profiles`;
DROP TABLE IF EXISTS `farmer_land_holdings`;
DROP TABLE IF EXISTS `farmer_transactions`;
DROP TABLE IF EXISTS `farmer_loans`;
DROP TABLE IF EXISTS `farmer_support_requests`;
DROP TABLE IF EXISTS `plots`;
DROP TABLE IF EXISTS `plot_bookings`;
DROP TABLE IF EXISTS `plot_payments`;
DROP TABLE IF EXISTS `commission_tracking`;
DROP TABLE IF EXISTS `employee_salary_structure`;
DROP TABLE IF EXISTS `salary_payments`;
DROP TABLE IF EXISTS `employee_attendance`;
DROP TABLE IF EXISTS `employee_advances`;
DROP TABLE IF EXISTS `employee_bonuses`;
DROP TABLE IF EXISTS `associate_levels`;
DROP TABLE IF EXISTS `commission_payouts`;
DROP TABLE IF EXISTS `associate_achievements`;
SET FOREIGN_KEY_CHECKS = 1;

-- Users table (Enhanced for multiple roles)
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `role` enum('admin','manager','employee','agent','associate','user','farmer') DEFAULT 'user',
  `department` varchar(50) DEFAULT NULL,
  `designation` varchar(100) DEFAULT NULL,
  `employee_id` varchar(20) DEFAULT NULL,
  `joining_date` date DEFAULT NULL,
  `salary` decimal(15,2) DEFAULT NULL,
  `email_verified` tinyint(1) DEFAULT 0,
  `phone_verified` tinyint(1) DEFAULT 0,
  `profile_image` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive','suspended','terminated') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_login` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `employee_id` (`employee_id`),
  KEY `idx_role` (`role`),
  KEY `idx_status` (`status`),
  KEY `idx_department` (`department`)
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

-- Associates table (MLM System)
CREATE TABLE `associates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `sponsor_id` int(11) DEFAULT NULL,
  `level` int(11) DEFAULT 1,
  `total_earnings` decimal(15,2) DEFAULT 0.00,
  `monthly_earnings` decimal(15,2) DEFAULT 0.00,
  `team_size` int(11) DEFAULT 0,
  `active_team_members` int(11) DEFAULT 0,
  `personal_sales` decimal(15,2) DEFAULT 0.00,
  `team_sales` decimal(15,2) DEFAULT 0.00,
  `status` enum('active','inactive','suspended') DEFAULT 'active',
  `join_date` date NOT NULL,
  `last_sale_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `sponsor_id` (`sponsor_id`),
  KEY `status` (`status`),
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

-- Farmer profiles table
CREATE TABLE `farmer_profiles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `farmer_number` varchar(50) NOT NULL UNIQUE,
  `full_name` varchar(100) NOT NULL,
  `father_name` varchar(100),
  `spouse_name` varchar(100),
  `date_of_birth` date,
  `gender` enum('male','female','other') DEFAULT 'male',
  `phone` varchar(15) NOT NULL,
  `alternate_phone` varchar(15),
  `email` varchar(100),
  `address` text,
  `village` varchar(100),
  `post_office` varchar(100),
  `tehsil` varchar(100),
  `district` varchar(100),
  `state` varchar(100),
  `pincode` varchar(10),
  `aadhar_number` varchar(20),
  `pan_number` varchar(20),
  `voter_id` varchar(20),
  `bank_account_number` varchar(30),
  `bank_name` varchar(100),
  `ifsc_code` varchar(20),
  `account_holder_name` varchar(100),
  `total_land_holding` decimal(10,2) DEFAULT 0,
  `cultivated_area` decimal(10,2) DEFAULT 0,
  `irrigated_area` decimal(10,2) DEFAULT 0,
  `non_irrigated_area` decimal(10,2) DEFAULT 0,
  `crop_types` json,
  `farming_experience` int(11) DEFAULT 0,
  `education_level` varchar(50),
  `family_members` int(11) DEFAULT 0,
  `family_income` decimal(15,2),
  `credit_score` enum('excellent','good','fair','poor') DEFAULT 'fair',
  `credit_limit` decimal(15,2) DEFAULT 50000,
  `outstanding_loans` decimal(15,2) DEFAULT 0,
  `payment_history` json,
  `status` enum('active','inactive','blacklisted','under_review') DEFAULT 'active',
  `associate_id` int(11),
  `created_by` int(11),
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `status` (`status`),
  KEY `district` (`district`),
  KEY `associate_id` (`associate_id`),
  FOREIGN KEY (`associate_id`) REFERENCES `associates` (`id`) ON DELETE SET NULL,
  FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Farmer land holdings table
CREATE TABLE `farmer_land_holdings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `farmer_id` int(11) NOT NULL,
  `khasra_number` varchar(50),
  `land_area` decimal(10,2) NOT NULL,
  `land_area_unit` varchar(20) DEFAULT 'sqft',
  `land_type` enum('agricultural','residential','commercial','mixed') DEFAULT 'agricultural',
  `soil_type` varchar(100),
  `irrigation_source` varchar(100),
  `water_source` varchar(100),
  `electricity_available` tinyint(1) DEFAULT 0,
  `road_access` tinyint(1) DEFAULT 0,
  `location` varchar(255),
  `village` varchar(100),
  `tehsil` varchar(100),
  `district` varchar(100),
  `state` varchar(100),
  `land_value` decimal(15,2),
  `current_status` enum('cultivated','fallow','sold','under_acquisition','disputed') DEFAULT 'cultivated',
  `ownership_document` varchar(255),
  `mutation_document` varchar(255),
  `acquisition_status` enum('not_acquired','under_negotiation','acquired','rejected') DEFAULT 'not_acquired',
  `acquisition_date` date,
  `acquisition_amount` decimal(15,2),
  `payment_status` enum('pending','partial','completed') DEFAULT 'pending',
  `payment_received` decimal(15,2) DEFAULT 0,
  `remarks` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `farmer_id` (`farmer_id`),
  KEY `current_status` (`current_status`),
  KEY `acquisition_status` (`acquisition_status`),
  FOREIGN KEY (`farmer_id`) REFERENCES `farmer_profiles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Farmer transactions table
CREATE TABLE `farmer_transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `farmer_id` int(11) NOT NULL,
  `transaction_type` enum('land_acquisition','payment','loan','commission','refund','penalty') NOT NULL,
  `transaction_number` varchar(50) NOT NULL UNIQUE,
  `amount` decimal(15,2) NOT NULL,
  `transaction_date` date NOT NULL,
  `payment_method` enum('cash','cheque','bank_transfer','online') DEFAULT 'cash',
  `bank_reference` varchar(100),
  `transaction_id` varchar(100),
  `description` text,
  `land_acquisition_id` int(11),
  `commission_id` int(11),
  `status` enum('pending','completed','failed','cancelled') DEFAULT 'completed',
  `created_by` int(11),
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `farmer_id` (`farmer_id`),
  KEY `transaction_type` (`transaction_type`),
  KEY `transaction_date` (`transaction_date`),
  FOREIGN KEY (`farmer_id`) REFERENCES `farmer_profiles` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`land_acquisition_id`) REFERENCES `land_acquisitions` (`id`) ON DELETE SET NULL,
  FOREIGN KEY (`commission_id`) REFERENCES `commission_tracking` (`id`) ON DELETE SET NULL,
  FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Farmer loans table
CREATE TABLE `farmer_loans` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `farmer_id` int(11) NOT NULL,
  `loan_number` varchar(50) NOT NULL UNIQUE,
  `loan_amount` decimal(15,2) NOT NULL,
  `interest_rate` decimal(5,2) NOT NULL,
  `loan_tenure` int(11) NOT NULL,
  `emi_amount` decimal(15,2),
  `purpose` varchar(255),
  `sanction_date` date NOT NULL,
  `disbursement_date` date,
  `maturity_date` date,
  `outstanding_amount` decimal(15,2),
  `status` enum('applied','sanctioned','disbursed','active','closed','defaulted') DEFAULT 'applied',
  `collateral_type` enum('land','gold','property','none') DEFAULT 'none',
  `collateral_value` decimal(15,2),
  `guarantor_name` varchar(100),
  `guarantor_phone` varchar(15),
  `repayment_schedule` json,
  `created_by` int(11),
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `farmer_id` (`farmer_id`),
  KEY `status` (`status`),
  FOREIGN KEY (`farmer_id`) REFERENCES `farmer_profiles` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Farmer support requests table
CREATE TABLE `farmer_support_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `farmer_id` int(11) NOT NULL,
  `request_number` varchar(50) NOT NULL UNIQUE,
  `request_type` enum('technical','financial','legal','infrastructure','other') NOT NULL,
  `priority` enum('low','medium','high','urgent') DEFAULT 'medium',
  `subject` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `status` enum('open','in_progress','resolved','closed','rejected') DEFAULT 'open',
  `assigned_to` int(11),
  `resolution` text,
  `resolution_date` date,
  `satisfaction_rating` int(11) DEFAULT 0,
  `feedback` text,
  `created_by` int(11),
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `farmer_id` (`farmer_id`),
  KEY `status` (`status`),
  KEY `request_type` (`request_type`),
  FOREIGN KEY (`farmer_id`) REFERENCES `farmer_profiles` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Plots table
CREATE TABLE `plots` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `plot_number` varchar(50) NOT NULL,
  `land_acquisition_id` int(11) NOT NULL,
  `plot_area` decimal(10,2) NOT NULL,
  `plot_area_unit` varchar(20) DEFAULT 'sqft',
  `plot_type` enum('residential','commercial','industrial','mixed') DEFAULT 'residential',
  `dimensions_length` decimal(8,2),
  `dimensions_width` decimal(8,2),
  `corner_plot` tinyint(1) DEFAULT 0,
  `park_facing` tinyint(1) DEFAULT 0,
  `road_facing` tinyint(1) DEFAULT 0,
  `plot_status` enum('available','booked','sold','blocked','cancelled') DEFAULT 'available',
  `base_price` decimal(15,2),
  `current_price` decimal(15,2),
  `development_cost` decimal(15,2) DEFAULT 0,
  `maintenance_cost` decimal(15,2) DEFAULT 0,
  `plot_features` json,
  `plot_restrictions` json,
  `coordinates` json,
  `sector_block` varchar(50),
  `colony_name` varchar(100),
  `remarks` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `land_acquisition_id` (`land_acquisition_id`),
  KEY `plot_status` (`plot_status`),
  KEY `colony_name` (`colony_name`),
  KEY `sector_block` (`sector_block`),
  FOREIGN KEY (`land_acquisition_id`) REFERENCES `land_acquisitions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Plot bookings table
CREATE TABLE `plot_bookings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `plot_id` int(11) NOT NULL,
  `customer_id` int(11),
  `associate_id` int(11),
  `booking_number` varchar(50) NOT NULL UNIQUE,
  `booking_type` enum('direct','associate','agent') DEFAULT 'direct',
  `booking_amount` decimal(15,2) NOT NULL,
  `total_amount` decimal(15,2),
  `payment_plan` enum('lump_sum','installment','custom') DEFAULT 'lump_sum',
  `installment_period` int(11),
  `installment_amount` decimal(15,2),
  `payment_status` enum('pending','partial','completed','cancelled') DEFAULT 'pending',
  `payment_method` varchar(50),
  `transaction_id` varchar(100),
  `booking_date` date NOT NULL,
  `agreement_date` date,
  `possession_date` date,
  `cancellation_date` date,
  `cancellation_reason` text,
  `commission_paid` decimal(15,2) DEFAULT 0,
  `commission_percentage` decimal(5,2),
  `associate_commission` decimal(15,2) DEFAULT 0,
  `agent_commission` decimal(15,2) DEFAULT 0,
  `remarks` text,
  `status` enum('pending','confirmed','cancelled','completed') DEFAULT 'pending',
  `created_by` int(11),
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `plot_id` (`plot_id`),
  KEY `customer_id` (`customer_id`),
  KEY `associate_id` (`associate_id`),
  KEY `status` (`status`),
  KEY `booking_date` (`booking_date`),
  FOREIGN KEY (`plot_id`) REFERENCES `plots` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  FOREIGN KEY (`associate_id`) REFERENCES `associates` (`id`) ON DELETE SET NULL,
  FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Plot payments table
CREATE TABLE `plot_payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `booking_id` int(11) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `payment_date` date NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `transaction_id` varchar(100),
  `installment_number` int(11),
  `payment_status` enum('pending','completed','failed','refunded') DEFAULT 'completed',
  `receipt_number` varchar(50),
  `bank_reference` varchar(100),
  `remarks` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `booking_id` (`booking_id`),
  KEY `payment_date` (`payment_date`),
  FOREIGN KEY (`booking_id`) REFERENCES `plot_bookings` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Commission tracking table
CREATE TABLE `commission_tracking` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `booking_id` int(11) NOT NULL,
  `associate_id` int(11),
  `commission_type` enum('direct','level','bonus','override') DEFAULT 'direct',
  `commission_level` int(11) DEFAULT 1,
  `commission_amount` decimal(15,2) NOT NULL,
  `commission_percentage` decimal(5,2),
  `payment_status` enum('pending','paid','cancelled') DEFAULT 'pending',
  `payment_date` date,
  `transaction_id` varchar(100),
  `remarks` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `booking_id` (`booking_id`),
  KEY `associate_id` (`associate_id`),
  KEY `commission_type` (`commission_type`),
  FOREIGN KEY (`booking_id`) REFERENCES `plot_bookings` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`associate_id`) REFERENCES `associates` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Associate levels table
CREATE TABLE `associate_levels` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `level_name` varchar(50) NOT NULL,
  `level_number` int(11) NOT NULL UNIQUE,
  `min_team_size` int(11) DEFAULT 0,
  `min_personal_sales` decimal(15,2) DEFAULT 0,
  `commission_percentage` decimal(5,2) NOT NULL,
  `bonus_percentage` decimal(5,2) DEFAULT 0,
  `override_percentage` decimal(5,2) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `level_number` (`level_number`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Commission payouts table
CREATE TABLE `commission_payouts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `associate_id` int(11) NOT NULL,
  `payout_period_start` date NOT NULL,
  `payout_period_end` date NOT NULL,
  `total_commission` decimal(15,2) DEFAULT 0,
  `total_bonus` decimal(15,2) DEFAULT 0,
  `total_override` decimal(15,2) DEFAULT 0,
  `gross_amount` decimal(15,2) NOT NULL,
  `tds_deducted` decimal(15,2) DEFAULT 0,
  `processing_fee` decimal(15,2) DEFAULT 0,
  `net_amount` decimal(15,2) NOT NULL,
  `payout_status` enum('pending','processed','paid','cancelled') DEFAULT 'pending',
  `payout_date` date,
  `transaction_id` varchar(100),
  `bank_reference` varchar(100),
  `remarks` text,
  `created_by` int(11),
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `associate_id` (`associate_id`),
  KEY `payout_status` (`payout_status`),
  FOREIGN KEY (`associate_id`) REFERENCES `associates` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Associate achievements table
CREATE TABLE `associate_achievements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `associate_id` int(11) NOT NULL,
  `achievement_type` enum('team_builder','sales_champion','leadership','target_achiever') NOT NULL,
  `achievement_title` varchar(100) NOT NULL,
  `achievement_description` text,
  `target_value` decimal(15,2),
  `achieved_value` decimal(15,2),
  `achievement_date` date NOT NULL,
  `reward_amount` decimal(15,2),
  `reward_type` enum('cash','gift','travel','recognition') DEFAULT 'cash',
  `status` enum('pending','approved','paid','cancelled') DEFAULT 'pending',
  `approved_by` int(11),
  `approved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `associate_id` (`associate_id`),
  KEY `achievement_type` (`achievement_type`),
  FOREIGN KEY (`associate_id`) REFERENCES `associates` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Employee salary structure table
CREATE TABLE `employee_salary_structure` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) NOT NULL,
  `basic_salary` decimal(15,2) NOT NULL,
  `hra` decimal(15,2) DEFAULT 0,
  `da` decimal(15,2) DEFAULT 0,
  `ta` decimal(15,2) DEFAULT 0,
  `medical_allowance` decimal(15,2) DEFAULT 0,
  `special_allowance` decimal(15,2) DEFAULT 0,
  `other_allowance` decimal(15,2) DEFAULT 0,
  `pf_deduction` decimal(15,2) DEFAULT 0,
  `esi_deduction` decimal(15,2) DEFAULT 0,
  `professional_tax` decimal(15,2) DEFAULT 0,
  `tds_deduction` decimal(15,2) DEFAULT 0,
  `other_deduction` decimal(15,2) DEFAULT 0,
  `gross_salary` decimal(15,2) NOT NULL,
  `net_salary` decimal(15,2) NOT NULL,
  `effective_from` date NOT NULL,
  `effective_to` date,
  `is_active` tinyint(1) DEFAULT 1,
  `approved_by` int(11),
  `approved_at` timestamp NULL DEFAULT NULL,
  `created_by` int(11),
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `employee_id` (`employee_id`),
  KEY `is_active` (`is_active`),
  FOREIGN KEY (`employee_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Salary payments table
CREATE TABLE `salary_payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) NOT NULL,
  `salary_structure_id` int(11),
  `payment_month` int(11) NOT NULL,
  `payment_year` int(11) NOT NULL,
  `payment_date` date NOT NULL,
  `basic_amount` decimal(15,2),
  `allowance_amount` decimal(15,2),
  `gross_amount` decimal(15,2),
  `deduction_amount` decimal(15,2),
  `net_amount` decimal(15,2),
  `payment_method` enum('bank_transfer','cash','cheque') DEFAULT 'bank_transfer',
  `transaction_id` varchar(100),
  `bank_reference` varchar(100),
  `payment_status` enum('pending','processed','paid','failed','cancelled') DEFAULT 'pending',
  `payment_processed_by` int(11),
  `payment_processed_at` timestamp NULL DEFAULT NULL,
  `remarks` text,
  `created_by` int(11),
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `employee_id` (`employee_id`),
  KEY `payment_status` (`payment_status`),
  KEY `payment_month` (`payment_month`),
  KEY `payment_year` (`payment_year`),
  FOREIGN KEY (`employee_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`salary_structure_id`) REFERENCES `employee_salary_structure` (`id`) ON DELETE SET NULL,
  FOREIGN KEY (`payment_processed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Employee attendance table
CREATE TABLE `employee_attendance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) NOT NULL,
  `attendance_date` date NOT NULL,
  `check_in_time` time,
  `check_out_time` time,
  `total_hours` decimal(4,2),
  `attendance_status` enum('present','absent','half_day','leave','holiday') DEFAULT 'present',
  `leave_type` enum('casual','sick','earned','maternity','paternity','other') DEFAULT NULL,
  `remarks` text,
  `approved_by` int(11),
  `approved_at` timestamp NULL DEFAULT NULL,
  `created_by` int(11),
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `employee_id` (`employee_id`),
  KEY `attendance_date` (`attendance_date`),
  KEY `attendance_status` (`attendance_status`),
  UNIQUE KEY `unique_attendance` (`employee_id`, `attendance_date`),
  FOREIGN KEY (`employee_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Employee advances table
CREATE TABLE `employee_advances` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) NOT NULL,
  `advance_number` varchar(50) NOT NULL UNIQUE,
  `advance_amount` decimal(15,2) NOT NULL,
  `advance_date` date NOT NULL,
  `reason` text,
  `repayment_method` enum('lump_sum','installment') DEFAULT 'installment',
  `installment_amount` decimal(15,2),
  `total_installments` int(11) DEFAULT 1,
  `paid_installments` int(11) DEFAULT 0,
  `outstanding_amount` decimal(15,2) NOT NULL,
  `status` enum('pending','approved','disbursed','repaid','cancelled') DEFAULT 'pending',
  `approved_by` int(11),
  `approved_at` timestamp NULL DEFAULT NULL,
  `disbursement_date` date,
  `created_by` int(11),
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `employee_id` (`employee_id`),
  KEY `status` (`status`),
  FOREIGN KEY (`employee_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Employee bonuses table
CREATE TABLE `employee_bonuses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) NOT NULL,
  `bonus_number` varchar(50) NOT NULL UNIQUE,
  `bonus_type` enum('performance','attendance','target_achievement','festival','other') NOT NULL,
  `bonus_amount` decimal(15,2) NOT NULL,
  `bonus_month` int(11),
  `bonus_year` int(11),
  `reason` text,
  `payment_status` enum('pending','paid','cancelled') DEFAULT 'pending',
  `payment_date` date,
  `transaction_id` varchar(100),
  `approved_by` int(11),
  `approved_at` timestamp NULL DEFAULT NULL,
  `created_by` int(11),
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `employee_id` (`employee_id`),
  KEY `bonus_type` (`bonus_type`),
  KEY `payment_status` (`payment_status`),
  FOREIGN KEY (`employee_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Land acquisitions table (for plotting system)
CREATE TABLE `land_acquisitions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `acquisition_number` varchar(50) NOT NULL UNIQUE,
  `farmer_id` int(11),
  `land_area` decimal(10,2) NOT NULL,
  `land_area_unit` varchar(20) DEFAULT 'sqft',
  `location` varchar(255) NOT NULL,
  `village` varchar(100),
  `tehsil` varchar(100),
  `district` varchar(100),
  `state` varchar(100),
  `acquisition_date` date NOT NULL,
  `acquisition_cost` decimal(15,2),
  `payment_status` enum('pending','partial','completed') DEFAULT 'pending',
  `land_type` enum('agricultural','residential','commercial','industrial') DEFAULT 'agricultural',
  `soil_type` varchar(100),
  `water_source` varchar(100),
  `electricity_available` tinyint(1) DEFAULT 0,
  `road_access` tinyint(1) DEFAULT 0,
  `documents` json,
  `remarks` text,
  `status` enum('active','sold','under_development','inactive') DEFAULT 'active',
  `created_by` int(11),
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `farmer_id` (`farmer_id`),
  KEY `status` (`status`),
  KEY `acquisition_date` (`acquisition_date`),
  FOREIGN KEY (`farmer_id`) REFERENCES `farmer_profiles` (`id`) ON DELETE SET NULL,
  FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default data
INSERT INTO `property_types` (`name`, `description`, `icon`) VALUES
('Apartment', 'Modern apartment units', 'building'),
('Villa', 'Independent villa houses', 'home'),
('Plot', 'Land plots for construction', 'map-marker-alt'),
('Commercial', 'Commercial properties', 'briefcase'),
('Office', 'Office spaces', 'desktop'),
('Shop', 'Retail shops', 'shopping-cart'),
('Warehouse', 'Storage and warehouse spaces', 'warehouse');

INSERT INTO `site_settings` (`setting_name`, `setting_value`, `setting_group`, `description`) VALUES
('site_title', 'APS Dream Home - Colonizer Management System', 'general', 'Main site title'),
('site_logo', '/assets/images/logo.png', 'appearance', 'Site logo URL'),
('header_background', '#1e3c72', 'appearance', 'Header background color'),
('header_text_color', '#ffffff', 'appearance', 'Header text color'),
('footer_about', 'Your trusted partner in land development and real estate. We specialize in plotting, land acquisition, and property development.', 'content', 'Footer about text'),
('footer_contact', '123 Property Street, City, State 12345 | Phone: +1 234 567 8900 | Email: info@apsdreamhome.com', 'contact', 'Footer contact information'),
('footer_copyright', 'APS Dream Home - Colonizer Company. All rights reserved.', 'content', 'Footer copyright text');

INSERT INTO `associate_levels` (`level_name`, `level_number`, `min_team_size`, `min_personal_sales`, `commission_percentage`, `bonus_percentage`, `override_percentage`, `status`) VALUES
('Associate', 1, 0, 0, 10.00, 0.00, 0.00, 'active'),
('Senior Associate', 2, 3, 500000, 12.00, 1.00, 0.50, 'active'),
('Team Leader', 3, 10, 1500000, 15.00, 2.00, 1.00, 'active'),
('Manager', 4, 25, 5000000, 18.00, 3.00, 2.00, 'active'),
('Senior Manager', 5, 50, 10000000, 20.00, 5.00, 3.00, 'active'),
('Director', 6, 100, 25000000, 22.00, 8.00, 5.00, 'active'),
('Senior Director', 7, 200, 50000000, 25.00, 10.00, 8.00, 'active');

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
('plot_booking_confirmation', 'Plot Booking Confirmation - {{plot_number}}', 'Dear {{customer_name}},

Your plot booking has been confirmed!

Plot Details:
- Plot Number: {{plot_number}}
- Colony: {{colony_name}}
- Area: {{plot_area}} {{plot_area_unit}}
- Total Amount: ₹{{total_amount}}
- Booking Amount: ₹{{booking_amount}}

Booking Number: {{booking_number}}
Booking Date: {{booking_date}}

Please keep this information for your records.

Best regards,
APS Dream Home Team', '["customer_name", "plot_number", "colony_name", "plot_area", "plot_area_unit", "total_amount", "booking_amount", "booking_number", "booking_date"]', 'booking'),
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
INSERT INTO `users` (`username`, `email`, `password`, `full_name`, `role`, `status`, `department`, `designation`, `employee_id`, `joining_date`, `salary`) VALUES
('admin', 'admin@apsdreamhome.com', '$2y$10$8K3VZvHDz5eD4vT8p4e3QeK4mN2X8yP9Q7tR2U4V6W8Y0A2C4E6G8', 'System Administrator', 'admin', 'active', 'Management', 'Managing Director', 'EMP001', '2024-01-01', 100000.00);

COMMIT;
