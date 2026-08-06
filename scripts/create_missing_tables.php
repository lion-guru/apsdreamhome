<?php
/**
 * Create all missing tables required by services
 * Run once: php scripts/create_missing_tables.php
 */
require __DIR__ . '/../config/bootstrap.php';

$db = \App\Core\Database\Database::getInstance();

$tables = [
    // CRM form submissions (embedded forms on landing pages)
    "CREATE TABLE IF NOT EXISTS crm_form_submissions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        form_id INT NOT NULL,
        lead_id INT DEFAULT NULL,
        submitted_data JSON NOT NULL,
        ip_address VARCHAR(45) DEFAULT NULL,
        user_agent TEXT DEFAULT NULL,
        utm_source VARCHAR(100) DEFAULT NULL,
        utm_medium VARCHAR(100) DEFAULT NULL,
        utm_campaign VARCHAR(100) DEFAULT NULL,
        page_url VARCHAR(500) DEFAULT NULL,
        device_type VARCHAR(50) DEFAULT NULL,
        tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_form (form_id),
        INDEX idx_lead (lead_id),
        INDEX idx_tenant (tenant_id),
        INDEX idx_created (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    // Daily operations log
    "CREATE TABLE IF NOT EXISTS daily_operations_log (
        id INT AUTO_INCREMENT PRIMARY KEY,
        log_date DATE NOT NULL,
        log_type VARCHAR(50) NOT NULL DEFAULT 'other',
        colony_id INT DEFAULT NULL,
        plot_id INT DEFAULT NULL,
        description TEXT DEFAULT NULL,
        amount DECIMAL(12,2) DEFAULT NULL,
        party_name VARCHAR(255) DEFAULT NULL,
        party_type VARCHAR(50) DEFAULT 'other',
        status VARCHAR(50) DEFAULT 'pending',
        priority VARCHAR(20) DEFAULT 'medium',
        assigned_to INT DEFAULT NULL,
        completed_at TIMESTAMP NULL,
        notes TEXT DEFAULT NULL,
        created_by INT DEFAULT NULL,
        tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_log_date (log_date),
        INDEX idx_log_type (log_type),
        INDEX idx_colony (colony_id),
        INDEX idx_status (status),
        INDEX idx_assigned (assigned_to),
        INDEX idx_tenant (tenant_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    // NACH/e-Mandate debit log
    "CREATE TABLE IF NOT EXISTS nach_debit_log (
        id INT AUTO_INCREMENT PRIMARY KEY,
        mandate_id_ref VARCHAR(100) NOT NULL,
        installment_id INT NOT NULL,
        debit_amount DECIMAL(12,2) NOT NULL,
        debit_date DATE NOT NULL,
        status ENUM('pending','success','failed','reversed') DEFAULT 'pending',
        tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_mandate (mandate_id_ref),
        INDEX idx_installment (installment_id),
        INDEX idx_debit_date (debit_date),
        INDEX idx_tenant (tenant_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    // RERA compliance tracking
    "CREATE TABLE IF NOT EXISTS rera_compliance_log (
        id INT AUTO_INCREMENT PRIMARY KEY,
        project_colony_id INT NOT NULL,
        quarter VARCHAR(10) NOT NULL,
        year YEAR NOT NULL,
        progress_percent DECIMAL(5,2) DEFAULT 0,
        amount_withdrawn DECIMAL(12,2) DEFAULT 0,
        status ENUM('compliant','non_compliant','pending') DEFAULT 'pending',
        submitted_at TIMESTAMP NULL,
        tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_project (project_colony_id),
        INDEX idx_quarter (quarter, year),
        INDEX idx_tenant (tenant_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    // User preferences
    "CREATE TABLE IF NOT EXISTS user_preferences (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        preference_key VARCHAR(100) NOT NULL,
        preference_value TEXT DEFAULT NULL,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
        UNIQUE INDEX idx_user_key (user_id, preference_key),
        INDEX idx_tenant (tenant_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    // Demand letter templates
    "CREATE TABLE IF NOT EXISTS demand_letter_template (
        id INT AUTO_INCREMENT PRIMARY KEY,
        template_type VARCHAR(50) NOT NULL,
        subject VARCHAR(255) NOT NULL,
        body TEXT NOT NULL,
        is_active TINYINT(1) DEFAULT 1,
        tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_type (template_type),
        INDEX idx_active (is_active),
        INDEX idx_tenant (tenant_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    // Reconciliation collections
    "CREATE TABLE IF NOT EXISTS reconciliation_collections (
        id INT AUTO_INCREMENT PRIMARY KEY,
        reconciliation_id INT NOT NULL,
        collection_date DATE NOT NULL,
        amount DECIMAL(12,2) NOT NULL,
        payment_mode VARCHAR(50) DEFAULT NULL,
        reference_number VARCHAR(100) DEFAULT NULL,
        notes TEXT DEFAULT NULL,
        tenant_id INT UNSIGNED NOT NULL DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_reconciliation (reconciliation_id),
        INDEX idx_date (collection_date),
        INDEX idx_tenant (tenant_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
];

$created = 0;
foreach ($tables as $sql) {
    try {
        $db->query($sql);
        if (preg_match('/CREATE TABLE IF NOT EXISTS `?(\w+)`?/i', $sql, $m)) {
            echo "Created: {$m[1]}\n";
            $created++;
        }
    } catch (\Throwable $e) {
        if (preg_match('/CREATE TABLE IF NOT EXISTS `?(\w+)`?/i', $sql, $m)) {
            echo "ERROR creating {$m[1]}: " . $e->getMessage() . "\n";
        }
    }
}

echo "\nTotal tables created: $created\n";
