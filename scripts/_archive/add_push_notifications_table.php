<?php
/**
 * Push Notifications — Database Migration
 * Creates push_subscriptions and push_notification_log tables.
 * Run: php scripts/add_push_notifications_table.php
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

    $pdo->exec("CREATE TABLE IF NOT EXISTS `push_subscriptions` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `user_id` BIGINT(20) UNSIGNED NOT NULL,
        `endpoint` VARCHAR(500) NOT NULL,
        `p256dh_key` VARCHAR(255) NOT NULL,
        `auth_key` VARCHAR(255) NOT NULL,
        `user_agent` VARCHAR(500) DEFAULT NULL,
        `is_active` TINYINT(1) DEFAULT 1,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `last_used_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY `uniq_endpoint` (`endpoint`(191)),
        INDEX `idx_user` (`user_id`),
        INDEX `idx_active` (`is_active`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "[OK] push_subscriptions table created\n";

    $pdo->exec("CREATE TABLE IF NOT EXISTS `push_notification_log` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `user_id` BIGINT(20) UNSIGNED DEFAULT NULL,
        `title` VARCHAR(255) NOT NULL,
        `body` TEXT NOT NULL,
        `url` VARCHAR(500) DEFAULT '/',
        `status` ENUM('sent', 'failed', 'expired') DEFAULT 'sent',
        `error_message` TEXT DEFAULT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX `idx_user` (`user_id`),
        INDEX `idx_status` (`status`),
        INDEX `idx_created` (`created_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "[OK] push_notification_log table created\n";

    echo "Migration complete. 2 tables created.\n";
} catch (Exception $e) {
    echo "[ERROR] " . $e->getMessage() . "\n";
    exit(1);
}
