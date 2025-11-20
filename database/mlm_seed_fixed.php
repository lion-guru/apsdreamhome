<?php
/**
 * MLM Database Seeding Script - Fixed Version
 * Seeds sample data for MLM system with proper SQL escaping
 */

// Include database configuration
require_once __DIR__ . '/../includes/config/config.php';

// Function to execute SQL queries with error handling
function executeQuery($conn, $sql, $successMessage, $errorMessage) {
    if ($conn->query($sql) === TRUE) {
        echo $successMessage . "\n";
    } else {
        echo $errorMessage . ": " . $conn->error . "\n";
    }
}

// 1. Create mlm_levels table
$sql_create_mlm_levels = "
CREATE TABLE IF NOT EXISTS mlm_levels (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    level_name VARCHAR(255) NOT NULL UNIQUE,
    level_order INT(11) NOT NULL UNIQUE,
    direct_commission_percentage DECIMAL(5,2) NOT NULL,
    team_commission_percentage DECIMAL(5,2) NOT NULL,
    min_personal_sales DECIMAL(12,2) DEFAULT 0.00,
    min_team_sales DECIMAL(12,2) DEFAULT 0.00,
    min_direct_referrals INT(11) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
";

// 2. Create mlm_users table
$sql_create_mlm_users = "
CREATE TABLE IF NOT EXISTS mlm_users (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NOT NULL,
    parent_id INT(11) DEFAULT NULL,
    level_id INT(11) DEFAULT NULL,
    sponsor_id INT(11) DEFAULT NULL,
    left_child_id INT(11) DEFAULT NULL,
    right_child_id INT(11) DEFAULT NULL,
    personal_sales DECIMAL(12,2) DEFAULT 0.00,
    team_sales DECIMAL(12,2) DEFAULT 0.00,
    direct_referrals INT(11) DEFAULT 0,
    total_commission DECIMAL(12,2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_id) REFERENCES mlm_users(id) ON DELETE SET NULL,
    FOREIGN KEY (level_id) REFERENCES mlm_levels(id) ON DELETE SET NULL,
    FOREIGN KEY (sponsor_id) REFERENCES users(id) ON DELETE SET NULL
);
";

// 3. Create mlm_transactions table
$sql_create_mlm_transactions = "
CREATE TABLE IF NOT EXISTS mlm_transactions (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NOT NULL,
    transaction_type ENUM('sale', 'commission', 'payout', 'adjustment') NOT NULL,
    amount DECIMAL(12,2) NOT NULL,
    description TEXT,
    related_user_id INT(11) DEFAULT NULL,
    commission_percentage DECIMAL(5,2) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (related_user_id) REFERENCES users(id) ON DELETE SET NULL
);
";

// 4. Create mlm_rewards table
$sql_create_mlm_rewards = "
CREATE TABLE IF NOT EXISTS mlm_rewards (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    type ENUM('cash', 'travel', 'jewelry', 'car', 'house', 'recognition') NOT NULL,
    description TEXT,
    value DECIMAL(12,2) DEFAULT 0.00,
    requirements JSON,
    image_url VARCHAR(500) DEFAULT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
";

// Sample data with properly escaped strings
$mlm_levels = [
    ['Bronze', 1, 5.00, 2.00, 1000.00, 5000.00, 5],
    ['Silver', 2, 7.00, 3.00, 5000.00, 25000.00, 10],
    ['Gold', 3, 10.00, 5.00, 10000.00, 50000.00, 15],
    ['Platinum', 4, 12.00, 7.00, 25000.00, 100000.00, 20],
    ['Diamond', 5, 15.00, 10.00, 50000.00, 250000.00, 25],
    ['Crown', 6, 18.00, 12.00, 100000.00, 500000.00, 30],
    ['Global Director', 7, 20.00, 15.00, 250000.00, 1000000.00, 50],
    ['Ambassador', 8, 25.00, 20.00, 500000.00, 2500000.00, 100]
];

$rewards_recognition = [
    ['Cash Bonus $500', 'cash', 'Direct cash bonus for exceptional performance.', 500.00, json_encode(['min_direct_sales' => 2000, 'within_month' => 1]), NULL, TRUE],
    ['Luxury Travel Voucher', 'travel', 'All-expenses-paid trip to an exotic location.', 5000.00, json_encode(['min_team_sales' => 50000, 'min_rank' => 'Diamond']), 'https://example.com/travel.jpg', TRUE],
    ['Diamond Ring', 'jewelry', 'Exclusive diamond ring for top performers.', 2500.00, json_encode(['min_team_sales' => 30000, 'min_direct_referrals' => 10]), 'https://example.com/diamond_ring.jpg', TRUE],
    ['Car Fund Contribution', 'car', 'Contribution towards a new car purchase.', 10000.00, json_encode(['min_rank' => 'Crown', 'min_consistent_sales' => 30000]), NULL, TRUE],
    ['House Down Payment', 'house', 'Significant contribution towards a house down payment.', 25000.00, json_encode(['min_rank' => 'Global Director', 'min_years_active' => 3]), NULL, TRUE],
    ['Hall of Fame Recognition', 'recognition', 'Public recognition in the company\'s Hall of Fame.', 0.00, json_encode(['lifetime_sales' => 1000000, 'min_rank' => 'Ambassador']), NULL, TRUE]
];

// Execute table creation
echo "Creating MLM tables...\n";
executeQuery($conn, $sql_create_mlm_levels, "✅ mlm_levels table created successfully", "❌ Error creating mlm_levels table");
executeQuery($conn, $sql_create_mlm_users, "✅ mlm_users table created successfully", "❌ Error creating mlm_users table");
executeQuery($conn, $sql_create_mlm_transactions, "✅ mlm_transactions table created successfully", "❌ Error creating mlm_transactions table");
executeQuery($conn, $sql_create_mlm_rewards, "✅ mlm_rewards table created successfully", "❌ Error creating mlm_rewards table");

// Insert sample data
echo "\nInserting sample MLM levels...\n";
foreach ($mlm_levels as $level) {
    $sql = "INSERT INTO mlm_levels (level_name, level_order, direct_commission_percentage, team_commission_percentage, min_personal_sales, min_team_sales, min_direct_referrals) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('siddiii', $level[0], $level[1], $level[2], $level[3], $level[4], $level[5], $level[6]);
    $stmt->execute() ? print("✅ Level '{$level[0]}' added\n") : print("❌ Error adding level '{$level[0]}'\n");
}

echo "\nInserting sample rewards...\n";
foreach ($rewards_recognition as $reward) {
    $sql = "INSERT INTO mlm_rewards (name, type, description, value, requirements, image_url, is_active) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sssdssi', $reward[0], $reward[1], $reward[2], $reward[3], $reward[4], $reward[5], $reward[6]);
    $stmt->execute() ? print("✅ Reward '{$reward[0]}' added\n") : print("❌ Error adding reward '{$reward[0]}'\n");
}

echo "\n🎉 MLM database seeding completed successfully!\n";
?>
