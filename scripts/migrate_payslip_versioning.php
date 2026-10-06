<?php
/**
 * Migration: Add versioning to employee_payslips for immutability
 * Run: php scripts/migrate_payslip_versioning.php
 */

require_once __DIR__ . '/../config/bootstrap.php';

try {
    $db = \App\Core\Database\Database::getInstance();
    $pdo = $db->getPdo();
    
    // Check if columns already exist
    $cols = $pdo->query("SHOW COLUMNS FROM employee_payslips LIKE 'version'")->fetchAll();
    if (empty($cols)) {
        $pdo->exec("ALTER TABLE employee_payslips 
            ADD COLUMN version INT NOT NULL DEFAULT 1 AFTER status,
            ADD COLUMN previous_version_id INT NULL AFTER version,
            ADD INDEX idx_payslip_version (employee_id, period_month, period_year, version)");
        echo "✓ Added version and previous_version_id columns\n";
    } else {
        echo "✓ Version columns already exist\n";
    }
    
    // Set default version=1 for existing rows
    $pdo->exec("UPDATE employee_payslips SET version = 1 WHERE version IS NULL OR version = 0");
    echo "✓ Set default version=1 for existing rows\n";
    
    // Add CHECK constraint for paid slips immutability (MySQL 8.0.16+)
    try {
        $pdo->exec("ALTER TABLE employee_payslips 
            ADD CONSTRAINT chk_paid_immutable 
            CHECK (status != 'paid' OR (version = 1 AND previous_version_id IS NULL))");
        echo "✓ Added CHECK constraint for paid slip immutability\n";
    } catch (\Exception $e) {
        echo "! CHECK constraint skipped (MySQL version): " . $e->getMessage() . "\n";
    }
    
    echo "\n✅ Migration completed successfully!\n";
    
} catch (\Exception $e) {
    echo "❌ Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}