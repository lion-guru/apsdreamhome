<?php
/**
 * Migration: Full & Final Settlement table
 * Run: php scripts/migrate_fnf_settlement.php
 */

require_once __DIR__ . '/../config/bootstrap.php';

try {
    $db = \App\Core\Database\Database::getInstance();
    $pdo = $db->getPdo();
    
    // Employee F&F Settlements table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS employee_fnf_settlements (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
            settlement_no VARCHAR(50) NOT NULL,
            employee_id INT NOT NULL,
            resignation_date DATE NOT NULL,
            last_working_day DATE NOT NULL,
            exit_type ENUM('resignation','termination','retirement','abandonment') DEFAULT 'resignation',
            notice_period_days INT DEFAULT 30,
            notice_served_days INT DEFAULT 0,
            earnings_total DECIMAL(12,2) NOT NULL DEFAULT 0,
            deductions_total DECIMAL(12,2) NOT NULL DEFAULT 0,
            net_payable DECIMAL(12,2) NOT NULL DEFAULT 0,
            settlement_details JSON NULL,
            status ENUM('calculated','approved','paid','cancelled') DEFAULT 'calculated',
            approved_by INT NULL,
            approved_at DATETIME NULL,
            paid_at DATETIME NULL,
            payment_reference VARCHAR(100) NULL,
            created_by INT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uk_settlement_no (tenant_id, settlement_no),
            INDEX idx_employee (employee_id),
            INDEX idx_status (status),
            INDEX idx_dates (resignation_date, last_working_day),
            INDEX idx_tenant (tenant_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    echo "✓ employee_fnf_settlements table created\n";

    // Employee Assets table (for asset recovery)
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS employee_assets (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
            employee_id INT NOT NULL,
            asset_name VARCHAR(100) NOT NULL,
            asset_category ENUM('laptop','mobile','sim','vehicle','id_card','access_card','other') DEFAULT 'other',
            serial_number VARCHAR(100) NULL,
            purchase_value DECIMAL(12,2) DEFAULT 0,
            current_value DECIMAL(12,2) DEFAULT 0,
            assigned_date DATE NULL,
            returned_at DATETIME NULL,
            condition_on_return ENUM('good','damaged','lost') NULL,
            remarks TEXT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_employee (employee_id),
            INDEX idx_returned (returned_at),
            INDEX idx_tenant (tenant_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    echo "✓ employee_assets table created\n";

    // Add is_encashable column to leave_types if not exists
    $cols = $pdo->query("SHOW COLUMNS FROM leave_types LIKE 'is_encashable'")->fetchAll();
    if (empty($cols)) {
        $pdo->exec("ALTER TABLE leave_types ADD COLUMN is_encashable TINYINT(1) DEFAULT 1 AFTER color");
        echo "✓ is_encashable column added to leave_types\n";
    } else {
        echo "✓ is_encashable column already exists\n";
    }

    // Add offboard columns to employees if not exists
    $cols = $pdo->query("SHOW COLUMNS FROM employees LIKE 'offboarded_at'")->fetchAll();
    if (empty($cols)) {
        $pdo->exec("ALTER TABLE employees ADD COLUMN offboarded_at DATETIME NULL AFTER status, ADD COLUMN offboard_reason VARCHAR(100) NULL AFTER offboarded_at");
        echo "✓ offboard columns added to employees\n";
    } else {
        echo "✓ offboard columns already exist\n";
    }

    // Add payment_type column to salary_payments for F&F tracking
    $cols = $pdo->query("SHOW COLUMNS FROM salary_payments LIKE 'payment_type'")->fetchAll();
    if (empty($cols)) {
        $pdo->exec("ALTER TABLE salary_payments ADD COLUMN payment_type ENUM('regular','bonus','arrears','fnf_settlement','advance_recovery') DEFAULT 'regular' AFTER payment_method");
        echo "✓ payment_type column added to salary_payments\n";
    } else {
        echo "✓ payment_type column already exists\n";
    }

    echo "\n✅ F&F Settlement migration completed!\n";
    
} catch (\Exception $e) {
    echo "❌ Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}