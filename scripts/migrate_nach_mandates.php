<?php
/**
 * Migration: Create NACH Mandate table for EMI auto-debit
 * Also adds early_payment_discount column to booking_payment_schedules
 *
 * Run: php scripts/migrate_nach_mandates.php
 */

$root = dirname(__DIR__);
$config = require $root . '/config/database.php';

try {
    $pdo = new PDO(
        "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4",
        $config['username'], $config['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // 1. NACH Mandate table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `nach_mandates` (
            `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            `booking_id` BIGINT UNSIGNED NOT NULL,
            `customer_id` BIGINT UNSIGNED NOT NULL,
            `mandate_id` VARCHAR(50) DEFAULT NULL COMMENT 'NACH mandate reference from bank',
            `bank_name` VARCHAR(100) NOT NULL,
            `bank_account_number` VARCHAR(30) NOT NULL,
            `ifsc_code` VARCHAR(20) NOT NULL,
            `account_holder_name` VARCHAR(150) NOT NULL,
            `mandate_type` ENUM('emandate', 'physical', 'nach') NOT NULL DEFAULT 'emandate',
            `mandate_amount` DECIMAL(12,2) NOT NULL COMMENT 'Maximum per-debit amount',
            `frequency` ENUM('monthly', 'quarterly', 'as_presented') NOT NULL DEFAULT 'monthly',
            `start_date` DATE NOT NULL,
            `end_date` DATE NOT NULL,
            `status` ENUM('draft','submitted','approved','rejected','cancelled','expired') NOT NULL DEFAULT 'draft',
            `approved_at` DATETIME DEFAULT NULL,
            `rejected_at` DATETIME DEFAULT NULL,
            `rejection_reason` VARCHAR(255) DEFAULT NULL,
            `next_debit_date` DATE DEFAULT NULL COMMENT 'Auto-debit runs on this date',
            `last_debit_date` DATE DEFAULT NULL,
            `total_debits` INT UNSIGNED NOT NULL DEFAULT 0,
            `total_debit_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_nach_booking` (`booking_id`),
            KEY `idx_nach_customer` (`customer_id`),
            KEY `idx_nach_status` (`status`),
            KEY `idx_nach_next_debit` (`next_debit_date`, `status`),
            CONSTRAINT `fk_nach_booking` FOREIGN KEY (`booking_id`) REFERENCES `plot_bookings`(`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_nach_customer` FOREIGN KEY (`customer_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    echo "✓ Created nach_mandates table\n";

    // 2. NACH debit log (audit trail for auto-debit attempts)
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `nach_debit_log` (
            `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            `mandate_id_ref` BIGINT UNSIGNED NOT NULL,
            `installment_id` BIGINT UNSIGNED NOT NULL,
            `debit_amount` DECIMAL(12,2) NOT NULL,
            `debit_date` DATE NOT NULL,
            `status` ENUM('initiated','success','failed','retry') NOT NULL DEFAULT 'initiated',
            `bank_reference` VARCHAR(100) DEFAULT NULL,
            `failure_reason` VARCHAR(255) DEFAULT NULL,
            `retry_count` TINYINT UNSIGNED NOT NULL DEFAULT 0,
            `next_retry_date` DATE DEFAULT NULL,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_ndl_mandate` (`mandate_id_ref`),
            KEY `idx_ndl_installment` (`installment_id`),
            KEY `idx_ndl_status_date` (`status`, `debit_date`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    echo "✓ Created nach_debit_log table\n";

    // 3. Add early_payment_discount column to booking_payment_schedules
    try {
        $pdo->exec("ALTER TABLE `booking_payment_schedules` 
            ADD COLUMN `early_payment_discount` DECIMAL(10,2) NOT NULL DEFAULT 0.00 
            AFTER `accrued_penalty`");
        echo "✓ Added early_payment_discount column to booking_payment_schedules\n";
    } catch (PDOException $e) {
        if (str_contains($e->getMessage(), 'Duplicate column')) {
            echo "○ early_payment_discount column already exists\n";
        } else {
            throw $e;
        }
    }

    // 4. Add discount_applied flag
    try {
        $pdo->exec("ALTER TABLE `booking_payment_schedules` 
            ADD COLUMN `discount_applied` TINYINT(1) NOT NULL DEFAULT 0 
            AFTER `early_payment_discount`");
        echo "✓ Added discount_applied column to booking_payment_schedules\n";
    } catch (PDOException $e) {
        if (str_contains($e->getMessage(), 'Duplicate column')) {
            echo "○ discount_applied column already exists\n";
        } else {
            throw $e;
        }
    }

    echo "\n✅ Migration complete — NACH mandate + early payment discount tables ready\n";

} catch (Exception $e) {
    echo "✗ Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
