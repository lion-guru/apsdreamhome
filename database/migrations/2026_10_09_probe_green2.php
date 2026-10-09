<?php
/**
 * Probe-Green Migration Part 2 (2026-10-09)
 * Fixes the 3 remaining smoke 500s:
 *   - /associate/genealogy  (users.customer_id + users.referred_by + network_tree)
 *   - /associate/wallet     (wallet_points.tenant_id + ledger.beneficiary_user_id + network_tree)
 *   - /admin/payout-batches (payout_batches table)
 *
 * Idempotent: safe to re-run.
 */

$pdo = new PDO('mysql:host=localhost;dbname=apsdreamhome;charset=utf8mb4', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

function run2(PDO $pdo, string $label, string $sql): void {
    try {
        $pdo->exec($sql);
        echo "OK   $label\n";
    } catch (Throwable $e) {
        echo "SKIP $label -- " . substr($e->getMessage(), 0, 120) . "\n";
    }
}

// ── 1. users: MLM identity columns (MLMTreeController::genealogy/getUpline) ──
run2($pdo, 'users.customer_id', "ALTER TABLE `users` ADD COLUMN IF NOT EXISTS `customer_id` VARCHAR(50) NULL AFTER `referral_code`");
run2($pdo, 'users.referred_by', "ALTER TABLE `users` ADD COLUMN IF NOT EXISTS `referred_by` INT NULL AFTER `customer_id`");

// ── 2. wallet_points.tenant_id (WalletController::associateWallet insert) ──
run2($pdo, 'wallet_points.tenant_id', "ALTER TABLE `wallet_points` ADD COLUMN IF NOT EXISTS `tenant_id` INT NOT NULL DEFAULT 1 AFTER `user_id`");

// ── 3. mlm_commission_ledger: canonical beneficiary columns ──
// Canonical writers (MLMCommissionEngine, ReferralService, RERAComplianceService…)
// use beneficiary_user_id / source_user_id / commission_type / level /
// sale_amount / commission_percentage / notes / booking_id.
run2($pdo, 'ledger.beneficiary_user_id', "ALTER TABLE `mlm_commission_ledger` ADD COLUMN IF NOT EXISTS `beneficiary_user_id` INT NULL AFTER `id`");
run2($pdo, 'ledger.source_user_id', "ALTER TABLE `mlm_commission_ledger` ADD COLUMN IF NOT EXISTS `source_user_id` INT NULL AFTER `beneficiary_user_id`");
run2($pdo, 'ledger.commission_type', "ALTER TABLE `mlm_commission_ledger` ADD COLUMN IF NOT EXISTS `commission_type` VARCHAR(50) NOT NULL DEFAULT 'direct_sale' AFTER `source_user_id`");
run2($pdo, 'ledger.level', "ALTER TABLE `mlm_commission_ledger` ADD COLUMN IF NOT EXISTS `level` INT NOT NULL DEFAULT 1 AFTER `commission_type`");
run2($pdo, 'ledger.sale_amount', "ALTER TABLE `mlm_commission_ledger` ADD COLUMN IF NOT EXISTS `sale_amount` DECIMAL(15,2) NOT NULL DEFAULT 0 AFTER `amount`");
run2($pdo, 'ledger.commission_percentage', "ALTER TABLE `mlm_commission_ledger` ADD COLUMN IF NOT EXISTS `commission_percentage` DECIMAL(5,2) NOT NULL DEFAULT 0 AFTER `sale_amount`");
run2($pdo, 'ledger.notes', "ALTER TABLE `mlm_commission_ledger` ADD COLUMN IF NOT EXISTS `notes` TEXT NULL AFTER `status`");
run2($pdo, 'ledger.booking_id', "ALTER TABLE `mlm_commission_ledger` ADD COLUMN IF NOT EXISTS `booking_id` INT NULL AFTER `notes`");
// Backfill the part-1 seed row so beneficiary queries see it.
run2($pdo, 'ledger backfill', "UPDATE `mlm_commission_ledger` SET `beneficiary_user_id` = `associate_id`, `commission_type` = 'direct_sale', `sale_amount` = 100000, `commission_percentage` = 5 WHERE `beneficiary_user_id` IS NULL");

// ── 4. network_tree (MLMTreeController fallback branch + WalletController stats) ──
run2($pdo, 'network_tree', "CREATE TABLE IF NOT EXISTS `network_tree` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `associate_id` INT NOT NULL,
  `parent_id` INT NULL,
  `level` INT NOT NULL DEFAULT 1,
  `position` VARCHAR(10) NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uq_nt_associate` (`associate_id`),
  KEY `idx_nt_parent` (`parent_id`),
  KEY `idx_nt_level` (`level`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// ── 5. payout_batches (PayoutBatchService::getStats/getBatches) ──
run2($pdo, 'payout_batches', "CREATE TABLE IF NOT EXISTS `payout_batches` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `tenant_id` INT NOT NULL DEFAULT 1,
  `batch_name` VARCHAR(255) NOT NULL,
  `batch_type` VARCHAR(50) NOT NULL DEFAULT 'commission',
  `period_from` DATE NULL,
  `period_to` DATE NULL,
  `notes` TEXT NULL,
  `status` VARCHAR(50) NOT NULL DEFAULT 'draft',
  `total_amount` DECIMAL(15,2) NOT NULL DEFAULT 0,
  `created_by` INT NULL,
  `approved_by` INT NULL,
  `bank_export_file` VARCHAR(500) NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `idx_pb_status` (`status`),
  KEY `idx_pb_tenant` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

echo "DONE probe-green part 2\n";
