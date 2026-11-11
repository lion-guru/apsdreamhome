<?php
/**
 * Migration: Create Final Test Suite Tables — Session 391
 * Phase 4/7: Identity Unification — DB Rebuild Recovery
 *
 * Creates remaining tables needed for test suite:
 *   1. registry_appointments
 *   2. plot_demarcations
 *   3. customer_solar_requests
 *   4. construction_approvals
 *   5. commercial_leases
 *
 * Adds account_holder_name column to user_bank_accounts.
 *
 * Idempotent: Uses CREATE TABLE IF NOT EXISTS and ALTER TABLE.
 *
 * Run via: php database/migrations/create_final_test_tables_s391.php
 */

require_once dirname(__DIR__, 2) . '/config/bootstrap.php';

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
$errors = 0;

// Helper: table exists check
function tableExists(PDO $pdo, string $table): bool {
    $row = $pdo->query("SHOW TABLES LIKE '$table'")->fetch();
    return $row !== false;
}

// Helper: create table if not exists
function createTable(PDO $pdo, string $table, string $sql): bool {
    if (tableExists($pdo, $table)) {
        echo "⏭  SKIP: $table already exists\n";
        return false;
    }
    try {
        $pdo->exec($sql);
        echo "✅ CREATED: $table\n";
        return true;
    } catch (\Throwable $e) {
        echo "❌ ERROR creating $table: " . $e->getMessage() . "\n";
        return false;
    }
}

echo "━━━ Final Test Suite Tables ━━━\n\n";

