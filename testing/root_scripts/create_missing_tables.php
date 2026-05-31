<?php
/**
 * Create All Missing Tables
 * Phase 1 Critical Setup
 */

require __DIR__ . '/config/bootstrap.php';

$pdo = \App\Core\Database::getInstance()->getConnection();

echo "========================================\n";
echo "  APS DREAM HOME - TABLE SETUP\n";
echo "========================================\n\n";

$tablesCreated = 0;
$errors = [];

// 1. VISITS TABLE - For site visits
$sql = "CREATE TABLE IF NOT EXISTS visits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    lead_id INT,
    customer_id INT,
    property_id INT,
    agent_id INT,
    scheduled_date DATETIME,
    status ENUM('scheduled', 'completed', 'cancelled', 'no_show') DEFAULT 'scheduled',
    notes TEXT,
    feedback TEXT,
    rating INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_lead (lead_id),
    INDEX idx_customer (customer_id),
    INDEX idx_property (property_id),
    INDEX idx_agent (agent_id),
    INDEX idx_status (status),
    INDEX idx_date (scheduled_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

try {
    $pdo->exec($sql);
    echo "✅ visits table created\n";
    $tablesCreated++;
} catch (Exception $e) {
    $errors[] = "visits: " . $e->getMessage();
}

// 2. SALES TABLE - For sales records
$sql = "CREATE TABLE IF NOT EXISTS sales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT,
    property_id INT,
    customer_id INT,
    agent_id INT,
    associate_id INT,
    sale_date DATE,
    sale_amount DECIMAL(12,2),
    commission_amount DECIMAL(12,2),
    payment_status ENUM('pending', 'partial', 'completed') DEFAULT 'pending',
    status ENUM('active', 'cancelled', 'refunded') DEFAULT 'active',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_booking (booking_id),
    INDEX idx_property (property_id),
    INDEX idx_customer (customer_id),
    INDEX idx_agent (agent_id),
    INDEX idx_associate (associate_id),
    INDEX idx_date (sale_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

try {
    $pdo->exec($sql);
    echo "✅ sales table created\n";
    $tablesCreated++;
} catch (Exception $e) {
    $errors[] = "sales: " . $e->getMessage();
}

// 3. LEAD_SCORING TABLE - For lead scoring
$sql = "CREATE TABLE IF NOT EXISTS lead_scoring (
    id INT AUTO_INCREMENT PRIMARY KEY,
    lead_id INT NOT NULL,
    score INT DEFAULT 0,
    breakdown_json TEXT,
    calculated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_lead (lead_id),
    INDEX idx_score (score),
    INDEX idx_calculated (calculated_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

try {
    $pdo->exec($sql);
    echo "✅ lead_scoring table created\n";
    $tablesCreated++;
} catch (Exception $e) {
    $errors[] = "lead_scoring: " . $e->getMessage();
}

// 4. LEAD_SCORING_HISTORY TABLE - For score history
$sql = "CREATE TABLE IF NOT EXISTS lead_scoring_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    lead_id INT NOT NULL,
    score INT DEFAULT 0,
    calculated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_lead (lead_id),
    INDEX idx_calculated (calculated_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

try {
    $pdo->exec($sql);
    echo "✅ lead_scoring_history table created\n";
    $tablesCreated++;
} catch (Exception $e) {
    $errors[] = "lead_scoring_history: " . $e->getMessage();
}

// 5. WALLET_TRANSACTIONS TABLE - For wallet system
$sql = "CREATE TABLE IF NOT EXISTS wallet_transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    user_type ENUM('customer', 'associate', 'agent', 'employee') DEFAULT 'associate',
    type ENUM('credit', 'debit') NOT NULL,
    amount DECIMAL(12,2) NOT NULL,
    balance DECIMAL(12,2) NOT NULL,
    description TEXT,
    reference_type VARCHAR(50),
    reference_id INT,
    status ENUM('pending', 'completed', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user (user_id, user_type),
    INDEX idx_type (type),
    INDEX idx_status (status),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

try {
    $pdo->exec($sql);
    echo "✅ wallet_transactions table created\n";
    $tablesCreated++;
} catch (Exception $e) {
    $errors[] = "wallet_transactions: " . $e->getMessage();
}

// 6. COMMISSION_RULES TABLE - For MLM commission rules
$sql = "CREATE TABLE IF NOT EXISTS commission_rules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    level INT DEFAULT 1,
    percentage DECIMAL(5,2) DEFAULT 0.00,
    fixed_amount DECIMAL(12,2) DEFAULT 0.00,
    min_sale_amount DECIMAL(12,2) DEFAULT 0.00,
    max_sale_amount DECIMAL(12,2) DEFAULT 999999999.99,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_level (level),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

try {
    $pdo->exec($sql);
    echo "✅ commission_rules table created\n";
    $tablesCreated++;
} catch (Exception $e) {
    $errors[] = "commission_rules: " . $e->getMessage();
}

// 7. COMMISSIONS TABLE - For commission records
$sql = "CREATE TABLE IF NOT EXISTS commissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sale_id INT,
    user_id INT NOT NULL,
    user_type ENUM('associate', 'agent', 'employee') DEFAULT 'associate',
    level INT DEFAULT 1,
    sale_amount DECIMAL(12,2),
    commission_amount DECIMAL(12,2),
    percentage DECIMAL(5,2),
    status ENUM('pending', 'approved', 'paid', 'cancelled') DEFAULT 'pending',
    paid_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_sale (sale_id),
    INDEX idx_user (user_id, user_type),
    INDEX idx_status (status),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

try {
    $pdo->exec($sql);
    echo "✅ commissions table created\n";
    $tablesCreated++;
} catch (Exception $e) {
    $errors[] = "commissions: " . $e->getMessage();
}

// 8. PAYOUTS TABLE - For payout requests
$sql = "CREATE TABLE IF NOT EXISTS payouts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    user_type ENUM('associate', 'agent', 'employee') DEFAULT 'associate',
    amount DECIMAL(12,2) NOT NULL,
    status ENUM('pending', 'approved', 'rejected', 'processing', 'completed') DEFAULT 'pending',
    bank_name VARCHAR(100),
    account_number VARCHAR(50),
    ifsc_code VARCHAR(20),
    account_holder_name VARCHAR(100),
    transaction_id VARCHAR(100),
    notes TEXT,
    requested_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    processed_at TIMESTAMP NULL,
    INDEX idx_user (user_id, user_type),
    INDEX idx_status (status),
    INDEX idx_requested (requested_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

try {
    $pdo->exec($sql);
    echo "✅ payouts table created\n";
    $tablesCreated++;
} catch (Exception $e) {
    $errors[] = "payouts: " . $e->getMessage();
}

// 9. NETWORK_TREE TABLE - For MLM genealogy
$sql = "CREATE TABLE IF NOT EXISTS network_tree (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    user_type ENUM('associate', 'agent', 'customer') DEFAULT 'associate',
    parent_id INT,
    level INT DEFAULT 1,
    position ENUM('left', 'right') DEFAULT 'left',
    left_child_id INT,
    right_child_id INT,
    total_downline INT DEFAULT 0,
    total_commission DECIMAL(12,2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_user (user_id, user_type),
    INDEX idx_parent (parent_id),
    INDEX idx_level (level),
    INDEX idx_position (position)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

try {
    $pdo->exec($sql);
    echo "✅ network_tree table created\n";
    $tablesCreated++;
} catch (Exception $e) {
    $errors[] = "network_tree: " . $e->getMessage();
}

// 10. REFERRALS TABLE - For tracking referrals
$sql = "CREATE TABLE IF NOT EXISTS referrals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    referrer_id INT NOT NULL,
    referred_id INT NOT NULL,
    referrer_type ENUM('associate', 'agent', 'customer') DEFAULT 'associate',
    referred_type ENUM('associate', 'agent', 'customer') DEFAULT 'associate',
    referral_code VARCHAR(50),
    status ENUM('pending', 'active', 'inactive') DEFAULT 'pending',
    commission_earned DECIMAL(12,2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    activated_at TIMESTAMP NULL,
    UNIQUE KEY unique_referral (referrer_id, referred_id),
    INDEX idx_referrer (referrer_id),
    INDEX idx_referred (referred_id),
    INDEX idx_code (referral_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

try {
    $pdo->exec($sql);
    echo "✅ referrals table created\n";
    $tablesCreated++;
} catch (Exception $e) {
    $errors[] = "referrals: " . $e->getMessage();
}

// 11. PROPERTY_IMAGES TABLE - For property images
$sql = "CREATE TABLE IF NOT EXISTS property_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    property_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    thumbnail_path VARCHAR(255),
    caption VARCHAR(255),
    is_primary TINYINT(1) DEFAULT 0,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_property (property_id),
    INDEX idx_primary (is_primary)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

try {
    $pdo->exec($sql);
    echo "✅ property_images table created\n";
    $tablesCreated++;
} catch (Exception $e) {
    $errors[] = "property_images: " . $e->getMessage();
}

// 12. LEAD_ENGAGEMENT_METRICS TABLE - For engagement tracking
$sql = "CREATE TABLE IF NOT EXISTS lead_engagement_metrics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    lead_id INT NOT NULL,
    engagement_type ENUM('view', 'click', 'form_submit', 'email_open', 'call') NOT NULL,
    engagement_count INT DEFAULT 1,
    last_engagement_at TIMESTAMP,
    time_spent INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_lead (lead_id),
    INDEX idx_type (engagement_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

try {
    $pdo->exec($sql);
    echo "✅ lead_engagement_metrics table created\n";
    $tablesCreated++;
} catch (Exception $e) {
    $errors[] = "lead_engagement_metrics: " . $e->getMessage();
}

echo "\n========================================\n";
echo "  RESULT: $tablesCreated tables created\n";
echo "========================================\n";

if (!empty($errors)) {
    echo "\n❌ ERRORS:\n";
    foreach ($errors as $error) {
        echo "  - $error\n";
    }
}

echo "\n✅ Critical tables setup complete!\n";
echo "Next: Run setup_rbac_menu.php for menu system\n";
