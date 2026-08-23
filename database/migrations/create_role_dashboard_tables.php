<?php
/**
 * Migration: Create missing legal-domain + consolidated reminder tables
 * Session: role-dashboard error cleanup
 *
 * Merge analysis (real DB verified 2026-08-23):
 * - blogs -> blog_posts, suppliers -> vendors (merged already)
 * - tax_reminders -> efiling_deadlines, department_budgets -> budgets,
 *   work_schedules -> employee_shifts, calling_scripts -> ai_calling_scripts,
 *   operation_approvals -> daily_operations_log,
 *   task/legal/land activities -> employee_activities,
 *   visitors -> inline site_visits.visitor_* columns
 * Only genuinely-missing tables are created here:
 *   compliance_tasks, legal_disputes, dispute_timeline, contracts,
 *   employee_reminders (consolidates hr_reminders / legal_deadlines /
 *   financial_reminders / property_alerts via scope column)
 */

require_once __DIR__ . '/../../config/database.php';

$dbConfig = $config ?? [
    'host' => '127.0.0.1',
    'port' => 3307,
    'database' => 'apsdreamhome',
    'username' => 'root',
    'password' => getenv('MYSQL_PWD') ?: '',
];

$dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', $dbConfig['host'], $dbConfig['port'], $dbConfig['database']);
$pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

$migrations = [
    'compliance_tasks' => "CREATE TABLE IF NOT EXISTS compliance_tasks (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
        title VARCHAR(200) NOT NULL,
        description TEXT NULL,
        department_id INT NULL COMMENT 'departments.id is signed int',
        responsible_person INT UNSIGNED NULL,
        assigned_to INT UNSIGNED NULL,
        due_date DATE NULL,
        priority ENUM('low','medium','high') NOT NULL DEFAULT 'medium',
        status VARCHAR(30) NOT NULL DEFAULT 'pending',
        completion_notes TEXT NULL,
        completed_by INT UNSIGNED NULL,
        completed_at DATETIME NULL,
        created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY idx_ct_tenant (tenant_id),
        KEY idx_ct_assigned_status (assigned_to, status),
        KEY idx_ct_due_date (due_date),
        CONSTRAINT fk_ct_department FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    'legal_disputes' => "CREATE TABLE IF NOT EXISTS legal_disputes (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
        title VARCHAR(200) NOT NULL,
        description TEXT NULL,
        client_id BIGINT UNSIGNED NULL COMMENT 'users.id',
        property_id BIGINT UNSIGNED NULL COMMENT 'properties.id',
        assigned_lawyer BIGINT UNSIGNED NULL COMMENT 'users.id',
        assigned_to BIGINT UNSIGNED NULL,
        action_taken TEXT NULL,
        next_action_date DATE NULL,
        priority ENUM('low','medium','high') NOT NULL DEFAULT 'medium',
        status VARCHAR(30) NOT NULL DEFAULT 'open' COMMENT 'open|active|investigation|negotiation|resolved|closed',
        resolved_at DATETIME NULL,
        notes TEXT NULL,
        updated_by INT UNSIGNED NULL,
        created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY idx_ld_tenant (tenant_id),
        KEY idx_ld_status (status),
        KEY idx_ld_assigned_lawyer (assigned_lawyer),
        KEY idx_ld_client (client_id),
        CONSTRAINT fk_ld_client FOREIGN KEY (client_id) REFERENCES users(id) ON DELETE SET NULL,
        CONSTRAINT fk_ld_property FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    'dispute_timeline' => "CREATE TABLE IF NOT EXISTS dispute_timeline (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        dispute_id BIGINT UNSIGNED NOT NULL,
        action VARCHAR(100) NOT NULL,
        description TEXT NULL,
        performed_by INT UNSIGNED NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY idx_dt_dispute (dispute_id),
        CONSTRAINT fk_dt_dispute FOREIGN KEY (dispute_id) REFERENCES legal_disputes(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    'contracts' => "CREATE TABLE IF NOT EXISTS contracts (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
        contract_number VARCHAR(50) NULL,
        contract_type VARCHAR(50) NOT NULL DEFAULT 'general',
        client_id BIGINT UNSIGNED NULL,
        property_id BIGINT UNSIGNED NULL,
        start_date DATE NULL,
        expiry_date DATE NULL,
        contract_value DECIMAL(15,2) NOT NULL DEFAULT 0.00,
        status VARCHAR(30) NOT NULL DEFAULT 'active',
        assigned_to BIGINT UNSIGNED NULL,
        notes TEXT NULL,
        created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY idx_contracts_tenant (tenant_id),
        KEY idx_contracts_expiry (expiry_date, status),
        KEY idx_contracts_client (client_id),
        KEY idx_contracts_assigned (assigned_to),
        CONSTRAINT fk_contracts_client FOREIGN KEY (client_id) REFERENCES users(id) ON DELETE SET NULL,
        CONSTRAINT fk_contracts_property FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    'employee_reminders' => "CREATE TABLE IF NOT EXISTS employee_reminders (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
        scope VARCHAR(30) NOT NULL DEFAULT 'general' COMMENT 'hr|legal|finance|property|general',
        title VARCHAR(200) NOT NULL,
        description TEXT NULL,
        employee_id INT UNSIGNED NULL COMMENT 'assigned_to semantics',
        property_id INT UNSIGNED NULL,
        due_date DATE NOT NULL,
        priority ENUM('low','medium','high') NOT NULL DEFAULT 'medium',
        status VARCHAR(20) NOT NULL DEFAULT 'pending',
        notes TEXT NULL,
        created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY idx_er_scope_due (scope, due_date, status),
        KEY idx_er_employee (employee_id),
        KEY idx_er_tenant (tenant_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
];

foreach ($migrations as $table => $ddl) {
    try {
        $pdo->exec($ddl);
        echo "[OK] {$table}\n";
    } catch (PDOException $e) {
        echo "[FAIL] {$table}: " . $e->getMessage() . "\n";
    }
}

echo "\nVerification:\n";
foreach (array_keys($migrations) as $table) {
    $exists = $pdo->query("SHOW TABLES LIKE '{$table}'")->fetchColumn();
    echo $exists ? "[EXISTS] {$table}\n" : "[MISSING] {$table}\n";
}
