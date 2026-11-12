<?php
/**
 * Migration: Add Flexible Brokerage Units & Types to land_brokers table (S455)
 *
 * Adds:
 *   - brokerage_type: enum('percentage', 'per_bigha', 'per_kattha', 'per_decimil', 'per_sqft', 'fixed') DEFAULT 'percentage'
 *   - brokerage_rate: decimal(15,2) DEFAULT 2.00
 *
 * Idempotent: checks column existence before adding.
 *
 * Usage: php database/migrations/add_brokerage_unit_options_s455.php
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

// 1. Check brokerage_type column
$stmt = $pdo->prepare("
    SELECT COUNT(*) FROM information_schema.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
      AND TABLE_NAME = 'land_brokers' 
      AND COLUMN_NAME = 'brokerage_type'
");
$stmt->execute();
if (!(bool)$stmt->fetchColumn()) {
    $pdo->exec("
        ALTER TABLE `land_brokers` 
        ADD COLUMN `brokerage_type` ENUM('percentage', 'per_bigha', 'per_kattha', 'per_decimil', 'per_sqft', 'fixed') 
        NOT NULL DEFAULT 'percentage' AFTER `commission_percentage`
    ");
    echo "CREATE Added column `brokerage_type` to `land_brokers`\n";
} else {
    echo "SKIP   Column `brokerage_type` already exists\n";
}

// 2. Check brokerage_rate column
$stmt = $pdo->prepare("
    SELECT COUNT(*) FROM information_schema.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
      AND TABLE_NAME = 'land_brokers' 
      AND COLUMN_NAME = 'brokerage_rate'
");
$stmt->execute();
if (!(bool)$stmt->fetchColumn()) {
    $pdo->exec("
        ALTER TABLE `land_brokers` 
        ADD COLUMN `brokerage_rate` DECIMAL(15,2) NOT NULL DEFAULT 2.00 AFTER `brokerage_type`
    ");
    // Backfill brokerage_rate from commission_percentage where available
    $pdo->exec("
        UPDATE `land_brokers` 
        SET `brokerage_rate` = COALESCE(commission_percentage, 2.00) 
        WHERE brokerage_rate = 2.00 AND commission_percentage IS NOT NULL
    ");
    echo "CREATE Added column `brokerage_rate` to `land_brokers`\n";
} else {
    echo "SKIP   Column `brokerage_rate` already exists\n";
}

echo "\nMigration add_brokerage_unit_options_s455 completed successfully.\n";

