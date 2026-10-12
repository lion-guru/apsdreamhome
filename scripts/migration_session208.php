<?php
/**
 * Migration for Session 208:
 * 1. construction_work_orders (township civil contracts & infrastructure milestones)
 * 2. contractor_bills (measurement-based Running Account [RA] milestone bills with retention & TDS)
 * 3. registry_appointments (Sub-Registrar Office [SRO] deed registration & execution desk)
 */

define('APS_ROOT', dirname(__DIR__));
require_once APS_ROOT . '/config/bootstrap.php';

$pdo = \App\Core\Database\Database::getInstance()->getConnection();

echo "Running migrations for Session 208...\n";

// 1. construction_work_orders
$pdo->exec("
    CREATE TABLE IF NOT EXISTS construction_work_orders (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        tenant_id INT(10) UNSIGNED NOT NULL DEFAULT 1,
        colony_id INT(11) NOT NULL,
        order_number VARCHAR(50) NOT NULL UNIQUE,
        contractor_name VARCHAR(150) NOT NULL,
        contractor_phone VARCHAR(20) NOT NULL,
        contractor_pan VARCHAR(20) NULL,
        contractor_gstin VARCHAR(30) NULL,
        work_title VARCHAR(200) NOT NULL,
        work_category ENUM('boundary_wall', 'road_tarring', 'drainage', 'electrification', 'leveling', 'gate_arch', 'landscaping', 'general') NOT NULL DEFAULT 'general',
        contract_amount DECIMAL(15,2) NOT NULL DEFAULT 0.00,
        retention_pct DECIMAL(5,2) NOT NULL DEFAULT 5.00,
        tds_pct DECIMAL(5,2) NOT NULL DEFAULT 1.00,
        start_date DATE NULL,
        completion_deadline DATE NULL,
        status ENUM('active', 'completed', 'cancelled') NOT NULL DEFAULT 'active',
        scope_description TEXT NULL,
        created_by INT(11) NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_cwo_colony (colony_id),
        INDEX idx_cwo_status (status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");
echo "Table construction_work_orders ready.\n";

// 2. contractor_bills
$pdo->exec("
    CREATE TABLE IF NOT EXISTS contractor_bills (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        tenant_id INT(10) UNSIGNED NOT NULL DEFAULT 1,
        work_order_id INT(11) NOT NULL,
        bill_number VARCHAR(50) NOT NULL,
        measurement_ref VARCHAR(100) NULL,
        measurement_notes TEXT NULL,
        gross_amount DECIMAL(15,2) NOT NULL DEFAULT 0.00,
        retention_amount DECIMAL(15,2) NOT NULL DEFAULT 0.00,
        tds_amount DECIMAL(15,2) NOT NULL DEFAULT 0.00,
        net_payable_amount DECIMAL(15,2) NOT NULL DEFAULT 0.00,
        paid_amount DECIMAL(15,2) NOT NULL DEFAULT 0.00,
        status ENUM('submitted', 'approved', 'paid', 'rejected') NOT NULL DEFAULT 'submitted',
        payment_ref VARCHAR(100) NULL,
        paid_at DATETIME NULL,
        bill_date DATE NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_cb_wo (work_order_id),
        INDEX idx_cb_status (status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");
echo "Table contractor_bills ready.\n";

// 3. registry_appointments
$pdo->exec("
    CREATE TABLE IF NOT EXISTS registry_appointments (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        tenant_id INT(10) UNSIGNED NOT NULL DEFAULT 1,
        booking_id INT(11) NOT NULL,
        appointment_token VARCHAR(50) NOT NULL UNIQUE,
        sro_office VARCHAR(100) NOT NULL DEFAULT 'Tehsil Sadar Gorakhpur',
        preferred_date_1 DATE NOT NULL,
        preferred_date_2 DATE NULL,
        confirmed_date DATE NULL,
        witness_1_name VARCHAR(100) NULL,
        witness_1_phone VARCHAR(20) NULL,
        witness_1_aadhaar VARCHAR(20) NULL,
        witness_2_name VARCHAR(100) NULL,
        witness_2_phone VARCHAR(20) NULL,
        witness_2_aadhaar VARCHAR(20) NULL,
        stamp_duty_amount DECIMAL(15,2) NOT NULL DEFAULT 0.00,
        reg_fee_amount DECIMAL(15,2) NOT NULL DEFAULT 0.00,
        assigned_executive_name VARCHAR(100) NULL,
        assigned_executive_phone VARCHAR(20) NULL,
        status ENUM('requested', 'eligible', 'slot_confirmed', 'completed', 'cancelled') NOT NULL DEFAULT 'requested',
        deed_registration_number VARCHAR(100) NULL,
        registered_deed_file VARCHAR(500) NULL,
        notes TEXT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_ra_booking (booking_id),
        INDEX idx_ra_status (status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");
echo "Table registry_appointments ready.\n";

echo "Session 208 migrations completed successfully!\n";
