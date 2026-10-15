<?php
/**
 * Migration: Add associate-specific salary tracking
 * 
 * This migration adds associate_id to salary_payments table
 * and adds registration tracking columns to associates table
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database\Database;

$pdo = Database::getInstance()->getPdo();

echo "=== Adding Associate Salary Tracking ===\n\n";

try {
    // 1. Add associate_id to salary_payments table
    echo "Step 1: Adding associate_id to salary_payments...\n";
    $pdo->exec("ALTER TABLE salary_payments ADD COLUMN associate_id INT(11) NULL AFTER employee_id");
    $pdo->exec("ALTER TABLE salary_payments ADD INDEX idx_associate_id (associate_id)");
    echo "âœ“ associate_id column added to salary_payments\n\n";

    // 2. Add registration tracking to associates table
    echo "Step 2: Adding registration tracking to associates...\n";
    $pdo->exec("ALTER TABLE associates ADD COLUMN registration_count INT DEFAULT 0 AFTER commission_earned");
    $pdo->exec("ALTER TABLE associates ADD COLUMN required_registrations INT DEFAULT 5 AFTER registration_count");
    $pdo->exec("ALTER TABLE associates ADD COLUMN salary_eligible TINYINT(1) DEFAULT 0 AFTER required_registrations");
    $pdo->exec("ALTER TABLE associates ADD COLUMN salary_amount DECIMAL(10,2) DEFAULT 0 AFTER salary_eligible");
    $pdo->exec("ALTER TABLE associates ADD COLUMN diwali_bonus_eligible TINYINT(1) DEFAULT 0 AFTER salary_amount");
    $pdo->exec("ALTER TABLE associates ADD COLUMN diwali_bonus_amount DECIMAL(10,2) DEFAULT 0 AFTER diwali_bonus_eligible");
    echo "âœ“ Registration tracking columns added to associates\n\n";

    // 3. Verify changes
    echo "Step 3: Verifying changes...\n";
    echo "salary_payments columns:\n";
    $stmt = $pdo->query("SHOW COLUMNS FROM salary_payments LIKE 'associate_id'");
    $col = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($col) {
        echo "  âœ“ associate_id: {$col['Type']}\n";
    }

    echo "\nassociates new columns:\n";
    $newCols = ['registration_count', 'required_registrations', 'salary_eligible', 'salary_amount', 'diwali_bonus_eligible', 'diwali_bonus_amount'];
    foreach ($newCols as $colName) {
        $stmt = $pdo->query("SHOW COLUMNS FROM associates LIKE '$colName'");
        $col = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($col) {
            echo "  âœ“ {$colName}: {$col['Type']}\n";
        }
    }

    echo "\nâœ“ Migration completed successfully!\n";

} catch (Exception $e) {
    echo "âœ— Error: " . $e->getMessage() . "\n";
    exit(1);
}?>