<?php
/**
 * Migration Session 210:
 * 1. construction_approvals: Architectural Map Approval & Township Construction Permit NOC Desk
 * 2. commercial_leases: Commercial Plazas & Retail Shop Lease Desk
 * 3. lease_rent_invoices: Monthly Rent Invoicing & Demand Notes Desk
 */

define('APS_ROOT', dirname(__DIR__));
require_once APS_ROOT . '/config/bootstrap.php';

$db = \App\Core\Database\Database::getInstance();
$pdo = $db->getConnection();

echo "Running enterprise migrations for Session 210...\n";

// 1. construction_approvals table
$pdo->exec("
    CREATE TABLE IF NOT EXISTS construction_approvals (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        tenant_id INT(10) UNSIGNED NOT NULL DEFAULT 1,
        customer_id INT(11) NOT NULL,
        booking_id INT(11) NULL,
        plot_id INT(11) NOT NULL,
        permit_token VARCHAR(50) NOT NULL UNIQUE,
        building_type ENUM('residential_g1','residential_g2','commercial_shop','duplex_villa') NOT NULL DEFAULT 'residential_g1',
        ground_coverage_sqft DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        total_builtup_sqft DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        front_setback_ft DECIMAL(6,2) NOT NULL DEFAULT 0.00,
        rear_setback_ft DECIMAL(6,2) NOT NULL DEFAULT 0.00,
        side_setback_left_ft DECIMAL(6,2) NOT NULL DEFAULT 0.00,
        side_setback_right_ft DECIMAL(6,2) NOT NULL DEFAULT 0.00,
        rainwater_harvesting_pit TINYINT(1) NOT NULL DEFAULT 1,
        architect_name VARCHAR(150) NULL,
        architect_council_reg VARCHAR(50) NULL,
        site_engineer_name VARCHAR(150) NULL,
        inspection_date DATE NULL,
        inspection_notes TEXT NULL,
        status ENUM('submitted','site_inspected','approved','rejected') NOT NULL DEFAULT 'submitted',
        approval_date DATE NULL,
        permit_validity_date DATE NULL,
        rejection_reason TEXT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_ca_tenant (tenant_id),
        INDEX idx_ca_plot (plot_id),
        INDEX idx_ca_customer (customer_id),
        INDEX idx_ca_status (status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

// 2. commercial_leases table
$pdo->exec("
    CREATE TABLE IF NOT EXISTS commercial_leases (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        tenant_id INT(10) UNSIGNED NOT NULL DEFAULT 1,
        lease_token VARCHAR(50) NOT NULL UNIQUE,
        colony_id INT(11) NOT NULL,
        unit_number VARCHAR(50) NOT NULL,
        unit_type ENUM('retail_shop','office_space','atm_kiosk','commercial_plot') NOT NULL DEFAULT 'retail_shop',
        carpet_area_sqft DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        tenant_name VARCHAR(150) NOT NULL,
        tenant_business_name VARCHAR(150) NULL,
        tenant_phone VARCHAR(20) NOT NULL,
        tenant_email VARCHAR(100) NULL,
        tenant_gstin VARCHAR(25) NULL,
        monthly_rent DECIMAL(15,2) NOT NULL DEFAULT 0.00,
        security_deposit DECIMAL(15,2) NOT NULL DEFAULT 0.00,
        annual_escalation_pct DECIMAL(5,2) NOT NULL DEFAULT 5.00,
        lock_in_months INT(11) NOT NULL DEFAULT 12,
        lease_start_date DATE NOT NULL,
        lease_end_date DATE NOT NULL,
        electricity_submeter_reading DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        status ENUM('active','notice_period','terminated','expired') NOT NULL DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_comm_tenant (tenant_id),
        INDEX idx_comm_colony (colony_id),
        INDEX idx_comm_status (status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

// 3. lease_rent_invoices table
$pdo->exec("
    CREATE TABLE IF NOT EXISTS lease_rent_invoices (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        tenant_id INT(10) UNSIGNED NOT NULL DEFAULT 1,
        lease_id INT(11) NOT NULL,
        invoice_no VARCHAR(50) NOT NULL UNIQUE,
        billing_month VARCHAR(7) NOT NULL,
        base_rent DECIMAL(15,2) NOT NULL DEFAULT 0.00,
        electricity_units DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        electricity_rate DECIMAL(8,2) NOT NULL DEFAULT 9.50,
        electricity_amount DECIMAL(15,2) NOT NULL DEFAULT 0.00,
        maintenance_fee DECIMAL(15,2) NOT NULL DEFAULT 0.00,
        total_due DECIMAL(15,2) NOT NULL DEFAULT 0.00,
        paid_amount DECIMAL(15,2) NOT NULL DEFAULT 0.00,
        payment_date DATE NULL,
        payment_mode VARCHAR(30) NULL,
        payment_ref VARCHAR(100) NULL,
        status ENUM('unpaid','partially_paid','paid','overdue') NOT NULL DEFAULT 'unpaid',
        notes TEXT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_lri_tenant (tenant_id),
        INDEX idx_lri_lease (lease_id),
        INDEX idx_lri_month (billing_month),
        INDEX idx_lri_status (status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

echo "Session 210 tables created or verified successfully.\n";
