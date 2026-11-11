<?php
/**
 * Migration: Create Township Utility Tables — Session 393
 * Phase 4/7: Identity Unification — DB Rebuild Recovery
 *
 * Creates remaining township utility tables:
 *   1. township_horticulture_logs
 *   2. township_cctv_logs
 *   3. township_waste_logs
 *   4. township_lighting_logs
 *   5. township_rwh_logs
 *
 * Idempotent: Uses CREATE TABLE IF NOT EXISTS.
 *
 * Run via: php database/migrations/create_township_utility_tables_s393.php
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

echo "━━━ Township Utility Tables ━━━\n\n";

// 1. township_horticulture_logs
createTable($pdo, 'township_horticulture_logs', "
    CREATE TABLE `township_horticulture_logs` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `colony_id` int(11) DEFAULT NULL,
        `census_date` date DEFAULT NULL,
        `park_name` varchar(255) DEFAULT NULL,
        `green_area_sqft` decimal(12,2) DEFAULT 0.00,
        `trees_planted_count` int(11) DEFAULT 0,
        `tree_species_summary` varchar(255) DEFAULT NULL,
        `survival_rate_pct` decimal(5,2) DEFAULT 0.00,
        `watering_method` enum('automated_sprinkler','tractor_tanker','manual_hose','drip_irrigation') DEFAULT 'automated_sprinkler',
        `fertilizer_used_kg` decimal(10,2) DEFAULT 0.00,
        `gardener_headcount` int(11) DEFAULT 0,
        `maintenance_tasks` enum('routine_watering','grass_cutting_mowing','pest_control_spray','sapling_plantation','hedges_pruning') DEFAULT 'routine_watering',
        `horticulture_supervisor` varchar(255) DEFAULT NULL,
        `supervisor_contact` varchar(20) DEFAULT NULL,
        `remarks` text DEFAULT NULL,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `colony_id` (`colony_id`),
        KEY `census_date` (`census_date`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");
$created++;

// 2. township_cctv_logs
createTable($pdo, 'township_cctv_logs', "
    CREATE TABLE `township_cctv_logs` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `colony_id` int(11) DEFAULT NULL,
        `audit_date` date DEFAULT NULL,
        `nvr_identifier` varchar(100) DEFAULT NULL,
        `total_cameras_installed` int(11) DEFAULT 0,
        `cameras_online_count` int(11) DEFAULT 0,
        `cameras_offline_count` int(11) DEFAULT 0,
        `uptime_percentage` decimal(5,2) DEFAULT 0.00,
        `recording_retention_days` int(11) DEFAULT 30,
        `hdd_storage_status` enum('healthy','warning_space_low','hdd_fault','overwriting_normal') DEFAULT 'healthy',
        `night_vision_ir_status` enum('all_functional','minor_led_failure','maintenance_needed') DEFAULT 'all_functional',
        `footage_exported_count` int(11) DEFAULT 0,
        `cctv_technician_name` varchar(255) DEFAULT NULL,
        `technician_phone` varchar(20) DEFAULT NULL,
        `offline_camera_locations` text DEFAULT NULL,
        `remarks` text DEFAULT NULL,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `colony_id` (`colony_id`),
        KEY `audit_date` (`audit_date`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");
$created++;

// 3. township_waste_logs
createTable($pdo, 'township_waste_logs', "
    CREATE TABLE `township_waste_logs` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `colony_id` int(11) DEFAULT NULL,
        `collection_date` date DEFAULT NULL,
        `shift` enum('morning_sweep','evening_sweep') DEFAULT 'morning_sweep',
        `plots_serviced_count` int(11) DEFAULT 0,
        `wet_waste_kg` decimal(10,2) DEFAULT 0.00,
        `dry_waste_kg` decimal(10,2) DEFAULT 0.00,
        `total_waste_kg` decimal(10,2) DEFAULT 0.00,
        `compost_pit_diverted_kg` decimal(10,2) DEFAULT 0.00,
        `disposal_destination` enum('society_compost_pit','nagar_nigam_designated_dump','recycling_vendor') DEFAULT 'society_compost_pit',
        `tractor_trips_count` int(11) DEFAULT 0,
        `sanitation_headcount` int(11) DEFAULT 0,
        `sanitation_supervisor` varchar(255) DEFAULT NULL,
        `supervisor_contact` varchar(20) DEFAULT NULL,
        `drain_cleaning_status` enum('completed','in_progress','scheduled','clogged_alert') DEFAULT 'completed',
        `remarks` text DEFAULT NULL,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `colony_id` (`colony_id`),
        KEY `collection_date` (`collection_date`),
        KEY `shift` (`shift`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");
$created++;

// 4. township_lighting_logs
createTable($pdo, 'township_lighting_logs', "
    CREATE TABLE `township_lighting_logs` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `colony_id` int(11) DEFAULT NULL,
        `audit_date` date DEFAULT NULL,
        `feeder_pillar_code` varchar(50) DEFAULT NULL,
        `total_poles_connected` int(11) DEFAULT 0,
        `poles_functional` int(11) DEFAULT 0,
        `poles_faulty` int(11) DEFAULT 0,
        `uptime_percent` decimal(5,2) DEFAULT 0.00,
        `timer_mode` enum('astronomical_timer','photocell_sensor','manual_mcb_switch','smart_iot') DEFAULT 'astronomical_timer',
        `on_time` time DEFAULT NULL,
        `off_time` time DEFAULT NULL,
        `phase_balance_status` enum('balanced_ryb','phase_r_trip','phase_y_trip','phase_b_trip','voltage_fluctuation') DEFAULT 'balanced_ryb',
        `cable_health` enum('healthy_underground','minor_joint_fault','cut_repair_needed') DEFAULT 'healthy_underground',
        `faulty_pole_numbers` varchar(255) DEFAULT NULL,
        `electrician_name` varchar(255) DEFAULT NULL,
        `electrician_phone` varchar(20) DEFAULT NULL,
        `remarks` text DEFAULT NULL,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `colony_id` (`colony_id`),
        KEY `audit_date` (`audit_date`),
        KEY `feeder_pillar_code` (`feeder_pillar_code`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");
$created++;

// 5. township_rwh_logs
createTable($pdo, 'township_rwh_logs', "
    CREATE TABLE `township_rwh_logs` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `colony_id` int(11) DEFAULT NULL,
        `audit_date` date DEFAULT NULL,
        `pit_identifier` varchar(50) DEFAULT NULL,
        `recharge_type` enum('borewell_recharge_shaft','percolation_tank','injection_well','catchment_swale') DEFAULT 'borewell_recharge_shaft',
        `catchment_area_sqft` decimal(10,2) DEFAULT 0.00,
        `filtration_media_status` enum('clean_gravel_sand','needs_flushing','replaced_charcoal','clogged') DEFAULT 'clean_gravel_sand',
        `desilting_chambers_cleared` tinyint(1) DEFAULT 0,
        `recharge_capacity_kl` decimal(10,2) DEFAULT 0.00,
        `water_table_depth_ft` decimal(10,2) DEFAULT 0.00,
        `monsoon_readiness_score` enum('optimal_ready','needs_cleaning','urgent_civil_repair','decommissioned') DEFAULT 'optimal_ready',
        `up_groundwater_noc_status` enum('compliant_valid','inspection_due','renewal_applied','exempt') DEFAULT 'compliant_valid',
        `environmental_officer_name` varchar(255) DEFAULT NULL,
        `officer_contact` varchar(20) DEFAULT NULL,
        `maintenance_actions_done` text DEFAULT NULL,
        `remarks` text DEFAULT NULL,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `colony_id` (`colony_id`),
        KEY `audit_date` (`audit_date`),
        KEY `pit_identifier` (`pit_identifier`),
        KEY `recharge_type` (`recharge_type`)
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

echo "\n✅ Township utility tables migration complete!\n";
