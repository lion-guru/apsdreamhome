<?php
/**
 * Migration: Create plot_locks table for 30-minute reservation timer.
 * Run: php scripts/migrate_plot_locks.php
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

    echo "=== Plot Locks Migration ===\n\n";

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `plot_locks` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `plot_id` INT UNSIGNED NOT NULL,
            `user_id` INT UNSIGNED NOT NULL,
            `locked_at` DATETIME NOT NULL,
            `expires_at` DATETIME NOT NULL,
            `status` ENUM('active','released','expired','converted') NOT NULL DEFAULT 'active',
            `booking_id` INT UNSIGNED DEFAULT NULL COMMENT 'Set when booking is created from this lock',
            PRIMARY KEY (`id`),
            KEY `idx_plot_locks_plot` (`plot_id`),
            KEY `idx_plot_locks_user` (`user_id`),
            KEY `idx_plot_locks_expires` (`expires_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    echo "  [OK] plot_locks table created\n";

    echo "\nDone. 1 table created.\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}?>