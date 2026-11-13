<?php
/**
 * Migration S469: Create Missing Domain Tables
 *
 * Problem:
 *   Static schema scanner (testing/scan_schema_mismatches.php) identified 44 sites
 *   referencing 9 genuine domain tables that existed in controllers/services but
 *   had no schema migrations:
 *     1. plot_locks               (BookingController::lockPlot - prevents race condition / double booking)
 *     2. customer_assignments     (AgentAssignmentService - automated customer routing to agents/offices)
 *     3. performance_alerts       (PerformanceMonitoringService - system health & threshold warnings)
 *     4. alert_queue              (AlertManagerService - multi-channel notification dispatch queue)
 *     5. auction_watchers         (AuctionService - customer watchlist & outbid notifications)
 *     6. auction_deposits         (AuctionService - earnest money deposits / EMD handling)
 *     7. backup_verifications     (BackupIntegrityService - automated backup verification logs)
 *     8. backup_integrity_log     (BackupIntegrityService - detailed integrity check results)
 *     9. marketing_strategies     (MarketingController - marketing strategy campaigns CRUD)
 *
 * Idempotent:
 *   Uses CREATE TABLE IF NOT EXISTS with InnoDB and utf8mb4. Re-run is 100% safe.
 *
 * Usage:
 *   php database/migrations/create_missing_domain_tables_s469.php
 */

require_once dirname(__DIR__, 2) . '/config/bootstrap.php';

use App\Core\Database\Database;

