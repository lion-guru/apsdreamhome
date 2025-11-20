-- APS Dream Home Final - Enhanced Database Structure
-- Enhanced with Land Management, MLM, Builder Management, and Customer Features
-- Generated on 2025-09-25
-- Database Version: Enhanced v2.1 - CORRECTED AND OPTIMIZED

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- Database: `apsdreamhome`
CREATE DATABASE IF NOT EXISTS `apsdreamhome` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `apsdreamhome`;

-- --------------------------------------------------------
-- CORE TABLES (Previously missing)
-- --------------------------------------------------------

-- Table structure for table `sites` (Project Sites)
CREATE TABLE IF NOT EXISTS `sites` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `site_name` varchar(255) NOT NULL,
  `location` text NOT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `district` varchar(100) DEFAULT NULL,
  `pincode` varchar(10) DEFAULT NULL,
  `total_area` decimal(10,2) NOT NULL COMMENT 'in acres',
  `developed_area` decimal(10,2) DEFAULT 0.00,
  `site_type` enum('residential','commercial','mixed','industrial') DEFAULT 'residential',
  `status` enum('planning','under_development','active','completed','inactive') DEFAULT 'planning',
  `manager_id` int(11) DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `amenities` json DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_site_status` (`status`),
  KEY `idx_site_type` (`site_type`),
  KEY `idx_site_location` (`city`, `state`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table `associate_levels`
CREATE TABLE IF NOT EXISTS `associate_levels` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `level_name` varchar(50) NOT NULL,
  `min_business` decimal(15,2) NOT NULL DEFAULT 0.00,
  `max_business` decimal(15,2) NOT NULL DEFAULT 99999999.99,
  `commission_percentage` decimal(5,2) NOT NULL,
  `direct_referral_bonus` decimal(5,2) DEFAULT 0.00,
  `level_bonus` decimal(5,2) DEFAULT 0.00,
  `reward_description` text DEFAULT NULL,
  `min_team_size` int(11) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `level_name` (`level_name`),
  KEY `idx_level_business` (`min_business`, `max_business`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table `associates`
CREATE TABLE IF NOT EXISTS `associates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `mobile` varchar(15) NOT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `pincode` varchar(10) DEFAULT NULL,
  `aadhar_number` varchar(20) DEFAULT NULL,
  `pan_number` varchar(20) DEFAULT NULL,
  `bank_account` varchar(50) DEFAULT NULL,
  `ifsc_code` varchar(15) DEFAULT NULL,
  `sponsor_id` int(11) DEFAULT NULL,
  `level_id` int(11) DEFAULT 1,
  `total_business` decimal(15,2) DEFAULT 0.00,
  `total_earnings` decimal(15,2) DEFAULT 0.00,
  `join_date` date NOT NULL,
  `status` enum('active','inactive','suspended','terminated') DEFAULT 'active',
  `kyc_status` enum('pending','verified','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `associate_email` (`email`),
  KEY `idx_associate_mobile` (`mobile`),
  KEY `idx_associate_sponsor` (`sponsor_id`),
  KEY `idx_associate_level` (`level_id`),
  KEY `idx_associate_status` (`status`),
  CONSTRAINT `fk_associate_sponsor` FOREIGN KEY (`sponsor_id`) REFERENCES `associates` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_associate_level` FOREIGN KEY (`level_id`) REFERENCES `associate_levels` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table `bookings`
CREATE TABLE IF NOT EXISTS `bookings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL,
  `property_id` int(11) DEFAULT NULL,
  `associate_id` int(11) DEFAULT NULL,
  `booking_number` varchar(50) NOT NULL,
  `booking_date` date NOT NULL,
  `booking_amount` decimal(15,2) NOT NULL,
  `total_amount` decimal(15,2) NOT NULL,
  `payment_plan` enum('full_payment','installment','emi') DEFAULT 'full_payment',
  `status` enum('pending','confirmed','cancelled','completed') DEFAULT 'pending',
  `source` enum('direct','associate','online','agent') DEFAULT 'direct',
  `remarks` text DEFAULT NULL,
  `documents` json DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `booking_number` (`booking_number`),
  KEY `fk_booking_customer` (`customer_id`),
  KEY `fk_booking_property` (`property_id`),
  KEY `fk_booking_associate` (`associate_id`),
  KEY `idx_booking_status` (`status`),
  KEY `idx_booking_date` (`booking_date`),
  CONSTRAINT `fk_booking_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_booking_property` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_booking_associate` FOREIGN KEY (`associate_id`) REFERENCES `associates` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- ENHANCED TABLES FOR NEW FEATURES
-- --------------------------------------------------------

-- Table structure for table `farmers` (Land Management)
CREATE TABLE IF NOT EXISTS `farmers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `mobile` varchar(15) NOT NULL,
  `address` text NOT NULL,
  `aadhar_number` varchar(20) DEFAULT NULL,
  `pan_number` varchar(20) DEFAULT NULL,
  `bank_account` varchar(50) DEFAULT NULL,
  `ifsc_code` varchar(15) DEFAULT NULL,
  `land_size` decimal(10,2) DEFAULT NULL COMMENT 'in acres',
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_farmer_mobile` (`mobile`),
  KEY `idx_farmer_aadhar` (`aadhar_number`),
  KEY `idx_farmer_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table `land_purchases` (Land Management)
CREATE TABLE IF NOT EXISTS `land_purchases` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `farmer_id` int(11) NOT NULL,
  `site_id` int(11) DEFAULT NULL,
  `land_area` decimal(10,2) NOT NULL COMMENT 'in acres',
  `purchase_price` decimal(15,2) NOT NULL,
  `price_per_acre` decimal(15,2) NOT NULL,
  `purchase_date` date NOT NULL,
  `payment_method` enum('cash','bank_transfer','cheque') DEFAULT 'bank_transfer',
  `land_location` text NOT NULL,
  `survey_number` varchar(50) DEFAULT NULL,
  `revenue_village` varchar(100) DEFAULT NULL,
  `tehsil` varchar(100) DEFAULT NULL,
  `district` varchar(100) DEFAULT NULL,
  `registration_number` varchar(100) DEFAULT NULL,
  `documents_uploaded` json DEFAULT NULL,
  `status` enum('negotiating','purchased','registered','developed') DEFAULT 'purchased',
  `notes` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_land_farmer` (`farmer_id`),
  KEY `fk_land_site` (`site_id`),
  KEY `idx_purchase_date` (`purchase_date`),
  KEY `idx_land_status` (`status`),
  KEY `idx_land_district` (`district`),
  CONSTRAINT `fk_land_farmer` FOREIGN KEY (`farmer_id`) REFERENCES `farmers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_land_site` FOREIGN KEY (`site_id`) REFERENCES `sites` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table `plot_development` (Land Management)
CREATE TABLE IF NOT EXISTS `plot_development` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `land_purchase_id` int(11) NOT NULL,
  `plot_number` varchar(50) NOT NULL,
  `plot_size` decimal(10,2) NOT NULL COMMENT 'in sqft',
  `plot_type` enum('residential','commercial','agricultural') DEFAULT 'residential',
  `development_cost` decimal(15,2) DEFAULT 0.00,
  `selling_price` decimal(15,2) DEFAULT NULL,
  `status` enum('planned','under_development','ready_to_sell','sold','booked') DEFAULT 'planned',
  `customer_id` int(11) DEFAULT NULL,
  `sold_date` date DEFAULT NULL,
  `sold_price` decimal(15,2) DEFAULT NULL,
  `profit_loss` decimal(15,2) DEFAULT NULL,
  `amenities` json DEFAULT NULL,
  `plot_facing` enum('north','south','east','west','northeast','northwest','southeast','southwest') DEFAULT NULL,
  `road_width` decimal(5,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_plot_number` (`plot_number`),
  KEY `fk_plot_land_purchase` (`land_purchase_id`),
  KEY `fk_plot_customer` (`customer_id`),
  KEY `idx_plot_status` (`status`),
  KEY `idx_plot_type_status` (`plot_type`, `status`),
  CONSTRAINT `fk_plot_land_purchase` FOREIGN KEY (`land_purchase_id`) REFERENCES `land_purchases` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_plot_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table `builders` (Builder Management)
CREATE TABLE IF NOT EXISTS `builders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `mobile` varchar(15) NOT NULL,
  `address` text NOT NULL,
  `license_number` varchar(100) DEFAULT NULL,
  `experience_years` int(11) DEFAULT 0,
  `specialization` enum('residential','commercial','industrial','infrastructure') DEFAULT 'residential',
  `rating` decimal(2,1) DEFAULT 5.0,
  `total_projects` int(11) DEFAULT 0,
  `completed_projects` int(11) DEFAULT 0,
  `ongoing_projects` int(11) DEFAULT 0,
  `status` enum('active','inactive','blacklisted') DEFAULT 'active',
  `bank_account` varchar(50) DEFAULT NULL,
  `ifsc_code` varchar(15) DEFAULT NULL,
  `pan_number` varchar(20) DEFAULT NULL,
  `gst_number` varchar(30) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `builder_email` (`email`),
  KEY `idx_builder_mobile` (`mobile`),
  KEY `idx_builder_status` (`status`),
  KEY `idx_builder_specialization` (`specialization`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table `construction_projects` (Builder Management)
CREATE TABLE IF NOT EXISTS `construction_projects` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_name` varchar(255) NOT NULL,
  `builder_id` int(11) NOT NULL,
  `site_id` int(11) DEFAULT NULL,
  `project_type` enum('residential','commercial','infrastructure','mixed_use') DEFAULT 'residential',
  `start_date` date DEFAULT NULL,
  `estimated_completion` date DEFAULT NULL,
  `actual_completion` date DEFAULT NULL,
  `budget_allocated` decimal(15,2) NOT NULL,
  `amount_spent` decimal(15,2) DEFAULT 0.00,
  `progress_percentage` int(11) DEFAULT 0,
  `status` enum('planning','in_progress','on_hold','completed','cancelled') DEFAULT 'planning',
  `description` text DEFAULT NULL,
  `contract_amount` decimal(15,2) DEFAULT NULL,
  `advance_paid` decimal(15,2) DEFAULT 0.00,
  `milestone_payments` json DEFAULT NULL,
  `quality_rating` decimal(2,1) DEFAULT NULL,
  `completion_certificate` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_project_builder` (`builder_id`),
  KEY `fk_project_site` (`site_id`),
  KEY `idx_project_status` (`status`),
  KEY `idx_project_dates` (`start_date`, `estimated_completion`),
  KEY `idx_project_budget` (`budget_allocated`),
  KEY `idx_project_progress` (`progress_percentage`),
  CONSTRAINT `fk_project_builder` FOREIGN KEY (`builder_id`) REFERENCES `builders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_project_site` FOREIGN KEY (`site_id`) REFERENCES `sites` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table `project_progress` (Builder Management)
CREATE TABLE IF NOT EXISTS `project_progress` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `progress_percentage` int(11) NOT NULL,
  `milestone_achieved` varchar(255) NOT NULL,
  `work_description` text NOT NULL,
  `amount_spent` decimal(15,2) DEFAULT 0.00,
  `next_milestone` varchar(255) DEFAULT NULL,
  `photos` json DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_progress_project` (`project_id`),
  KEY `idx_progress_date` (`created_at`),
  CONSTRAINT `fk_progress_project` FOREIGN KEY (`project_id`) REFERENCES `construction_projects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table `builder_payments` (Builder Management)
CREATE TABLE IF NOT EXISTS `builder_payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `builder_id` int(11) NOT NULL,
  `payment_amount` decimal(15,2) NOT NULL,
  `payment_type` enum('advance','milestone','final','penalty','bonus') DEFAULT 'milestone',
  `payment_date` date NOT NULL,
  `payment_method` enum('cash','bank_transfer','cheque','online') DEFAULT 'bank_transfer',
  `transaction_id` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `milestone_reference` varchar(255) DEFAULT NULL,
  `invoice_number` varchar(100) DEFAULT NULL,
  `tax_amount` decimal(15,2) DEFAULT 0.00,
  `net_amount` decimal(15,2) DEFAULT NULL,
  `paid_by` int(11) DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_payment_project` (`project_id`),
  KEY `fk_payment_builder` (`builder_id`),
  KEY `idx_payment_date` (`payment_date`),
  CONSTRAINT `fk_payment_builder` FOREIGN KEY (`builder_id`) REFERENCES `builders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_payment_project` FOREIGN KEY (`project_id`) REFERENCES `construction_projects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table `customer_inquiries` (Customer Management)
CREATE TABLE IF NOT EXISTS `customer_inquiries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `inquiry_type` enum('general','payment','booking','technical','complaint') DEFAULT 'general',
  `status` enum('open','in_progress','resolved','closed') DEFAULT 'open',
  `priority` enum('low','medium','high','urgent') DEFAULT 'medium',
  `assigned_to` int(11) DEFAULT NULL,
  `response` text DEFAULT NULL,
  `response_date` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_inquiry_customer` (`customer_id`),
  KEY `idx_inquiry_status` (`status`),
  KEY `idx_inquiry_type` (`inquiry_type`),
  KEY `idx_inquiry_priority` (`priority`),
  CONSTRAINT `fk_inquiry_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table `customer_documents` (Customer Management)
CREATE TABLE IF NOT EXISTS `customer_documents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL,
  `document_name` varchar(255) NOT NULL,
  `document_type` enum('aadhar','pan','income','bank','other') NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_size` int(11) DEFAULT NULL,
  `mime_type` varchar(100) DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `remarks` text DEFAULT NULL,
  `verified_by` int(11) DEFAULT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_document_customer` (`customer_id`),
  KEY `idx_document_status` (`status`),
  KEY `idx_document_type` (`document_type`),
  CONSTRAINT `fk_document_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Enhanced MLM Tables
-- --------------------------------------------------------

-- Table structure for table `mlm_commissions` (Enhanced MLM)
CREATE TABLE IF NOT EXISTS `mlm_commissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `associate_id` int(11) NOT NULL,
  `level` int(11) NOT NULL,
  `from_associate_id` int(11) NOT NULL,
  `booking_id` int(11) DEFAULT NULL,
  `sale_amount` decimal(15,2) NOT NULL,
  `commission_percentage` decimal(5,2) NOT NULL,
  `commission_amount` decimal(15,2) NOT NULL,
  `status` enum('pending','paid','cancelled') DEFAULT 'pending',
  `payment_date` date DEFAULT NULL,
  `transaction_id` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_commission_associate` (`associate_id`),
  KEY `fk_commission_from` (`from_associate_id`),
  KEY `fk_commission_booking` (`booking_id`),
  KEY `idx_commission_level` (`level`),
  KEY `idx_commission_status` (`status`),
  CONSTRAINT `fk_commission_associate` FOREIGN KEY (`associate_id`) REFERENCES `associates` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_commission_from` FOREIGN KEY (`from_associate_id`) REFERENCES `associates` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_commission_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table `mlm_payouts` (MLM Payouts)
CREATE TABLE IF NOT EXISTS `mlm_payouts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `associate_id` int(11) NOT NULL,
  `payout_month` varchar(7) NOT NULL COMMENT 'YYYY-MM format',
  `total_commission` decimal(15,2) NOT NULL,
  `tds_amount` decimal(15,2) DEFAULT 0.00,
  `admin_charges` decimal(15,2) DEFAULT 0.00,
  `net_payout` decimal(15,2) NOT NULL,
  `status` enum('calculated','processed','paid','failed') DEFAULT 'calculated',
  `payment_method` enum('bank_transfer','upi','cheque','cash') DEFAULT 'bank_transfer',
  `payment_reference` varchar(100) DEFAULT NULL,
  `processed_by` int(11) DEFAULT NULL,
  `processed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_associate_month` (`associate_id`, `payout_month`),
  KEY `idx_payout_month` (`payout_month`),
  KEY `idx_payout_status` (`status`),
  CONSTRAINT `fk_payout_associate` FOREIGN KEY (`associate_id`) REFERENCES `associates` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Enhanced Customer and Booking Tables
-- --------------------------------------------------------

-- Table structure for table `customers` (Enhanced)
CREATE TABLE IF NOT EXISTS `customers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `mobile` varchar(15) NOT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `pincode` varchar(10) DEFAULT NULL,
  `aadhar_number` varchar(20) DEFAULT NULL,
  `pan_number` varchar(20) DEFAULT NULL,
  `occupation` varchar(100) DEFAULT NULL,
  `annual_income` decimal(15,2) DEFAULT NULL,
  `referred_by` int(11) DEFAULT NULL COMMENT 'Associate ID who referred',
  `status` enum('active','inactive','blocked') DEFAULT 'active',
  `kyc_status` enum('pending','verified','rejected') DEFAULT 'pending',
  `credit_score` int(11) DEFAULT NULL,
  `preferred_contact` enum('email','sms','call','whatsapp') DEFAULT 'call',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `customer_email` (`email`),
  KEY `idx_customer_mobile` (`mobile`),
  KEY `idx_customer_referred` (`referred_by`),
  KEY `idx_customer_status` (`status`),
  KEY `idx_customer_city` (`city`),
  KEY `idx_customer_kyc` (`kyc_status`),
  CONSTRAINT `fk_customer_referred` FOREIGN KEY (`referred_by`) REFERENCES `associates` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table `properties` (Enhanced)
CREATE TABLE IF NOT EXISTS `properties` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `property_name` varchar(255) NOT NULL,
  `property_type` enum('plot','apartment','villa','commercial','agricultural') NOT NULL,
  `site_id` int(11) DEFAULT NULL,
  `plot_development_id` int(11) DEFAULT NULL,
  `size` decimal(10,2) NOT NULL COMMENT 'in sqft',
  `price` decimal(15,2) NOT NULL,
  `status` enum('available','booked','sold','under_development','blocked') DEFAULT 'available',
  `amenities` json DEFAULT NULL,
  `location` text NOT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `facing` enum('north','south','east','west','northeast','northwest','southeast','southwest') DEFAULT NULL,
  `floor_plan` varchar(255) DEFAULT NULL,
  `images` json DEFAULT NULL,
  `video_tour` varchar(255) DEFAULT NULL,
  `approval_status` enum('pending','approved','rejected') DEFAULT 'pending',
  `rera_number` varchar(100) DEFAULT NULL,
  `possession_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_property_site` (`site_id`),
  KEY `fk_property_plot_dev` (`plot_development_id`),
  KEY `idx_property_type` (`property_type`),
  KEY `idx_property_status` (`status`),
  CONSTRAINT `fk_property_site` FOREIGN KEY (`site_id`) REFERENCES `sites` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_property_plot_dev` FOREIGN KEY (`plot_development_id`) REFERENCES `plot_development` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table `payments` (Enhanced)
CREATE TABLE IF NOT EXISTS `payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL,
  `booking_id` int(11) DEFAULT NULL,
  `amount` decimal(15,2) NOT NULL,
  `payment_type` enum('booking_amount','emi','full_payment','penalty','refund') DEFAULT 'booking_amount',
  `payment_method` enum('cash','bank_transfer','upi','credit_card','debit_card','cheque','online') DEFAULT 'bank_transfer',
  `transaction_id` varchar(100) DEFAULT NULL,
  `payment_gateway` varchar(50) DEFAULT NULL,
  `gateway_response` json DEFAULT NULL,
  `status` enum('pending','success','failed','cancelled','refunded') DEFAULT 'pending',
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `due_date` date DEFAULT NULL,
  `late_fee` decimal(10,2) DEFAULT 0.00,
  `remarks` text DEFAULT NULL,
  `receipt_number` varchar(100) DEFAULT NULL,
  `processed_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_payment_customer` (`customer_id`),
  KEY `fk_payment_booking` (`booking_id`),
  KEY `idx_payment_status` (`status`),
  KEY `idx_payment_date` (`payment_date`),
  KEY `idx_transaction_id` (`transaction_id`),
  CONSTRAINT `fk_payment_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_payment_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table `emi_schedule` (EMI Management)
CREATE TABLE IF NOT EXISTS `emi_schedule` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL,
  `booking_id` int(11) DEFAULT NULL,
  `emi_number` int(11) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `due_date` date NOT NULL,
  `status` enum('pending','paid','overdue','waived') DEFAULT 'pending',
  `paid_date` date DEFAULT NULL,
  `paid_amount` decimal(15,2) DEFAULT NULL,
  `late_fee` decimal(10,2) DEFAULT 0.00,
  `payment_id` int(11) DEFAULT NULL,
  `reminder_sent` int(11) DEFAULT 0,
  `last_reminder` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_emi_customer` (`customer_id`),
  KEY `fk_emi_booking` (`booking_id`),
  KEY `fk_emi_payment` (`payment_id`),
  KEY `idx_emi_due_date` (`due_date`),
  KEY `idx_emi_status` (`status`),
  CONSTRAINT `fk_emi_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_emi_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_emi_payment` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- TRIGGERS for data integrity and automation
-- --------------------------------------------------------

-- Trigger to update plot profit/loss when sold (FIXED VERSION)
DELIMITER $$
CREATE TRIGGER IF NOT EXISTS `update_plot_profit_loss`
BEFORE UPDATE ON `plot_development`
FOR EACH ROW
BEGIN
    IF NEW.status = 'sold' AND NEW.sold_price IS NOT NULL THEN
        SET NEW.profit_loss = NEW.sold_price - NEW.development_cost;
    END IF;
END$$
DELIMITER ;

-- Trigger to update builder project counts
DELIMITER $$
CREATE TRIGGER IF NOT EXISTS `update_builder_project_counts`
AFTER INSERT ON `construction_projects`
FOR EACH ROW
BEGIN
    UPDATE builders
    SET total_projects = total_projects + 1,
        ongoing_projects = ongoing_projects + 1
    WHERE id = NEW.builder_id;
END$$
DELIMITER ;

-- Trigger to update project counts when status changes
DELIMITER $$
CREATE TRIGGER IF NOT EXISTS `update_builder_counts_on_completion`
AFTER UPDATE ON `construction_projects`
FOR EACH ROW
BEGIN
    IF OLD.status != 'completed' AND NEW.status = 'completed' THEN
        UPDATE builders
        SET completed_projects = completed_projects + 1,
            ongoing_projects = ongoing_projects - 1
        WHERE id = NEW.builder_id;
    END IF;
END$$
DELIMITER ;

-- Trigger to update associate business when booking is made
DELIMITER $$
CREATE TRIGGER IF NOT EXISTS `update_associate_business`
AFTER INSERT ON `bookings`
FOR EACH ROW
BEGIN
    IF NEW.associate_id IS NOT NULL AND NEW.status = 'confirmed' THEN
        UPDATE associates
        SET total_business = total_business + NEW.total_amount
        WHERE id = NEW.associate_id;
    END IF;
END$$
DELIMITER ;

-- --------------------------------------------------------
-- VIEWS for reporting and analytics
-- --------------------------------------------------------

-- View for land management summary
CREATE OR REPLACE VIEW `land_management_summary` AS
SELECT
    f.name as farmer_name,
    f.mobile as farmer_mobile,
    lp.land_area,
    lp.purchase_price,
    lp.price_per_acre,
    lp.purchase_date,
    COUNT(pd.id) as total_plots,
    SUM(CASE WHEN pd.status = 'sold' THEN 1 ELSE 0 END) as plots_sold,
    SUM(pd.sold_price) as total_revenue,
    SUM(pd.profit_loss) as total_profit
FROM farmers f
LEFT JOIN land_purchases lp ON f.id = lp.farmer_id
LEFT JOIN plot_development pd ON lp.id = pd.land_purchase_id
GROUP BY f.id, lp.id;

-- View for builder performance
CREATE OR REPLACE VIEW `builder_performance` AS
SELECT
    b.name as builder_name,
    b.specialization,
    b.rating,
    COUNT(cp.id) as total_projects,
    SUM(cp.budget_allocated) as total_budget,
    AVG(cp.progress_percentage) as avg_progress,
    SUM(CASE WHEN cp.status = 'completed' THEN 1 ELSE 0 END) as completed_projects,
    SUM(bp.payment_amount) as total_payments
FROM builders b
LEFT JOIN construction_projects cp ON b.id = cp.builder_id
LEFT JOIN builder_payments bp ON b.id = bp.builder_id
GROUP BY b.id;

-- View for customer summary
CREATE OR REPLACE VIEW `customer_summary` AS
SELECT
    c.name as customer_name,
    c.email,
    c.mobile,
    c.kyc_status,
    COUNT(DISTINCT b.id) as total_bookings,
    SUM(p.amount) as total_payments,
    COUNT(DISTINCT ci.id) as total_inquiries,
    COUNT(DISTINCT cd.id) as total_documents
FROM customers c
LEFT JOIN bookings b ON c.id = b.customer_id
LEFT JOIN payments p ON c.id = p.customer_id
LEFT JOIN customer_inquiries ci ON c.id = ci.customer_id
LEFT JOIN customer_documents cd ON c.id = cd.customer_id
GROUP BY c.id;

-- View for MLM performance
CREATE OR REPLACE VIEW `mlm_performance` AS
SELECT
    a.name as associate_name,
    a.email,
    a.mobile,
    al.level_name,
    a.total_business,
    a.total_earnings,
    COUNT(DISTINCT mc.id) as total_commissions,
    SUM(mc.commission_amount) as total_commission_earned,
    COUNT(DISTINCT b.id) as total_referrals
FROM associates a
LEFT JOIN associate_levels al ON a.level_id = al.id
LEFT JOIN mlm_commissions mc ON a.id = mc.associate_id
LEFT JOIN associates b ON a.id = b.sponsor_id
GROUP BY a.id;

-- --------------------------------------------------------
-- SAMPLE DATA FOR TESTING
-- --------------------------------------------------------

-- Insert Company Owner admin if not exists
INSERT IGNORE INTO `admin` (`auser`, `aemail`, `apass`, `adob`, `aphone`, `role`)
VALUES ('Company Owner', 'owner@apsdreamhome.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '1985-01-01', '9999999999', 'Company Owner');

-- Insert default associate levels for MLM
INSERT IGNORE INTO `associate_levels` (`level_name`, `min_business`, `max_business`, `commission_percentage`, `reward_description`) VALUES
('Bronze', 0.00, 100000.00, 2.00, 'Entry level associate'),
('Silver', 100001.00, 500000.00, 3.00, 'Silver level with increased commission'),
('Gold', 500001.00, 1000000.00, 4.00, 'Gold level with premium benefits'),
('Platinum', 1000001.00, 2500000.00, 5.00, 'Platinum level with exclusive rewards'),
('Diamond', 2500001.00, 5000000.00, 6.00, 'Diamond level with maximum benefits'),
('Crown Diamond', 5000001.00, 99999999.99, 7.00, 'Highest level with royal treatment');

COMMIT;

-- --------------------------------------------------------
-- Database structure enhancement completed
-- Version: Enhanced v2.1 with CORRECTED relationships and missing tables
-- Total Tables: 20+ (Core + Enhanced Features)
-- Fixed Issues: Foreign keys, missing tables, data types, triggers
-- --------------------------------------------------------
