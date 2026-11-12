<?php
/**
 * Migration: Add Dynamic Festival & Promotional Offers Architecture (Session 460)
 *
 * 1. Enhances `associate_offers` with festival categorization, physical rewards, and visit criteria.
 * 2. Creates `associate_offer_claims` table for associate festival claims, admin approval, and wallet disbursement.
 * 3. Enhances `promotional_offers` with multi-tier customer discounts (flat amount, percentage, per sqft, cashback),
 *    promo codes, colony scoping, and redemption limits.
 * 4. Enhances `plot_bookings` with discount tracking and promo code application.
 * 5. Idempotent seed data for festival campaigns.
 */

define('APS_ROOT', dirname(__DIR__, 2));
require_once APS_ROOT . '/config/bootstrap.php';

$pdo = \App\Core\Database\Database::getInstance()->getConnection();

echo "Running Migration: add_festival_promotional_offers_s460.php\n";

function columnExists(PDO $pdo, string $table, string $column): bool {
    $table = preg_replace('/[^a-zA-Z0-9_]/', '', $table);
    $column = preg_replace('/[^a-zA-Z0-9_]/', '', $column);
    $stmt = $pdo->query("SHOW COLUMNS FROM `$table` LIKE '$column'");
    return (bool)$stmt->fetch();
}

function tableExists(PDO $pdo, string $table): bool {
    $table = preg_replace('/[^a-zA-Z0-9_]/', '', $table);
    $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
    return (bool)$stmt->fetch();
}

// 1. Enhance associate_offers
$assocColumns = [
    'festival_name'    => "ALTER TABLE `associate_offers` ADD COLUMN `festival_name` VARCHAR(100) NULL AFTER `title`",
    'reward_item'      => "ALTER TABLE `associate_offers` ADD COLUMN `reward_item` VARCHAR(150) NULL AFTER `reward_type`",
    'reward_mode'      => "ALTER TABLE `associate_offers` ADD COLUMN `reward_mode` ENUM('wallet_cash', 'physical_gift', 'extra_commission_pct') DEFAULT 'wallet_cash' AFTER `reward_value`",
    'min_site_visits'  => "ALTER TABLE `associate_offers` ADD COLUMN `min_site_visits` INT DEFAULT 0 AFTER `criteria_value`",
    'banner_image'     => "ALTER TABLE `associate_offers` ADD COLUMN `banner_image` VARCHAR(255) NULL AFTER `ends_at`",
];

foreach ($assocColumns as $col => $sql) {
    if (!columnExists($pdo, 'associate_offers', $col)) {
        $pdo->exec($sql);
        echo "  [+] Added column `associate_offers`.`$col`\n";
    } else {
        echo "  [.] (SKIP) `associate_offers`.`$col` already exists\n";
    }
}

