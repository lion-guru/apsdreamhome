<?php
require_once __DIR__ . '/../config/bootstrap.php';

$db = \App\Core\Database\Database::getInstance();

echo "Creating property tax tables...\n";

$db->execute("
    CREATE TABLE IF NOT EXISTS property_tax_master (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 1,
        colony_id BIGINT UNSIGNED NULL,
        plot_id BIGINT UNSIGNED NULL,
        tax_type ENUM('property_tax', 'water_tax', 'sewerage_tax', 'fire_tax', 'education_cess', 'other') NOT NULL DEFAULT 'property_tax',
        assessment_year YEAR NOT NULL,
        tax_authority VARCHAR(150) NOT NULL COMMENT 'Municipal corporation / panchayat name',
        ward_zone VARCHAR(100) NULL,
        property_category ENUM('residential', 'commercial', 'industrial', 'agricultural', 'vacant_land') NOT NULL DEFAULT 'residential',
        built_up_area DECIMAL(10,2) NULL COMMENT 'sq ft',
        land_area DECIMAL(10,2) NULL COMMENT 'sq ft',
        annual_rental_value DECIMAL(14,2) NULL,
        capital_value DECIMAL(14,2) NULL,
        tax_rate DECIMAL(6,4) NOT NULL DEFAULT 0.0000 COMMENT 'Rate per unit (e.g., per sq ft or % of ARV)',
        rate_unit ENUM('per_sqft', 'per_sqm', 'percent_arv', 'percent_cv', 'flat') NOT NULL DEFAULT 'per_sqft',
        base_tax_amount DECIMAL(14,2) NOT NULL DEFAULT 0.00,
        rebate_percent DECIMAL(5,2) NOT NULL DEFAULT 0.00 COMMENT 'Early payment / senior citizen / etc.',
        surcharge_percent DECIMAL(5,2) NOT NULL DEFAULT 0.00,
        net_tax_amount DECIMAL(14,2) NOT NULL DEFAULT 0.00,
        due_date DATE NOT NULL,
        penalty_rate_per_month DECIMAL(5,2) NOT NULL DEFAULT 1.00 COMMENT '% per month after due date',
        max_penalty_percent DECIMAL(5,2) NOT NULL DEFAULT 25.00,
        status ENUM('draft', 'published', 'demand_generated', 'closed') NOT NULL DEFAULT 'draft',
        demand_notice_number VARCHAR(50) NULL,
        demand_notice_date DATE NULL,
        notes TEXT NULL,
        created_by BIGINT UNSIGNED NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY uq_tax_master_unique (tenant_id, colony_id, plot_id, tax_type, assessment_year),
        KEY idx_colony_year (colony_id, assessment_year),
        KEY idx_plot_year (plot_id, assessment_year),
        KEY idx_status (status),
        KEY idx_due_date (due_date)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");

echo "property_tax_master created\n";

$db->execute("
    CREATE TABLE IF NOT EXISTS property_tax_payments (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 1,
        tax_master_id BIGINT UNSIGNED NOT NULL,
        plot_id BIGINT UNSIGNED NULL,
        customer_id BIGINT UNSIGNED NULL,
        booking_id BIGINT UNSIGNED NULL,
        payment_date DATE NOT NULL,
        payment_mode ENUM('cash', 'cheque', 'dd', 'online', 'upi', 'neft', 'rtgs', 'card') NOT NULL,
        cheque_dd_number VARCHAR(50) NULL,
        cheque_dd_date DATE NULL,
        bank_name VARCHAR(100) NULL,
        bank_branch VARCHAR(100) NULL,
        transaction_ref VARCHAR(100) NULL,
        upi_ref VARCHAR(100) NULL,
        base_tax_paid DECIMAL(14,2) NOT NULL DEFAULT 0.00,
        penalty_paid DECIMAL(14,2) NOT NULL DEFAULT 0.00,
        interest_paid DECIMAL(14,2) NOT NULL DEFAULT 0.00,
        rebate_availed DECIMAL(14,2) NOT NULL DEFAULT 0.00,
        total_paid DECIMAL(14,2) NOT NULL DEFAULT 0.00,
        receipt_number VARCHAR(50) NOT NULL,
        receipt_date DATE NOT NULL,
        receipt_generated_at TIMESTAMP NULL DEFAULT NULL,
        status ENUM('pending', 'verified', 'rejected', 'bounced') NOT NULL DEFAULT 'pending',
        verified_by BIGINT UNSIGNED NULL,
        verified_at TIMESTAMP NULL DEFAULT NULL,
        rejection_reason TEXT NULL,
        notes TEXT NULL,
        created_by BIGINT UNSIGNED NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY uq_receipt_number (tenant_id, receipt_number),
        KEY idx_tax_master (tax_master_id),
        KEY idx_plot (plot_id),
        KEY idx_customer (customer_id),
        KEY idx_booking (booking_id),
        KEY idx_payment_date (payment_date),
        KEY idx_status (status),
        KEY idx_receipt (receipt_number)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");

echo "property_tax_payments created\n";

$db->execute("
    CREATE TABLE IF NOT EXISTS property_tax_demand_register (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 1,
        colony_id BIGINT UNSIGNED NULL,
        assessment_year YEAR NOT NULL,
        total_properties INT NOT NULL DEFAULT 0,
        total_demand DECIMAL(18,2) NOT NULL DEFAULT 0.00,
        total_collected DECIMAL(18,2) NOT NULL DEFAULT 0.00,
        total_pending DECIMAL(18,2) NOT NULL DEFAULT 0.00,
        total_penalty_collected DECIMAL(18,2) NOT NULL DEFAULT 0.00,
        demand_generated_at TIMESTAMP NULL DEFAULT NULL,
        generated_by BIGINT UNSIGNED NULL,
        status ENUM('draft', 'generated', 'closed') NOT NULL DEFAULT 'draft',
        notes TEXT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY uq_demand_register (tenant_id, colony_id, assessment_year),
        KEY idx_colony_year (colony_id, assessment_year),
        KEY idx_status (status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");

echo "property_tax_demand_register created\n";

$db->execute("
    CREATE TABLE IF NOT EXISTS property_tax_rebate_rules (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        tenant_id BIGINT UNSIGNED NOT NULL DEFAULT 1,
        rule_name VARCHAR(100) NOT NULL,
        rule_code VARCHAR(30) NOT NULL UNIQUE,
        applicable_tax_types JSON NOT NULL COMMENT 'Array of tax_type values',
        rebate_type ENUM('percent', 'flat_amount') NOT NULL DEFAULT 'percent',
        rebate_value DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        max_rebate_amount DECIMAL(14,2) NULL,
        conditions_json JSON NULL COMMENT 'e.g., senior_citizen=true, age_above=60, early_payment_days=30',
        is_active TINYINT(1) NOT NULL DEFAULT 1,
        effective_from DATE NOT NULL,
        effective_to DATE NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY idx_active (is_active),
        KEY idx_effective (effective_from, effective_to)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");

echo "property_tax_rebate_rules created\n";

$defaults = [
    ['early_payment', 'Early Payment Rebate', '["property_tax","water_tax","sewerage_tax"]', 'percent', 5.00, null, json_encode(['early_payment_days' => 30]), '2024-01-01', null],
    ['senior_citizen', 'Senior Citizen Rebate', '["property_tax"]', 'percent', 10.00, 5000.00, json_encode(['senior_citizen' => true, 'age_above' => 60]), '2024-01-01', null],
    ['disabled_person', 'Disabled Person Rebate', '["property_tax"]', 'percent', 15.00, 10000.00, json_encode(['disabled' => true]), '2024-01-01', null],
    ['women_owner', 'Women Property Owner Rebate', '["property_tax"]', 'percent', 5.00, 3000.00, json_encode(['women_owner' => true]), '2024-01-01', null],
];
foreach ($defaults as $d) {
    $db->execute("
        INSERT IGNORE INTO property_tax_rebate_rules (rule_code, rule_name, applicable_tax_types, rebate_type, rebate_value, max_rebate_amount, conditions_json, effective_from, effective_to)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)", $d);
}
echo "Default rebate rules seeded\n";

echo "\nAll property tax tables created and seeded successfully.\n";