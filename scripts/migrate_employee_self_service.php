<?php
/**
 * Migration: Employee Self-Service Portal Tables
 * Run: php scripts/migrate_employee_self_service.php
 */

require_once __DIR__ . '/../config/bootstrap.php';

try {
    $db = \App\Core\Database\Database::getInstance();
    $pdo = $db->getPdo();
    
    // 1. Employee Tax Regime
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS employee_tax_regime (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
            employee_id INT NOT NULL,
            financial_year YEAR NOT NULL,
            regime ENUM('old','new') NOT NULL DEFAULT 'new',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uk_emp_fy (tenant_id, employee_id, financial_year),
            INDEX idx_tenant (tenant_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    echo "✓ employee_tax_regime table created\n";

    // 2. Employee Investment Declarations
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS employee_investment_declarations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
            employee_id INT NOT NULL,
            financial_year YEAR NOT NULL,
            section VARCHAR(20) NOT NULL,           -- '80C', '80D', 'HRA', '24b', '80E', '80G', '80TTA'
            subsection VARCHAR(50) NOT NULL,        -- 'PPF', 'ELSS', 'Life Insurance', 'NPS', 'Health Insurance', 'Home Loan Interest', 'Rent Paid'
            investment_type VARCHAR(100) NOT NULL,  -- 'Public Provident Fund', 'Equity Linked Savings Scheme', etc.
            amount DECIMAL(12,2) NOT NULL DEFAULT 0,
            description TEXT NULL,
            status ENUM('draft','submitted','approved','rejected') DEFAULT 'draft',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_emp_fy (employee_id, financial_year),
            INDEX idx_section (section),
            INDEX idx_tenant (tenant_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    echo "✓ employee_investment_declarations table created\n";

    // 3. Employee Investment Proofs
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS employee_investment_proofs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
            employee_id INT NOT NULL,
            financial_year YEAR NOT NULL,
            section VARCHAR(20) NOT NULL,
            file_path VARCHAR(500) NOT NULL,
            original_name VARCHAR(255) NOT NULL,
            file_size INT UNSIGNED DEFAULT 0,
            mime_type VARCHAR(100) NULL,
            uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uk_emp_fy_section (tenant_id, employee_id, financial_year, section),
            INDEX idx_tenant (tenant_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    echo "✓ employee_investment_proofs table created\n";

    // 4. Employee Form 16 Records
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS employee_form16 (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
            employee_id INT NOT NULL,
            financial_year YEAR NOT NULL,
            file_path VARCHAR(500) NOT NULL,
            generated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uk_emp_fy (tenant_id, employee_id, financial_year),
            INDEX idx_tenant (tenant_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    echo "✓ employee_form16 table created\n";

    // 5. Employee Reimbursements
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS employee_reimbursements (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
            employee_id INT NOT NULL,
            claim_type ENUM('medical','lta','fuel','phone','internet','books','training','other') NOT NULL DEFAULT 'other',
            amount DECIMAL(12,2) NOT NULL,
            description TEXT NULL,
            expense_date DATE NOT NULL,
            receipt_path VARCHAR(500) NULL,
            status ENUM('pending','approved','rejected','paid') DEFAULT 'pending',
            approved_by INT NULL,
            approved_at DATETIME NULL,
            rejection_reason TEXT NULL,
            paid_at DATETIME NULL,
            payment_reference VARCHAR(100) NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_emp_status (employee_id, status),
            INDEX idx_expense_date (expense_date),
            INDEX idx_tenant (tenant_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    echo "✓ employee_reimbursements table created\n";

    // 6. Add missing columns to users table for profile
    $cols = $pdo->query("SHOW COLUMNS FROM users LIKE 'date_of_birth'")->fetchAll();
    if (empty($cols)) {
        $pdo->exec("ALTER TABLE users ADD COLUMN date_of_birth DATE NULL AFTER phone");
        echo "✓ Added date_of_birth to users\n";
    }
    $cols = $pdo->query("SHOW COLUMNS FROM users LIKE 'emergency_contact'")->fetchAll();
    if (empty($cols)) {
        $pdo->exec("ALTER TABLE users ADD COLUMN emergency_contact VARCHAR(255) NULL AFTER date_of_birth");
        echo "✓ Added emergency_contact to users\n";
    }
    $cols = $pdo->query("SHOW COLUMNS FROM users LIKE 'pan_number'")->fetchAll();
    if (empty($cols)) {
        $pdo->exec("ALTER TABLE users ADD COLUMN pan_number VARCHAR(20) NULL AFTER emergency_contact");
        echo "✓ Added pan_number to users\n";
    }
    $cols = $pdo->query("SHOW COLUMNS FROM users LIKE 'aadhaar_number'")->fetchAll();
    if (empty($cols)) {
        $pdo->exec("ALTER TABLE users ADD COLUMN aadhaar_number VARCHAR(20) NULL AFTER pan_number");
        echo "✓ Added aadhaar_number to users\n";
    }
    $cols = $pdo->query("SHOW COLUMNS FROM users LIKE 'bank_account'")->fetchAll();
    if (empty($cols)) {
        $pdo->exec("ALTER TABLE users ADD COLUMN bank_account VARCHAR(50) NULL AFTER aadhaar_number");
        echo "✓ Added bank_account to users\n";
    }
    $cols = $pdo->query("SHOW COLUMNS FROM users LIKE 'bank_ifsc'")->fetchAll();
    if (empty($cols)) {
        $pdo->exec("ALTER TABLE users ADD COLUMN bank_ifsc VARCHAR(20) NULL AFTER bank_account");
        echo "✓ Added bank_ifsc to users\n";
    }
    $cols = $pdo->query("SHOW COLUMNS FROM users LIKE 'address'")->fetchAll();
    if (empty($cols)) {
        $pdo->exec("ALTER TABLE users ADD COLUMN address TEXT NULL AFTER bank_ifsc");
        echo "✓ Added address to users\n";
    }

    echo "\n✅ Employee Self-Service migration completed!\n";
    
} catch (\Exception $e) {
    echo "❌ Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}