// 1. registry_appointments
createTable($pdo, 'registry_appointments', "
    CREATE TABLE `registry_appointments` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `booking_id` int(11) DEFAULT NULL,
        `appointment_token` varchar(50) DEFAULT NULL,
        `sro_office` varchar(255) DEFAULT NULL,
        `preferred_date` date DEFAULT NULL,
        `confirmed_date` date DEFAULT NULL,
        `witness1_name` varchar(255) DEFAULT NULL,
        `witness1_aadhaar` varchar(20) DEFAULT NULL,
        `witness2_name` varchar(255) DEFAULT NULL,
        `witness2_aadhaar` varchar(20) DEFAULT NULL,
        `stamp_duty` decimal(12,2) DEFAULT 0.00,
        `registration_fee` decimal(12,2) DEFAULT 0.00,
        `assigned_executive` int(11) DEFAULT NULL,
        `status` enum('requested','confirmed','completed','cancelled') DEFAULT 'requested',
        `deed_number` varchar(100) DEFAULT NULL,
        `deed_file` varchar(500) DEFAULT NULL,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `booking_id` (`booking_id`),
        KEY `appointment_token` (`appointment_token`),
        KEY `status` (`status`),
        KEY `sro_office` (`sro_office`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");
$created++;

// 2. plot_demarcations
createTable($pdo, 'plot_demarcations', "
    CREATE TABLE `plot_demarcations` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `plot_id` int(11) DEFAULT NULL,
        `token` varchar(50) DEFAULT NULL,
        `surveyor_name` varchar(255) DEFAULT NULL,
        `surveyor_phone` varchar(20) DEFAULT NULL,
        `demarcation_date` date DEFAULT NULL,
        `pillar_status` enum('pegs_driven','pillars_erected','verified','disputed') DEFAULT 'pegs_driven',
        `corner_nw_lat` decimal(10,8) DEFAULT NULL,
        `corner_nw_lng` decimal(11,8) DEFAULT NULL,
        `corner_ne_lat` decimal(10,8) DEFAULT NULL,
        `corner_ne_lng` decimal(11,8) DEFAULT NULL,
        `corner_sw_lat` decimal(10,8) DEFAULT NULL,
        `corner_sw_lng` decimal(11,8) DEFAULT NULL,
        `corner_se_lat` decimal(10,8) DEFAULT NULL,
        `corner_se_lng` decimal(11,8) DEFAULT NULL,
        `possession_peg_handover` tinyint(1) DEFAULT 0,
        `customer_acknowledgement` tinyint(1) DEFAULT 0,
        `boundary_notes` text DEFAULT NULL,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `plot_id` (`plot_id`),
        KEY `token` (`token`),
        KEY `pillar_status` (`pillar_status`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");
$created++;

// 3. customer_solar_requests
createTable($pdo, 'customer_solar_requests', "
    CREATE TABLE `customer_solar_requests` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `customer_id` int(11) DEFAULT NULL,
        `booking_id` int(11) DEFAULT NULL,
        `token` varchar(50) DEFAULT NULL,
        `system_capacity_kw` decimal(5,2) DEFAULT 0.00,
        `estimated_units` decimal(10,2) DEFAULT 0.00,
        `project_cost` decimal(12,2) DEFAULT 0.00,
        `central_subsidy` decimal(12,2) DEFAULT 0.00,
        `state_subsidy` decimal(12,2) DEFAULT 0.00,
        `net_payable` decimal(12,2) DEFAULT 0.00,
        `annual_savings` decimal(12,2) DEFAULT 0.00,
        `discom` varchar(100) DEFAULT NULL,
        `consumer_account_no` varchar(100) DEFAULT NULL,
        `status` enum('inquiry','site_feasibility','subsidy_approved','discom_sanctioned','installed','rejected') DEFAULT 'inquiry',
        `epc_partner` varchar(255) DEFAULT NULL,
        `epc_contact` varchar(20) DEFAULT NULL,
        `sanction_ref` varchar(100) DEFAULT NULL,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `customer_id` (`customer_id`),
        KEY `booking_id` (`booking_id`),
        KEY `token` (`token`),
        KEY `status` (`status`),
        KEY `discom` (`discom`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");
$created++;

// 4. construction_approvals
createTable($pdo, 'construction_approvals', "
    CREATE TABLE `construction_approvals` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `buyer` varchar(255) DEFAULT NULL,
        `plot_id` int(11) DEFAULT NULL,
        `token` varchar(50) DEFAULT NULL,
        `building_category` enum('residential_g1','residential_g2','commercial_shop','duplex_villa') DEFAULT 'residential_g1',
        `ground_coverage_sqft` decimal(10,2) DEFAULT 0.00,
        `total_builtup_sqft` decimal(10,2) DEFAULT 0.00,
        `front_setback_ft` decimal(5,2) DEFAULT 0.00,
        `rear_setback_ft` decimal(5,2) DEFAULT 0.00,
        `side_setback_left_ft` decimal(5,2) DEFAULT 0.00,
        `side_setback_right_ft` decimal(5,2) DEFAULT 0.00,
        `rainwater_harvesting_pit` tinyint(1) DEFAULT 0,
        `architect_name` varchar(255) DEFAULT NULL,
        `architect_coa_reg` varchar(100) DEFAULT NULL,
        `site_engineer` varchar(255) DEFAULT NULL,
        `site_engineer_notes` text DEFAULT NULL,
        `status` enum('submitted','site_inspected','approved','rejected') DEFAULT 'submitted',
        `permit_validity_years` int(11) DEFAULT 1,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `plot_id` (`plot_id`),
        KEY `token` (`token`),
        KEY `status` (`status`),
        KEY `building_category` (`building_category`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");
$created++;

// 5. commercial_leases
createTable($pdo, 'commercial_leases', "
    CREATE TABLE `commercial_leases` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `unit_number` varchar(50) DEFAULT NULL,
        `colony_id` int(11) DEFAULT NULL,
        `unit_type` enum('retail_shop','office_space','atm_kiosk','commercial_plot') DEFAULT 'retail_shop',
        `carpet_area_sqft` decimal(10,2) DEFAULT 0.00,
        `tenant_name` varchar(255) DEFAULT NULL,
        `tenant_gstin` varchar(50) DEFAULT NULL,
        `monthly_rent` decimal(12,2) DEFAULT 0.00,
        `security_deposit` decimal(12,2) DEFAULT 0.00,
        `annual_escalation_pct` decimal(5,2) DEFAULT 0.00,
        `lock_in_period_months` int(11) DEFAULT 0,
        `submeter_reading` decimal(10,2) DEFAULT 0.00,
        `status` enum('active','expired','terminated','vacant') DEFAULT 'active',
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `unit_number` (`unit_number`),
        KEY `colony_id` (`colony_id`),
        KEY `unit_type` (`unit_type`),
        KEY `status` (`status`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");
$created++;

// ─────────────────────────────────────────────────────────
// Add account_holder_name to user_bank_accounts
// ─────────────────────────────────────────────────────────
echo "\n━━━ Fixing user_bank_accounts ━━━\n";

try {
    // Check if column exists
    $cols = $pdo->query("SHOW COLUMNS FROM user_bank_accounts LIKE 'account_holder_name'")->fetch();
    if (!$cols) {
        $pdo->exec("ALTER TABLE user_bank_accounts ADD COLUMN account_holder_name varchar(255) DEFAULT NULL AFTER user_id");
        echo "✅ Added account_holder_name to user_bank_accounts\n";
    } else {
        echo "⏭  SKIP: account_holder_name already exists\n";
    }
} catch (\Throwable $e) {
    echo "❌ ERROR adding account_holder_name: " . $e->getMessage() . "\n";
    $errors++;
}

// ─────────────────────────────────────────────────────────
// Summary
// ─────────────────────────────────────────────────────────
echo "\n=== Summary ===\n";
echo "Tables created: $created\n";
echo "Errors: $errors\n";

if ($errors > 0) {
    echo "\n⚠️  Migration completed with errors\n";
    exit(1);
}

echo "\n✅ Final test suite tables migration complete!\n";
