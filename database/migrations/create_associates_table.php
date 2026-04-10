<?php

/**
 * Migration: Create Associates Table
 *
 * This migration creates the associates table to store property associate information
 * including their contact details, commission rates, performance metrics, and wallet information.
 */

// Database configuration
$host = 'localhost';
$port = '3307';
$dbname = 'apsdreamhome';
$username = 'root';
$password = '';

try {
    // Create connection
    $conn = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create associates table
    $sql = "CREATE TABLE IF NOT EXISTS `associates` (
        `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `user_id` INT(11) UNSIGNED DEFAULT NULL,
        `name` VARCHAR(255) NOT NULL,
        `email` VARCHAR(255) NOT NULL UNIQUE,
        `phone` VARCHAR(20) NOT NULL,
        `password` VARCHAR(255) NOT NULL,
        `referral_code` VARCHAR(50) DEFAULT NULL,
        `referred_by` INT(11) UNSIGNED DEFAULT NULL,
        `commission_rate` DECIMAL(5,2) DEFAULT 2.50,
        `experience_years` INT(11) DEFAULT 0,
        `status` ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
        `total_properties` INT(11) DEFAULT 0,
        `sold_properties` INT(11) DEFAULT 0,
        `total_commission` DECIMAL(15,2) DEFAULT 0.00,
        `wallet_balance` DECIMAL(15,2) DEFAULT 0.00,
        `rating` DECIMAL(3,2) DEFAULT 0.00,
        `total_reviews` INT(11) DEFAULT 0,
        `address` TEXT DEFAULT NULL,
        `city` VARCHAR(100) DEFAULT NULL,
        `state` VARCHAR(100) DEFAULT NULL,
        `pincode` VARCHAR(10) DEFAULT NULL,
        `pan_number` VARCHAR(20) DEFAULT NULL,
        `aadhar_number` VARCHAR(20) DEFAULT NULL,
        `bank_name` VARCHAR(100) DEFAULT NULL,
        `account_number` VARCHAR(50) DEFAULT NULL,
        `ifsc_code` VARCHAR(20) DEFAULT NULL,
        `profile_image` VARCHAR(255) DEFAULT NULL,
        `joined_date` DATETIME DEFAULT CURRENT_TIMESTAMP,
        `last_login` DATETIME DEFAULT NULL,
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `email` (`email`),
        KEY `user_id` (`user_id`),
        KEY `referral_code` (`referral_code`),
        KEY `referred_by` (`referred_by`),
        KEY `status` (`status`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    $conn->exec($sql);
    echo "✓ Associates table created successfully\n";

    // Create associate_activities table for tracking associate actions
    $sql = "CREATE TABLE IF NOT EXISTS `associate_activities` (
        `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `associate_id` INT(11) UNSIGNED NOT NULL,
        `activity_type` VARCHAR(50) NOT NULL,
        `description` TEXT DEFAULT NULL,
        `property_id` INT(11) UNSIGNED DEFAULT NULL,
        `lead_id` INT(11) UNSIGNED DEFAULT NULL,
        `metadata` JSON DEFAULT NULL,
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `associate_id` (`associate_id`),
        KEY `activity_type` (`activity_type`),
        KEY `created_at` (`created_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    $conn->exec($sql);
    echo "✓ Associate activities table created successfully\n";

    // Create associate_commissions table for tracking commission payments
    $sql = "CREATE TABLE IF NOT EXISTS `associate_commissions` (
        `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `associate_id` INT(11) UNSIGNED NOT NULL,
        `property_id` INT(11) UNSIGNED DEFAULT NULL,
        `lead_id` INT(11) UNSIGNED DEFAULT NULL,
        `deal_id` INT(11) UNSIGNED DEFAULT NULL,
        `commission_amount` DECIMAL(15,2) NOT NULL,
        `commission_rate` DECIMAL(5,2) NOT NULL,
        `property_value` DECIMAL(15,2) DEFAULT NULL,
        `status` ENUM('pending', 'approved', 'paid', 'rejected') DEFAULT 'pending',
        `payment_date` DATETIME DEFAULT NULL,
        `remarks` TEXT DEFAULT NULL,
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `associate_id` (`associate_id`),
        KEY `status` (`status`),
        KEY `created_at` (`created_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    $conn->exec($sql);
    echo "✓ Associate commissions table created successfully\n";

    // Create associate_properties table to link associates with properties
    $sql = "CREATE TABLE IF NOT EXISTS `associate_properties` (
        `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `associate_id` INT(11) UNSIGNED NOT NULL,
        `property_id` INT(11) UNSIGNED NOT NULL,
        `commission_rate` DECIMAL(5,2) DEFAULT 2.50,
        `status` ENUM('active', 'sold', 'withdrawn') DEFAULT 'active',
        `assigned_date` DATETIME DEFAULT CURRENT_TIMESTAMP,
        `sold_date` DATETIME DEFAULT NULL,
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `associate_property` (`associate_id`, `property_id`),
        KEY `associate_id` (`associate_id`),
        KEY `property_id` (`property_id`),
        KEY `status` (`status`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    $conn->exec($sql);
    echo "✓ Associate properties table created successfully\n";

    // Create associate_leads table to link associates with leads
    $sql = "CREATE TABLE IF NOT EXISTS `associate_leads` (
        `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `associate_id` INT(11) UNSIGNED NOT NULL,
        `lead_id` INT(11) UNSIGNED NOT NULL,
        `status` ENUM('new', 'contacted', 'followup', 'converted', 'lost') DEFAULT 'new',
        `priority` ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
        `notes` TEXT DEFAULT NULL,
        `next_followup` DATETIME DEFAULT NULL,
        `assigned_date` DATETIME DEFAULT CURRENT_TIMESTAMP,
        `converted_date` DATETIME DEFAULT NULL,
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `associate_lead` (`associate_id`, `lead_id`),
        KEY `associate_id` (`associate_id`),
        KEY `lead_id` (`lead_id`),
        KEY `status` (`status`),
        KEY `priority` (`priority`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    $conn->exec($sql);
    echo "✓ Associate leads table created successfully\n";

    echo "\n✓ All associate tables created successfully!\n";
} catch (PDOException $e) {
    echo "✗ Error creating associate tables: " . $e->getMessage() . "\n";
    exit(1);
}
