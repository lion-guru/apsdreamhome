<?php
/**
 * Migration S459: Career Rank Reward Fulfillment & Physical Handover System
 * 
 * 1. Enhances mlm_career_rewards with end-to-end claim, dispatch, serial tracking,
 *    office ceremony handover, and wallet cash conversion attributes.
 * 2. 100% Idempotent. Re-run skips safely.
 */

require_once __DIR__ . '/../../config/bootstrap.php';

try {
    $pdo = \App\Core\Database\Database::getInstance()->getPdo();
} catch (\Throwable $e) {
    die("DB connection failed: " . $e->getMessage() . "\n");
}

echo "=== MIGRATION S459: Career Rank Reward Fulfillment & Handover System ===\n";

function columnExists(PDO $pdo, string $table, string $column): bool {
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM information_schema.COLUMNS 
        WHERE TABLE_SCHEMA = DATABASE() 
          AND TABLE_NAME = ? 
          AND COLUMN_NAME = ?
    ");
    $stmt->execute([$table, $column]);
    return (int)$stmt->fetchColumn() > 0;
}

function tableExists(PDO $pdo, string $table): bool {
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM information_schema.TABLES 
        WHERE TABLE_SCHEMA = DATABASE() 
          AND TABLE_NAME = ?
    ");
    $stmt->execute([$table]);
    return (int)$stmt->fetchColumn() > 0;
}

if (!tableExists($pdo, 'mlm_career_rewards')) {
    $pdo->exec("
        CREATE TABLE `mlm_career_rewards` (
            `id` bigint(20) NOT NULL AUTO_INCREMENT,
            `tenant_id` int(10) unsigned NOT NULL DEFAULT 1,
            `user_id` bigint(20) unsigned NOT NULL,
            `rank_slug` varchar(30) NOT NULL,
            `reward_name` varchar(100) NOT NULL,
            `reward_value` decimal(10,2) NOT NULL DEFAULT 0.00,
            `gbv_at_award` decimal(15,2) NOT NULL DEFAULT 0.00,
            `status` enum('pending','awarded','cancelled') NOT NULL DEFAULT 'pending',
            `awarded_at` timestamp NULL DEFAULT NULL,
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_mcr_user` (`user_id`),
            KEY `idx_mcr_rank` (`rank_slug`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "  [+] Created table mlm_career_rewards\n";
}

$rewardCols = [
    'claim_status' => "ALTER TABLE mlm_career_rewards ADD COLUMN claim_status ENUM('eligible','claimed','approved','dispatched','delivered','converted_to_wallet','rejected') NOT NULL DEFAULT 'eligible' AFTER status",
    'delivery_type' => "ALTER TABLE mlm_career_rewards ADD COLUMN delivery_type ENUM('physical_handover','courier_dispatch','wallet_cash_equivalent','tour_voucher') NOT NULL DEFAULT 'physical_handover' AFTER claim_status",
    'shipping_address' => "ALTER TABLE mlm_career_rewards ADD COLUMN shipping_address TEXT NULL AFTER delivery_type",
    'contact_phone' => "ALTER TABLE mlm_career_rewards ADD COLUMN contact_phone VARCHAR(20) NULL AFTER shipping_address",
    'tracking_number' => "ALTER TABLE mlm_career_rewards ADD COLUMN tracking_number VARCHAR(100) NULL AFTER contact_phone",
    'courier_partner' => "ALTER TABLE mlm_career_rewards ADD COLUMN courier_partner VARCHAR(100) NULL AFTER tracking_number",
    'serial_number' => "ALTER TABLE mlm_career_rewards ADD COLUMN serial_number VARCHAR(100) NULL AFTER courier_partner",
    'handover_event' => "ALTER TABLE mlm_career_rewards ADD COLUMN handover_event VARCHAR(150) NULL AFTER serial_number",
    'handover_photo' => "ALTER TABLE mlm_career_rewards ADD COLUMN handover_photo VARCHAR(255) NULL AFTER handover_event",
    'admin_notes' => "ALTER TABLE mlm_career_rewards ADD COLUMN admin_notes TEXT NULL AFTER handover_photo",
    'claimed_at' => "ALTER TABLE mlm_career_rewards ADD COLUMN claimed_at DATETIME NULL AFTER admin_notes",
    'approved_at' => "ALTER TABLE mlm_career_rewards ADD COLUMN approved_at DATETIME NULL AFTER claimed_at",
    'dispatched_at' => "ALTER TABLE mlm_career_rewards ADD COLUMN dispatched_at DATETIME NULL AFTER approved_at",
    'delivered_at' => "ALTER TABLE mlm_career_rewards ADD COLUMN delivered_at DATETIME NULL AFTER dispatched_at",
    'cash_converted_amount' => "ALTER TABLE mlm_career_rewards ADD COLUMN cash_converted_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00 AFTER delivered_at",
    'approved_by' => "ALTER TABLE mlm_career_rewards ADD COLUMN approved_by BIGINT UNSIGNED NULL AFTER cash_converted_amount"
];

foreach ($rewardCols as $col => $sql) {
    if (!columnExists($pdo, 'mlm_career_rewards', $col)) {
        $pdo->exec($sql);
        echo "  [+] Added $col to mlm_career_rewards\n";
    } else {
        echo "  [.] Column $col already exists on mlm_career_rewards (SKIP)\n";
    }
}

// Backfill existing rows where status = 'awarded'
$pdo->exec("UPDATE mlm_career_rewards SET claim_status = 'delivered', delivered_at = COALESCE(awarded_at, created_at) WHERE status = 'awarded' AND claim_status = 'eligible'");

echo "=== MIGRATION S459 COMPLETE ===\n";

