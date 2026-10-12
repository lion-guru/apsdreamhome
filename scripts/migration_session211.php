<?php
/**
 * Migration Session 211:
 * 1. farmer_land_acquisitions: Farmer Land Acquisition & Bainama Desk
 * 2. farmer_compensation_milestones: Staged Acquisition Compensation Payouts & RTGS Vouchers
 * 3. township_resident_grievances: Township Infrastructure Grievance & Society AMC Helpdesk
 */

define('APS_ROOT', dirname(__DIR__));
require_once APS_ROOT . '/config/bootstrap.php';

$db = \App\Core\Database\Database::getInstance();
$pdo = $db->getConnection();

echo "Running enterprise migrations for Session 211...\n";

// 1. farmer_land_acquisitions table
$pdo->exec("
    CREATE TABLE IF NOT EXISTS farmer_land_acquisitions (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        tenant_id INT(10) UNSIGNED NOT NULL DEFAULT 1,
        deal_token VARCHAR(50) NOT NULL UNIQUE,
        target_colony_id INT(11) NULL,
        farmer_id INT(11) NULL,
        farmer_name VARCHAR(150) NOT NULL,
        farmer_phone VARCHAR(20) NOT NULL,
        farmer_aadhaar VARCHAR(20) NULL,
        farmer_pan VARCHAR(20) NULL,
        bank_account_no VARCHAR(50) NULL,
        bank_ifsc VARCHAR(20) NULL,
        bank_name VARCHAR(100) NULL,
        mauza_village VARCHAR(150) NOT NULL,
        tehsil VARCHAR(100) NOT NULL DEFAULT 'Sadar',
        district VARCHAR(100) NOT NULL DEFAULT 'Gorakhpur',
        gata_number VARCHAR(100) NOT NULL,
        fasli_year VARCHAR(20) NULL,
        khatauni_verified TINYINT(1) NOT NULL DEFAULT 0,
        area_bigha DECIMAL(8,4) NOT NULL DEFAULT 0.0000,
        area_sqft DECIMAL(12,2) NOT NULL DEFAULT 0.00,
        rate_per_bigha DECIMAL(15,2) NOT NULL DEFAULT 0.00,
        total_deal_amount DECIMAL(15,2) NOT NULL DEFAULT 0.00,
        paid_amount DECIMAL(15,2) NOT NULL DEFAULT 0.00,
        status ENUM('bayana_agreement','mutation_in_progress','possession_taken','registry_completed','disputed','cancelled') NOT NULL DEFAULT 'bayana_agreement',
        agreement_date DATE NOT NULL,
        target_registry_date DATE NULL,
        remarks TEXT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_fla_tenant (tenant_id),
        INDEX idx_fla_colony (target_colony_id),
        INDEX idx_fla_status (status),
        INDEX idx_fla_gata (gata_number)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

// 2. farmer_compensation_milestones table
$pdo->exec("
    CREATE TABLE IF NOT EXISTS farmer_compensation_milestones (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        tenant_id INT(10) UNSIGNED NOT NULL DEFAULT 1,
        acquisition_id INT(11) NOT NULL,
        milestone_name VARCHAR(100) NOT NULL,
        percentage DECIMAL(5,2) NOT NULL DEFAULT 0.00,
        amount DECIMAL(15,2) NOT NULL DEFAULT 0.00,
        due_date DATE NULL,
        status ENUM('pending','disbursed','held') NOT NULL DEFAULT 'pending',
        disbursed_at DATE NULL,
        payment_mode VARCHAR(30) NULL,
        payment_ref VARCHAR(100) NULL,
        tds_deducted DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        voucher_token VARCHAR(50) NULL UNIQUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_fcm_tenant (tenant_id),
        INDEX idx_fcm_acq (acquisition_id),
        INDEX idx_fcm_status (status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

// 3. township_resident_grievances table
$pdo->exec("
    CREATE TABLE IF NOT EXISTS township_resident_grievances (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        tenant_id INT(10) UNSIGNED NOT NULL DEFAULT 1,
        ticket_token VARCHAR(50) NOT NULL UNIQUE,
        customer_id INT(11) NOT NULL,
        colony_id INT(11) NOT NULL,
        plot_id INT(11) NULL,
        category ENUM('water_supply','drainage_sanitation','street_lighting','road_civil_work','park_horticulture','security_gate','other') NOT NULL DEFAULT 'water_supply',
        priority ENUM('low','medium','high','emergency') NOT NULL DEFAULT 'medium',
        title VARCHAR(200) NOT NULL,
        description TEXT NOT NULL,
        assigned_contractor_name VARCHAR(150) NULL,
        assigned_contact VARCHAR(20) NULL,
        estimated_cost DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        status ENUM('open','in_progress','resolved','closed','reopened') NOT NULL DEFAULT 'open',
        sla_deadline DATETIME NULL,
        resolution_notes TEXT NULL,
        resolved_at DATETIME NULL,
        resident_rating TINYINT(1) NULL,
        resident_feedback TEXT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_trg_tenant (tenant_id),
        INDEX idx_trg_customer (customer_id),
        INDEX idx_trg_colony (colony_id),
        INDEX idx_trg_status (status),
        INDEX idx_trg_category (category)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

echo "Session 211 tables created or verified successfully.\n";
