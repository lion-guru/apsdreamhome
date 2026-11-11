<?php
/**
 * Migration: Create Test Suite Tables — Session 390
 * Phase 4/7: Identity Unification — DB Rebuild Recovery
 *
 * Creates remaining tables needed for test suite:
 *   1. lease_rent_invoices
 *   2. farmer_land_acquisitions
 *   3. farmer_compensation_milestones
 *   4. user_bank_accounts
 *
 * Fixes collation on gata_master and rera_projects.
 *
 * Idempotent: Uses CREATE TABLE IF NOT EXISTS and ALTER TABLE for collation.
 *
 * Run via: php database/migrations/create_test_suite_tables_s390.php
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

echo "━━━ Test Suite Tables ━━━\n\n";

// 1. lease_rent_invoices
createTable($pdo, 'lease_rent_invoices', "
    CREATE TABLE `lease_rent_invoices` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `lease_id` int(11) DEFAULT NULL,
        `invoice_no` varchar(100) DEFAULT NULL,
        `billing_month` varchar(20) DEFAULT NULL,
        `base_rent` decimal(12,2) DEFAULT 0.00,
        `electricity_units` int(11) DEFAULT 0,
        `electricity_rate` decimal(10,2) DEFAULT 0.00,
        `electricity_amount` decimal(12,2) DEFAULT 0.00,
        `maintenance_fee` decimal(12,2) DEFAULT 0.00,
        `total_demand` decimal(12,2) DEFAULT 0.00,
        `paid_amount` decimal(12,2) DEFAULT 0.00,
        `payment_mode` varchar(50) DEFAULT NULL,
        `utr_number` varchar(100) DEFAULT NULL,
        `status` enum('unpaid','partially_paid','paid','overdue') DEFAULT 'unpaid',
        `due_date` date DEFAULT NULL,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `lease_id` (`lease_id`),
        KEY `invoice_no` (`invoice_no`),
        KEY `status` (`status`),
        KEY `billing_month` (`billing_month`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");
$created++;

// 2. farmer_land_acquisitions
createTable($pdo, 'farmer_land_acquisitions', "
    CREATE TABLE `farmer_land_acquisitions` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `deal_token` varchar(50) DEFAULT NULL,
        `farmer_name` varchar(255) DEFAULT NULL,
        `farmer_phone` varchar(20) DEFAULT NULL,
        `farmer_aadhaar` varchar(20) DEFAULT NULL,
        `farmer_pan` varchar(20) DEFAULT NULL,
        `farmer_bank_account` varchar(50) DEFAULT NULL,
        `mauza_village` varchar(255) DEFAULT NULL,
        `tehsil` varchar(100) DEFAULT NULL,
        `district` varchar(100) DEFAULT NULL DEFAULT 'Gorakhpur',
        `gata_khasra` varchar(100) DEFAULT NULL,
        `fasli_year` int(11) DEFAULT NULL,
        `khatauni_clearance` tinyint(1) DEFAULT 0,
        `area_pucca_bigha` decimal(10,2) DEFAULT 0.00,
        `area_sqft` decimal(12,2) DEFAULT 0.00,
        `rate_per_bigha` decimal(12,2) DEFAULT 0.00,
        `total_consideration` decimal(15,2) DEFAULT 0.00,
        `paid_amount` decimal(15,2) DEFAULT 0.00,
        `status` enum('bayana_agreement','mutation_in_progress','possession_taken','registry_completed','disputed','cancelled') DEFAULT 'bayana_agreement',
        `agreement_date` date DEFAULT NULL,
        `target_registry_date` date DEFAULT NULL,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `deal_token` (`deal_token`),
        KEY `status` (`status`),
        KEY `mauza_village` (`mauza_village`),
        KEY `gata_khasra` (`gata_khasra`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");
$created++;

// 3. farmer_compensation_milestones
createTable($pdo, 'farmer_compensation_milestones', "
    CREATE TABLE `farmer_compensation_milestones` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `acquisition_id` int(11) DEFAULT NULL,
        `stage` enum('bayana_advance','mutation','possession','registry') DEFAULT 'bayana_advance',
        `stage_percentage` decimal(5,2) DEFAULT 0.00,
        `amount` decimal(15,2) DEFAULT 0.00,
        `payment_mode` varchar(50) DEFAULT NULL,
        `bank_utr` varchar(100) DEFAULT NULL,
        `tds_194la` decimal(10,2) DEFAULT 0.00,
        `voucher_token` varchar(100) DEFAULT NULL,
        `status` enum('pending','paid','failed') DEFAULT 'pending',
        `paid_date` date DEFAULT NULL,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `acquisition_id` (`acquisition_id`),
        KEY `stage` (`stage`),
        KEY `status` (`status`),
        KEY `voucher_token` (`voucher_token`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");
$created++;

// 4. user_bank_accounts
createTable($pdo, 'user_bank_accounts', "
    CREATE TABLE `user_bank_accounts` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `user_id` int(11) DEFAULT NULL,
        `account_type` enum('savings','current') DEFAULT 'savings',
        `bank_name` varchar(100) DEFAULT NULL,
        `account_number` varchar(50) DEFAULT NULL,
        `ifsc_code` varchar(20) DEFAULT NULL,
        `account_holder_name` varchar(255) DEFAULT NULL,
        `is_primary` tinyint(1) DEFAULT 0,
        `is_verified` tinyint(1) DEFAULT 0,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `user_id` (`user_id`),
        KEY `account_number` (`account_number`),
        KEY `is_primary` (`is_primary`),
        KEY `is_verified` (`is_verified`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");
$created++;

// ─────────────────────────────────────────────────────────
// Fix collation on gata_master and rera_projects
// ─────────────────────────────────────────────────────────
echo "\n━━━ Fixing Collation ━━━\n";

$tablesToFix = [
    'gata_master' => ['gata_no'],
    'rera_projects' => ['rera_number'],
];

foreach ($tablesToFix as $table => $columns) {
    foreach ($columns as $column) {
        try {
            $pdo->exec("ALTER TABLE `$table` MODIFY COLUMN `$column` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
            echo "✅ Fixed collation: $table.$column\n";
        } catch (\Throwable $e) {
            echo "❌ ERROR fixing collation $table.$column: " . $e->getMessage() . "\n";
            $errors++;
        }
    }
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

echo "\n✅ Test suite tables migration complete!\n";
