<?php
/**
 * Loyalty & Rewards Migration
 * Creates tables for customer loyalty program
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use App\Core\Database\Database;

echo "🚀 Creating Loyalty & Rewards Tables...\n\n";

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    // Loyalty points
    echo "💎 Creating loyalty_points table...\n";
    $pdo->exec("CREATE TABLE IF NOT EXISTS loyalty_points (
        id BIGINT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        user_type ENUM('customer', 'associate') NOT NULL DEFAULT 'customer',
        points INT NOT NULL DEFAULT 0,
        lifetime_points INT NOT NULL DEFAULT 0,
        redeemed_points INT NOT NULL DEFAULT 0,
        current_tier VARCHAR(20) DEFAULT 'bronze',
        tier_achieved_at TIMESTAMP NULL,
        referral_count INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY unique_user (user_id, user_type),
        INDEX idx_tier (current_tier),
        INDEX idx_points (points)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    
    // Points transactions
    echo "📊 Creating loyalty_transactions table...\n";
    $pdo->exec("CREATE TABLE IF NOT EXISTS loyalty_transactions (
        id BIGINT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        user_type ENUM('customer', 'associate') NOT NULL,
        transaction_type ENUM('earned', 'redeemed', 'bonus', 'expired', 'adjusted') NOT NULL,
        points INT NOT NULL,
        description VARCHAR(255) NOT NULL,
        reference_type VARCHAR(50) NULL,
        reference_id INT NULL,
        balance_after INT NOT NULL,
        expiry_date DATE NULL,
        is_expired TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_user (user_id, user_type),
        INDEX idx_type (transaction_type),
        INDEX idx_created (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    
    // Rewards catalog
    echo "🎁 Creating rewards_catalog table...\n";
    $pdo->exec("CREATE TABLE IF NOT EXISTS rewards_catalog (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        description TEXT NULL,
        points_required INT NOT NULL,
        reward_type ENUM('discount', 'service', 'product', 'cashback') NOT NULL,
        reward_value DECIMAL(10,2) NULL,
        image_url VARCHAR(255) NULL,
        stock_quantity INT DEFAULT -1,
        is_limited TINYINT(1) DEFAULT 0,
        start_date DATE NULL,
        end_date DATE NULL,
        terms_conditions TEXT NULL,
        is_active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_type (reward_type),
        INDEX idx_active (is_active),
        INDEX idx_points (points_required)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    
    // Reward redemptions
    echo "🎫 Creating reward_redemptions table...\n";
    $pdo->exec("CREATE TABLE IF NOT EXISTS reward_redemptions (
        id BIGINT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        user_type ENUM('customer', 'associate') NOT NULL,
        reward_id INT NOT NULL,
        points_used INT NOT NULL,
        redemption_code VARCHAR(50) NOT NULL UNIQUE,
        status ENUM('pending', 'approved', 'delivered', 'cancelled', 'expired') DEFAULT 'pending',
        delivery_method ENUM('digital', 'physical', 'auto_credit') DEFAULT 'digital',
        delivery_details JSON NULL,
        used_at TIMESTAMP NULL,
        expires_at TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_user (user_id, user_type),
        INDEX idx_status (status),
        INDEX idx_code (redemption_code)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    
    // Tier benefits
    echo "⭐ Creating tier_benefits table...\n";
    $pdo->exec("CREATE TABLE IF NOT EXISTS tier_benefits (
        id INT AUTO_INCREMENT PRIMARY KEY,
        tier_name VARCHAR(20) NOT NULL,
        benefit_type VARCHAR(50) NOT NULL,
        benefit_name VARCHAR(100) NOT NULL,
        benefit_description TEXT NULL,
        benefit_value VARCHAR(50) NULL,
        is_active TINYINT(1) DEFAULT 1,
        sort_order INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_benefit (tier_name, benefit_type),
        INDEX idx_tier (tier_name)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    
    // Points earning rules
    echo "📋 Creating points_rules table...\n";
    $pdo->exec("CREATE TABLE IF NOT EXISTS points_rules (
        id INT AUTO_INCREMENT PRIMARY KEY,
        rule_name VARCHAR(100) NOT NULL,
        action_type VARCHAR(50) NOT NULL,
        points_earned INT NOT NULL,
        multiplier DECIMAL(3,2) DEFAULT 1.00,
        min_transaction_amount DECIMAL(15,2) NULL,
        max_points_per_day INT NULL,
        is_active TINYINT(1) DEFAULT 1,
        start_date DATE NULL,
        end_date DATE NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_action (action_type),
        INDEX idx_active (is_active)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    
    // Seed default tier benefits
    echo "\n🌱 Seeding tier benefits...\n";
    $benefits = [
        ['bronze', 'discount', 'Base Discount', 'Standard pricing', '0%'],
        ['bronze', 'booking', 'Free Consultation', 'Free property consultation', '1 session'],
        ['silver', 'discount', 'Silver Discount', '5% off on all bookings', '5%'],
        ['silver', 'emi', 'EMI Waiver', 'Zero EMI processing fee', '1 waiver/year'],
        ['gold', 'discount', 'Gold Discount', '10% off on all bookings', '10%'],
        ['gold', 'priority', 'Priority Support', '24/7 priority customer support', 'unlimited'],
        ['platinum', 'discount', 'Platinum Discount', '15% off on all bookings', '15%'],
        ['platinum', 'visits', 'Free Site Visits', 'Complimentary site visits', 'unlimited'],
        ['diamond', 'discount', 'Diamond Discount', '20% off on all bookings', '20%'],
        ['diamond', 'exclusive', 'Exclusive Previews', 'Early access to new projects', 'priority']
    ];
    
    $stmt = $pdo->prepare("INSERT IGNORE INTO tier_benefits 
        (tier_name, benefit_type, benefit_name, benefit_description, benefit_value)
        VALUES (?, ?, ?, ?, ?)");
    
    foreach ($benefits as $benefit) {
        $stmt->execute($benefit);
    }
    
    // Seed points earning rules
    echo "🎯 Seeding points earning rules...\n";
    $rules = [
        ['Booking Property', 'booking', 100, 1.00, 50000, 500],
        ['Site Visit Completion', 'site_visit', 50, 1.00, null, 200],
        ['Referral Signup', 'referral_signup', 200, 1.00, null, null],
        ['Referral Purchase', 'referral_purchase', 1000, 2.00, null, null],
        ['App Download', 'app_download', 100, 1.00, null, 100],
        ['Profile Completion', 'profile_complete', 50, 1.00, null, 50],
        ['Property Review', 'review', 25, 1.00, null, 100],
        ['Social Share', 'social_share', 10, 1.00, null, 50],
        ['Newsletter Subscription', 'newsletter', 20, 1.00, null, 20],
        ['Birthday Bonus', 'birthday', 500, 1.00, null, 500]
    ];
    
    $ruleStmt = $pdo->prepare("INSERT IGNORE INTO points_rules 
        (rule_name, action_type, points_earned, multiplier, min_transaction_amount, max_points_per_day)
        VALUES (?, ?, ?, ?, ?, ?)");
    
    foreach ($rules as $rule) {
        $ruleStmt->execute([$rule[0], $rule[1], $rule[2], $rule[3], $rule[4], $rule[5]]);
    }
    
    // Seed rewards catalog
    echo "🎁 Seeding rewards catalog...\n";
    $rewards = [
        ['₹500 Cashback', 'Get ₹500 credited to your wallet', 500, 'cashback', 500],
        ['₹1000 Cashback', 'Get ₹1000 credited to your wallet', 1000, 'cashback', 1000],
        ['₹2000 Cashback', 'Get ₹2000 credited to your wallet', 2000, 'cashback', 2000],
        ['Free Site Visit', 'Complimentary site visit with transport', 200, 'service', 0],
        ['Legal Consultation', 'Free legal consultation for property', 500, 'service', 0],
        ['Home Decor Voucher', '₹5000 home decor voucher', 2500, 'product', 5000],
        ['5% Booking Discount', 'Extra 5% off on next booking', 1000, 'discount', 5],
        ['10% Booking Discount', 'Extra 10% off on next booking', 2000, 'discount', 10],
        ['Priority Processing', 'Fast-track your booking process', 300, 'service', 0]
    ];
    
    $rewardStmt = $pdo->prepare("INSERT IGNORE INTO rewards_catalog 
        (name, description, points_required, reward_type, reward_value)
        VALUES (?, ?, ?, ?, ?)");
    
    foreach ($rewards as $reward) {
        $rewardStmt->execute($reward);
    }
    
    echo "\n✅ Loyalty & Rewards tables created successfully!\n";
    echo "📊 Summary:\n";
    echo "   - loyalty_points\n";
    echo "   - loyalty_transactions\n";
    echo "   - rewards_catalog (9 rewards seeded)\n";
    echo "   - reward_redemptions\n";
    echo "   - tier_benefits (10 benefits)\n";
    echo "   - points_rules (10 rules)\n";
    echo "\n🎉 Tiers: Bronze → Silver → Gold → Platinum → Diamond\n";
    
} catch (\Exception $e) {
    echo "\n❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
