<?php
$pdo = new PDO("mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "=== Creating Missing Tables ===\n\n";

$tables = [
    "plot_agreements" => "
        CREATE TABLE `plot_agreements` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `booking_id` INT UNSIGNED NOT NULL,
            `agreement_number` VARCHAR(50) NOT NULL,
            `agreement_date` DATE NOT NULL,
            `agreement_value` DECIMAL(15,2) NOT NULL DEFAULT 0,
            `status` ENUM('draft','pending_signature','signed','registered','cancelled') DEFAULT 'draft',
            `signed_at` TIMESTAMP NULL,
            `registered_at` TIMESTAMP NULL,
            `registry_number` VARCHAR(100) NULL,
            `registry_date` DATE NULL,
            `stamp_duty_paid` DECIMAL(15,2) DEFAULT 0,
            `registration_charges` DECIMAL(15,2) DEFAULT 0,
            `notes` TEXT NULL,
            `created_by` INT UNSIGNED NULL,
            `updated_by` INT UNSIGNED NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY `uk_agreement_number` (`agreement_number`),
            KEY `idx_booking_id` (`booking_id`),
            KEY `idx_status` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ",
    
    "cash_book_entries" => "
        CREATE TABLE `cash_book_entries` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `entry_date` DATE NOT NULL,
            `type` ENUM('cash_in','cash_out') NOT NULL,
            `amount` DECIMAL(15,2) NOT NULL DEFAULT 0,
            `description` VARCHAR(500) NOT NULL,
            `reference_type` VARCHAR(50) NULL,
            `reference_id` INT UNSIGNED NULL,
            `payment_mode` ENUM('cash','cheque','online','upi','card','other') DEFAULT 'cash',
            `cheque_number` VARCHAR(50) NULL,
            `cheque_date` DATE NULL,
            `bank_name` VARCHAR(100) NULL,
            `received_from_paid_to` VARCHAR(200) NULL,
            `category` VARCHAR(100) NULL,
            `project_id` INT UNSIGNED NULL,
            `status` ENUM('pending','cleared','bounced','cancelled') DEFAULT 'pending',
            `cleared_at` TIMESTAMP NULL,
            `created_by` INT UNSIGNED NOT NULL,
            `updated_by` INT UNSIGNED NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            KEY `idx_entry_date` (`entry_date`),
            KEY `idx_type` (`type`),
            KEY `idx_reference` (`reference_type`, `reference_id`),
            KEY `idx_status` (`status`),
            KEY `idx_project_id` (`project_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ",
    
    "live_chat_sessions" => "
        CREATE TABLE `live_chat_sessions` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `session_id` VARCHAR(64) NOT NULL,
            `user_id` INT UNSIGNED NULL,
            `user_name` VARCHAR(150) NULL,
            `user_email` VARCHAR(150) NULL,
            `user_phone` VARCHAR(20) NULL,
            `status` ENUM('waiting','active','closed','transferred') DEFAULT 'waiting',
            `agent_id` INT UNSIGNED NULL,
            `started_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `ended_at` TIMESTAMP NULL,
            `last_message_at` TIMESTAMP NULL,
            `unread_user` INT UNSIGNED DEFAULT 0,
            `unread_agent` INT UNSIGNED DEFAULT 0,
            `rating` TINYINT UNSIGNED NULL,
            `feedback` TEXT NULL,
            `transcript_sent` BOOLEAN DEFAULT FALSE,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY `uk_session_id` (`session_id`),
            KEY `idx_user_id` (`user_id`),
            KEY `idx_agent_id` (`agent_id`),
            KEY `idx_status` (`status`),
            KEY `idx_started_at` (`started_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ",
    
    "kyc_submissions" => "
        CREATE TABLE `kyc_submissions` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `user_id` INT UNSIGNED NOT NULL,
            `pan_number` VARCHAR(20) NULL,
            `aadhaar_number` VARCHAR(20) NULL,
            `pan_file` VARCHAR(500) NULL,
            `aadhaar_front_file` VARCHAR(500) NULL,
            `aadhaar_back_file` VARCHAR(500) NULL,
            `selfie_file` VARCHAR(500) NULL,
            `status` ENUM('pending','approved','rejected') DEFAULT 'pending',
            `submitted_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `reviewed_at` TIMESTAMP NULL,
            `reviewer_id` INT UNSIGNED NULL,
            `rejection_reason` TEXT NULL,
            `notes` TEXT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            KEY `idx_user_id` (`user_id`),
            KEY `idx_status` (`status`),
            KEY `idx_reviewer_id` (`reviewer_id`),
            KEY `idx_submitted_at` (`submitted_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ",
    
    "attendance_records" => "
        CREATE TABLE `attendance_records` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `user_id` INT UNSIGNED NOT NULL,
            `date` DATE NOT NULL,
            `check_in` TIME NULL,
            `check_out` TIME NULL,
            `status` ENUM('present','absent','late','half_day','on_leave','holiday','week_off') DEFAULT 'absent',
            `work_hours` DECIMAL(4,2) DEFAULT 0,
            `overtime_hours` DECIMAL(4,2) DEFAULT 0,
            `location` VARCHAR(200) NULL,
            `ip_address` VARCHAR(45) NULL,
            `device_info` TEXT NULL,
            `notes` TEXT NULL,
            `approved_by` INT UNSIGNED NULL,
            `approved_at` TIMESTAMP NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY `uk_user_date` (`user_id`, `date`),
            KEY `idx_date` (`date`),
            KEY `idx_status` (`status`),
            KEY `idx_approved_by` (`approved_by`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ",
    
    "leave_applications" => "
        CREATE TABLE `leave_applications` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `user_id` INT UNSIGNED NOT NULL,
            `leave_type` ENUM('annual','sick','casual','maternity','paternity','emergency','compensatory','unpaid') NOT NULL,
            `from_date` DATE NOT NULL,
            `to_date` DATE NOT NULL,
            `total_days` DECIMAL(4,1) NOT NULL,
            `reason` TEXT NOT NULL,
            `status` ENUM('pending','approved','rejected','cancelled') DEFAULT 'pending',
            `applied_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `reviewed_at` TIMESTAMP NULL,
            `reviewer_id` INT UNSIGNED NULL,
            `reviewer_comments` TEXT NULL,
            `attachment` VARCHAR(500) NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            KEY `idx_user_id` (`user_id`),
            KEY `idx_status` (`status`),
            KEY `idx_dates` (`from_date`, `to_date`),
            KEY `idx_reviewer_id` (`reviewer_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ",
];

$created = 0;
$failed = 0;

foreach ($tables as $name => $sql) {
    try {
        $pdo->exec($sql);
        echo "✅ Created: $name\n";
        $created++;
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'already exists') !== false) {
            echo "⚠️  Already exists: $name\n";
        } else {
            echo "❌ Failed: $name - " . $e->getMessage() . "\n";
            $failed++;
        }
    }
}

echo "\n=== Summary ===\n";
echo "Created: $created\n";
echo "Failed: $failed\n";
echo "Total tables processed: " . count($tables) . "\n";