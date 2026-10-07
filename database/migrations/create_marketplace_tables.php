<?php
require 'config/bootstrap.php';
$db = \App\Core\Database\Database::getInstance()->getConnection();

echo "Creating missing tables for complete marketplace system...\n\n";

// 1. user_saved_properties - shortlist/favorites
echo "1. Creating user_saved_properties...\n";
$sql = "
CREATE TABLE IF NOT EXISTS `user_saved_properties` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `property_id` BIGINT UNSIGNED NOT NULL,
    `listing_type` ENUM('user','company','resell') NOT NULL DEFAULT 'user',
    `saved_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `notes` VARCHAR(500) NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_user_property` (`user_id`, `property_id`, `listing_type`),
    KEY `idx_user` (`user_id`),
    KEY `idx_property` (`property_id`, `listing_type`),
    KEY `idx_tenant` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='User shortlisted/favorited properties';
";
try { $db->exec($sql); echo "✅ user_saved_properties created\n"; } catch (\Exception $e) { echo "❌ " . $e->getMessage() . "\n"; }

// 2. followup_schedules - automated follow-up system
echo "\n2. Creating followup_schedules...\n";
$sql = "
CREATE TABLE IF NOT EXISTS `followup_schedules` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1,
    `lead_id` BIGINT UNSIGNED NOT NULL,
    `property_id` BIGINT UNSIGNED NULL,
    `listing_type` ENUM('user','company','resell') NULL,
    `buyer_id` BIGINT UNSIGNED NULL,
    `scheduled_for` DATETIME NOT NULL,
    `followup_type` ENUM('call','whatsapp','email','sms','site_visit','meeting') NOT NULL DEFAULT 'call',
    `status` ENUM('pending','completed','cancelled','rescheduled','overdue') NOT NULL DEFAULT 'pending',
    `priority` ENUM('low','medium','high','urgent') NOT NULL DEFAULT 'medium',
    `assigned_to` BIGINT UNSIGNED NULL COMMENT 'agent/associate/employee who will follow up',
    `notes` TEXT NULL,
    `completed_at` DATETIME NULL,
    `completed_by` BIGINT UNSIGNED NULL,
    `outcome` ENUM('interested','not_interested','callback_later','site_visit_booked','deal_closed','wrong_number','no_response') NULL,
    `created_by` BIGINT UNSIGNED NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_lead` (`lead_id`),
    KEY `idx_buyer` (`buyer_id`),
    KEY `idx_property` (`property_id`, `listing_type`),
    KEY `idx_scheduled` (`scheduled_for`, `status`),
    KEY `idx_assigned` (`assigned_to`, `status`),
    KEY `idx_tenant` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Automated follow-up schedules for interested buyers';
";
try { $db->exec($sql); echo "✅ followup_schedules created\n"; } catch (\Exception $e) { echo "❌ " . $e->getMessage() . "\n"; }

// 3. builder_packages - monetization for builders/brokers
echo "\n3. Creating builder_packages...\n";
$sql = "
CREATE TABLE IF NOT EXISTS `builder_packages` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1,
    `name` VARCHAR(100) NOT NULL,
    `slug` VARCHAR(100) NOT NULL,
    `description` TEXT NULL,
    `price_monthly` DECIMAL(10,2) NOT NULL DEFAULT 0,
    `price_yearly` DECIMAL(10,2) NOT NULL DEFAULT 0,
    `max_listings` INT UNSIGNED NOT NULL DEFAULT 10,
    `max_featured` INT UNSIGNED NOT NULL DEFAULT 0,
    `max_urgent` INT UNSIGNED NOT NULL DEFAULT 0,
    `logo_on_listing` TINYINT(1) NOT NULL DEFAULT 0,
    `priority_support` TINYINT(1) NOT NULL DEFAULT 0,
    `api_access` TINYINT(1) NOT NULL DEFAULT 0,
    `analytics_access` TINYINT(1) NOT NULL DEFAULT 0,
    `lead_export` TINYINT(1) NOT NULL DEFAULT 0,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `sort_order` INT NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_slug` (`slug`),
    KEY `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Subscription packages for builders/brokers';
";
try { $db->exec($sql); echo "✅ builder_packages created\n"; } catch (\Exception $e) { echo "❌ " . $e->getMessage() . "\n"; }

// 4. builder_subscriptions - track builder subscriptions
echo "\n4. Creating builder_subscriptions...\n";
$sql = "
CREATE TABLE IF NOT EXISTS `builder_subscriptions` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1,
    `builder_id` BIGINT UNSIGNED NOT NULL COMMENT 'User who is a builder/broker',
    `package_id` BIGINT UNSIGNED NOT NULL,
    `status` ENUM('active','expired','cancelled','pending_payment') NOT NULL DEFAULT 'pending_payment',
    `starts_at` DATETIME NOT NULL,
    `ends_at` DATETIME NOT NULL,
    `auto_renew` TINYINT(1) NOT NULL DEFAULT 1,
    `payment_method` VARCHAR(50) NULL,
    `payment_reference` VARCHAR(100) NULL,
    `amount_paid` DECIMAL(10,2) NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_builder` (`builder_id`, `status`),
    KEY `idx_package` (`package_id`),
    KEY `idx_dates` (`starts_at`, `ends_at`),
    KEY `idx_tenant` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Active builder/broker subscriptions';
