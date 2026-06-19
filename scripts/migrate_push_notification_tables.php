<?php
/**
 * Migration: Create mobile_devices and notification_logs tables
 * These tables are used by PushNotificationService for device registration and push logging.
 *
 * Run: php scripts/migrate_push_notification_tables.php
 */

$root = dirname(__DIR__);
$config = require $root . '/config/database.php';

try {
    $pdo = new PDO(
        "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4",
        $config['username'],
        $config['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    echo "=== Push Notification Tables Migration ===\n\n";

    // 1. mobile_devices
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `mobile_devices` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `user_id` INT UNSIGNED NOT NULL,
            `device_token` VARCHAR(500) NOT NULL,
            `platform` VARCHAR(50) NOT NULL DEFAULT 'android',
            `device_id` VARCHAR(255) DEFAULT NULL,
            `is_active` TINYINT(1) NOT NULL DEFAULT 1,
            `last_used_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uniq_device_token` (`device_token`(191)),
            KEY `idx_user_id` (`user_id`),
            KEY `idx_platform` (`platform`),
            KEY `idx_active` (`is_active`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "[OK] mobile_devices table created\n";

    // 2. notification_logs
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `notification_logs` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `type` VARCHAR(50) NOT NULL DEFAULT 'push',
            `recipient_token` VARCHAR(500) DEFAULT NULL,
            `title` VARCHAR(255) DEFAULT NULL,
            `body` TEXT DEFAULT NULL,
            `payload` JSON DEFAULT NULL,
            `response` JSON DEFAULT NULL,
            `status` ENUM('sent','delivered','failed','pending') NOT NULL DEFAULT 'pending',
            `error_message` TEXT DEFAULT NULL,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_type` (`type`),
            KEY `idx_status` (`status`),
            KEY `idx_created` (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "[OK] notification_logs table created\n";

    echo "\n=== Migration complete ===\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
