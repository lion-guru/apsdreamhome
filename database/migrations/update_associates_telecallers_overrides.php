<?php
/**
 * Migration: Add Telecaller settings and overrides to associates table.
 * 
 * Run via: php database/migrations/update_associates_telecallers_overrides.php
 */

$root = dirname(__DIR__, 2);
require_once $root . '/config/database.php';

try {
    $db = \App\Core\Database\Database::getInstance();
    $pdo = $db->getPdo();

    echo "Running migration: update_associates_telecallers_overrides...\n";

    // 1. Add columns to associates table
    $pdo->exec("ALTER TABLE associates 
        ADD COLUMN IF NOT EXISTS telecaller_salary DECIMAL(10,2) DEFAULT 0.00 AFTER brokerage_rate,
        ADD COLUMN IF NOT EXISTS telecaller_incentive_rate DECIMAL(10,2) DEFAULT 0.00 AFTER telecaller_salary,
        ADD COLUMN IF NOT EXISTS telecaller_sqft_rate DECIMAL(10,2) DEFAULT 0.00 AFTER telecaller_incentive_rate,
        ADD COLUMN IF NOT EXISTS telecaller_parent_id INT DEFAULT NULL AFTER telecaller_sqft_rate;
    ");
    echo "  - Added telecaller_salary, telecaller_incentive_rate, telecaller_sqft_rate, telecaller_parent_id columns to associates.\n";

    // 2. Add index for telecaller_parent_id
    $pdo->exec("ALTER TABLE associates 
        ADD INDEX IF NOT EXISTS idx_telecaller_parent_id (telecaller_parent_id);
    ");
    echo "  - Created index idx_telecaller_parent_id on associates.\n";

    echo "Migration completed successfully!\n";
} catch (Exception $e) {
    echo "Migration FAILED: " . $e->getMessage() . "\n";
}
