<?php
/**
 * Probe-Green Migration (2026-10-09)
 * Creates the core tables + columns + seed rows required by:
 *   - testing/workflow_probe.php (login, properties, favorites, inquiry,
 *     colonies, dashboard, notifications, payment-history, profile + DB checks)
 *   - testing/production_smoke_runner.php (/api/health, /api/v2/mobile/colonies,
 *     /api/v2/mobile/plots/all)
 *   - ApiAuthMiddleware (users.tenant_id + api_tokens)
 *
 * Idempotent: safe to re-run (IF NOT EXISTS / ADD COLUMN IF NOT EXISTS / INSERT IGNORE).
 */

$pdo = new PDO('mysql:host=localhost;dbname=apsdreamhome;charset=utf8mb4', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

function run(PDO $pdo, string $label, string $sql): void {
    try {
        $pdo->exec($sql);
        echo "OK   $label\n";
    } catch (Throwable $e) {
        echo "SKIP $label -- " . substr($e->getMessage(), 0, 120) . "\n";
    }
}

// ── 1. users: tenant_id, referral_code, wider role enum ──
run($pdo, 'users.tenant_id', "ALTER TABLE `users` ADD COLUMN IF NOT EXISTS `tenant_id` INT NOT NULL DEFAULT 1 AFTER `id`");
run($pdo, 'users.referral_code', "ALTER TABLE `users` ADD COLUMN IF NOT EXISTS `referral_code` VARCHAR(50) NULL AFTER `phone`");
run($pdo, 'users.role enum', "ALTER TABLE `users` MODIFY COLUMN `role` ENUM('admin','user','employee','customer','associate','agent','manager','telecaller','ceo','sales_manager','accountant') NOT NULL DEFAULT 'user'");

// ── 2. districts (joined by colonies APIs) ──
run($pdo, 'districts table', "CREATE TABLE IF NOT EXISTS `districts` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `state_id` INT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
run($pdo, 'districts seed', "INSERT IGNORE INTO `districts` (`id`, `name`) VALUES (1, 'Gorakhpur'), (2, 'Lucknow')");

// ── 3. colonies: columns required by MobilePropertyApiController::getColonies ──
run($pdo, 'colonies.district_id', "ALTER TABLE `colonies` ADD COLUMN IF NOT EXISTS `district_id` INT NULL AFTER `tenant_id`");
run($pdo, 'colonies.image_path', "ALTER TABLE `colonies` ADD COLUMN IF NOT EXISTS `image_path` VARCHAR(500) NULL AFTER `available_plots`");
run($pdo, 'colonies.layout_image', "ALTER TABLE `colonies` ADD COLUMN IF NOT EXISTS `layout_image` VARCHAR(500) NULL AFTER `image_path`");
run($pdo, 'colonies.is_featured', "ALTER TABLE `colonies` ADD COLUMN IF NOT EXISTS `is_featured` TINYINT(1) NOT NULL DEFAULT 0 AFTER `layout_image`");
run($pdo, 'colonies.starting_price', "ALTER TABLE `colonies` ADD COLUMN IF NOT EXISTS `starting_price` DECIMAL(15,2) NOT NULL DEFAULT 0 AFTER `is_featured`");
run($pdo, 'colonies.map_link', "ALTER TABLE `colonies` ADD COLUMN IF NOT EXISTS `map_link` VARCHAR(500) NULL AFTER `longitude`");
run($pdo, 'colonies seed', "INSERT IGNORE INTO `colonies`
  (`id`, `tenant_id`, `district_id`, `name`, `slug`, `description`, `total_plots`, `available_plots`, `starting_price`, `is_featured`, `is_active`, `status`)
  VALUES
  (1, 1, 1, 'Suryoday Heights', 'suryoday-heights', 'Premium colony, plots from 5.5L', 120, 96, 550000, 1, 1, 'active'),
  (2, 1, 1, 'Raghunath City Center', 'raghunath-city-center', 'Commercial plots available', 80, 60, 850000, 1, 1, 'active'),
  (3, 1, 1, 'Green Valley Enclave', 'green-valley-enclave', 'Park-facing residential plots', 150, 121, 450000, 0, 1, 'active'),
  (4, 1, 2, 'Lakeview Residency', 'lakeview-residency', 'Lake-view premium plots', 60, 44, 1200000, 0, 1, 'active'),
  (5, 1, 2, 'Sunrise Meadows', 'sunrise-meadows', 'Budget-friendly east-facing plots', 200, 173, 320000, 0, 1, 'active')");

// ── 4. properties: columns required by browse/favorites/inquiry ──
run($pdo, 'properties.status enum', "ALTER TABLE `properties` MODIFY COLUMN `status` ENUM('available','sold','rented','active','inactive','under_offer') NOT NULL DEFAULT 'available'");
run($pdo, 'properties.city', "ALTER TABLE `properties` ADD COLUMN IF NOT EXISTS `city` VARCHAR(100) NULL AFTER `location`");
run($pdo, 'properties.state', "ALTER TABLE `properties` ADD COLUMN IF NOT EXISTS `state` VARCHAR(100) NULL AFTER `city`");
run($pdo, 'properties.bedrooms', "ALTER TABLE `properties` ADD COLUMN IF NOT EXISTS `bedrooms` INT NULL AFTER `type`");
run($pdo, 'properties.bathrooms', "ALTER TABLE `properties` ADD COLUMN IF NOT EXISTS `bathrooms` INT NULL AFTER `bedrooms`");
run($pdo, 'properties.area_sqft', "ALTER TABLE `properties` ADD COLUMN IF NOT EXISTS `area_sqft` DECIMAL(10,2) NULL AFTER `bathrooms`");
run($pdo, 'properties.featured', "ALTER TABLE `properties` ADD COLUMN IF NOT EXISTS `featured` TINYINT(1) NOT NULL DEFAULT 0 AFTER `area_sqft`");
run($pdo, 'properties.property_type_id', "ALTER TABLE `properties` ADD COLUMN IF NOT EXISTS `property_type_id` INT NULL AFTER `featured`");
run($pdo, 'properties.site_id', "ALTER TABLE `properties` ADD COLUMN IF NOT EXISTS `site_id` INT NULL AFTER `property_type_id`");
run($pdo, 'properties.tenant_id', "ALTER TABLE `properties` ADD COLUMN IF NOT EXISTS `tenant_id` INT NOT NULL DEFAULT 1 AFTER `id`");
run($pdo, 'properties.created_by', "ALTER TABLE `properties` ADD COLUMN IF NOT EXISTS `created_by` INT NULL AFTER `tenant_id`");

// ── 5. property lookup tables ──
run($pdo, 'property_types', "CREATE TABLE IF NOT EXISTS `property_types` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `type` VARCHAR(50) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
run($pdo, 'property_types seed', "INSERT IGNORE INTO `property_types` (`id`, `type`) VALUES (1, 'plot'), (2, 'apartment'), (3, 'villa')");
run($pdo, 'property_images', "CREATE TABLE IF NOT EXISTS `property_images` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `property_id` INT NOT NULL,
  `image_path` VARCHAR(500) NULL,
  `is_primary` TINYINT(1) NOT NULL DEFAULT 0,
  `sort_order` INT NOT NULL DEFAULT 0,
  KEY `idx_pi_property` (`property_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
run($pdo, 'property_favorites', "CREATE TABLE IF NOT EXISTS `property_favorites` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `property_id` INT NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uq_fav` (`user_id`, `property_id`),
  KEY `idx_fav_property` (`property_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
run($pdo, 'property_inquiries', "CREATE TABLE IF NOT EXISTS `property_inquiries` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `property_id` INT NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(20) NOT NULL,
  `message` TEXT NULL,
  `tenant_id` INT NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_pinq_property` (`property_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// ── 6. plots (MobilePropertyApiController::getAllPlots + holdPlot) ──
run($pdo, 'plots table', "CREATE TABLE IF NOT EXISTS `plots` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `tenant_id` INT NOT NULL DEFAULT 1,
  `colony_id` INT NOT NULL,
  `plot_number` VARCHAR(50) NOT NULL,
  `block` VARCHAR(10) NULL,
  `area_sqft` DECIMAL(10,2) NOT NULL DEFAULT 1000,
  `status` ENUM('available','hold','reserved','booked','sold') NOT NULL DEFAULT 'available',
  `total_price` DECIMAL(15,2) NOT NULL DEFAULT 0,
  `price_per_sqft` DECIMAL(10,2) NOT NULL DEFAULT 0,
  `facing` VARCHAR(20) NULL,
  `corner_plot` TINYINT(1) NOT NULL DEFAULT 0,
  `width_ft` DECIMAL(6,2) NULL,
  `length_ft` DECIMAL(6,2) NULL,
  `held_by` INT NULL,
  `held_at` DATETIME NULL,
  `hold_expires_at` DATETIME NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `idx_plots_colony` (`colony_id`),
  KEY `idx_plots_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
run($pdo, 'plots seed', "INSERT IGNORE INTO `plots`
  (`id`, `tenant_id`, `colony_id`, `plot_number`, `block`, `area_sqft`, `status`, `total_price`, `price_per_sqft`, `facing`, `width_ft`, `length_ft`)
  VALUES
  (1, 1, 1, 'A-1', 'A', 1000, 'available', 550000, 550, 'East', 20, 50),
  (2, 1, 1, 'A-2', 'A', 1200, 'available', 660000, 550, 'East', 24, 50),
  (3, 1, 2, 'B-1', 'B', 800, 'available', 680000, 850, 'North', 20, 40),
  (4, 1, 3, 'C-1', 'C', 1500, 'available', 675000, 450, 'West', 30, 50),
  (5, 1, 4, 'D-1', 'D', 2000, 'available', 2400000, 1200, 'East', 40, 50),
  (6, 1, 5, 'E-1', 'E', 900, 'available', 288000, 320, 'North', 18, 50)");

// ── 7. bookings + emi_schedule (dashboardV3, admin bookings) ──
run($pdo, 'bookings table', "CREATE TABLE IF NOT EXISTS `bookings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `customer_id` INT NOT NULL,
  `property_id` INT NULL,
  `plot_id` INT NULL,
  `booking_number` VARCHAR(50) NULL,
  `booking_date` DATE NULL,
  `amount` DECIMAL(15,2) NOT NULL DEFAULT 0,
  `status` ENUM('pending','confirmed','cancelled','completed') NOT NULL DEFAULT 'pending',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_bookings_customer` (`customer_id`),
  KEY `idx_bookings_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
run($pdo, 'emi_schedule', "CREATE TABLE IF NOT EXISTS `emi_schedule` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `booking_id` INT NOT NULL,
  `amount` DECIMAL(15,2) NOT NULL DEFAULT 0,
  `paid_amount` DECIMAL(15,2) NOT NULL DEFAULT 0,
  `status` ENUM('pending','paid','overdue') NOT NULL DEFAULT 'pending',
  KEY `idx_emi_booking` (`booking_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// ── 8. plot_bookings + booking_payments (workflow orphan check + payment history) ──
run($pdo, 'plot_bookings', "CREATE TABLE IF NOT EXISTS `plot_bookings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `customer_id` INT NOT NULL,
  `plot_id` INT NOT NULL,
  `booking_number` VARCHAR(50) NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_pb_plot` (`plot_id`),
  KEY `idx_pb_customer` (`customer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
run($pdo, 'booking_payments', "CREATE TABLE IF NOT EXISTS `booking_payments` (
  `payment_id` INT AUTO_INCREMENT PRIMARY KEY,
  `booking_id` INT NOT NULL,
  `payment_amount` DECIMAL(15,2) NOT NULL DEFAULT 0,
  `payment_method` VARCHAR(50) NULL,
  `transaction_id` VARCHAR(100) NULL,
  `payment_notes` TEXT NULL,
  `payment_date` DATETIME NULL,
  KEY `idx_bp_booking` (`booking_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// ── 9. MLM auth/profile/ledger ──
run($pdo, 'api_tokens', "CREATE TABLE IF NOT EXISTS `api_tokens` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `token` VARCHAR(128) NOT NULL,
  `expires_at` DATETIME NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uq_token` (`token`),
  KEY `idx_token_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
run($pdo, 'mlm_profiles', "CREATE TABLE IF NOT EXISTS `mlm_profiles` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `current_level` VARCHAR(50) NOT NULL DEFAULT 'Customer',
  `total_commission` DECIMAL(15,2) NOT NULL DEFAULT 0,
  UNIQUE KEY `uq_mp_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
run($pdo, 'mlm_commission_ledger', "CREATE TABLE IF NOT EXISTS `mlm_commission_ledger` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `associate_id` INT NOT NULL,
  `user_id` INT NULL,
  `booking_id` INT NULL,
  `amount` DECIMAL(15,2) NOT NULL DEFAULT 0,
  `percentage` DECIMAL(5,2) NOT NULL DEFAULT 0,
  `status` VARCHAR(20) NOT NULL DEFAULT 'paid',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_mcl_associate` (`associate_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// ── 10. wallets (associate wallet page + Wallet model) ──
run($pdo, 'wallet_points', "CREATE TABLE IF NOT EXISTS `wallet_points` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `points_balance` DECIMAL(10,2) NOT NULL DEFAULT 0,
  `total_earned` DECIMAL(10,2) NOT NULL DEFAULT 0,
  `total_used` DECIMAL(10,2) NOT NULL DEFAULT 0,
  `total_transferred_to_emi` DECIMAL(10,2) NOT NULL DEFAULT 0,
  `referral_earnings` DECIMAL(10,2) NOT NULL DEFAULT 0,
  `commission_earnings` DECIMAL(10,2) NOT NULL DEFAULT 0,
  `bonus_earnings` DECIMAL(10,2) NOT NULL DEFAULT 0,
  `status` ENUM('active','frozen','blocked') NOT NULL DEFAULT 'active',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `uq_wp_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
run($pdo, 'wallet_transactions', "CREATE TABLE IF NOT EXISTS `wallet_transactions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `transaction_type` ENUM('credit','debit','transfer') NOT NULL,
  `transaction_category` ENUM('referral','commission','bonus','emi_transfer','withdrawal','adjustment') NOT NULL,
  `amount` DECIMAL(10,2) NOT NULL,
  `balance_before` DECIMAL(10,2) NOT NULL DEFAULT 0,
  `balance_after` DECIMAL(10,2) NOT NULL DEFAULT 0,
  `description` TEXT NULL,
  `status` ENUM('pending','completed','failed','cancelled') NOT NULL DEFAULT 'completed',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_wt_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// ── 11. notifications: columns required by getCustomerNotifications ──
run($pdo, 'notifications.template_data', "ALTER TABLE `notifications` ADD COLUMN IF NOT EXISTS `template_data` TEXT NULL AFTER `message`");
run($pdo, 'notifications.action_url', "ALTER TABLE `notifications` ADD COLUMN IF NOT EXISTS `action_url` VARCHAR(500) NULL AFTER `template_data`");

// ── 12. seed users (admin kept; workflow customer added) ──
$hash = password_hash('Aps@2026', PASSWORD_DEFAULT);
$stmt = $pdo->prepare("INSERT IGNORE INTO `users` (`tenant_id`, `name`, `email`, `phone`, `password`, `role`, `status`) VALUES (1, 'Workflow Probe', 'testuser@example.com', '9999990001', ?, 'customer', 'active')");
$stmt->execute([$hash]);
echo "OK   testuser seed\n";

$testId = (int)$pdo->query("SELECT `id` FROM `users` WHERE `email`='testuser@example.com' LIMIT 1")->fetchColumn();
$adminId = (int)$pdo->query("SELECT `id` FROM `users` WHERE `email`='admin@apsdreamhome.com' LIMIT 1")->fetchColumn();

// ── 13. seed property (active, tenant 1, owned by admin) ──
run($pdo, 'properties seed', "INSERT INTO `properties`
  (`id`, `tenant_id`, `created_by`, `title`, `description`, `price`, `location`, `city`, `state`, `type`, `bedrooms`, `bathrooms`, `area_sqft`, `featured`, `property_type_id`, `status`)
  VALUES (1, 1, " . ($adminId ?: 1) . ", 'Probe Villa Gorakhpur', 'Workflow smoke property', 5500000, 'Suryoday Heights', 'Gorakhpur', 'UP', 'villa', 3, 2, 1500, 0, 3, 'active')
  ON DUPLICATE KEY UPDATE `tenant_id`=1, `created_by`=VALUES(`created_by`), `title`=VALUES(`title`), `city`=VALUES(`city`), `state`=VALUES(`state`), `bedrooms`=VALUES(`bedrooms`), `bathrooms`=VALUES(`bathrooms`), `area_sqft`=VALUES(`area_sqft`), `property_type_id`=VALUES(`property_type_id`), `status`='active'");

// ── 14. seed mlm profile + wallet + ledger for testuser ──
if ($testId > 0) {
    run($pdo, 'mlm_profiles seed', "INSERT IGNORE INTO `mlm_profiles` (`user_id`, `current_level`) VALUES ($testId, 'Customer')");
    run($pdo, 'wallet_points seed', "INSERT IGNORE INTO `wallet_points` (`user_id`) VALUES ($testId)");
    run($pdo, 'ledger seed', "INSERT INTO `mlm_commission_ledger` (`associate_id`, `user_id`, `amount`, `percentage`, `status`) SELECT $testId, $testId, 5000, 5, 'paid' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `mlm_commission_ledger` LIMIT 1)");
}

echo "DONE probe-green migration\n";