";
try { $db->exec($sql); echo "✅ builder_subscriptions created\n"; } catch (\Exception $e) { echo "❌ " . $e->getMessage() . "\n"; }

// 5. platform_revenue - track all revenue streams
echo "\n5. Creating platform_revenue...\n";
$sql = "
CREATE TABLE IF NOT EXISTS `platform_revenue` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1,
    `source_type` ENUM('resell_transaction','user_property_transaction','boost','featured','urgent','premium','builder_subscription','ad_revenue','associate_commission','agent_commission','verification_fee') NOT NULL,
    `source_id` BIGINT UNSIGNED NULL COMMENT 'transaction_id, property_id, subscription_id, etc.',
    `amount` DECIMAL(15,2) NOT NULL,
    `currency` VARCHAR(3) NOT NULL DEFAULT 'INR',
    `description` VARCHAR(255) NULL,
    `metadata` JSON NULL,
    `recorded_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_source` (`source_type`, `source_id`),
    KEY `idx_date` (`recorded_at`),
    KEY `idx_tenant` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='All platform revenue streams';
";
try { $db->exec($sql); echo "✅ platform_revenue created\n"; } catch (\Exception $e) { echo "❌ " . $e->getMessage() . "\n"; }

// 6. property_verification_logs - track verification workflow
echo "\n6. Creating property_verification_logs...\n";
$sql = "
CREATE TABLE IF NOT EXISTS `property_verification_logs` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1,
    `property_id` BIGINT UNSIGNED NOT NULL,
    `listing_type` ENUM('user','resell') NOT NULL DEFAULT 'user',
    `verification_type` ENUM('title','ownership','legal','physical','price','documents') NOT NULL,
    `status` ENUM('initiated','in_progress','verified','rejected','needs_info') NOT NULL DEFAULT 'initiated',
    `initiated_by` BIGINT UNSIGNED NOT NULL,
    `verified_by` BIGINT UNSIGNED NULL,
    `documents` JSON NULL COMMENT 'Array of document references',
    `notes` TEXT NULL,
    `started_at` DATETIME NULL,
    `completed_at` DATETIME NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_property` (`property_id`, `listing_type`),
    KEY `idx_status` (`status`),
    KEY `idx_verifier` (`verified_by`),
    KEY `idx_tenant` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Property verification audit trail';
";
try { $db->exec($sql); echo "✅ property_verification_logs created\n"; } catch (\Exception $e) { echo "❌ " . $e->getMessage() . "\n"; }

// 7. associate_resale_commissions - track associate/agent commissions on resale
echo "\n7. Creating associate_resale_commissions...\n";
$sql = "
CREATE TABLE IF NOT EXISTS `associate_resale_commissions` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1,
    `associate_id` BIGINT UNSIGNED NOT NULL COMMENT 'associates.id or agents table',
    `transaction_id` BIGINT UNSIGNED NOT NULL COMMENT 'resell_transactions.id or property_transactions.id',
    `transaction_type` ENUM('resell','user_property') NOT NULL,
    `commission_type` ENUM('referral_buyer','referral_seller','direct_buyer','direct_seller','team_override') NOT NULL,
    `commission_amount` DECIMAL(15,2) NOT NULL,
    `commission_rate` DECIMAL(5,2) NOT NULL,
    `status` ENUM('pending','approved','paid','cancelled') NOT NULL DEFAULT 'pending',
    `approved_by` BIGINT UNSIGNED NULL,
    `approved_at` DATETIME NULL,
    `paid_at` DATETIME NULL,
    `payment_reference` VARCHAR(100) NULL,
    `notes` TEXT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_associate` (`associate_id`, `status`),
    KEY `idx_transaction` (`transaction_id`, `transaction_type`),
    KEY `idx_status` (`status`),
    KEY `idx_tenant` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Associate/agent commissions from resale deals';
