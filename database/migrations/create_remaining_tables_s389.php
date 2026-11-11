<?php
/**
 * Migration: Create Remaining Missing Tables — Session 389
 * Phase 4/7: Identity Unification — DB Rebuild Recovery
 *
 * Creates remaining tables needed for test suite:
 *   1. cash_drawer_closings
 *   2. plot_transfers
 *   3. rera_projects (with rera_number column)
 *   4. gata_master (with gata_no column)
 *
 * Idempotent: Uses CREATE TABLE IF NOT EXISTS.
 *
 * Run via: php database/migrations/create_remaining_tables_s389.php
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

echo "━━━ Remaining Tables ━━━\n\n";

// 1. cash_drawer_closings
createTable($pdo, 'cash_drawer_closings', "
    CREATE TABLE `cash_drawer_closings` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `user_id` int(11) DEFAULT NULL,
        `colony_id` int(11) DEFAULT NULL,
        `closing_date` date DEFAULT NULL,
        `opening_balance` decimal(12,2) DEFAULT 0.00,
        `closing_balance` decimal(12,2) DEFAULT 0.00,
        `total_collected` decimal(12,2) DEFAULT 0.00,
        `total_spent` decimal(12,2) DEFAULT 0.00,
        `cash_in_hand` decimal(12,2) DEFAULT 0.00,
        `bank_deposit` decimal(12,2) DEFAULT 0.00,
        `discrepancy` decimal(12,2) DEFAULT 0.00,
        `notes` text DEFAULT NULL,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `user_id` (`user_id`),
        KEY `colony_id` (`colony_id`),
        KEY `closing_date` (`closing_date`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");
$created++;

// 2. plot_transfers
createTable($pdo, 'plot_transfers', "
    CREATE TABLE `plot_transfers` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `plot_id` int(11) DEFAULT NULL,
        `from_user_id` int(11) DEFAULT NULL,
        `to_user_id` int(11) DEFAULT NULL,
        `transfer_date` date DEFAULT NULL,
        `transfer_type` enum('sale','gift','inheritance','exchange') DEFAULT 'sale',
        `transfer_amount` decimal(12,2) DEFAULT 0.00,
        `noc_number` varchar(100) DEFAULT NULL,
        `noc_date` date DEFAULT NULL,
        `status` enum('pending','approved','completed','rejected') DEFAULT 'pending',
        `remarks` text DEFAULT NULL,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `plot_id` (`plot_id`),
        KEY `from_user_id` (`from_user_id`),
        KEY `to_user_id` (`to_user_id`),
        KEY `status` (`status`),
        KEY `noc_number` (`noc_number`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");
$created++;

// 3. rera_projects (with rera_number column)
createTable($pdo, 'rera_projects', "
    CREATE TABLE `rera_projects` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `project_name` varchar(255) NOT NULL,
        `rera_number` varchar(100) NOT NULL,
        `promoter_name` varchar(255) DEFAULT NULL,
        `promoter_address` text DEFAULT NULL,
        `project_address` text DEFAULT NULL,
        `state` varchar(100) DEFAULT NULL,
        `district` varchar(100) DEFAULT NULL,
        `pincode` varchar(20) DEFAULT NULL,
        `latitude` decimal(10,8) DEFAULT NULL,
        `longitude` decimal(11,8) DEFAULT NULL,
        `total_plots` int(11) DEFAULT 0,
        `total_area_sqft` decimal(12,2) DEFAULT 0.00,
        `registration_date` date DEFAULT NULL,
        `valid_upto` date DEFAULT NULL,
        `status` enum('registered','expired','cancelled') DEFAULT 'registered',
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `rera_number` (`rera_number`),
        KEY `status` (`status`),
        KEY `state` (`state`),
        KEY `district` (`district`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");
$created++;

// 4. gata_master (with gata_no column)
createTable($pdo, 'gata_master', "
    CREATE TABLE `gata_master` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `gata_no` varchar(50) NOT NULL,
        `khasra_no` varchar(50) DEFAULT NULL,
        `khata_no` varchar(50) DEFAULT NULL,
        `colony_id` int(11) DEFAULT NULL,
        `village` varchar(255) DEFAULT NULL,
        `tehsil` varchar(100) DEFAULT NULL,
        `district` varchar(100) DEFAULT NULL,
        `state` varchar(100) DEFAULT NULL,
        `area_acres` decimal(10,3) DEFAULT 0.000,
        `area_sqft` decimal(12,2) DEFAULT 0.000,
        `owner_name` varchar(255) DEFAULT NULL,
        `owner_father_name` varchar(255) DEFAULT NULL,
        `mutation_status` enum('not_mutated','mutated','pending') DEFAULT 'not_mutated',
        `mutation_date` date DEFAULT NULL,
        `land_type` enum('agricultural','residential','commercial','mixed') DEFAULT 'agricultural',
        `soil_type` varchar(50) DEFAULT NULL,
        `irrigation_type` enum('irrigated','rainfed') DEFAULT 'rainfed',
        `encumbrance` tinyint(1) DEFAULT 0,
        `encumbrance_details` text DEFAULT NULL,
        `court_case` tinyint(1) DEFAULT 0,
        `court_case_details` text DEFAULT NULL,
        `govt_acquisition` tinyint(1) DEFAULT 0,
        `govt_acquisition_details` text DEFAULT NULL,
        `remarks` text DEFAULT NULL,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `gata_no` (`gata_no`),
        KEY `colony_id` (`colony_id`),
        KEY `khasra_no` (`khasra_no`),
        KEY `khata_no` (`khata_no`),
        KEY `mutation_status` (`mutation_status`),
        KEY `land_type` (`land_type`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");
$created++;

// ─────────────────────────────────────────────────────────
// Seed rera_projects with sample data
// ─────────────────────────────────────────────────────────
echo "\n━━━ Seeding RERA Projects ━━━\n";

$reraProjects = [
    ['project_name' => 'Suryoday Colony RERA', 'rera_number' => 'UP-RERA-2026-001', 'promoter_name' => 'APS Dream Homes', 'state' => 'Uttar Pradesh', 'district' => 'Gorakhpur', 'total_plots' => 500, 'total_area_sqft' => 500000, 'status' => 'registered'],
    ['project_name' => 'Raghunath Nagri RERA', 'rera_number' => 'UP-RERA-2026-002', 'promoter_name' => 'APS Dream Homes', 'state' => 'Uttar Pradesh', 'district' => 'Gorakhpur', 'total_plots' => 400, 'total_area_sqft' => 400000, 'status' => 'registered'],
    ['project_name' => 'Braj Radha Nagari RERA', 'rera_number' => 'UP-RERA-2026-003', 'promoter_name' => 'APS Dream Homes', 'state' => 'Uttar Pradesh', 'district' => 'Gorakhpur', 'total_plots' => 300, 'total_area_sqft' => 300000, 'status' => 'registered'],
];

$stmt = $pdo->prepare("INSERT IGNORE INTO rera_projects (project_name, rera_number, promoter_name, state, district, total_plots, total_area_sqft, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
foreach ($reraProjects as $p) {
    $stmt->execute([$p['project_name'], $p['rera_number'], $p['promoter_name'], $p['state'], $p['district'], $p['total_plots'], $p['total_area_sqft'], $p['status']]);
}
echo "✅ Seeded " . count($reraProjects) . " RERA projects\n";

// ─────────────────────────────────────────────────────────
// Seed gata_master with sample data
// ─────────────────────────────────────────────────────────
echo "\n━━━ Seeding Gata Master ━━━\n";

$gatas = [];
for ($i = 1; $i <= 50; $i++) {
    $gatas[] = [
        'gata_no' => 'GATA-' . str_pad($i, 4, '0', STR_PAD_LEFT),
        'khasra_no' => 'KH-' . str_pad($i, 4, '0', STR_PAD_LEFT),
        'khata_no' => 'KHTA-' . str_pad($i, 4, '0', STR_PAD_LEFT),
        'colony_id' => ($i % 7) + 1,
        'village' => 'Gorakhpur',
        'tehsil' => 'Gorakhpur',
        'district' => 'Gorakhpur',
        'state' => 'Uttar Pradesh',
        'area_acres' => round(0.5 + ($i * 0.1), 3),
        'area_sqft' => round((0.5 + ($i * 0.1)) * 43560, 2),
        'owner_name' => 'Owner ' . $i,
        'owner_father_name' => 'Father ' . $i,
        'mutation_status' => ['not_mutated', 'mutated', 'pending'][array_rand(['not_mutated', 'mutated', 'pending'])],
        'land_type' => ['agricultural', 'residential', 'commercial'][array_rand(['agricultural', 'residential', 'commercial'])],
        'soil_type' => 'loamy',
        'irrigation_type' => 'irrigated',
        'encumbrance' => 0,
        'court_case' => 0,
        'govt_acquisition' => 0,
        'remarks' => 'Sample gata record ' . $i,
    ];
}

$stmt = $pdo->prepare("INSERT IGNORE INTO gata_master (gata_no, khasra_no, khata_no, colony_id, village, tehsil, district, state, area_acres, area_sqft, owner_name, owner_father_name, mutation_status, land_type, soil_type, irrigation_type, encumbrance, court_case, govt_acquisition, remarks) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
foreach ($gatas as $g) {
    $stmt->execute([$g['gata_no'], $g['khasra_no'], $g['khata_no'], $g['colony_id'], $g['village'], $g['tehsil'], $g['district'], $g['state'], $g['area_acres'], $g['area_sqft'], $g['owner_name'], $g['owner_father_name'], $g['mutation_status'], $g['land_type'], $g['soil_type'], $g['irrigation_type'], $g['encumbrance'], $g['court_case'], $g['govt_acquisition'], $g['remarks']]);
}
echo "✅ Seeded " . count($gatas) . " gata records\n";

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

echo "\n✅ Remaining tables migration complete!\n";
