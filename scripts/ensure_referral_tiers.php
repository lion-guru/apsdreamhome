<?php
/**
 * Ensure referral_tiers table + seed current 4 tiers (matches getTiers fallback).
 * Idempotent. Run: php scripts/ensure_referral_tiers.php
 */
require_once __DIR__ . '/../config/bootstrap.php';

try {
    $db = App\Core\Database\Database::getInstance()->getConnection();
    $db->exec("
    CREATE TABLE IF NOT EXISTS `referral_tiers` (
        `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1,
        `tier_key` VARCHAR(20) NOT NULL,
        `label` VARCHAR(50) NOT NULL,
        `min_referrals` INT NOT NULL DEFAULT 0,
        `bonus_per_referral` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        `bonus_on_booking` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
        `color` VARCHAR(20) NOT NULL DEFAULT '#94a3b8',
        `icon` VARCHAR(80) NOT NULL DEFAULT 'fas fa-medal',
        `perks` JSON NULL,
        `sort_order` INT NOT NULL DEFAULT 0,
        `is_active` TINYINT(1) NOT NULL DEFAULT 1,
        `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `uq_tier_key` (`tier_key`),
        INDEX `idx_active` (`is_active`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    $seed = [
        ['bronze', 'Bronze', 0, 100, 500, '#CD7F32', 'fas fa-medal', ['Rs.100 per signup', 'Rs.500 on booking', 'Basic referral badge'], 10],
        ['silver', 'Silver', 5, 200, 1000, '#94a3b8', 'fas fa-medal', ['Rs.200 per signup', 'Rs.1,000 on booking', 'Silver badge', 'Priority support'], 20],
        ['gold', 'Gold', 15, 500, 2500, '#f59e0b', 'fas fa-crown', ['Rs.500 per signup', 'Rs.2,500 on booking', 'Gold badge', 'Priority support', 'Exclusive offers'], 30],
        ['platinum', 'Platinum', 30, 1000, 5000, '#6366f1', 'fas fa-gem', ['Rs.1,000 per signup', 'Rs.5,000 on booking', 'Platinum badge', 'Dedicated manager', 'VIP events'], 40],
    ];
    foreach ($seed as [$key, $label, $min, $perRef, $onBook, $color, $icon, $perks, $sort]) {
        $exists = $db->prepare("SELECT id FROM referral_tiers WHERE tier_key = ? LIMIT 1");
        $exists->execute([$key]);
        if (!$exists->fetch()) {
            $ins = $db->prepare("INSERT INTO referral_tiers (tier_key, label, min_referrals, bonus_per_referral, bonus_on_booking, color, icon, perks, sort_order, tenant_id) VALUES (?,?,?,?,?,?,?,?,?,1)");
            $ins->execute([$key, $label, $min, $perRef, $onBook, $color, $icon, json_encode($perks), $sort]);
            echo "seeded $key\n";
        } else {
            echo "kept $key\n";
        }
    }
    echo "OK\n";
} catch (Throwable $e) {
    echo 'FAIL: ' . $e->getMessage() . "\n";
    exit(1);
}
