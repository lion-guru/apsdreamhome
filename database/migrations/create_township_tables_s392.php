<?php
/**
 * Migration: Create Township Tables — Session 392
 * Phase 4/7: Identity Unification — DB Rebuild Recovery
 *
 * Creates township operation tables:
 *   1. township_fire_safety_logs
 *   2. township_stp_logs
 *   3. township_resident_grievances
 *   4. construction_work_orders
 *   5. contractor_bills
 *
 * Adds account_holder column default to user_bank_accounts.
 *
 * Idempotent: Uses CREATE TABLE IF NOT EXISTS and ALTER TABLE.
 *
 * Run via: php database/migrations/create_township_tables_s392.php
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

echo "━━━ Township Tables ━━━\n\n";

// 1. township_fire_safety_logs
createTable($pdo, 'township_fire_safety_logs', "
    CREATE TABLE `township_fire_safety_logs` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `colony_id` int(11) DEFAULT NULL,
        `audit_date` date DEFAULT NULL,
        `fire_extinguishers_count` int(11) DEFAULT 0,
        `fire_extinguishers_serviced` int(11) DEFAULT 0,
        `fire_hydrants_count` int(11) DEFAULT 0,
        `fire_hydrants_working` int(11) DEFAULT 0,
        `smoke_detectors_count` int(11) DEFAULT 0,
        `smoke_detectors_working` int(11) DEFAULT 0,
        `fire_alarm_system` tinyint(1) DEFAULT 0,
        `fire_alarm_tested` tinyint(1) DEFAULT 0,
        `emergency_exit_signs` tinyint(1) DEFAULT 0,
        `emergency_lighting` tinyint(1) DEFAULT 0,
        `fire_safety_officer` varchar(255) DEFAULT NULL,
        `officer_contact` varchar(20) DEFAULT NULL,
        `last_drill_date` date DEFAULT NULL,
        `next_drill_date` date DEFAULT NULL,
        `compliance_status` enum('compliant','non_compliant','pending') DEFAULT 'pending',
        `remarks` text DEFAULT NULL,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `colony_id` (`colony_id`),
        KEY `audit_date` (`audit_date`),
        KEY `compliance_status` (`compliance_status`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");
$created++;

// 2. township_stp_logs
createTable($pdo, 'township_stp_logs', "
    CREATE TABLE `township_stp_logs` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `colony_id` int(11) DEFAULT NULL,
        `log_date` date DEFAULT NULL,
        `stp_capacity_kld` decimal(10,2) DEFAULT 0.00,
        `inflow_kld` decimal(10,2) DEFAULT 0.00,
        `outflow_kld` decimal(10,2) DEFAULT 0.00,
        `inflow_bod` decimal(10,2) DEFAULT 0.00,
        `outflow_bod` decimal(10,2) DEFAULT 0.00,
        `inflow_cod` decimal(10,2) DEFAULT 0.00,
        `outflow_cod` decimal(10,2) DEFAULT 0.00,
        `inflow_tss` decimal(10,2) DEFAULT 0.00,
        `outflow_tss` decimal(10,2) DEFAULT 0.00,
        `sludge_dried_kg` decimal(10,2) DEFAULT 0.00,
        `sludge_disposed` tinyint(1) DEFAULT 0,
        `electricity_units` decimal(10,2) DEFAULT 0.00,
        `operational_hours` decimal(10,2) DEFAULT 0.00,
        `operator_name` varchar(255) DEFAULT NULL,
        `operator_contact` varchar(20) DEFAULT NULL,
        `compliance_status` enum('compliant','non_compliant','pending') DEFAULT 'pending',
        `remarks` text DEFAULT NULL,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `colony_id` (`colony_id`),
        KEY `log_date` (`log_date`),
        KEY `compliance_status` (`compliance_status`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");
$created++;

// 3. township_resident_grievances
createTable($pdo, 'township_resident_grievances', "
    CREATE TABLE `township_resident_grievances` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `ticket_token` varchar(50) DEFAULT NULL,
        `colony_id` int(11) DEFAULT NULL,
        `resident_name` varchar(255) DEFAULT NULL,
        `resident_phone` varchar(20) DEFAULT NULL,
        `resident_email` varchar(255) DEFAULT NULL,
        `plot_number` varchar(50) DEFAULT NULL,
        `category` enum('water_supply','drainage_sanitation','street_lighting','road_civil_work','park_horticulture','security_gate','other') DEFAULT 'other',
        `priority` enum('emergency','high','medium','low') DEFAULT 'medium',
        `title` varchar(255) DEFAULT NULL,
        `description` text DEFAULT NULL,
        `status` enum('open','in_progress','resolved','closed') DEFAULT 'open',
        `assigned_to` int(11) DEFAULT NULL,
        `assigned_amc_contractor` varchar(255) DEFAULT NULL,
        `amc_contractor_contact` varchar(20) DEFAULT NULL,
        `amc_contractor_cost` decimal(12,2) DEFAULT 0.00,
        `resolution_notes` text DEFAULT NULL,
        `resident_rating` int(11) DEFAULT NULL,
        `resident_feedback` text DEFAULT NULL,
        `sla_deadline` datetime DEFAULT NULL,
        `resolved_at` datetime DEFAULT NULL,
        `closed_at` datetime DEFAULT NULL,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `ticket_token` (`ticket_token`),
        KEY `colony_id` (`colony_id`),
        KEY `category` (`category`),
        KEY `priority` (`priority`),
        KEY `status` (`status`),
        KEY `assigned_to` (`assigned_to`),
        KEY `sla_deadline` (`sla_deadline`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");
$created++;

// 4. construction_work_orders
createTable($pdo, 'construction_work_orders', "
    CREATE TABLE `construction_work_orders` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `colony_id` int(11) DEFAULT NULL,
        `work_order_no` varchar(50) DEFAULT NULL,
        `contractor_name` varchar(255) DEFAULT NULL,
        `contractor_contact` varchar(20) DEFAULT NULL,
        `contract_sum` decimal(15,2) DEFAULT 0.00,
        `retention_pct` decimal(5,2) DEFAULT 5.00,
        `tds_pct` decimal(5,2) DEFAULT 1.00,
        `start_date` date DEFAULT NULL,
        `deadline_date` date DEFAULT NULL,
        `completion_date` date DEFAULT NULL,
        `status` enum('draft','active','completed','cancelled') DEFAULT 'draft',
        `work_description` text DEFAULT NULL,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `work_order_no` (`work_order_no`),
        KEY `colony_id` (`colony_id`),
        KEY `status` (`status`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");
$created++;

// 5. contractor_bills
createTable($pdo, 'contractor_bills', "
    CREATE TABLE `contractor_bills` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `work_order_id` int(11) DEFAULT NULL,
        `ra_bill_no` varchar(50) DEFAULT NULL,
        `measurement_book_ref` varchar(100) DEFAULT NULL,
        `measurement_book_notes` text DEFAULT NULL,
        `gross_amount` decimal(15,2) DEFAULT 0.00,
        `retention_amount` decimal(15,2) DEFAULT 0.00,
        `tds_amount` decimal(15,2) DEFAULT 0.00,
        `net_payable` decimal(15,2) DEFAULT 0.00,
        `payment_utr` varchar(100) DEFAULT NULL,
        `payment_date` date DEFAULT NULL,
        `status` enum('submitted','approved','paid','rejected') DEFAULT 'submitted',
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `work_order_id` (`work_order_id`),
        KEY `ra_bill_no` (`ra_bill_no`),
        KEY `status` (`status`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");
$created++;

// ─────────────────────────────────────────────────────────
// Fix user_bank_accounts - add default for account_holder
// ─────────────────────────────────────────────────────────
echo "\n━━━ Fixing user_bank_accounts ━━━\n";

try {
    // Check if account_holder column exists
    $cols = $pdo->query("SHOW COLUMNS FROM user_bank_accounts LIKE 'account_holder'")->fetch();
    if (!$cols) {
        $pdo->exec("ALTER TABLE user_bank_accounts ADD COLUMN account_holder varchar(255) DEFAULT '' AFTER user_id");
        echo "✅ Added account_holder to user_bank_accounts\n";
    } else {
        // Make it have a default value
        $pdo->exec("ALTER TABLE user_bank_accounts MODIFY COLUMN account_holder varchar(255) DEFAULT ''");
        echo "✅ Set default for account_holder\n";
    }
} catch (\Throwable $e) {
    echo "❌ ERROR fixing account_holder: " . $e->getMessage() . "\n";
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

echo "\n✅ Township tables migration complete!\n";
