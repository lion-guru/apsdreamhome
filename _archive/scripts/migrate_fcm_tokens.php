<?php
/**
 * Migration: Ensure push_tokens table exists + add FCM columns if needed
 * 
 * This fixes the broken FCM pipeline:
 * - push_tokens: stores FCM tokens (written by Flutter app)
 * - push_notifications: stores notification queue (written by NotificationService)
 * - PushNotificationService: reads from push_tokens for sending
 *
 * Run: php scripts/migrate_fcm_tokens.php
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

    echo "=== FCM Token Migration ===\n\n";

    // 1. Create push_tokens table if it doesn't exist
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `push_tokens` (
            `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            `user_id` BIGINT(20) UNSIGNED NOT NULL,
            `user_type` VARCHAR(20) NOT NULL DEFAULT 'customer',
            `device_token` TEXT NOT NULL,
            `platform` VARCHAR(20) DEFAULT 'android',
            `device_id` VARCHAR(255),
            `app_version` VARCHAR(20) DEFAULT NULL,
            `is_active` TINYINT(1) DEFAULT 1,
            `last_used_at` DATETIME,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uk_user_device` (`user_id`, `device_token`(191)),
            INDEX `idx_user_id` (`user_id`),
            INDEX `idx_active` (`is_active`),
            INDEX `idx_platform` (`platform`),
            INDEX `idx_last_used` (`last_used_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "âœ“ push_tokens table ready\n";

    // 2. Create notification_logs table if it doesn't exist (for FCM response logging)
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `notification_logs` (
            `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            `type` VARCHAR(20) NOT NULL DEFAULT 'push',
            `recipient_token` TEXT,
            `title` VARCHAR(255),
            `body` TEXT,
            `payload` JSON,
            `response` JSON,
            `status` VARCHAR(20) DEFAULT 'pending',
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            INDEX `idx_type_status` (`type`, `status`),
            INDEX `idx_created` (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "âœ“ notification_logs table ready\n";

    // 3. Ensure push_notifications table exists (notification queue)
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `push_notifications` (
            `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            `user_id` BIGINT(20) UNSIGNED NOT NULL,
            `app_id` INT DEFAULT 1,
            `title` VARCHAR(255) NOT NULL,
            `message` TEXT,
            `data` JSON,
            `device_tokens_sent` INT DEFAULT 0,
            `status` VARCHAR(20) DEFAULT 'pending',
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            INDEX `idx_user_status` (`user_id`, `status`),
            INDEX `idx_status_created` (`status`, `created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "âœ“ push_notifications table ready\n";

    // 4. Show current token count
    $stmt = $pdo->query("SELECT COUNT(*) FROM push_tokens WHERE is_active = 1");
    $activeTokens = $stmt->fetchColumn();

    $stmt = $pdo->query("SELECT COUNT(*) FROM push_tokens");
    $totalTokens = $stmt->fetchColumn();

    echo "\n=== Current State ===\n";
    echo "Active FCM tokens: $activeTokens\n";
    echo "Total FCM tokens: $totalTokens\n";

    // 5. Show any orphaned tokens (user doesn't exist in users table)
    $orphaned = $pdo->query("
        SELECT pt.id, pt.user_id, pt.user_type, LEFT(pt.device_token, 30) as token_start
        FROM push_tokens pt
        LEFT JOIN users u ON u.id = pt.user_id
        WHERE u.id IS NULL AND pt.is_active = 1
        LIMIT 10
    ")->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($orphaned)) {
        echo "\nâš  Orphaned tokens (user not in users table):\n";
        foreach ($orphaned as $o) {
            echo "  - ID {$o['id']}: user_id={$o['user_id']} ({$o['user_type']}) token={$o['token_start']}...\n";
        }
    } else {
        echo "\nâœ“ No orphaned tokens\n";
    }

    echo "\nâœ… Migration complete\n";

} catch (Exception $e) {
    echo "âœ— Error: " . $e->getMessage() . "\n";
    exit(1);
}?>