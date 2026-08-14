<?php
/**
 * Migration: Create customer_mandates table for EMI auto-payment
 *
 * Run: php scripts/migrate_customer_mandates.php
 */

$root = dirname(__DIR__);
$config = require $root . '/config/database.php';

try {
    $pdo = new PDO(
        "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4",
        $config['username'],
        $config['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );

    echo "Connected to database.\n";

    // 1. Create customer_mandates table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `customer_mandates` (
        `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        `booking_id` BIGINT(20) UNSIGNED NOT NULL,
        `customer_id` BIGINT(20) UNSIGNED NOT NULL,
        `razorpay_customer_id` VARCHAR(100) DEFAULT NULL,
        `subscription_id` VARCHAR(100) NOT NULL,
        `mandate_id` VARCHAR(100) DEFAULT NULL,
        `plan_id` VARCHAR(100) DEFAULT NULL,
        `amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
        `status` ENUM('active','paused','cancelled','failed','expired') NOT NULL DEFAULT 'active',
        `next_charge_at` DATETIME DEFAULT NULL,
        `last_charged_at` DATETIME DEFAULT NULL,
        `failure_count` INT(11) NOT NULL DEFAULT 0,
        `notes` TEXT DEFAULT NULL,
        `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `uniq_subscription` (`subscription_id`),
        KEY `idx_mandate_booking` (`booking_id`),
        KEY `idx_mandate_customer` (`customer_id`),
        KEY `idx_mandate_status` (`status`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    echo "Table 'customer_mandates' created.\n";

    // 2. Ensure gateway_logs has the columns we need (created_at may already exist)
    try {
        $pdo->exec("ALTER TABLE `gateway_logs`
            ADD COLUMN IF NOT EXISTS `method` VARCHAR(10) DEFAULT NULL AFTER `gateway`,
            ADD COLUMN IF NOT EXISTS `endpoint` VARCHAR(255) DEFAULT NULL AFTER `method`,
            ADD COLUMN IF NOT EXISTS `request_payload` TEXT DEFAULT NULL AFTER `endpoint`,
            ADD COLUMN IF NOT EXISTS `response_payload` TEXT DEFAULT NULL AFTER `request_payload`,
            ADD COLUMN IF NOT EXISTS `response_code` INT(11) DEFAULT NULL AFTER `response_payload`,
            ADD COLUMN IF NOT EXISTS `retry_count` INT(11) DEFAULT 0 AFTER `duration_ms`,
            ADD COLUMN IF NOT EXISTS `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER `error_message`
        ");
        echo "gateway_logs columns ensured.\n";
    } catch (Exception $e) {
        echo "gateway_logs ALTER skipped (columns may already exist): " . $e->getMessage() . "\n";
    }

    echo "\nMigration complete.\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}?>