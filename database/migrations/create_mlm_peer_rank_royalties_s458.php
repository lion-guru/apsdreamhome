<?php
/**
 * Migration S458: Peer Rank Mentorship Royalties Table
 * 
 * Creates mlm_peer_rank_royalties table to track 1% mentorship royalty
 * paid to seniors when their downline junior reaches the same rank.
 * 
 * 100% Idempotent. Re-run skips safely.
 */

require_once __DIR__ . '/../../config/bootstrap.php';

try {
    $pdo = \App\Core\Database\Database::getInstance()->getPdo();
} catch (\Throwable $e) {
    die("DB connection failed: " . $e->getMessage() . "\n");
}

echo "=== MIGRATION S458: mlm_peer_rank_royalties Table ===\n";

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

if (!tableExists($pdo, 'mlm_peer_rank_royalties')) {
    $pdo->exec("
        CREATE TABLE `mlm_peer_rank_royalties` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1,
            `senior_id` BIGINT UNSIGNED NOT NULL COMMENT 'The senior who earns the royalty',
            `junior_id` BIGINT UNSIGNED NOT NULL COMMENT 'The junior who reached the same rank',
            `junior_rank` VARCHAR(50) NOT NULL COMMENT 'The rank both senior and junior share',
            `junior_gbv` DECIMAL(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Junior team volume at time of royalty',
            `royalty_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT '1% of junior GBV',
            `month_year` CHAR(7) NOT NULL COMMENT 'Format: YYYY-MM',
            `status` ENUM('paid','cancelled','refunded') NOT NULL DEFAULT 'paid',
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `paid_at` TIMESTAMP NULL,
            INDEX idx_senior_month (senior_id, month_year),
            INDEX idx_junior (junior_id),
            INDEX idx_month (month_year),
            INDEX idx_tenant (tenant_id),
            UNIQUE KEY uq_senior_junior_month (senior_id, junior_id, month_year)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "  [+] Created table mlm_peer_rank_royalties\n";
} else {
    echo "  [*] Table mlm_peer_rank_royalties already exists (skipped)\n";
}

echo "=== MIGRATION S458 COMPLETE: Success ===\n";
