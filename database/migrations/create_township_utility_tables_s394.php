<?php
/**
 * Migration: Create Township Utility Tables Part 2 — Session 394
 * Phase 4/7: Identity Unification — DB Rebuild Recovery
 *
 * Creates remaining township utility tables:
 *   1. township_water_logs
 *   2. township_security_rosters
 *   3. township_security_incidents
 *   4. township_genset_logs
 *
 * Idempotent: Uses CREATE TABLE IF NOT EXISTS.
 *
 * Run via: php database/migrations/create_township_utility_tables_s394.php
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

echo "━━━ Township Utility Tables Part 2 ━━━\n\n";

// 1. township_water_logs
createTable($pdo, 'township_water_logs', "
    CREATE TABLE `township_water_logs` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `colony_id` int(11) DEFAULT NULL,
        `log_date` date DEFAULT NULL,
        `shift` enum('morning','afternoon','evening','night') DEFAULT 'morning',
        `pump_number` varchar(50) DEFAULT NULL,
        `pump_start_time` time DEFAULT NULL,
        `pump_stop_time` time DEFAULT NULL,
        `run_hours` decimal(5,2) DEFAULT 0.00,
        `power_units_kwh` decimal(10,2) DEFAULT 0.00,
        `water_pumped_kl` decimal(10,2) DEFAULT 0.00,
        `oht_level_pct` decimal(5,2) DEFAULT 0.00,
        `tds_ppm` int(11) DEFAULT 0,
        `ph_level` decimal(4,2) DEFAULT 0.00,
        `chlorine_dose_gm` int(11) DEFAULT 0,
        `line_pressure_bar` decimal(5,2) DEFAULT 0.00,
        `operator_name` varchar(255) DEFAULT NULL,
        `operator_phone` varchar(20) DEFAULT NULL,
        `pump_health` enum('optimal','minor_vibration','low_yield','needs_servicing','breakdown') DEFAULT 'optimal',
        `remarks` text DEFAULT NULL,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `colony_id` (`colony_id`),
        KEY `log_date` (`log_date`),
        KEY `pump_number` (`pump_number`),
        KEY `pump_health` (`pump_health`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");
$created++;

// 2. township_security_rosters
createTable($pdo, 'township_security_rosters', "
    CREATE TABLE `township_security_rosters` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `colony_id` int(11) DEFAULT NULL,
        `roster_date` date DEFAULT NULL,
        `shift` enum('morning_06_14','evening_14_22','night_22_06') DEFAULT 'morning_06_14',
        `gate_name` varchar(100) DEFAULT NULL,
        `guard_name` varchar(255) DEFAULT NULL,
        `guard_aadhaar_last4` varchar(4) DEFAULT NULL,
        `guard_phone` varchar(20) DEFAULT NULL,
        `agency_name` varchar(255) DEFAULT NULL,
        `supervisor_name` varchar(255) DEFAULT NULL,
        `supervisor_contact` varchar(20) DEFAULT NULL,
        `status` enum('scheduled','checked_in','relieved','absent') DEFAULT 'scheduled',
        `checkin_time` time DEFAULT NULL,
        `checkout_time` time DEFAULT NULL,
        `boom_barrier_status` enum('functional','manual_override','under_maintenance') DEFAULT 'functional',
        `visitor_vehicles_logged` int(11) DEFAULT 0,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `colony_id` (`colony_id`),
        KEY `roster_date` (`roster_date`),
        KEY `shift` (`shift`),
        KEY `status` (`status`),
        KEY `gate_name` (`gate_name`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");
$created++;

// 3. township_security_incidents
createTable($pdo, 'township_security_incidents', "
    CREATE TABLE `township_security_incidents` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `colony_id` int(11) DEFAULT NULL,
        `roster_id` int(11) DEFAULT NULL,
        `incident_date` date DEFAULT NULL,
        `incident_time` time DEFAULT NULL,
        `category` enum('unauthorized_trespass','vehicle_parking_dispute','street_light_blackout','stray_cattle_intrusion','gate_boom_barrier_hit','suspicious_activity','other') DEFAULT 'other',
        `severity` enum('low','medium','high','critical') DEFAULT 'low',
        `location_detail` varchar(255) DEFAULT NULL,
        `description` text DEFAULT NULL,
        `action_taken` text DEFAULT NULL,
        `police_informed` tinyint(1) DEFAULT 0,
        `status` enum('logged','under_review','resolved') DEFAULT 'logged',
        `closed_at` datetime DEFAULT NULL,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `colony_id` (`colony_id`),
        KEY `roster_id` (`roster_id`),
        KEY `incident_date` (`incident_date`),
        KEY `category` (`category`),
        KEY `severity` (`severity`),
        KEY `status` (`status`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");
$created++;

// 4. township_genset_logs
createTable($pdo, 'township_genset_logs', "
    CREATE TABLE `township_genset_logs` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `colony_id` int(11) DEFAULT NULL,
        `log_date` date DEFAULT NULL,
        `genset_number` varchar(50) DEFAULT NULL,
        `start_time` time DEFAULT NULL,
        `stop_time` time DEFAULT NULL,
        `run_hours` decimal(5,2) DEFAULT 0.00,
        `diesel_consumed_litres` decimal(10,2) DEFAULT 0.00,
        `diesel_added_litres` decimal(10,2) DEFAULT 0.00,
        `diesel_closing_stock_litres` decimal(10,2) DEFAULT 0.00,
        `power_generated_kwh` decimal(10,2) DEFAULT 0.00,
        `fuel_efficiency_kwh_per_litre` decimal(5,2) DEFAULT 0.00,
        `diesel_rate_per_litre` decimal(10,2) DEFAULT 0.00,
        `diesel_expense_inr` decimal(12,2) DEFAULT 0.00,
        `outage_reason` enum('discom_grid_failure','transformer_overload','storm_feeder_breakdown','scheduled_load_shedding','preventive_routine_trial') DEFAULT 'discom_grid_failure',
        `operator_name` varchar(255) DEFAULT NULL,
        `operator_phone` varchar(20) DEFAULT NULL,
        `mechanical_status` enum('optimal','lube_oil_service_due','low_coolant','battery_charger_fault','breakdown') DEFAULT 'optimal',
        `remarks` text DEFAULT NULL,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `colony_id` (`colony_id`),
        KEY `log_date` (`log_date`),
        KEY `genset_number` (`genset_number`),
        KEY `outage_reason` (`outage_reason`),
        KEY `mechanical_status` (`mechanical_status`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");
$created++;

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

echo "\n✅ Township utility tables part 2 migration complete!\n";
