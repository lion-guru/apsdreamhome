<?php
/**
 * Check and Create Missing Tables for User Registration
 */

require __DIR__ . '/config/bootstrap.php';

$pdo = \App\Core\Database::getInstance()->getConnection();

echo "=== CHECKING TABLES FOR REGISTRATION ===\n\n";

$requiredTables = [
    'users' => "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        customer_id VARCHAR(50) UNIQUE,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) UNIQUE NOT NULL,
        phone VARCHAR(20),
        password VARCHAR(255) NOT NULL,
        referral_code VARCHAR(50),
        referred_by INT,
        user_type ENUM('customer', 'associate', 'agent', 'employee', 'admin') DEFAULT 'customer',
        role ENUM('super_admin', 'admin', 'manager', 'agent', 'associate', 'employee', 'user') DEFAULT 'user',
        status ENUM('active', 'inactive', 'pending') DEFAULT 'active',
        email_verified TINYINT(1) DEFAULT 0,
        phone_verified TINYINT(1) DEFAULT 0,
        last_login DATETIME,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_email (email),
        INDEX idx_phone (phone),
        INDEX idx_referral (referral_code),
        INDEX idx_user_type (user_type),
        INDEX idx_status (status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    
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
        UNIQUE KEY unique_user (user_id),
        INDEX idx_status (status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    
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
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_user (user_id),
        INDEX idx_type (transaction_type),
        INDEX idx_category (transaction_category),
        INDEX idx_status (status),
        INDEX idx_created (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    
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
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_referrer (referrer_id),
        INDEX idx_referred (referred_id),
        INDEX idx_status (status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    
    'user_profiles' => "CREATE TABLE IF NOT EXISTS user_profiles (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        avatar VARCHAR(255),
        date_of_birth DATE,
        gender ENUM('male', 'female', 'other'),
        address TEXT,
        city VARCHAR(100),
        state VARCHAR(100),
        pincode VARCHAR(20),
        country VARCHAR(100) DEFAULT 'India',
        occupation VARCHAR(100),
        company VARCHAR(100),
        income_range VARCHAR(50),
        investment_budget VARCHAR(50),
        property_interest VARCHAR(100),
        preferred_location VARCHAR(255),
        emergency_contact_name VARCHAR(100),
        emergency_contact_phone VARCHAR(20),
        pan_number VARCHAR(20),
        aadhar_number VARCHAR(20),
        kyc_status ENUM('pending', 'verified', 'rejected') DEFAULT 'pending',
        kyc_documents TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY unique_user (user_id),
        INDEX idx_kyc (kyc_status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    
    'user_activity_logs' => "CREATE TABLE IF NOT EXISTS user_activity_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        activity_type VARCHAR(50) NOT NULL,
        activity_description TEXT,
        ip_address VARCHAR(50),
        user_agent TEXT,
        reference_id INT,
        reference_type VARCHAR(50),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_user (user_id),
        INDEX idx_type (activity_type),
        INDEX idx_created (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
];

$created = 0;
$existing = 0;

foreach ($requiredTables as $tableName => $sql) {
    $check = $pdo->query("SHOW TABLES LIKE '$tableName'")->fetch();
    if ($check) {
        echo "✓ $tableName - EXISTS\n";
        $existing++;
    } else {
        echo "✗ $tableName - CREATING...\n";
        try {
            $pdo->exec($sql);
            echo "  ✓ $tableName created successfully\n";
            $created++;
        } catch (Exception $e) {
            echo "  ✗ Error: " . $e->getMessage() . "\n";
        }
    }
}

echo "\n=== RESULT ===\n";
echo "Existing: $existing tables\n";
echo "Created: $created tables\n";
echo "\n✅ Registration tables ready!\n";
