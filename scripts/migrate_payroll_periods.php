<?php
/**
 * Migration: Create payroll_periods table for month-end lock/close
 * Run: php scripts/migrate_payroll_periods.php
 */

require_once __DIR__ . '/../config/bootstrap.php';

try {
    $db = \App\Core\Database\Database::getInstance();
    $pdo = $db->getPdo();
    
    // Create payroll_periods table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS payroll_periods (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
            period_month TINYINT UNSIGNED NOT NULL,
            period_year SMALLINT UNSIGNED NOT NULL,
            status ENUM('open','locked','closed') NOT NULL DEFAULT 'open',
            locked_by INT NULL,
            locked_at DATETIME NULL,
            closed_by INT NULL,
            closed_at DATETIME NULL,
            notes TEXT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uk_tenant_period (tenant_id, period_month, period_year),
            INDEX idx_status (status),
            INDEX idx_period (period_year, period_month)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    echo "✓ Created payroll_periods table\n";
    
    // Initialize current and past periods as 'closed' for safety
    $currentMonth = (int)date('n');
    $currentYear = (int)date('Y');
    
    // Mark all past periods as closed
    $stmt = $pdo->prepare("
        INSERT IGNORE INTO payroll_periods (tenant_id, period_month, period_year, status, closed_by, closed_at, notes)
        SELECT 1, m.month, y.year, 'closed', 1, NOW(), 'Auto-closed historical period'
        FROM (
            SELECT 1 AS month UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6
            UNION SELECT 7 UNION SELECT 8 UNION SELECT 9 UNION SELECT 10 UNION SELECT 11 UNION SELECT 12
        ) m
        CROSS JOIN (
            SELECT 2020 AS year UNION SELECT 2021 UNION SELECT 2022 UNION SELECT 2023 UNION SELECT 2024 UNION SELECT 2025
        ) y
        WHERE (y.year < ?) OR (y.year = ? AND m.month < ?)
    ");
    $stmt->execute([$currentYear, $currentYear, $currentMonth]);
    echo "✓ Initialized historical periods as closed\n";
    
    // Current period as open
    $pdo->prepare("
        INSERT IGNORE INTO payroll_periods (tenant_id, period_month, period_year, status, notes)
        VALUES (1, ?, ?, 'open', 'Current period')
    ")->execute([$currentMonth, $currentYear]);
    echo "✓ Initialized current period as open\n";
    
    // Add period_id to salary_payments for tracking
    $cols = $pdo->query("SHOW COLUMNS FROM salary_payments LIKE 'payroll_period_id'")->fetchAll();
    if (empty($cols)) {
        $pdo->exec("ALTER TABLE salary_payments ADD COLUMN payroll_period_id INT NULL AFTER payment_year");
        $pdo->exec("ALTER TABLE salary_payments ADD INDEX idx_payroll_period (payroll_period_id)");
        echo "✓ Added payroll_period_id to salary_payments\n";
    }
    
    // Add period_id to employee_payslips
    $cols = $pdo->query("SHOW COLUMNS FROM employee_payslips LIKE 'payroll_period_id'")->fetchAll();
    if (empty($cols)) {
        $pdo->exec("ALTER TABLE employee_payslips ADD COLUMN payroll_period_id INT NULL AFTER period_year");
        $pdo->exec("ALTER TABLE employee_payslips ADD INDEX idx_payroll_period (payroll_period_id)");
        echo "✓ Added payroll_period_id to employee_payslips\n";
    }
    
    echo "\n✅ Payroll periods migration completed!\n";
    
} catch (\Exception $e) {
    echo "❌ Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}