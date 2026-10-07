<?php
/**
 * Migration: Create resell_transactions table for tracking completed resale deals
 * Also adds property_transactions for user_properties marketplace
 */

require_once __DIR__ . '/../../config/bootstrap.php';

$db = \App\Core\Database\Database::getInstance()->getConnection();

echo "Creating resell_transactions table...\n";

$resellTxnSql = "
CREATE TABLE IF NOT EXISTS `resell_transactions` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1,
    `property_id` BIGINT UNSIGNED NOT NULL COMMENT 'resell_properties.id',
    `seller_id` BIGINT UNSIGNED NOT NULL COMMENT 'User who listed the property',
    `buyer_id` BIGINT UNSIGNED NOT NULL COMMENT 'User who bought the property',
    `buyer_referrer_id` BIGINT UNSIGNED NULL COMMENT 'Referrer of the buyer (gets commission)',
    `final_price` DECIMAL(15,2) NOT NULL COMMENT 'Agreed sale price',
    `platform_fee` DECIMAL(15,2) NOT NULL DEFAULT 0 COMMENT 'Platform transaction fee',
    `referral_commission` DECIMAL(15,2) NOT NULL DEFAULT 0 COMMENT 'Commission paid to buyer referrer',
    `commission_rate` DECIMAL(5,2) NOT NULL DEFAULT 0 COMMENT 'Referral commission %',
    `status` ENUM('pending','completed','cancelled','disputed') NOT NULL DEFAULT 'pending',
    `deal_closed_at` DATETIME NULL,
    `payment_method` VARCHAR(50) NULL,
    `payment_reference` VARCHAR(100) NULL,
    `notes` TEXT NULL,
    `created_by` BIGINT UNSIGNED NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_property` (`property_id`),
    KEY `idx_seller` (`seller_id`),
    KEY `idx_buyer` (`buyer_id`),
    KEY `idx_referrer` (`buyer_referrer_id`),
    KEY `idx_status` (`status`),
    KEY `idx_tenant` (`tenant_id`),
    KEY `idx_closed_at` (`deal_closed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Completed resale transactions with commission tracking';
";

try {
    $db->exec($resellTxnSql);
    echo "✅ resell_transactions table created\n";
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\nCreating property_transactions table (for user_properties marketplace)...\n";

$propTxnSql = "
CREATE TABLE IF NOT EXISTS `property_transactions` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1,
    `property_id` BIGINT UNSIGNED NOT NULL COMMENT 'user_properties.id',
    `listing_type` ENUM('user','company') NOT NULL DEFAULT 'user',
    `seller_id` BIGINT UNSIGNED NOT NULL COMMENT 'User who listed the property',
    `buyer_id` BIGINT UNSIGNED NOT NULL COMMENT 'User who bought the property',
    `buyer_referrer_id` BIGINT UNSIGNED NULL COMMENT 'Referrer of the buyer (gets commission)',
    `final_price` DECIMAL(15,2) NOT NULL COMMENT 'Agreed sale price',
    `platform_fee` DECIMAL(15,2) NOT NULL DEFAULT 0 COMMENT 'Platform transaction fee',
    `referral_commission` DECIMAL(15,2) NOT NULL DEFAULT 0 COMMENT 'Commission paid to buyer referrer',
    `commission_rate` DECIMAL(5,2) NOT NULL DEFAULT 0 COMMENT 'Referral commission %',
    `status` ENUM('pending','completed','cancelled','disputed') NOT NULL DEFAULT 'pending',
    `deal_closed_at` DATETIME NULL,
    `payment_method` VARCHAR(50) NULL,
    `payment_reference` VARCHAR(100) NULL,
    `notes` TEXT NULL,
    `created_by` BIGINT UNSIGNED NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_property` (`property_id`),
    KEY `idx_listing_type` (`listing_type`),
    KEY `idx_seller` (`seller_id`),
    KEY `idx_buyer` (`buyer_id`),
    KEY `idx_referrer` (`buyer_referrer_id`),
    KEY `idx_status` (`status`),
    KEY `idx_tenant` (`tenant_id`),
    KEY `idx_closed_at` (`deal_closed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Completed property transactions (user_properties marketplace) with commission tracking';
";

try {
    $db->exec($propTxnSql);
    echo "✅ property_transactions table created\n";
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\nAdding platform_fee_pct to resell_commission_structure if not exists...\n";

try {
    $cols = $db->query("SHOW COLUMNS FROM resell_commission_structure LIKE 'platform_fee_pct'")->fetchAll();
    if (empty($cols)) {
        $db->exec("ALTER TABLE resell_commission_structure ADD COLUMN platform_fee_pct DECIMAL(5,2) NOT NULL DEFAULT 1.00 AFTER flat_amount");
        echo "✅ platform_fee_pct added to resell_commission_structure\n";
    } else {
        echo "⏭️ platform_fee_pct already exists\n";
    }
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\nAdding boost/feature tracking columns to user_properties if not exists...\n";

$boostCols = [
    'boosted_at' => "ADD COLUMN `boosted_at` DATETIME NULL COMMENT 'When listing was boosted'",
    'boost_expires_at' => "ADD COLUMN `boost_expires_at` DATETIME NULL COMMENT 'When boost expires'",
    'boost_amount' => "ADD COLUMN `boost_amount` DECIMAL(10,2) NOT NULL DEFAULT 0 COMMENT 'Amount paid for boost'",
    'promoted_until' => "ADD COLUMN `promoted_until` DATETIME NULL COMMENT 'Promoted listing expiry'",
    'transaction_id' => "ADD COLUMN `transaction_id` BIGINT UNSIGNED NULL COMMENT 'Linked transaction when sold'",
];

foreach ($boostCols as $col => $ddl) {
    try {
        $exists = $db->query("SHOW COLUMNS FROM user_properties LIKE '$col'")->fetchAll();
        if (empty($exists)) {
            $db->exec("ALTER TABLE user_properties $ddl");
            echo "✅ Added $col to user_properties\n";
        } else {
            echo "⏭️ $col already exists\n";
        }
    } catch (\Exception $e) {
        echo "❌ Error adding $col: " . $e->getMessage() . "\n";
    }
}

echo "\nAdding transaction_id to resell_properties...\n";
try {
    $exists = $db->query("SHOW COLUMNS FROM resell_properties LIKE 'transaction_id'")->fetchAll();
    if (empty($exists)) {
        $db->exec("ALTER TABLE resell_properties ADD COLUMN `transaction_id` BIGINT UNSIGNED NULL COMMENT 'Linked transaction when sold'");
        echo "✅ Added transaction_id to resell_properties\n";
    } else {
        echo "⏭️ transaction_id already exists\n";
    }
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\nDone!\n";