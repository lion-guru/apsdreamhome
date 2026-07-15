<?php
/**
 * Migration: Create customer_referrals table
 * Tracks referral links and successful conversions
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

    $pdo->exec("CREATE TABLE IF NOT EXISTS `customer_referrals` (
        `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        `referrer_user_id` BIGINT UNSIGNED NOT NULL,
        `referred_user_id` BIGINT UNSIGNED DEFAULT NULL,
        `referral_code` VARCHAR(32) NOT NULL,
        `property_id` INT UNSIGNED DEFAULT NULL,
        `source` VARCHAR(50) DEFAULT 'whatsapp',
        `status` ENUM('pending','registered','booked','expired') DEFAULT 'pending',
        `ip_address` VARCHAR(45) DEFAULT NULL,
        `user_agent` TEXT DEFAULT NULL,
        `registered_at` DATETIME DEFAULT NULL,
        `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `idx_referrer` (`referrer_user_id`),
        KEY `idx_referred` (`referred_user_id`),
        KEY `idx_referral_code` (`referral_code`),
        KEY `idx_status` (`status`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    echo "✓ customer_referrals table created successfully\n";

    // Verify
    $stmt = $pdo->query("SHOW TABLES LIKE 'customer_referrals'");
    if ($stmt->fetch()) {
        echo "✓ Table verified in database\n";
    }
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
