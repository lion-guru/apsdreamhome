<?php
/**
 * Migration: Add plot_booking_id and Foreign Key to payments table (S455)
 *
 * Problem:
 *   payments table previously only had `booking_id` referencing `bookings.id` (legacy property booking).
 *   Plot bookings for colonies live in `plot_bookings`. Recording plot booking payments
 *   via `booking_id` caused MySQL FK error 1452 (Integrity constraint violation).
 *
 * Solution:
 *   Add `plot_booking_id` (bigint(20) unsigned NULL DEFAULT NULL) to `payments`,
 *   indexed with FK to `plot_bookings(id)` ON DELETE SET NULL ON UPDATE CASCADE.
 *
 * Idempotent:
 *   Checks if column, index, and foreign key exist before applying. Safe to re-run.
 *
 * Usage:
 *   php database/migrations/add_plot_booking_id_to_payments_s455.php
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

// 1. Check if column exists
$stmt = $pdo->prepare("
    SELECT COUNT(*) FROM information_schema.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
      AND TABLE_NAME = 'payments' 
      AND COLUMN_NAME = 'plot_booking_id'
");
$stmt->execute();
$columnExists = (bool)$stmt->fetchColumn();

if (!$columnExists) {
    $pdo->exec("
        ALTER TABLE `payments` 
        ADD COLUMN `plot_booking_id` BIGINT(20) UNSIGNED NULL DEFAULT NULL AFTER `booking_id`
    ");
    echo "CREATE Added column `plot_booking_id` to `payments`\n";
} else {
    echo "SKIP   Column `plot_booking_id` already exists on `payments`\n";
}

// 2. Check if index exists
$stmt = $pdo->prepare("
    SELECT COUNT(*) FROM information_schema.STATISTICS 
    WHERE TABLE_SCHEMA = DATABASE() 
      AND TABLE_NAME = 'payments' 
      AND INDEX_NAME = 'idx_payments_plot_booking_id'
");
$stmt->execute();
$indexExists = (bool)$stmt->fetchColumn();

if (!$indexExists) {
    $pdo->exec("
        ALTER TABLE `payments` 
        ADD KEY `idx_payments_plot_booking_id` (`plot_booking_id`)
    ");
    echo "CREATE Added index `idx_payments_plot_booking_id` on `payments`\n";
} else {
    echo "SKIP   Index `idx_payments_plot_booking_id` already exists on `payments`\n";
}

// 3. Check if foreign key exists
$stmt = $pdo->prepare("
    SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS 
    WHERE TABLE_SCHEMA = DATABASE() 
      AND TABLE_NAME = 'payments' 
      AND CONSTRAINT_NAME = 'fk_payments_plot_booking_id'
      AND CONSTRAINT_TYPE = 'FOREIGN KEY'
");
$stmt->execute();
$fkExists = (bool)$stmt->fetchColumn();

if (!$fkExists) {
    // Ensure parent table plot_bookings exists
    $plotBookingsExists = (bool)$pdo->query("SHOW TABLES LIKE 'plot_bookings'")->fetch();
    if ($plotBookingsExists) {
        $pdo->exec("
            ALTER TABLE `payments` 
            ADD CONSTRAINT `fk_payments_plot_booking_id` 
            FOREIGN KEY (`plot_booking_id`) REFERENCES `plot_bookings` (`id`) 
            ON DELETE SET NULL ON UPDATE CASCADE
        ");
        echo "CREATE Added foreign key `fk_payments_plot_booking_id` referencing `plot_bookings`\n";
    } else {
        echo "WARN   Parent table `plot_bookings` not found, foreign key skipped\n";
    }
} else {
    echo "SKIP   Foreign key `fk_payments_plot_booking_id` already exists on `payments`\n";
}

echo "\nMigration add_plot_booking_id_to_payments_s455 completed successfully.\n";

