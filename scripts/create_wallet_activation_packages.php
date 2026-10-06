<?php
/**
 * Create Wallet Activation Packages Table
 * Run: php scripts/create_wallet_activation_packages.php
 */

require_once __DIR__ . '/../config/bootstrap.php';

use App\Core\Database\Database;

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();

    // Create wallet_activation_packages table
    $sql = "
    CREATE TABLE IF NOT EXISTS `wallet_activation_packages` (
        `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        `name` VARCHAR(100) NOT NULL,
        `slug` VARCHAR(100) NOT NULL UNIQUE,
        `description` TEXT,
        `price` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
        `referral_pct_l1` DECIMAL(5,2) NOT NULL DEFAULT 20.00,
        `referral_pct_l2` DECIMAL(5,2) NOT NULL DEFAULT 5.00,
        `features` JSON NOT NULL,
        `is_active` TINYINT(1) NOT NULL DEFAULT 1,
        `sort_order` INT NOT NULL DEFAULT 0,
        `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1,
        `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        INDEX `idx_active` (`is_active`),
        INDEX `idx_tenant` (`tenant_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    $conn->exec($sql);
    echo "✓ wallet_activation_packages table created\n";

    // Create wallet_activation_purchases table
    $sql = "
    CREATE TABLE IF NOT EXISTS `wallet_activation_purchases` (
        `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        `user_id` BIGINT UNSIGNED NOT NULL,
        `package_id` BIGINT UNSIGNED NOT NULL,
        `amount_paid` DECIMAL(12,2) NOT NULL,
        `payment_mode` VARCHAR(50) NOT NULL DEFAULT 'razorpay',
        `payment_ref` VARCHAR(100),
        `status` ENUM('pending','completed','failed','refunded') NOT NULL DEFAULT 'pending',
        `activated_at` TIMESTAMP NULL,
        `referral_commission_paid` TINYINT(1) NOT NULL DEFAULT 0,
        `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1,
        `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        INDEX `idx_user` (`user_id`),
        INDEX `idx_package` (`package_id`),
        INDEX `idx_status` (`status`),
        INDEX `idx_tenant` (`tenant_id`),
        FOREIGN KEY (`package_id`) REFERENCES `wallet_activation_packages`(`id`) ON DELETE RESTRICT
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    $conn->exec($sql);
    echo "✓ wallet_activation_purchases table created\n";

    // Add wallet_activated column to wallet_points if not exists
    $sql = "
    ALTER TABLE `wallet_points` 
    ADD COLUMN IF NOT EXISTS `wallet_activated` TINYINT(1) NOT NULL DEFAULT 0,
    ADD COLUMN IF NOT EXISTS `activation_package_id` BIGINT UNSIGNED NULL,
    ADD COLUMN IF NOT EXISTS `activation_expires_at` TIMESTAMP NULL,
    ADD INDEX IF NOT EXISTS `idx_wallet_activated` (`wallet_activated`);
    ";
    $conn->exec($sql);
    echo "✓ wallet_points columns added\n";

    // Insert default packages
    $packages = [
        [
            'name' => 'Basic Wallet',
            'slug' => 'basic',
            'description' => 'Unlock basic wallet features: EMI payment, referral earnings view',
            'price' => 499.00,
            'referral_pct_l1' => 20.00,
            'referral_pct_l2' => 5.00,
            'features' => json_encode([
                'emi_payment' => true,
                'referral_earnings_view' => true,
                'withdrawal_request' => false,
                'booking_adjustment' => false,
                'priority_support' => false,
            ]),
            'sort_order' => 1,
        ],
        [
            'name' => 'Pro Wallet',
            'slug' => 'pro',
            'description' => 'Full wallet access: EMI pay, withdrawals, booking adjust, priority support',
            'price' => 1499.00,
            'referral_pct_l1' => 20.00,
            'referral_pct_l2' => 5.00,
            'features' => json_encode([
                'emi_payment' => true,
                'referral_earnings_view' => true,
                'withdrawal_request' => true,
                'booking_adjustment' => true,
                'priority_support' => true,
                'auto_emi_deduction' => true,
                'detailed_analytics' => true,
            ]),
            'sort_order' => 2,
        ],
        [
            'name' => 'Premium Wallet',
            'slug' => 'premium',
            'description' => 'Premium wallet with all features + dedicated manager + VIP perks',
            'price' => 2999.00,
            'referral_pct_l1' => 20.00,
            'referral_pct_l2' => 5.00,
            'features' => json_encode([
                'emi_payment' => true,
                'referral_earnings_view' => true,
                'withdrawal_request' => true,
                'booking_adjustment' => true,
                'priority_support' => true,
                'auto_emi_deduction' => true,
                'detailed_analytics' => true,
                'dedicated_manager' => true,
                'vip_offers' => true,
                'zero_withdrawal_fee' => true,
            ]),
            'sort_order' => 3,
        ],
    ];

    foreach ($packages as $pkg) {
        // Check if exists
        $stmt = $conn->prepare("SELECT id FROM wallet_activation_packages WHERE slug = ? LIMIT 1");
        $stmt->execute([$pkg['slug']]);
        if (!$stmt->fetch()) {
            $insertData = array_merge($pkg, [
                'is_active' => 1,
                'tenant_id' => 1,
            ]);
            $columns = implode(', ', array_keys($insertData));
            $placeholders = implode(', ', array_fill(0, count($insertData), '?'));
            $stmt = $conn->prepare("INSERT INTO wallet_activation_packages ($columns) VALUES ($placeholders)");
            $stmt->execute(array_values($insertData));
            echo "✓ Package inserted: {$pkg['name']}\n";
        } else {
            echo "- Package exists: {$pkg['name']}\n";
        }
    }

    echo "\n✅ Wallet activation packages system created successfully!\n";

} catch (Throwable $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}