// 2. Create associate_offer_claims table
if (!tableExists($pdo, 'associate_offer_claims')) {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `associate_offer_claims` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `tenant_id` INT UNSIGNED DEFAULT 1,
            `offer_id` INT UNSIGNED NOT NULL,
            `user_id` BIGINT UNSIGNED NOT NULL,
            `qualification_metric` VARCHAR(50) NOT NULL,
            `achieved_value` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
            `target_value` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
            `claim_status` ENUM('eligible', 'claimed', 'approved', 'dispatched', 'delivered', 'wallet_credited', 'rejected') DEFAULT 'eligible',
            `claim_mode` ENUM('physical_gift', 'wallet_cash') DEFAULT 'wallet_cash',
            `cash_amount` DECIMAL(15,2) DEFAULT 0.00,
            `shipping_address` TEXT NULL,
            `contact_phone` VARCHAR(20) NULL,
            `admin_notes` TEXT NULL,
            `approved_by` BIGINT UNSIGNED NULL,
            `claimed_at` DATETIME NULL,
            `approved_at` DATETIME NULL,
            `delivered_at` DATETIME NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX `idx_user_offer` (`user_id`, `offer_id`),
            INDEX `idx_status` (`claim_status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    echo "  [+] Created table `associate_offer_claims`\n";
} else {
    echo "  [.] (SKIP) Table `associate_offer_claims` already exists\n";
}

// 3. Enhance promotional_offers (customer facing)
$promoColumns = [
    'festival_name'       => "ALTER TABLE `promotional_offers` ADD COLUMN `festival_name` VARCHAR(100) NULL AFTER `title`",
    'discount_type'       => "ALTER TABLE `promotional_offers` ADD COLUMN `discount_type` ENUM('percentage', 'flat_amount', 'per_sqft', 'gift_item', 'cashback_wallet') DEFAULT 'percentage' AFTER `description`",
    'discount_value'      => "ALTER TABLE `promotional_offers` ADD COLUMN `discount_value` DECIMAL(15,2) DEFAULT 0.00 AFTER `discount_type`",
    'min_booking_amount'  => "ALTER TABLE `promotional_offers` ADD COLUMN `min_booking_amount` DECIMAL(15,2) DEFAULT 0.00 AFTER `discount_value`",
    'colony_id'           => "ALTER TABLE `promotional_offers` ADD COLUMN `colony_id` INT UNSIGNED NULL AFTER `min_booking_amount`",
    'promo_code'          => "ALTER TABLE `promotional_offers` ADD COLUMN `promo_code` VARCHAR(50) NULL AFTER `colony_id`",
    'valid_from'          => "ALTER TABLE `promotional_offers` ADD COLUMN `valid_from` DATE NULL AFTER `promo_code`",
    'max_redemptions'     => "ALTER TABLE `promotional_offers` ADD COLUMN `max_redemptions` INT DEFAULT 0 AFTER `valid_until`",
    'times_redeemed'      => "ALTER TABLE `promotional_offers` ADD COLUMN `times_redeemed` INT DEFAULT 0 AFTER `max_redemptions`",
    'banner_image'        => "ALTER TABLE `promotional_offers` ADD COLUMN `banner_image` VARCHAR(255) NULL AFTER `times_redeemed`",
];

foreach ($promoColumns as $col => $sql) {
    if (!columnExists($pdo, 'promotional_offers', $col)) {
        $pdo->exec($sql);
        echo "  [+] Added column `promotional_offers`.`$col`\n";
    } else {
        echo "  [.] (SKIP) `promotional_offers`.`$col` already exists\n";
    }
}

// 4. Enhance plot_bookings with discount tracking
$bookingColumns = [
    'discount_amount'       => "ALTER TABLE `plot_bookings` ADD COLUMN `discount_amount` DECIMAL(15,2) DEFAULT 0.00 AFTER `booking_amount`",
    'promotional_offer_id'  => "ALTER TABLE `plot_bookings` ADD COLUMN `promotional_offer_id` INT NULL AFTER `discount_amount`",
    'promo_code_applied'    => "ALTER TABLE `plot_bookings` ADD COLUMN `promo_code_applied` VARCHAR(50) NULL AFTER `promotional_offer_id`",
];

foreach ($bookingColumns as $col => $sql) {
    if (!columnExists($pdo, 'plot_bookings', $col)) {
        $pdo->exec($sql);
        echo "  [+] Added column `plot_bookings`.`$col`\n";
    } else {
        echo "  [.] (SKIP) `plot_bookings`.`$col` already exists\n";
    }
}

// 5. Seed Idempotent Festive Campaigns
// 5.1 Associate Festival Offers
$count = (int)$pdo->query("SELECT COUNT(*) FROM `associate_offers` WHERE `title` LIKE '%Mega Festive%'")->fetchColumn();
if ($count === 0) {
    $today = date('Y-m-d');
    $nextMonth = date('Y-m-d', strtotime('+60 days'));
    $pdo->prepare("
        INSERT INTO `associate_offers` (
            tenant_id, title, festival_name, description, reward_type, reward_item, reward_mode, reward_value,
            criteria_type, criteria_value, min_site_visits, starts_at, ends_at, status, created_by, created_at
        ) VALUES (
            1, 'Mega Festive Leadership Target', 'Festival Special',
            'Achieve ₹10,00,000 team volume or 3 verified bookings during festive window to win a 5g Pure Gold Coin or ₹35,000 cash in wallet.',
            'bonus_amount', '5g Pure 24K Gold Coin', 'wallet_cash', 35000.00,
            'sale_volume', 1000000.00, 2, ?, ?, 'active', 1, NOW()
        )
    ")->execute([$today, $nextMonth]);
    echo "  [+] Seeded Associate Mega Festive Target\n";
} else {
    echo "  [.] (SKIP) Associate Mega Festive Target already seeded\n";
}

// 5.2 Customer Promotional Offers with Promo Codes
$countPromo = (int)$pdo->query("SELECT COUNT(*) FROM `promotional_offers` WHERE `promo_code` = 'FESTIVE21'")->fetchColumn();
if ($countPromo === 0) {
    $today = date('Y-m-d');
    $nextYear = date('Y-m-d', strtotime('+90 days'));
    $pdo->prepare("
        INSERT INTO `promotional_offers` (
            tenant_id, title, festival_name, description, discount_type, discount_value, discount_percentage,
            min_booking_amount, promo_code, valid_from, valid_until, status, created_at
        ) VALUES (
            1, 'Festive Subh Labh Spot Discount', 'Festival Special',
            'Flat ₹21,000 instant festive concession on any plot booking token across all townships.',
            'flat_amount', 21000.00, 0.00,
            100000.00, 'FESTIVE21', ?, ?, 'active', NOW()
        )
    ")->execute([$today, $nextYear]);
    echo "  [+] Seeded Customer Promo Code FESTIVE21\n";
} else {
    echo "  [.] (SKIP) Customer Promo Code FESTIVE21 already seeded\n";
}

$countPromo5 = (int)$pdo->query("SELECT COUNT(*) FROM `promotional_offers` WHERE `promo_code` = 'SUBHLABH5'")->fetchColumn();
if ($countPromo5 === 0) {
    $today = date('Y-m-d');
    $nextYear = date('Y-m-d', strtotime('+90 days'));
    $pdo->prepare("
        INSERT INTO `promotional_offers` (
            tenant_id, title, festival_name, description, discount_type, discount_value, discount_percentage,
            min_booking_amount, promo_code, valid_from, valid_until, status, created_at
        ) VALUES (
            1, 'Festival Percentage Concession (5% Off)', 'Festival Special',
            'Flat 5% festival discount on full plot agreement value during festival celebration season.',
            'percentage', 0.00, 5.00,
            50000.00, 'SUBHLABH5', ?, ?, 'active', NOW()
        )
    ")->execute([$today, $nextYear]);
    echo "  [+] Seeded Customer Promo Code SUBHLABH5\n";
} else {
    echo "  [.] (SKIP) Customer Promo Code SUBHLABH5 already seeded\n";
}

echo "Migration completed successfully.\n";