";
try { $db->exec($sql); echo "✅ associate_resale_commissions created\n"; } catch (\Exception $e) { echo "❌ " . $e->getMessage() . "\n"; }

// 8. property_interest_logs - track all buyer interest signals
echo "\n8. Creating property_interest_logs...\n";
$sql = "
CREATE TABLE IF NOT EXISTS `property_interest_logs` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1,
    `property_id` BIGINT UNSIGNED NOT NULL,
    `listing_type` ENUM('user','company','resell') NOT NULL DEFAULT 'user',
    `user_id` BIGINT UNSIGNED NULL COMMENT 'Logged in user',
    `session_id` VARCHAR(64) NULL COMMENT 'For anonymous users',
    `interest_type` ENUM('view','save','share','inquire','site_visit_request','offer','callback_request','download_brochure') NOT NULL,
    `referral_code` VARCHAR(50) NULL,
    `metadata` JSON NULL COMMENT 'budget, timeline, specific questions, etc.',
    `ip_address` VARCHAR(45) NULL,
    `user_agent` TEXT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_property` (`property_id`, `listing_type`, `interest_type`),
    KEY `idx_user` (`user_id`),
    KEY `idx_session` (`session_id`),
    KEY `idx_date` (`created_at`),
    KEY `idx_tenant` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Granular buyer interest tracking for retargeting/followup';
";
try { $db->exec($sql); echo "✅ property_interest_logs created\n"; } catch (\Exception $e) { echo "❌ " . $e->getMessage() . "\n"; }

// 9. Insert default builder packages
echo "\n9. Seeding default builder packages...\n";
$packages = [
    ['name' => 'Starter', 'slug' => 'starter', 'description' => 'Perfect for individual brokers', 'price_monthly' => 2999, 'price_yearly' => 29990, 'max_listings' => 20, 'max_featured' => 2, 'max_urgent' => 1, 'logo_on_listing' => 0, 'priority_support' => 0, 'analytics_access' => 1],
    ['name' => 'Professional', 'slug' => 'professional', 'description' => 'For growing brokerage firms', 'price_monthly' => 7999, 'price_yearly' => 79990, 'max_listings' => 100, 'max_featured' => 10, 'max_urgent' => 5, 'logo_on_listing' => 1, 'priority_support' => 1, 'analytics_access' => 1, 'lead_export' => 1],
    ['name' => 'Enterprise', 'slug' => 'enterprise', 'description' => 'For large builders & developers', 'price_monthly' => 19999, 'price_yearly' => 199990, 'max_listings' => 500, 'max_featured' => 50, 'max_urgent' => 20, 'logo_on_listing' => 1, 'priority_support' => 1, 'analytics_access' => 1, 'lead_export' => 1, 'api_access' => 1],
];
foreach ($packages as $pkg) {
    try {
        $stmt = $db->prepare("INSERT IGNORE INTO builder_packages (name, slug, description, price_monthly, price_yearly, max_listings, max_featured, max_urgent, logo_on_listing, priority_support, analytics_access, lead_export, api_access, is_active, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?)");
        $stmt->execute([$pkg['name'], $pkg['slug'], $pkg['description'], $pkg['price_monthly'], $pkg['price_yearly'], $pkg['max_listings'], $pkg['max_featured'], $pkg['max_urgent'], $pkg['logo_on_listing'] ?? 0, $pkg['priority_support'] ?? 0, $pkg['analytics_access'] ?? 0, $pkg['lead_export'] ?? 0, $pkg['api_access'] ?? 0, array_search($pkg['slug'], ['starter','professional','enterprise'])]);
        echo "✅ Package: {$pkg['name']}\n";
    } catch (\Exception $e) { echo "❌ " . $e->getMessage() . "\n"; }
}

// 10. Insert default commission structure for resell
echo "\n10. Seeding resell_commission_structure...\n";
$commissions = [
    ['property_type' => 'plot', 'min_price' => 0, 'max_price' => 5000000, 'commission_pct' => 1.5, 'flat_amount' => 0, 'platform_fee_pct' => 1.0],
    ['property_type' => 'plot', 'min_price' => 5000000, 'max_price' => 20000000, 'commission_pct' => 1.25, 'flat_amount' => 0, 'platform_fee_pct' => 1.0],
    ['property_type' => 'plot', 'min_price' => 20000000, 'max_price' => null, 'commission_pct' => 1.0, 'flat_amount' => 0, 'platform_fee_pct' => 0.75],
    ['property_type' => 'house', 'min_price' => 0, 'max_price' => 10000000, 'commission_pct' => 1.5, 'flat_amount' => 0, 'platform_fee_pct' => 1.0],
    ['property_type' => 'house', 'min_price' => 10000000, 'max_price' => 50000000, 'commission_pct' => 1.25, 'flat_amount' => 0, 'platform_fee_pct' => 1.0],
    ['property_type' => 'house', 'min_price' => 50000000, 'max_price' => null, 'commission_pct' => 1.0, 'flat_amount' => 0, 'platform_fee_pct' => 0.75],
    ['property_type' => 'flat', 'min_price' => 0, 'max_price' => 5000000, 'commission_pct' => 1.5, 'flat_amount' => 0, 'platform_fee_pct' => 1.0],
    ['property_type' => 'flat', 'min_price' => 5000000, 'max_price' => 20000000, 'commission_pct' => 1.25, 'flat_amount' => 0, 'platform_fee_pct' => 1.0],
    ['property_type' => 'flat', 'min_price' => 20000000, 'max_price' => null, 'commission_pct' => 1.0, 'flat_amount' => 0, 'platform_fee_pct' => 0.75],
    ['property_type' => 'shop', 'min_price' => 0, 'max_price' => 10000000, 'commission_pct' => 2.0, 'flat_amount' => 0, 'platform_fee_pct' => 1.5],
    ['property_type' => 'shop', 'min_price' => 10000000, 'max_price' => null, 'commission_pct' => 1.5, 'flat_amount' => 0, 'platform_fee_pct' => 1.0],
    ['property_type' => 'commercial', 'min_price' => 0, 'max_price' => 50000000, 'commission_pct' => 2.0, 'flat_amount' => 0, 'platform_fee_pct' => 1.5],
    ['property_type' => 'commercial', 'min_price' => 50000000, 'max_price' => null, 'commission_pct' => 1.5, 'flat_amount' => 0, 'platform_fee_pct' => 1.0],
];
foreach ($commissions as $c) {
    try {
        $stmt = $db->prepare("INSERT IGNORE INTO resell_commission_structure (property_type, min_price, max_price, commission_pct, flat_amount, platform_fee_pct, is_active, effective_from) VALUES (?, ?, ?, ?, ?, ?, 1, CURDATE())");
        $stmt->execute([$c['property_type'], $c['min_price'], $c['max_price'], $c['commission_pct'], $c['flat_amount'], $c['platform_fee_pct']]);
    } catch (\Exception $e) { /* ignore duplicates */ }
}
echo "✅ Commission structure seeded\n";

echo "\n=== ALL TABLES CREATED ===\n";