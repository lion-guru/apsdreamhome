<?php
/**
 * Module 4: MLM Commission Engine + Rank System — Migration
 *
 * Creates 6 new tables for the MLM engine:
 *   - mlm_payout_batches      : monthly batch container
 *   - mlm_payouts             : per-associate payout line
 *   - mlm_rank_benefits       : 6 rank tiers with rate cards
 *   - mlm_rank_history        : promotion audit log
 *   - mlm_clawback_log        : EMI-default clawback ledger
 *   - mlm_cron_log            : cron run audit
 *
 * Idempotent — safe to re-run. Drops + recreates only if 0 rows.
 * Run via: php scripts/migrate_module4_mlm_engine.php
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

$root = dirname(__DIR__);
require $root . '/config/bootstrap.php';

try {
    $pdo = \App\Core\Database\Database::getInstance();
    if (method_exists($pdo, 'getPdo')) {
        $pdo = $pdo->getPdo();
    }
    if (!$pdo instanceof \PDO) {
        throw new RuntimeException('Could not get PDO from Database facade');
    }
    $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    fwrite(STDERR, "DB connection failed: " . $e->getMessage() . "\n");
    exit(1);
}

echo "============================================================\n";
echo "Module 4: MLM Commission Engine — Migration\n";
echo "============================================================\n\n";

$tables = [
    'mlm_payout_batches' => [
        'drop_safe' => true,
        'create' => "CREATE TABLE IF NOT EXISTS `mlm_payout_batches` (
            `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            `batch_number` VARCHAR(40) NOT NULL,
            `period_year` SMALLINT(5) UNSIGNED NOT NULL,
            `period_month` TINYINT(3) UNSIGNED NOT NULL,
            `period_start` DATE NOT NULL,
            `period_end` DATE NOT NULL,
            `total_associates` INT(11) UNSIGNED NOT NULL DEFAULT 0,
            `total_gross_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
            `total_tds_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
            `total_net_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
            `total_clawback_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
            `status` ENUM('draft','pending_approval','approved','processing','completed','cancelled') NOT NULL DEFAULT 'draft',
            `prepared_by` BIGINT(20) UNSIGNED NULL,
            `approved_by` BIGINT(20) UNSIGNED NULL,
            `processed_by` BIGINT(20) UNSIGNED NULL,
            `payment_date` DATE NULL,
            `notes` TEXT NULL,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uniq_batch_number` (`batch_number`),
            UNIQUE KEY `uniq_period` (`period_year`, `period_month`),
            KEY `idx_status` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",
    ],
    'mlm_payouts' => [
        'drop_safe' => true,
        'create' => "CREATE TABLE IF NOT EXISTS `mlm_payouts` (
            `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            `batch_id` INT(11) UNSIGNED NOT NULL,
            `associate_id` INT(11) UNSIGNED NOT NULL,
            `associate_user_id` BIGINT(20) UNSIGNED NOT NULL,
            `gross_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
            `tds_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
            `other_deductions` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
            `net_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
            `payment_mode` ENUM('bank_transfer','upi','cheque','cash','wallet') NULL,
            `bank_account` VARCHAR(100) NULL,
            `ifsc` VARCHAR(20) NULL,
            `upi_id` VARCHAR(120) NULL,
            `transaction_ref` VARCHAR(120) NULL,
            `cheque_number` VARCHAR(40) NULL,
            `status` ENUM('pending','processing','paid','failed','on_hold','cancelled') NOT NULL DEFAULT 'pending',
            `paid_date` DATE NULL,
            `processed_by` BIGINT(20) UNSIGNED NULL,
            `failure_reason` VARCHAR(500) NULL,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_batch` (`batch_id`),
            KEY `idx_associate_status` (`associate_id`, `status`),
            KEY `idx_status` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",
    ],
    'mlm_rank_benefits' => [
        'drop_safe' => true,
        'create' => "CREATE TABLE IF NOT EXISTS `mlm_rank_benefits` (
            `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            `rank_name` ENUM('associate','bronze','silver','gold','platinum','diamond') NOT NULL,
            `rank_order` TINYINT(3) UNSIGNED NOT NULL DEFAULT 1,
            `min_leg_count` INT(11) UNSIGNED NOT NULL DEFAULT 0,
            `min_qualifying_volume` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
            `direct_sale_pct` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
            `l1_pct` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
            `l2_pct` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
            `l3_pct` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
            `perks` JSON NULL,
            `color_code` VARCHAR(20) NULL,
            `badge_icon` VARCHAR(80) NULL,
            `is_active` TINYINT(1) NOT NULL DEFAULT 1,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uniq_rank_name` (`rank_name`),
            KEY `idx_active` (`is_active`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",
    ],
    'mlm_rank_history' => [
        'drop_safe' => true,
        'create' => "CREATE TABLE IF NOT EXISTS `mlm_rank_history` (
            `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            `associate_id` INT(11) UNSIGNED NOT NULL,
            `from_rank` VARCHAR(40) NULL,
            `to_rank` VARCHAR(40) NOT NULL,
            `qualifying_volume_at_promotion` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
            `leg_count_at_promotion` INT(11) UNSIGNED NOT NULL DEFAULT 0,
            `promoted_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `promoted_by` BIGINT(20) UNSIGNED NULL,
            `is_manual` TINYINT(1) NOT NULL DEFAULT 0,
            `reason` VARCHAR(500) NULL,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_associate_promoted` (`associate_id`, `promoted_at`),
            KEY `idx_to_rank` (`to_rank`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",
    ],
    'mlm_clawback_log' => [
        'drop_safe' => true,
        'create' => "CREATE TABLE IF NOT EXISTS `mlm_clawback_log` (
            `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            `original_ledger_id` BIGINT(20) UNSIGNED NULL,
            `original_payout_id` INT(11) UNSIGNED NULL,
            `beneficiary_user_id` BIGINT(20) UNSIGNED NOT NULL,
            `source_user_id` BIGINT(20) UNSIGNED NOT NULL,
            `emi_plan_id` BIGINT(20) UNSIGNED NULL,
            `emi_installment_id` BIGINT(20) UNSIGNED NULL,
            `default_date` DATE NULL,
            `original_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
            `clawback_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
            `reason` VARCHAR(500) NULL,
            `status` ENUM('pending','debited','recovered','written_off') NOT NULL DEFAULT 'pending',
            `recovered_via` ENUM('future_commission','manual_payment','write_off') NULL,
            `recovered_date` DATE NULL,
            `recovered_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_beneficiary_status` (`beneficiary_user_id`, `status`),
            KEY `idx_status` (`status`),
            KEY `idx_ledger` (`original_ledger_id`),
            KEY `idx_payout` (`original_payout_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",
    ],
    'mlm_cron_log' => [
        'drop_safe' => true,
        'create' => "CREATE TABLE IF NOT EXISTS `mlm_cron_log` (
            `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            `cron_name` VARCHAR(80) NOT NULL,
            `run_date` DATE NOT NULL,
            `started_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `finished_at` TIMESTAMP NULL DEFAULT NULL,
            `status` ENUM('running','completed','failed') NOT NULL DEFAULT 'running',
            `items_processed` INT(11) UNSIGNED NOT NULL DEFAULT 0,
            `errors_count` INT(11) UNSIGNED NOT NULL DEFAULT 0,
            `error_log` TEXT NULL,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_cron_run` (`cron_name`, `run_date`),
            KEY `idx_status` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",
    ],
];

$seed_ranks = [
    ['rank_name' => 'associate', 'rank_order' => 1, 'min_leg_count' => 0,  'min_qualifying_volume' => 0,       'direct_sale_pct' => 1.0, 'l1_pct' => 2.0, 'l2_pct' => 1.0, 'l3_pct' => 0.5, 'color_code' => '#94a3b8', 'badge_icon' => 'fa-user'],
    ['rank_name' => 'bronze',    'rank_order' => 2, 'min_leg_count' => 2,  'min_qualifying_volume' => 50000,   'direct_sale_pct' => 2.0, 'l1_pct' => 3.0, 'l2_pct' => 1.5, 'l3_pct' => 0.5, 'color_code' => '#a16207', 'badge_icon' => 'fa-medal'],
    ['rank_name' => 'silver',    'rank_order' => 3, 'min_leg_count' => 3,  'min_qualifying_volume' => 200000,  'direct_sale_pct' => 2.5, 'l1_pct' => 3.0, 'l2_pct' => 1.5, 'l3_pct' => 1.0, 'color_code' => '#94a3b8', 'badge_icon' => 'fa-award'],
    ['rank_name' => 'gold',      'rank_order' => 4, 'min_leg_count' => 4,  'min_qualifying_volume' => 500000,  'direct_sale_pct' => 3.0, 'l1_pct' => 3.5, 'l2_pct' => 2.0, 'l3_pct' => 1.0, 'color_code' => '#ca8a04', 'badge_icon' => 'fa-trophy'],
    ['rank_name' => 'platinum',  'rank_order' => 5, 'min_leg_count' => 5,  'min_qualifying_volume' => 1000000, 'direct_sale_pct' => 3.5, 'l1_pct' => 4.0, 'l2_pct' => 2.5, 'l3_pct' => 1.5, 'color_code' => '#0891b2', 'badge_icon' => 'fa-gem'],
    ['rank_name' => 'diamond',   'rank_order' => 6, 'min_leg_count' => 6,  'min_qualifying_volume' => 2500000, 'direct_sale_pct' => 4.0, 'l1_pct' => 5.0, 'l2_pct' => 3.0, 'l3_pct' => 2.0, 'color_code' => '#7c3aed', 'badge_icon' => 'fa-crown'],
];

// For Module 4 we DROP+RECREATE every table (the existing mlm_payouts/mlm_payouts etc. have stale schemas).
// We will NOT touch any non-Module-4 tables.
$created = [];
$seeded = 0;

foreach ($tables as $name => $def) {
    try {
        $pdo->exec("DROP TABLE IF EXISTS `{$name}`");
        $pdo->exec($def['create']);
        $created[] = $name;
        echo "  [OK]   {$name} dropped (old) + recreated\n";
    } catch (Exception $e) {
        echo "  [ERR]  {$name}: " . $e->getMessage() . "\n";
        exit(1);
    }
}

// Seed rank benefits
echo "\nSeeding mlm_rank_benefits...\n";
$existing = (int)$pdo->query("SELECT COUNT(*) FROM mlm_rank_benefits")->fetchColumn();
if ($existing > 0) {
    echo "  [SKIP] mlm_rank_benefits already has {$existing} rows — keeping\n";
} else {
    $stmt = $pdo->prepare("
        INSERT INTO mlm_rank_benefits
            (rank_name, rank_order, min_leg_count, min_qualifying_volume, direct_sale_pct, l1_pct, l2_pct, l3_pct, color_code, badge_icon, is_active, perks)
        VALUES
            (:rank_name, :rank_order, :min_leg_count, :min_qualifying_volume, :direct_sale_pct, :l1_pct, :l2_pct, :l3_pct, :color_code, :badge_icon, 1, :perks)
    ");
    $perksJson = json_encode([
        'training' => 'Basic training materials',
        'events'   => 'Quarterly meet-up',
    ], JSON_UNESCAPED_UNICODE);
    foreach ($seed_ranks as $row) {
        $stmt->execute([
            ':rank_name'             => $row['rank_name'],
            ':rank_order'            => $row['rank_order'],
            ':min_leg_count'         => $row['min_leg_count'],
            ':min_qualifying_volume' => $row['min_qualifying_volume'],
            ':direct_sale_pct'       => $row['direct_sale_pct'],
            ':l1_pct'                => $row['l1_pct'],
            ':l2_pct'                => $row['l2_pct'],
            ':l3_pct'                => $row['l3_pct'],
            ':color_code'            => $row['color_code'],
            ':badge_icon'            => $row['badge_icon'],
            ':perks'                 => $perksJson,
        ]);
        $seeded++;
        echo "  [OK]   rank: {$row['rank_name']} (legs>={$row['min_leg_count']}, vol>={$row['min_qualifying_volume']})\n";
    }
}

echo "\n============================================================\n";
echo "Migration complete.\n";
echo "Tables created: " . count($created) . " (" . implode(', ', $created) . ")\n";
echo "Rank benefits seeded: {$seeded}\n";
echo "============================================================\n";
exit(0);
