<?php
/**
 * Migration for Session 209:
 * 1. plot_demarcations (Township plot demarcation, 4 corner GPS coordinates & boundary pillar survey)
 * 2. customer_solar_requests (PM Surya Ghar Green Solar Township Desk & UP subsidy feasibility tracker)
 */

define('APS_ROOT', dirname(__DIR__));
require_once APS_ROOT . '/config/bootstrap.php';

$pdo = \App\Core\Database\Database::getInstance()->getConnection();

echo "Running migrations for Session 209...\n";

// 1. plot_demarcations
$pdo->exec("
    CREATE TABLE IF NOT EXISTS plot_demarcations (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        tenant_id INT(10) UNSIGNED NOT NULL DEFAULT 1,
        plot_id INT(11) NOT NULL UNIQUE,
        certificate_token VARCHAR(50) NOT NULL UNIQUE,
        surveyor_name VARCHAR(100) NOT NULL,
        surveyor_phone VARCHAR(20) NOT NULL,
        demarcation_date DATE NOT NULL,
        pillar_status ENUM('pending', 'pegs_driven', 'pillars_erected', 'verified', 'disputed') NOT NULL DEFAULT 'pending',
        corner_nw_lat DECIMAL(10,8) NULL,
        corner_nw_lng DECIMAL(11,8) NULL,
        corner_ne_lat DECIMAL(10,8) NULL,
        corner_ne_lng DECIMAL(11,8) NULL,
        corner_sw_lat DECIMAL(10,8) NULL,
        corner_sw_lng DECIMAL(11,8) NULL,
        corner_se_lat DECIMAL(10,8) NULL,
        corner_se_lng DECIMAL(11,8) NULL,
        marker_photos_json TEXT NULL,
        possession_peg_handed_over TINYINT(1) NOT NULL DEFAULT 0,
        customer_acknowledged TINYINT(1) NOT NULL DEFAULT 0,
        boundary_notes TEXT NULL,
        verified_by INT(11) NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_pd_plot (plot_id),
        INDEX idx_pd_status (pillar_status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");
echo "Table plot_demarcations ready.\n";

// 2. customer_solar_requests
$pdo->exec("
    CREATE TABLE IF NOT EXISTS customer_solar_requests (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        tenant_id INT(10) UNSIGNED NOT NULL DEFAULT 1,
        booking_id INT(11) NULL,
        customer_id INT(11) NOT NULL,
        request_token VARCHAR(50) NOT NULL UNIQUE,
        system_capacity_kw DECIMAL(4,2) NOT NULL DEFAULT 3.00,
        estimated_monthly_units INT(11) NOT NULL DEFAULT 360,
        estimated_project_cost DECIMAL(15,2) NOT NULL DEFAULT 180000.00,
        central_subsidy_amount DECIMAL(15,2) NOT NULL DEFAULT 78000.00,
        state_subsidy_amount DECIMAL(15,2) NOT NULL DEFAULT 30000.00,
        net_customer_payable DECIMAL(15,2) NOT NULL DEFAULT 72000.00,
        estimated_annual_savings DECIMAL(15,2) NOT NULL DEFAULT 30240.00,
        discom_provider VARCHAR(100) NOT NULL DEFAULT 'Purvanchal Vidyut Vitaran Nigam Ltd (PVVNL)',
        consumer_number VARCHAR(50) NULL,
        status ENUM('inquiry', 'site_feasibility', 'subsidy_approved', 'discom_sanctioned', 'installed', 'rejected') NOT NULL DEFAULT 'inquiry',
        installer_partner VARCHAR(150) NULL,
        installer_contact VARCHAR(20) NULL,
        sanction_ref VARCHAR(100) NULL,
        installed_at DATETIME NULL,
        notes TEXT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_csr_cust (customer_id),
        INDEX idx_csr_status (status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");
echo "Table customer_solar_requests ready.\n";

echo "Session 209 migrations completed successfully!\n";
