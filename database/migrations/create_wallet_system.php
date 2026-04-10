<?php

/**
 * Wallet System Migration
 * Creates tables for wallet points, transactions, and EMI integration
 */

// Database configuration
$host = 'localhost';
$port = 3307;
$dbname = 'apsdreamhome';
$username = 'root';
$password = '';

try {
    // Connect to MySQL
    $pdo = new PDO("mysql:host=$host;port=$port", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "✅ Connected to MySQL successfully\n";
    echo "📊 Database: $dbname\n\n";

    $pdo->exec("USE $dbname");
    echo "✅ Using database: $dbname\n\n";

    // ============================================
    // 1. Create wallet_points table
    // ============================================
    echo "Creating wallet_points table...\n";

    $createWalletPoints = "
        CREATE TABLE IF NOT EXISTS wallet_points (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            points_balance DECIMAL(10, 2) DEFAULT 0.00,
            total_earned DECIMAL(10, 2) DEFAULT 0.00,
            total_used DECIMAL(10, 2) DEFAULT 0.00,
            total_transferred_to_emi DECIMAL(10, 2) DEFAULT 0.00,
            referral_earnings DECIMAL(10, 2) DEFAULT 0.00,
            commission_earnings DECIMAL(10, 2) DEFAULT 0.00,
            bonus_earnings DECIMAL(10, 2) DEFAULT 0.00,
            status ENUM('active', 'frozen', 'blocked') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_user_id (user_id),
            INDEX idx_status (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";

    $pdo->exec($createWalletPoints);
    echo "✅ wallet_points table created\n\n";

    // Add foreign key constraint
    echo "Adding foreign key constraint to wallet_points...\n";
    $addFKWalletPoints = "
        ALTER TABLE wallet_points
        ADD CONSTRAINT fk_wallet_points_user_id
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;
    ";
    try {
        $pdo->exec($addFKWalletPoints);
        echo "✅ Foreign key constraint added to wallet_points\n\n";
    } catch (PDOException $e) {
        echo "⚠️  Foreign key constraint already exists or failed: " . $e->getMessage() . "\n\n";
    }

    // ============================================
    // 2. Create wallet_transactions table
    // ============================================
    echo "Creating wallet_transactions table...\n";

    $createWalletTransactions = "
        CREATE TABLE IF NOT EXISTS wallet_transactions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            transaction_type ENUM('credit', 'debit', 'transfer') NOT NULL,
            transaction_category ENUM('referral', 'commission', 'bonus', 'emi_transfer', 'withdrawal', 'adjustment') NOT NULL,
            amount DECIMAL(10, 2) NOT NULL,
            balance_before DECIMAL(10, 2) NOT NULL,
            balance_after DECIMAL(10, 2) NOT NULL,
            description TEXT,
            reference_id INT NULL,
            reference_type VARCHAR(50) NULL,
            related_user_id INT NULL,
            status ENUM('pending', 'completed', 'failed', 'cancelled') DEFAULT 'completed',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_user_id (user_id),
            INDEX idx_transaction_type (transaction_type),
            INDEX idx_transaction_category (transaction_category),
            INDEX idx_status (status),
            INDEX idx_created_at (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";

    $pdo->exec($createWalletTransactions);
    echo "✅ wallet_transactions table created\n\n";

    // Add foreign key constraint
    echo "Adding foreign key constraint to wallet_transactions...\n";
    $addFKWalletTransactions = "
        ALTER TABLE wallet_transactions
        ADD CONSTRAINT fk_wallet_transactions_user_id
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;
    ";
    try {
        $pdo->exec($addFKWalletTransactions);
        echo "✅ Foreign key constraint added to wallet_transactions\n\n";
    } catch (PDOException $e) {
        echo "⚠️  Foreign key constraint already exists or failed: " . $e->getMessage() . "\n\n";
    }

    // ============================================
    // 3. Create referral_rewards table
    // ============================================
    echo "Creating referral_rewards table...\n";

    $createReferralRewards = "
        CREATE TABLE IF NOT EXISTS referral_rewards (
            id INT AUTO_INCREMENT PRIMARY KEY,
            referrer_id INT NOT NULL,
            referred_id INT NOT NULL,
            reward_amount DECIMAL(10, 2) NOT NULL,
            reward_type ENUM('points', 'cash', 'discount', 'bonus') DEFAULT 'points',
            reward_percentage DECIMAL(5, 2) DEFAULT 0.00,
            referral_code VARCHAR(20) NOT NULL,
            status ENUM('pending', 'credited', 'expired', 'reversed') DEFAULT 'pending',
            credited_at TIMESTAMP NULL,
            expires_at TIMESTAMP NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_referrer_id (referrer_id),
            INDEX idx_referred_id (referred_id),
            INDEX idx_referral_code (referral_code),
            INDEX idx_status (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";

    $pdo->exec($createReferralRewards);
    echo "✅ referral_rewards table created\n\n";

    // Add foreign key constraints
    echo "Adding foreign key constraints to referral_rewards...\n";
    try {
        $pdo->exec("ALTER TABLE referral_rewards ADD CONSTRAINT fk_referral_rewards_referrer_id FOREIGN KEY (referrer_id) REFERENCES users(id) ON DELETE CASCADE");
        $pdo->exec("ALTER TABLE referral_rewards ADD CONSTRAINT fk_referral_rewards_referred_id FOREIGN KEY (referred_id) REFERENCES users(id) ON DELETE CASCADE");
        echo "✅ Foreign key constraints added to referral_rewards\n\n";
    } catch (PDOException $e) {
        echo "⚠️  Foreign key constraints already exist or failed: " . $e->getMessage() . "\n\n";
    }

    // ============================================
    // 4. Create wallet_emi_transfers table
    // ============================================
    echo "Creating wallet_emi_transfers table...\n";

    $createWalletEmiTransfers = "
        CREATE TABLE IF NOT EXISTS wallet_emi_transfers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            emi_id INT NOT NULL,
            emi_amount DECIMAL(10, 2) NOT NULL,
            wallet_amount_used DECIMAL(10, 2) NOT NULL,
            transaction_id INT NOT NULL,
            transfer_status ENUM('pending', 'completed', 'failed', 'reversed') DEFAULT 'pending',
            transferred_at TIMESTAMP NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_user_id (user_id),
            INDEX idx_emi_id (emi_id),
            INDEX idx_transfer_status (transfer_status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";

    $pdo->exec($createWalletEmiTransfers);
    echo "✅ wallet_emi_transfers table created\n\n";

    // Add foreign key constraint
    echo "Adding foreign key constraint to wallet_emi_transfers...\n";
    $addFKWalletEmi = "
        ALTER TABLE wallet_emi_transfers
        ADD CONSTRAINT fk_wallet_emi_transfers_user_id
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;
    ";
    try {
        $pdo->exec($addFKWalletEmi);
        echo "✅ Foreign key constraint added to wallet_emi_transfers\n\n";
    } catch (PDOException $e) {
        echo "⚠️  Foreign key constraint already exists or failed: " . $e->getMessage() . "\n\n";
    }

    // ============================================
    // 5. Add wallet_balance column to users table
    // ============================================
    echo "Adding wallet_balance column to users table...\n";

    $alterUsers = "
        ALTER TABLE users 
        ADD COLUMN IF NOT EXISTS wallet_balance DECIMAL(10, 2) DEFAULT 0.00,
        ADD INDEX IF NOT EXISTS idx_wallet_balance (wallet_balance);
    ";

    $pdo->exec($alterUsers);
    echo "✅ wallet_balance column added to users table\n\n";

    // ============================================
    // 6. Insert default wallet configuration
    // ============================================
    echo "Creating wallet_configuration table...\n";

    $createWalletConfig = "
        CREATE TABLE IF NOT EXISTS wallet_configuration (
            id INT AUTO_INCREMENT PRIMARY KEY,
            config_key VARCHAR(100) UNIQUE NOT NULL,
            config_value TEXT NOT NULL,
            config_type ENUM('string', 'number', 'boolean', 'json') DEFAULT 'string',
            description TEXT,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";

    $pdo->exec($createWalletConfig);
    echo "✅ wallet_configuration table created\n\n";

    // Insert default configuration
    echo "Inserting default wallet configuration...\n";

    $defaultConfig = [
        ['referral_customer_points', '100', 'number', 'Points earned when customer refers customer'],
        ['referral_customer_discount', '5', 'number', 'Discount percentage for customer with referral code'],
        ['referral_associate_points', '200', 'number', 'Points earned when associate refers customer'],
        ['referral_agent_points', '250', 'number', 'Points earned when agent refers customer'],
        ['referral_commission_percentage', '2', 'number', 'Commission percentage for referrals'],
        ['wallet_to_emi_enabled', 'true', 'boolean', 'Enable wallet to EMI transfer'],
        ['minimum_wallet_transfer', '500', 'number', 'Minimum wallet points for EMI transfer'],
        ['point_to_rupee_conversion', '1', 'number', 'Conversion rate: 1 point = X rupees'],
        ['tax_savings_enabled', 'true', 'boolean', 'Enable tax savings on wallet transactions'],
    ];

    $stmt = $pdo->prepare("INSERT IGNORE INTO wallet_configuration (config_key, config_value, config_type, description) VALUES (?, ?, ?, ?)");
    foreach ($defaultConfig as $config) {
        $stmt->execute($config);
    }
    echo "✅ Default wallet configuration inserted\n\n";

    // ============================================
    // 7. Create wallet for existing users
    // ============================================
    echo "Creating wallet entries for existing users...\n";

    $stmt = $pdo->query("SELECT id FROM users");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $insertWallet = $pdo->prepare("INSERT IGNORE INTO wallet_points (user_id, points_balance, total_earned, status) VALUES (?, 0.00, 0.00, 'active')");

    foreach ($users as $user) {
        $insertWallet->execute([$user['id']]);
    }

    echo "✅ Wallet entries created for " . count($users) . " existing users\n\n";

    echo "🎉 Wallet System Setup Complete!\n\n";
    echo "📊 Summary:\n";
    echo "• Created wallet_points table\n";
    echo "• Created wallet_transactions table\n";
    echo "• Created referral_rewards table\n";
    echo "• Created wallet_emi_transfers table\n";
    echo "• Created wallet_configuration table\n";
    echo "• Added wallet_balance column to users table\n";
    echo "• Inserted default wallet configuration\n";
    echo "• Created wallet entries for existing users\n\n";

    echo "✅ System is ready for wallet and referral system!\n";
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
