<?php
/**
 * Migration: Shift Roster & Overtime tables
 * Run: php scripts/migrate_shift_roster.php
 */

require_once __DIR__ . '/../config/bootstrap.php';

try {
    $db = \App\Core\Database\Database::getInstance();
    $pdo = $db->getPdo();
    
    // Overtime Requests table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS overtime_requests (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
            employee_id INT NOT NULL,
            overtime_date DATE NOT NULL,
            hours DECIMAL(5,2) NOT NULL,
            reason TEXT NULL,
            status ENUM('pending','approved','rejected') DEFAULT 'pending',
            approved_by INT NULL,
            approved_at DATETIME NULL,
            remarks TEXT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_employee (employee_id),
            INDEX idx_date (overtime_date),
            INDEX idx_status (status),
            INDEX idx_tenant (tenant_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    echo "✓ overtime_requests table created\n";

    // Check if employee_shifts table exists (should exist from HR module)
    $tables = $pdo->query("SHOW TABLES LIKE 'employee_shifts'")->fetchAll();
    if (empty($tables)) {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS employee_shifts (
                id INT AUTO_INCREMENT PRIMARY KEY,
                tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
                employee_id INT NOT NULL,
                shift_type_id INT NOT NULL,
                shift_date DATE NOT NULL,
                start_time TIME NULL,
                end_time TIME NULL,
                duration_hours DECIMAL(5,2) DEFAULT 0,
                status ENUM('scheduled','completed','cancelled') DEFAULT 'scheduled',
                remarks TEXT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_employee (employee_id),
                INDEX idx_date (shift_date),
                INDEX idx_shift_type (shift_type_id),
                INDEX idx_status (status),
                INDEX idx_tenant (tenant_id),
                UNIQUE KEY uk_emp_date (tenant_id, employee_id, shift_date)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        echo "✓ employee_shifts table created\n";
    } else {
        echo "✓ employee_shifts table already exists\n";
    }

    // Check if shift_types table exists (should exist from HR module)
    $tables = $pdo->query("SHOW TABLES LIKE 'shift_types'")->fetchAll();
    if (empty($tables)) {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS shift_types (
                id INT AUTO_INCREMENT PRIMARY KEY,
                tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
                name VARCHAR(100) NOT NULL,
                code VARCHAR(20) NOT NULL,
                description TEXT NULL,
                start_time TIME NOT NULL,
                end_time TIME NOT NULL,
                duration_hours DECIMAL(5,2) DEFAULT 0,
                color VARCHAR(7) DEFAULT '#007bff',
                is_active TINYINT(1) DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY uk_code_tenant (tenant_id, code),
                INDEX idx_active (is_active),
                INDEX idx_tenant (tenant_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        echo "✓ shift_types table created\n";

        // Seed default shifts
        $pdo->exec("
            INSERT IGNORE INTO shift_types (name, code, description, start_time, end_time, duration_hours, color, is_active, tenant_id, created_at)
            VALUES 
            ('Morning Shift', 'MORNING', 'Standard morning shift 9AM-6PM', '09:00:00', '18:00:00', 9.00, '#28a745', 1, 1, NOW()),
            ('Evening Shift', 'EVENING', 'Evening shift 2PM-11PM', '14:00:00', '23:00:00', 9.00, '#ffc107', 1, 1, NOW()),
            ('Night Shift', 'NIGHT', 'Night shift 10PM-7AM', '22:00:00', '07:00:00', 9.00, '#dc3545', 1, 1, NOW()),
            ('General Shift', 'GENERAL', 'General shift 9:30AM-6:30PM', '09:30:00', '18:30:00', 9.00, '#007bff', 1, 1, NOW())
        ");
        echo "✓ Default shift types seeded\n";
    } else {
        echo "✓ shift_types table already exists\n";
    }

    echo "\n✅ Shift Roster & Overtime migration completed!\n";
    
} catch (\Exception $e) {
    echo "❌ Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}