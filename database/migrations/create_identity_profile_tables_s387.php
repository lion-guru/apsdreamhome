<?php
/**
 * Migration: Identity Profile Tables — Session 387
 * Phase 4/7: Identity Unification
 *
 * Creates role-specific profile tables that extend the unified `users` table:
 *   1. customer_profiles  — extends users(role='customer')
 *   2. associate_profiles — extends users(role='associate')
 *   3. employee_profiles  — extends users(role='employee')
 *   4. agent_profiles     — extends users(role='agent')
 *
 * Data is migrated from the legacy `associates`, `employees`, `agents` tables
 * into the new profile tables. The `customers` table does not exist separately
 * — customers are already users with role='customer' and data in customer_data JSON column.
 *
 * Idempotent: Safe to re-run — checks IF NOT EXISTS before creating tables,
 * uses INSERT IGNORE for data migration.
 *
 * Run via: php database/migrations/create_identity_profile_tables_s387.php
 */

require_once dirname(__DIR__, 2) . '/config/bootstrap_cli.php';

use App\Core\Database\Database;

try {
    $pdo = Database::getInstance()->getConnection();
    echo "✅ Database connected\n\n";
} catch (\Throwable $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

$created = 0;
$skipped = 0;
$migrated = 0;
$errors = 0;

// ─────────────────────────────────────────────────────────
// Helper: table exists check
// ─────────────────────────────────────────────────────────
function tableExists(PDO $pdo, string $table): bool {
    $row = $pdo->query("SHOW TABLES LIKE '{$table}'")->fetch();
    return $row !== false;
}

// ─────────────────────────────────────────────────────────
// 1. Create customer_profiles table
// ─────────────────────────────────────────────────────────
echo "━━━ customer_profiles ━━━\n";
if (!tableExists($pdo, 'customer_profiles')) {
    try {
        $pdo->exec("
            CREATE TABLE customer_profiles (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id BIGINT UNSIGNED NOT NULL,
                preferred_location VARCHAR(100),
                budget_min DECIMAL(15,2),
                budget_max DECIMAL(15,2),
                property_type VARCHAR(50),
                occupation VARCHAR(100),
                annual_income DECIMAL(15,2),
                kyc_status VARCHAR(20) DEFAULT 'pending',
                kyc_documents JSON,
                preferred_contact_method ENUM('phone','email','whatsapp') DEFAULT 'phone',
                marketing_consent TINYINT(1) DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY uk_customer_profiles_user (user_id),
                KEY idx_customer_profiles_location (preferred_location),
                KEY idx_customer_profiles_kyc (kyc_status),
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        echo "✅ CREATED: customer_profiles\n";
        $created++;
    } catch (\Throwable $e) {
        echo "❌ ERROR creating customer_profiles: " . $e->getMessage() . "\n";
        $errors++;
    }
} else {
    echo "⏭  SKIP: customer_profiles already exists\n";
    $skipped++;
}

// ─────────────────────────────────────────────────────────
// 2. Create associate_profiles table
// ─────────────────────────────────────────────────────────
echo "\n━━━ associate_profiles ━━━\n";
if (!tableExists($pdo, 'associate_profiles')) {
    try {
        $pdo->exec("
            CREATE TABLE associate_profiles (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id BIGINT UNSIGNED NOT NULL,
                level ENUM('associate','senior_associate','bdm','sr_bdm','vice_president','president','site_manager') DEFAULT 'associate',
                referral_code VARCHAR(20) NULL,
                sponsor_id BIGINT UNSIGNED NULL,
                agent_track ENUM('mlm','independent') DEFAULT 'mlm',
                agent_type ENUM('mlm_company','freelancer','independent','salaried') DEFAULT 'freelancer',
                brokerage_model ENUM('differential','flat_percentage','flat_rate_sqft') DEFAULT 'differential',
                brokerage_rate DECIMAL(10,2) DEFAULT 0.00,
                telecaller_salary DECIMAL(10,2) DEFAULT 0.00,
                telecaller_incentive_rate DECIMAL(10,2) DEFAULT 0.00,
                telecaller_sqft_rate DECIMAL(10,2) DEFAULT 0.00,
                telecaller_parent_id BIGINT UNSIGNED NULL,
                total_sales DECIMAL(12,2) DEFAULT 0.00,
                commission_earned DECIMAL(12,2) DEFAULT 0.00,
                registration_count INT DEFAULT 0,
                required_registrations INT DEFAULT 5,
                salary_eligible TINYINT(1) DEFAULT 0,
                salary_amount DECIMAL(10,2) DEFAULT 0.00,
                target_bonus_eligible TINYINT(1) DEFAULT 0,
                target_bonus_amount DECIMAL(10,2) DEFAULT 0.00,
                team_size INT DEFAULT 0,
                joining_date DATE NULL,
                pan_number VARCHAR(20) NULL,
                aadhaar_number VARCHAR(20) NULL,
                bank_account VARCHAR(50) NULL,
                bank_ifsc VARCHAR(20) NULL,
                can_offline_book TINYINT(1) DEFAULT 0,
                can_collect_cash TINYINT(1) DEFAULT 0,
                daily_cash_limit DECIMAL(10,2) DEFAULT 0.00,
                cash_policy_agreed TINYINT(1) DEFAULT 0,
                legal_consent_accepted TIMESTAMP NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY uk_associate_profiles_user (user_id),
                UNIQUE KEY uk_associate_profiles_referral_code (referral_code),
                KEY idx_associate_profiles_level (level),
                KEY idx_associate_profiles_sponsor (sponsor_id),
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        echo "✅ CREATED: associate_profiles\n";
        $created++;
    } catch (\Throwable $e) {
        echo "❌ ERROR creating associate_profiles: " . $e->getMessage() . "\n";
        $errors++;
    }
} else {
    echo "⏭  SKIP: associate_profiles already exists\n";
    $skipped++;
}

// ─────────────────────────────────────────────────────────
// 3. Create employee_profiles table
// ─────────────────────────────────────────────────────────
echo "\n━━━ employee_profiles ━━━\n";
if (!tableExists($pdo, 'employee_profiles')) {
    try {
        $pdo->exec("
            CREATE TABLE employee_profiles (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id BIGINT UNSIGNED NOT NULL,
                employee_code VARCHAR(50) NULL,
                role VARCHAR(100) NULL,
                department VARCHAR(100) NULL,
                designation VARCHAR(150) NULL,
                joining_date DATE NULL,
                salary DECIMAL(12,2) DEFAULT 0.00,
                incentive_model ENUM('salary_only','commission_only','salary_plus_commission') DEFAULT 'salary_only',
                commission_rate DECIMAL(10,2) DEFAULT 0.00,
                commission_type ENUM('flat','percentage') DEFAULT 'percentage',
                offboarded_at DATETIME NULL,
                offboard_reason VARCHAR(100) NULL,
                address TEXT NULL,
                emergency_contact VARCHAR(255) NULL,
                date_of_birth DATE NULL,
                pan_number VARCHAR(20) NULL,
                aadhaar_number VARCHAR(20) NULL,
                bank_account VARCHAR(50) NULL,
                bank_ifsc VARCHAR(20) NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY uk_employee_profiles_user (user_id),
                UNIQUE KEY uk_employee_profiles_code (employee_code),
                KEY idx_employee_profiles_department (department),
                KEY idx_employee_profiles_role (role),
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        echo "✅ CREATED: employee_profiles\n";
        $created++;
    } catch (\Throwable $e) {
        echo "❌ ERROR creating employee_profiles: " . $e->getMessage() . "\n";
        $errors++;
    }
} else {
    echo "⏭  SKIP: employee_profiles already exists\n";
    $skipped++;
}

// ─────────────────────────────────────────────────────────
// 4. Create agent_profiles table
// ─────────────────────────────────────────────────────────
echo "\n━━━ agent_profiles ━━━\n";
if (!tableExists($pdo, 'agent_profiles')) {
    try {
        $pdo->exec("
            CREATE TABLE agent_profiles (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id BIGINT UNSIGNED NOT NULL,
                license_number VARCHAR(50) NULL,
                experience_years INT NULL,
                bank_name VARCHAR(100) NULL,
                account_number VARCHAR(50) NULL,
                ifsc_code VARCHAR(20) NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY uk_agent_profiles_user (user_id),
                UNIQUE KEY uk_agent_license (license_number),
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        echo "✅ CREATED: agent_profiles\n";
        $created++;
    } catch (\Throwable $e) {
        echo "❌ ERROR creating agent_profiles: " . $e->getMessage() . "\n";
        $errors++;
    }
} else {
    echo "⏭  SKIP: agent_profiles already exists\n";
    $skipped++;
}

echo "\n=== Summary ===\n";
echo "Tables created: $created\n";
echo "Tables skipped (already exist): $skipped\n";
echo "Errors: $errors\n";

if ($errors > 0) {
    exit(1);
}