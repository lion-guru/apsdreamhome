<?php
$pdo = new PDO("mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "=== CREATING MISSING TABLES ===\n\n";

$tables = [
    'plot_agreements' => "
        CREATE TABLE `plot_agreements` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `booking_id` INT NOT NULL,
            `agreement_number` VARCHAR(50) UNIQUE,
            `agreement_date` DATE,
            `status` ENUM('draft','pending_signature','signed','cancelled') DEFAULT 'draft',
            `document_path` VARCHAR(255),
            `signed_by_customer_at` TIMESTAMP NULL,
            `signed_by_admin_at` TIMESTAMP NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX `idx_booking` (`booking_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ",
    'cash_book_entries' => "
        CREATE TABLE `cash_book_entries` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `entry_date` DATE NOT NULL,
            `type` ENUM('credit','debit') NOT NULL,
            `amount` DECIMAL(15,2) NOT NULL,
            `description` VARCHAR(255),
            `reference_type` VARCHAR(50),
            `reference_id` INT,
            `payment_mode` ENUM('cash','cheque','online','upi','bank_transfer') DEFAULT 'cash',
            `cheque_number` VARCHAR(50),
            `bank_name` VARCHAR(100),
            `status` ENUM('pending','cleared','bounced','cancelled') DEFAULT 'pending',
            `created_by` INT,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX `idx_date` (`entry_date`),
            INDEX `idx_type` (`type`),
            INDEX `idx_status` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ",
    'live_chat_sessions' => "
        CREATE TABLE `live_chat_sessions` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `session_id` VARCHAR(100) UNIQUE,
            `user_id` INT,
            `user_name` VARCHAR(100),
            `user_email` VARCHAR(100),
            `user_phone` VARCHAR(20),
            `status` ENUM('waiting','active','closed','transferred') DEFAULT 'waiting',
            `assigned_agent_id` INT,
            `started_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `ended_at` TIMESTAMP NULL,
            `last_message_at` TIMESTAMP NULL,
            `ip_address` VARCHAR(45),
            `user_agent` TEXT,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX `idx_status` (`status`),
            INDEX `idx_agent` (`assigned_agent_id`),
            INDEX `idx_user` (`user_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ",
    'kyc_submissions' => "
        CREATE TABLE `kyc_submissions` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `user_id` INT NOT NULL,
            `pan_number` VARCHAR(20),
            `aadhaar_number` VARCHAR(20),
            `pan_document` VARCHAR(255),
            `aadhaar_front` VARCHAR(255),
            `aadhaar_back` VARCHAR(255),
            `selfie_with_aadhaar` VARCHAR(255),
            `status` ENUM('pending','approved','rejected','resubmit') DEFAULT 'pending',
            `submitted_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `reviewed_at` TIMESTAMP NULL,
            `reviewer_id` INT,
            `rejection_reason` TEXT,
            `notes` TEXT,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX `idx_user` (`user_id`),
            INDEX `idx_status` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ",
    'attendance_records' => "
        CREATE TABLE `attendance_records` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `user_id` INT NOT NULL,
            `date` DATE NOT NULL,
            `check_in` TIMESTAMP NULL,
            `check_out` TIMESTAMP NULL,
            `status` ENUM('present','absent','late','half_day','on_leave','holiday') DEFAULT 'present',
            `work_hours` DECIMAL(4,2) DEFAULT 0,
            `overtime_hours` DECIMAL(4,2) DEFAULT 0,
            `late_minutes` INT DEFAULT 0,
            `notes` TEXT,
            `ip_address` VARCHAR(45),
            `location` VARCHAR(255),
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY `unique_user_date` (`user_id`, `date`),
            INDEX `idx_date` (`date`),
            INDEX `idx_status` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ",
    'leave_applications' => "
        CREATE TABLE `leave_applications` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `user_id` INT NOT NULL,
            `leave_type` ENUM('casual','sick','earned','maternity','paternity','comp_off','unpaid') DEFAULT 'casual',
            `from_date` DATE NOT NULL,
            `to_date` DATE NOT NULL,
            `total_days` INT NOT NULL,
            `reason` TEXT,
            `status` ENUM('pending','approved','rejected','cancelled') DEFAULT 'pending',
            `approved_by` INT,
            `approved_at` TIMESTAMP NULL,
            `rejection_reason` TEXT,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX `idx_user` (`user_id`),
            INDEX `idx_status` (`status`),
            INDEX `idx_dates` (`from_date`, `to_date`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    "
];

foreach ($tables as $name => $sql) {
    try {
        $pdo->exec($sql);
        echo "✅ Created: $name\n";
    } catch (Exception $e) {
        echo "❌ Failed to create $name: " . $e->getMessage() . "\n";
    }
}

echo "\n=== FIXING mlm_commission_ledger COLUMN ===\n";
// Check if 'type' column exists, if not check for 'commission_type'
try {
    $pdo->query("SELECT `type` FROM mlm_commission_ledger LIMIT 1");
    echo "✅ 'type' column exists\n";
} catch (Exception $e) {
    try {
        $pdo->query("SELECT `commission_type` FROM mlm_commission_ledger LIMIT 1");
        echo "Found 'commission_type' column, renaming to 'type'...\n";
        $pdo->exec("ALTER TABLE mlm_commission_ledger CHANGE `commission_type` `type` VARCHAR(50) NOT NULL");
        echo "✅ Renamed commission_type to type\n";
    } catch (Exception $e2) {
        echo "❌ Neither 'type' nor 'commission_type' found: " . $e2->getMessage() . "\n";
    }
}

echo "\n=== VERIFICATION ===\n";
$checkTables = ['plot_agreements', 'cash_book_entries', 'live_chat_sessions', 'kyc_submissions', 'attendance_records', 'leave_applications'];
foreach ($checkTables as $t) {
    try {
        $count = $pdo->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
        echo "✅ $t: $count rows\n";
    } catch (Exception $e) {
        echo "❌ $t: " . $e->getMessage() . "\n";
    }
}

echo "\n=== DONE ===\n";