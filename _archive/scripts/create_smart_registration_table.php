<?php
/**
 * Create smart_registration_sessions table for Phone-First Smart Registration
 * 
 * This table tracks:
 * - Phone number input
 * - OTP verification status
 * - Registration completion status
 * - User behavior tracking
 * - Follow-up reminders sent
 */

$host = '127.0.0.1';
$port = 3307;
$dbname = 'apsdreamhome';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;port=$port", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database if not exists
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `$dbname`");
    
    // Create smart_registration_sessions table
    $sql = "CREATE TABLE IF NOT EXISTS `smart_registration_sessions` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `session_token` VARCHAR(64) NOT NULL UNIQUE,
        `phone` VARCHAR(15) NOT NULL,
        `email` VARCHAR(255) DEFAULT NULL,
        `otp_channel` ENUM('whatsapp', 'sms', 'email') DEFAULT 'whatsapp',
        `otp_code` VARCHAR(10) DEFAULT NULL,
        `otp_verified` TINYINT(1) DEFAULT 0,
        `otp_sent_at` DATETIME DEFAULT NULL,
        `otp_verified_at` DATETIME DEFAULT NULL,
        `user_id` INT DEFAULT NULL,
        `user_created` TINYINT(1) DEFAULT 0,
        `registration_status` ENUM('pending_otp', 'otp_sent', 'otp_verified', 'account_created', 'profile_incomplete', 'profile_complete', 'abandoned') DEFAULT 'pending_otp',
        `profile_completion_pct` INT DEFAULT 0,
        `profile_data` JSON DEFAULT NULL,
        `detected_role` VARCHAR(50) DEFAULT 'customer',
        `role_confidence` DECIMAL(3,2) DEFAULT 0.00,
        `ip_address` VARCHAR(45) DEFAULT NULL,
        `user_agent` TEXT DEFAULT NULL,
        `referrer_url` VARCHAR(500) DEFAULT NULL,
        `landing_page` VARCHAR(500) DEFAULT NULL,
        `pages_viewed` INT DEFAULT 0,
        `properties_viewed` INT DEFAULT 0,
        `search_count` INT DEFAULT 0,
        `last_activity_at` DATETIME DEFAULT NULL,
        `followup_whatsapp_sent` TINYINT(1) DEFAULT 0,
        `followup_email_sent` TINYINT(1) DEFAULT 0,
        `followup_sms_sent` TINYINT(1) DEFAULT 0,
        `followup_count` INT DEFAULT 0,
        `last_followup_at` DATETIME DEFAULT NULL,
        `completed_at` DATETIME DEFAULT NULL,
        `abandoned_at` DATETIME DEFAULT NULL,
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX `idx_phone` (`phone`),
        INDEX `idx_user_id` (`user_id`),
        INDEX `idx_status` (`registration_status`),
        INDEX `idx_created` (`created_at`),
        INDEX `idx_abandoned` (`abandoned_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $pdo->exec($sql);
    echo "✅ Table 'smart_registration_sessions' created successfully\n";
    
    // Create smart_registration_behavior table for tracking
    $sql2 = "CREATE TABLE IF NOT EXISTS `smart_registration_behavior` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `session_id` INT NOT NULL,
        `user_id` INT DEFAULT NULL,
        `event_type` VARCHAR(50) NOT NULL,
        `event_data` JSON DEFAULT NULL,
        `page_url` VARCHAR(500) DEFAULT NULL,
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        INDEX `idx_session` (`session_id`),
        INDEX `idx_user` (`user_id`),
        INDEX `idx_event` (`event_type`),
        INDEX `idx_created` (`created_at`),
        FOREIGN KEY (`session_id`) REFERENCES `smart_registration_sessions`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $pdo->exec($sql2);
    echo "✅ Table 'smart_registration_behavior' created successfully\n";
    
    // Create smart_registration_reminders table
    $sql3 = "CREATE TABLE IF NOT EXISTS `smart_registration_reminders` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `session_id` INT NOT NULL,
        `user_id` INT DEFAULT NULL,
        `reminder_type` ENUM('whatsapp', 'email', 'sms') NOT NULL,
        `template_name` VARCHAR(100) DEFAULT NULL,
        `message_content` TEXT DEFAULT NULL,
        `sent_status` ENUM('pending', 'sent', 'failed', 'delivered', 'read') DEFAULT 'pending',
        `sent_at` DATETIME DEFAULT NULL,
        `delivered_at` DATETIME DEFAULT NULL,
        `read_at` DATETIME DEFAULT NULL,
        `error_message` TEXT DEFAULT NULL,
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        INDEX `idx_session` (`session_id`),
        INDEX `idx_user` (`user_id`),
        INDEX `idx_type` (`reminder_type`),
        INDEX `idx_status` (`sent_status`),
        FOREIGN KEY (`session_id`) REFERENCES `smart_registration_sessions`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $pdo->exec($sql3);
    echo "✅ Table 'smart_registration_reminders' created successfully\n";
    
    echo "\n🎉 All tables created successfully!\n";
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
