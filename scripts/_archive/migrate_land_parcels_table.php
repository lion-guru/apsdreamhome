<?php
/**
 * Migration: Create `land_parcels` table for geographic land records
 *
 * Indian land records are based on Khasra/Khata/Khatauni numbers.
 * This table provides a unified view of land parcels across colonies,
 * linking to the existing farmer_land_holdings khasra data.
 *
 * Run:  php scripts/migrate_land_parcels_table.php
 */

require_once __DIR__ . '/../app/Core/ConfigService.php';
require_once __DIR__ . '/../app/Core/Database/Database.php';

App\Core\ConfigService::getInstance();
$db = App\Core\Database\Database::getInstance();

echo "================================================================\n";
echo " Migration: land_parcels table\n";
echo "================================================================\n\n";

function tableExists($db, $name) {
    try {
        $row = $db->fetch(
            "SELECT COUNT(*) AS cnt FROM information_schema.TABLES
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?",
            [$name]
        );
        return !empty($row) && (int)($row['cnt'] ?? 0) > 0;
    } catch (\Throwable $e) {
        return false;
    }
}

// ============================================================
// land_parcels — geographic land records with Khasra/Khata
// ============================================================
if (tableExists($db, 'land_parcels')) {
    echo "[=] land_parcels already exists, skipping.\n";
} else {
    $sql = "CREATE TABLE `land_parcels` (
      `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
      `colony_id` BIGINT(20) UNSIGNED DEFAULT NULL COMMENT 'FK to colonies.id (if within a colony)',
      `khasra_no` VARCHAR(50) DEFAULT NULL COMMENT 'Khasra number (land survey identifier)',
      `khata_no` VARCHAR(50) DEFAULT NULL COMMENT 'Khata number (ownership ledger)',
      `khatauni_no` VARCHAR(50) DEFAULT NULL COMMENT 'Khatauni number (cultivation register)',
      `survey_number` VARCHAR(100) DEFAULT NULL COMMENT 'Revenue survey number',
      `village` VARCHAR(255) DEFAULT NULL,
      `tehsil` VARCHAR(255) DEFAULT NULL,
      `district` VARCHAR(255) DEFAULT NULL,
      `state` VARCHAR(255) DEFAULT NULL DEFAULT 'Uttar Pradesh',
      `pincode` VARCHAR(10) DEFAULT NULL,
      `area_acres` DECIMAL(10,2) DEFAULT NULL,
      `area_sqft` DECIMAL(12,2) DEFAULT NULL,
      `area_bigha` DECIMAL(10,2) DEFAULT NULL COMMENT '1 bigha = 26,667 sqft (UP standard)',
      `owner_name` VARCHAR(255) DEFAULT NULL,
      `owner_phone` VARCHAR(20) DEFAULT NULL,
      `owner_aadhaar` VARCHAR(20) DEFAULT NULL,
      `mutation_status` ENUM('pending','in_progress','completed','rejected') NOT NULL DEFAULT 'pending',
      `mutation_date` DATE DEFAULT NULL,
      `land_use` ENUM('residential','agricultural','commercial','industrial','mixed') NOT NULL DEFAULT 'residential',
      `gps_lat` DECIMAL(10,7) DEFAULT NULL,
      `gps_lng` DECIMAL(10,7) DEFAULT NULL,
      `notes` TEXT DEFAULT NULL,
      `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
      `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      KEY `idx_lp_colony` (`colony_id`),
      KEY `idx_lp_khasra` (`khasra_no`),
      KEY `idx_lp_khata` (`khata_no`),
      KEY `idx_lp_village` (`village`),
      KEY `idx_lp_district` (`district`),
      KEY `idx_lp_mutation` (`mutation_status`),
      KEY `idx_lp_land_use` (`land_use`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    try {
        $db->execute($sql);
        echo "[+] land_parcels created.\n";
    } catch (\Throwable $e) {
        echo "[!] land_parcels FAILED: " . $e->getMessage() . "\n";
        exit(1);
    }
}

// ============================================================
// FK constraint
// ============================================================
try {
    $db->execute("ALTER TABLE `land_parcels` ADD CONSTRAINT `fk_lp_colony` FOREIGN KEY (`colony_id`) REFERENCES `colonies`(`id`) ON DELETE SET NULL");
    echo "[+] FK to colonies added.\n";
} catch (\Throwable $e) {
    echo "[=] FK to colonies skipped: " . $e->getMessage() . "\n";
}

echo "\n================================================================\n";
echo " Done. land_parcels table is ready.\n";
echo "================================================================\n";