try {
    $pdo = Database::getInstance()->getConnection();
    echo "OK  Database connected\n\n";
} catch (\Throwable $e) {
    echo "ERR Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

function tableExists(PDO $pdo, string $table): bool {
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM information_schema.TABLES 
        WHERE TABLE_SCHEMA = DATABASE() 
          AND TABLE_NAME = ?
    ");
    $stmt->execute([$table]);
    return (int)$stmt->fetchColumn() > 0;
}

// 1. plot_locks
if (!tableExists($pdo, 'plot_locks')) {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `plot_locks` (
            `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1,
            `plot_id` INT NOT NULL,
            `user_id` BIGINT UNSIGNED NOT NULL,
            `locked_at` DATETIME NOT NULL,
            `expires_at` DATETIME NOT NULL,
            `status` ENUM('active','released','expired') NOT NULL DEFAULT 'active',
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            KEY `idx_plot_locks_plot` (`plot_id`),
            KEY `idx_plot_locks_user` (`user_id`),
            KEY `idx_plot_locks_status_expires` (`status`, `expires_at`),
            KEY `idx_plot_locks_tenant` (`tenant_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "CREATE Table `plot_locks` created\n";
} else {
    echo "SKIP   Table `plot_locks` already exists\n";
}

// 2. customer_assignments
if (!tableExists($pdo, 'customer_assignments')) {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `customer_assignments` (
            `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1,
            `customer_id` BIGINT UNSIGNED NOT NULL,
            `agent_id` BIGINT UNSIGNED NULL,
            `office_id` BIGINT UNSIGNED NULL,
            `assignment_type` VARCHAR(50) NOT NULL DEFAULT 'agent',
            `status` ENUM('active','inactive','transferred') NOT NULL DEFAULT 'active',
            `assigned_by` VARCHAR(100) NOT NULL DEFAULT 'system',
            `assigned_at` DATETIME NOT NULL,
            `unassigned_by` VARCHAR(100) NULL,
            `unassigned_at` DATETIME NULL,
            `reason` VARCHAR(255) NULL,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            KEY `idx_cust_assign_customer` (`customer_id`),
            KEY `idx_cust_assign_agent` (`agent_id`),
            KEY `idx_cust_assign_office` (`office_id`),
            KEY `idx_cust_assign_status` (`status`),
            KEY `idx_cust_assign_tenant` (`tenant_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "CREATE Table `customer_assignments` created\n";
} else {
    echo "SKIP   Table `customer_assignments` already exists\n";
}

// 3. performance_alerts
if (!tableExists($pdo, 'performance_alerts')) {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `performance_alerts` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1,
            `alert_type` VARCHAR(100) NOT NULL,
            `alert_message` TEXT NOT NULL,
            `current_value` VARCHAR(100) NULL,
            `threshold_value` VARCHAR(100) NULL,
            `severity` ENUM('low','medium','high','critical') NOT NULL DEFAULT 'medium',
            `resolved` TINYINT(1) NOT NULL DEFAULT 0,
            `resolved_at` DATETIME NULL,
            `resolved_by` INT NULL,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            KEY `idx_perf_alerts_resolved` (`resolved`),
            KEY `idx_perf_alerts_severity` (`severity`),
            KEY `idx_perf_alerts_created` (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "CREATE Table `performance_alerts` created\n";
} else {
    echo "SKIP   Table `performance_alerts` already exists\n";
}

// 4. alert_queue
if (!tableExists($pdo, 'alert_queue')) {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `alert_queue` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1,
            `alert_id` INT NULL,
            `channel` VARCHAR(50) NOT NULL,
            `recipient` VARCHAR(255) NULL,
            `message` TEXT NOT NULL,
            `priority` VARCHAR(20) NOT NULL DEFAULT 'medium',
            `status` ENUM('pending','sent','failed') NOT NULL DEFAULT 'pending',
            `error_message` TEXT NULL,
            `sent_at` DATETIME NULL,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            KEY `idx_alert_queue_status_prio` (`status`, `priority`),
            KEY `idx_alert_queue_created` (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "CREATE Table `alert_queue` created\n";
} else {
    echo "SKIP   Table `alert_queue` already exists\n";
}

// 5. auction_watchers
if (!tableExists($pdo, 'auction_watchers')) {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `auction_watchers` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1,
            `auction_id` INT NOT NULL,
            `user_id` BIGINT UNSIGNED NOT NULL,
            `notify_outbid` TINYINT(1) NOT NULL DEFAULT 1,
            `notify_ending` TINYINT(1) NOT NULL DEFAULT 1,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY `uq_auction_user` (`auction_id`, `user_id`),
            KEY `idx_auction_watchers_user` (`user_id`),
            KEY `idx_auction_watchers_auction` (`auction_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "CREATE Table `auction_watchers` created\n";
} else {
    echo "SKIP   Table `auction_watchers` already exists\n";
}

// 6. auction_deposits
if (!tableExists($pdo, 'auction_deposits')) {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `auction_deposits` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1,
            `auction_id` INT NOT NULL,
            `user_id` BIGINT UNSIGNED NOT NULL,
            `amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
            `payment_method` VARCHAR(50) NULL,
            `transaction_id` VARCHAR(100) NULL,
            `status` ENUM('pending','paid','refunded','forfeited') NOT NULL DEFAULT 'pending',
            `paid_at` DATETIME NULL,
            `refunded_at` DATETIME NULL,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY `uq_auction_deposits_auction_user` (`auction_id`, `user_id`),
            KEY `idx_auction_deposits_status` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "CREATE Table `auction_deposits` created\n";
} else {
    echo "SKIP   Table `auction_deposits` already exists\n";
}

// 7. backup_verifications
if (!tableExists($pdo, 'backup_verifications')) {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `backup_verifications` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `backup_file` VARCHAR(500) NOT NULL,
            `checksum` VARCHAR(64) NOT NULL,
            `file_size` BIGINT UNSIGNED DEFAULT 0,
            `status` ENUM('pending','verified','failed','test_restored') DEFAULT 'pending',
            `checks` JSON NULL,
            `verified_at` DATETIME NULL,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            KEY `idx_bv_status` (`status`),
            KEY `idx_bv_created` (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "CREATE Table `backup_verifications` created\n";
} else {
    echo "SKIP   Table `backup_verifications` already exists\n";
}

// 8. backup_integrity_log
if (!tableExists($pdo, 'backup_integrity_log')) {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `backup_integrity_log` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `backup_file` VARCHAR(500) NOT NULL,
            `check_type` VARCHAR(50) NOT NULL,
            `status` ENUM('passed','failed','warning') NOT NULL DEFAULT 'passed',
            `details` JSON NULL,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            KEY `idx_backup_file` (`backup_file`(191))
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "CREATE Table `backup_integrity_log` created\n";
} else {
    $cols = [
        'backup_file' => "VARCHAR(500) NOT NULL AFTER id",
        'check_type'  => "VARCHAR(50) NOT NULL AFTER backup_file",
        'details'     => "JSON NULL AFTER status"
    ];
    foreach ($cols as $col => $def) {
        $checkCol = $pdo->query("SHOW COLUMNS FROM `backup_integrity_log` LIKE '$col'")->fetch();
        if (!$checkCol) {
            $pdo->exec("ALTER TABLE `backup_integrity_log` ADD COLUMN `$col` $def");
            echo "ALTER  Added column `$col` to `backup_integrity_log`\n";
        }
    }
    // Ensure check_name if present does not block inserts without default
    $hasCheckName = $pdo->query("SHOW COLUMNS FROM `backup_integrity_log` LIKE 'check_name'")->fetch();
    if ($hasCheckName) {
        $pdo->exec("ALTER TABLE `backup_integrity_log` MODIFY COLUMN `check_name` VARCHAR(100) NULL DEFAULT NULL");
    }
    echo "SKIP   Table `backup_integrity_log` already exists\n";
}

// 9. marketing_strategies
if (!tableExists($pdo, 'marketing_strategies')) {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `marketing_strategies` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1,
            `title` VARCHAR(255) NOT NULL,
            `description` TEXT NULL,
            `image_url` VARCHAR(500) NULL,
            `active` TINYINT(1) NOT NULL DEFAULT 1,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            KEY `idx_ms_tenant` (`tenant_id`),
            KEY `idx_ms_active` (`active`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "CREATE Table `marketing_strategies` created\n";
} else {
    echo "SKIP   Table `marketing_strategies` already exists\n";
}

echo "\nMigration S469 completed successfully.\n";
