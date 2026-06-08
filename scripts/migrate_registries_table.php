<?php
/**
 * Migration: Create `registries` table for property registration tracking
 *
 * Tracks property registration at sub-registrar offices — mandatory
 * for Indian real estate regulatory compliance.
 *
 * The existing RegistryController uses bookings columns (registry_status,
 * sub_registrar_office) for the booking flow. This table provides a
 * separate audit trail for the actual registration event at the
 * sub-registrar office.
 *
 * Run:  php scripts/migrate_registries_table.php
 */

require_once __DIR__ . '/../app/Core/ConfigService.php';
require_once __DIR__ . '/../app/Core/Database/Database.php';

App\Core\ConfigService::getInstance();
$db = App\Core\Database\Database::getInstance();

echo "================================================================\n";
echo " Migration: registries table\n";
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
// 1. registries — property registration audit trail
// ============================================================
if (tableExists($db, 'registries')) {
    echo "[=] registries already exists, skipping.\n";
} else {
    $sql = "CREATE TABLE `registries` (
      `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
      `booking_id` BIGINT(20) UNSIGNED NOT NULL COMMENT 'FK to plot_bookings.id',
      `plot_id` BIGINT(20) UNSIGNED NOT NULL COMMENT 'FK to plots.id',
      `user_id` BIGINT(20) UNSIGNED NOT NULL COMMENT 'FK to users.id (buyer)',
      `associate_id` BIGINT(20) UNSIGNED DEFAULT NULL COMMENT 'FK to users.id (associate who facilitated)',
      `registration_no` VARCHAR(100) DEFAULT NULL COMMENT 'Sub-registrar registration number',
      `sub_registrar_office` VARCHAR(255) NOT NULL COMMENT 'e.g. SRO Gorakhpur',
      `registration_date` DATE DEFAULT NULL COMMENT 'Date of registration at SRO',
      `stamp_duty_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Stamp duty paid',
      `registration_fee` DECIMAL(15,2) NOT NULL DEFAULT 0.00 COMMENT 'SRO registration fee',
      `other_charges` DECIMAL(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Nomination, typing, etc.',
      `total_registry_cost` DECIMAL(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Sum of all charges',
      `document_url` VARCHAR(500) DEFAULT NULL COMMENT 'Path to scanned deed document',
      `status` ENUM('pending','appointment_scheduled','documents_submitted','in_progress','completed','rejected','cancelled') NOT NULL DEFAULT 'pending',
      `rejection_reason` TEXT DEFAULT NULL,
      `notes` TEXT DEFAULT NULL,
      `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
      `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      UNIQUE KEY `uniq_reg_booking` (`booking_id`),
      KEY `idx_reg_plot` (`plot_id`),
      KEY `idx_reg_user` (`user_id`),
      KEY `idx_reg_associate` (`associate_id`),
      KEY `idx_reg_status` (`status`),
      KEY `idx_reg_date` (`registration_date`),
      KEY `idx_reg_sro` (`sub_registrar_office`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    try {
        $db->execute($sql);
        echo "[+] registries created.\n";
    } catch (\Throwable $e) {
        echo "[!] registries FAILED: " . $e->getMessage() . "\n";
        exit(1);
    }
}

// ============================================================
// 2. Add FK constraints (safe — skips if column missing)
// ============================================================
$fks = [
    'ALTER TABLE `registries` ADD CONSTRAINT `fk_reg_booking` FOREIGN KEY (`booking_id`) REFERENCES `plot_bookings`(`id`) ON DELETE CASCADE',
    'ALTER TABLE `registries` ADD CONSTRAINT `fk_reg_plot` FOREIGN KEY (`plot_id`) REFERENCES `plots`(`id`) ON DELETE CASCADE',
    'ALTER TABLE `registries` ADD CONSTRAINT `fk_reg_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE',
];

foreach ($fks as $i => $fk) {
    try {
        $db->execute($fk);
        echo "[+] FK constraint " . ($i + 1) . " added.\n";
    } catch (\Throwable $e) {
        // FK may already exist or referenced column may be missing
        echo "[=] FK constraint " . ($i + 1) . " skipped: " . $e->getMessage() . "\n";
    }
}

echo "\n================================================================\n";
echo " Done. registries table is ready.\n";
echo "================================================================\n";
