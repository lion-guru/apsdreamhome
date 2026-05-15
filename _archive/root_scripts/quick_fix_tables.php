<?php
require __DIR__ . '/config/bootstrap.php';

$pdo = \App\Core\Database::getInstance()->getConnection();

// Check and create tables one by one
$tables = [
    'wallet_points' => "CREATE TABLE IF NOT EXISTS wallet_points (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        points_balance DECIMAL(12,2) DEFAULT 0.00,
        total_earned DECIMAL(12,2) DEFAULT 0.00,
        total_used DECIMAL(12,2) DEFAULT 0.00,
        total_transferred_to_emi DECIMAL(12,2) DEFAULT 0.00,
        referral_earnings DECIMAL(12,2) DEFAULT 0.00,
        commission_earnings DECIMAL(12,2) DEFAULT 0.00,
        bonus_earnings DECIMAL(12,2) DEFAULT 0.00,
        status ENUM('active', 'inactive') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY unique_user (user_id)
    )",
    
    'wallet_transactions' => "CREATE TABLE IF NOT EXISTS wallet_transactions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        transaction_type ENUM('credit', 'debit') NOT NULL,
        transaction_category ENUM('referral', 'commission', 'bonus', 'payout', 'transfer', 'emi', 'other') DEFAULT 'other',
        amount DECIMAL(12,2) NOT NULL,
        balance_before DECIMAL(12,2) DEFAULT 0.00,
        balance_after DECIMAL(12,2) DEFAULT 0.00,
        description TEXT,
        reference_id INT,
        reference_type VARCHAR(50),
        related_user_id INT,
        status ENUM('pending', 'completed', 'cancelled', 'failed') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    
    'referral_rewards' => "CREATE TABLE IF NOT EXISTS referral_rewards (
        id INT AUTO_INCREMENT PRIMARY KEY,
        referrer_id INT NOT NULL,
        referred_id INT NOT NULL,
        reward_amount DECIMAL(12,2) DEFAULT 0.00,
        reward_type ENUM('points', 'cash', 'percentage') DEFAULT 'points',
        reward_percentage DECIMAL(5,2) DEFAULT 0.00,
        referral_code VARCHAR(50),
        status ENUM('pending', 'credited', 'rejected') DEFAULT 'pending',
        credited_at TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )"
];

foreach ($tables as $name => $sql) {
    try {
        $pdo->exec($sql);
        echo "✓ $name created or already exists\n";
    } catch (Exception $e) {
        echo "✗ $name error: " . $e->getMessage() . "\n";
    }
}

echo "\nDone!\n";
