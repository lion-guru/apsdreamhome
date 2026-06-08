<?php
/**
 * Phase 30: Create dunning_log table + dunning_columns on booking_payment_schedules
 * Run: php scripts/create_dunning_log_table.php
 */

$root = dirname(__DIR__);
$config = require $root . '/config/database.php';

try {
    $pdo = new PDO(
        "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4",
        $config['username'], $config['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "Connected to database.\n";

    // 1. Create dunning_log table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `dunning_log` (
            `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            `booking_id` BIGINT(20) UNSIGNED NOT NULL,
            `installment_id` BIGINT(20) UNSIGNED NOT NULL,
            `customer_id` BIGINT(20) UNSIGNED DEFAULT NULL,
            `dunning_tier` ENUM('reminder','overdue_7','overdue_14','overdue_30','overdue_60','overdue_90','defaulted') NOT NULL DEFAULT 'reminder',
            `channel` ENUM('email','sms','both') NOT NULL DEFAULT 'email',
            `subject` VARCHAR(255) DEFAULT NULL,
            `message` TEXT DEFAULT NULL,
            `sent_by` VARCHAR(50) DEFAULT 'cron',
            `status` ENUM('sent','failed','skipped') NOT NULL DEFAULT 'sent',
            `error_message` TEXT DEFAULT NULL,
            `days_overdue` INT(11) DEFAULT 0,
            `penalty_amount` DECIMAL(10,2) DEFAULT 0.00,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_dunning_booking` (`booking_id`),
            KEY `idx_dunning_installment` (`installment_id`),
            KEY `idx_dunning_customer` (`customer_id`),
            KEY `idx_dunning_tier` (`dunning_tier`),
            KEY `idx_dunning_created` (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    echo "Created dunning_log table.\n";

    // 2. Add dunning columns to booking_payment_schedules (safe — only if missing)
    $columns = $pdo->query("SHOW COLUMNS FROM booking_payment_schedules")->fetchAll(\PDO::FETCH_COLUMN);
    
    $newCols = [
        'reminder_count' => "INT(11) NOT NULL DEFAULT 0",
        'last_reminder_at' => "DATETIME DEFAULT NULL",
        'escalation_level' => "TINYINT(4) NOT NULL DEFAULT 0",
    ];
    
    foreach ($newCols as $col => $def) {
        if (!in_array($col, $columns)) {
            $pdo->exec("ALTER TABLE `booking_payment_schedules` ADD COLUMN `{$col}` {$def}");
            echo "Added column: {$col}\n";
        } else {
            echo "Column {$col} already exists.\n";
        }
    }

    echo "\nDone. dunning_log created, booking_payment_schedules extended.\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
