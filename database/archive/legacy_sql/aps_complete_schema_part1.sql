-- ==============================
-- APS DREAM HOME - COMPLETE DATABASE SCHEMA
-- Generated from Deep Project Analysis
-- Date: 2025-09-24
-- ==============================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

-- Create database if not exists
CREATE DATABASE IF NOT EXISTS `apsdreamhome` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `apsdreamhome`;

-- ==============================
-- CORE SYSTEM TABLES
-- ==============================

-- 1. Admin Management
CREATE TABLE `admin` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `auser` varchar(100) NOT NULL,
    `apass` varchar(255) NOT NULL,
    `role` enum('superadmin','admin','ceo','cfo','cm','coo','cto','director','finance','hr','it_head','legal','manager','marketing','office_admin','official_employee','operations','sales','support') NOT NULL DEFAULT 'admin',
    `status` enum('active','inactive') NOT NULL DEFAULT 'active',
    `email` varchar(255) DEFAULT NULL,
    `phone` varchar(20) DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`id`),
    UNIQUE KEY `auser` (`auser`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Users (End Users/Customers)
CREATE TABLE `users` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(100) NOT NULL,
    `email` varchar(255) NOT NULL,
    `phone` varchar(20) NOT NULL,
    `password` varchar(255) NOT NULL,
    `role` enum('admin','user','agent','associate') NOT NULL DEFAULT 'user',
    `status` enum('active','inactive') NOT NULL DEFAULT 'active',
    `profile_picture` varchar(255) DEFAULT NULL,
    `api_access` tinyint(1) DEFAULT 0,
    `api_rate_limit` int(11) DEFAULT 1000,
    `google2fa_secret` text DEFAULT NULL,
    `two_factor_recovery_codes` text DEFAULT NULL,
    `two_factor_confirmed_at` timestamp NULL DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`id`),
    UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Customers (Property Buyers)
CREATE TABLE `customers` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(100) NOT NULL,
    `email` varchar(100) NOT NULL,
    `phone` varchar(20) NOT NULL,
    `address` text DEFAULT NULL,
    `status` enum('active','inactive') NOT NULL DEFAULT 'active',
    `kyc_status` enum('pending','verified','rejected') DEFAULT 'pending',
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    UNIQUE KEY `email` (`email`),
    UNIQUE KEY `phone` (`phone`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==============================
-- ASSOCIATE & MLM SYSTEM
-- ==============================

-- 4. Associates (MLM Network)  
CREATE TABLE `associates` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(100) NOT NULL,
    `email` varchar(100) NOT NULL,
    `phone` varchar(20) NOT NULL,
    `user_id` int(11) DEFAULT NULL,
    `parent_id` int(11) DEFAULT NULL,
    `level` int(11) DEFAULT 1,
    `current_level` int(11) DEFAULT 1,
    `commission_percent` decimal(5,2) DEFAULT 5.00,
    `commission_plan_id` int(11) DEFAULT 1,
    `join_date` date DEFAULT NULL,
    `status` enum('active','inactive') DEFAULT 'active',
    `total_business` decimal(15,2) DEFAULT 0.00,
    `direct_business` decimal(15,2) DEFAULT 0.00,
    `team_business` decimal(15,2) DEFAULT 0.00,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    UNIQUE KEY `email` (`email`),
    UNIQUE KEY `phone` (`phone`),
    KEY `parent_id` (`parent_id`),
    KEY `user_id` (`user_id`),
    FOREIGN KEY (`parent_id`) REFERENCES `associates` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Associate Levels (MLM Hierarchy)
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
    `created_at` timestamp DEFAULT current_timestamp(),
    `updated_at` timestamp DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==============================
-- PROPERTY MANAGEMENT
-- ==============================

-- 6. Projects (Property Developments)
CREATE TABLE `projects` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL,
    `description` text DEFAULT NULL,
    `location` varchar(255) NOT NULL,
    `status` enum('active','inactive','completed') NOT NULL DEFAULT 'active',
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Properties (Individual Properties)
CREATE TABLE `properties` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `title` varchar(255) NOT NULL,
    `description` text DEFAULT NULL,
    `price` decimal(12,2) NOT NULL,
    `location` varchar(255) NOT NULL,
    `bedrooms` int(11) DEFAULT NULL,
    `bathrooms` int(11) DEFAULT NULL,
    `area` decimal(10,2) DEFAULT NULL,
    `status` enum('available','sold','rented') NOT NULL DEFAULT 'available',
    `is_featured` tinyint(1) DEFAULT 0,
    `image_path` varchar(255) DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Plots (Project Plots)
CREATE TABLE `plots` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `project_id` int(11) NOT NULL,
    `plot_no` varchar(50) NOT NULL,
    `size_sqft` decimal(10,2) DEFAULT NULL,
    `current_price` decimal(12,2) DEFAULT NULL,
    `status` enum('available','booked','sold','rented','resale') NOT NULL DEFAULT 'available',
    `customer_id` int(11) DEFAULT NULL,
    `associate_id` int(11) DEFAULT NULL,
    `sale_id` int(11) DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `project_id` (`project_id`),
    KEY `customer_id` (`customer_id`),
    KEY `associate_id` (`associate_id`),
    FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==============================
-- BOOKING & TRANSACTION SYSTEM
-- ==============================

-- 9. Bookings (Property Bookings)
CREATE TABLE `bookings` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `property_id` int(11) DEFAULT NULL,
    `plot_id` int(11) DEFAULT NULL,
    `customer_id` int(11) NOT NULL,
    `associate_id` int(11) DEFAULT NULL,
    `booking_date` date DEFAULT NULL,
    `amount` decimal(15,2) DEFAULT NULL,
    `booking_amount` decimal(15,2) DEFAULT NULL,
    `total_amount` decimal(15,2) DEFAULT NULL,
    `status` enum('pending','booked','confirmed','cancelled','completed') DEFAULT 'booked',
    `payment_status` enum('pending','partial','paid','failed') DEFAULT 'pending',
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `property_id` (`property_id`),
    KEY `plot_id` (`plot_id`),
    KEY `customer_id` (`customer_id`),
    KEY `associate_id` (`associate_id`),
    FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE SET NULL,
    FOREIGN KEY (`plot_id`) REFERENCES `plots` (`id`) ON DELETE SET NULL,
    FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`associate_id`) REFERENCES `associates` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. Plot Bookings (Specific for Plot Bookings)  
CREATE TABLE `plot_bookings` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `plot_id` int(11) NOT NULL,
    `customer_id` int(11) NOT NULL,
    `associate_id` int(11) DEFAULT NULL,
    `booking_amount` decimal(15,2) NOT NULL,
    `total_amount` decimal(15,2) NOT NULL,
    `payment_mode` enum('cash','cheque','bank_transfer','online','emi') DEFAULT 'cash',
    `booking_date` date NOT NULL,
    `possession_date` date DEFAULT NULL,
    `status` enum('booked','confirmed','cancelled','completed') DEFAULT 'booked',
    `payment_status` enum('pending','partial','completed') DEFAULT 'pending',
    `created_at` timestamp DEFAULT current_timestamp(),
    `updated_at` timestamp DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `plot_id` (`plot_id`),
    KEY `customer_id` (`customer_id`),
    KEY `associate_id` (`associate_id`),
    FOREIGN KEY (`plot_id`) REFERENCES `plots` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`associate_id`) REFERENCES `associates` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Continue in next part...
COMMIT;