<?php

// Include database configuration
require_once __DIR__ . '/../includes/db_config.php';

// Establish mysqli connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "Starting MLM database seeding...
";

// Function to execute SQL queries
function executeQuery($conn, $sql, $successMessage, $errorMessage) {
    if ($conn->query($sql) === TRUE) {
        echo $successMessage . "
";
    } else {
        echo $errorMessage . ": " . $conn->error . "
";
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
    level_difference_commission_percentage DECIMAL(5,2) NOT NULL,
    matching_bonus_percentage DECIMAL(5,2) NOT NULL,
    leadership_bonus_percentage DECIMAL(5,2) NOT NULL,
    performance_bonus_percentage DECIMAL(5,2) NOT NULL,
    joining_fee DECIMAL(10,2) DEFAULT 0.00,
    monthly_maintenance DECIMAL(10,2) DEFAULT 0.00,
    team_size_required INT(11) DEFAULT 0,
    direct_referrals_required INT(11) DEFAULT 0,
    monthly_target DECIMAL(10,2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);";
executeQuery($conn, $sql_create_mlm_levels, "Table 'mlm_levels' created or already exists.", "Error creating table 'mlm_levels'");

// 2. Insert default MLM levels (10 levels)
$sql_insert_mlm_levels = "
INSERT IGNORE INTO mlm_levels (level_name, level_order, direct_commission_percentage, team_commission_percentage, level_difference_commission_percentage, matching_bonus_percentage, leadership_bonus_percentage, performance_bonus_percentage, joining_fee, monthly_maintenance, team_size_required, direct_referrals_required, monthly_target) VALUES
('Associate', 1, 10.00, 2.00, 0.00, 0.00, 0.00, 0.00, 100.00, 10.00, 0, 0, 500.00),
('Bronze', 2, 12.00, 3.00, 1.00, 0.50, 0.00, 0.00, 150.00, 15.00, 5, 2, 1000.00),
('Silver', 3, 14.00, 4.00, 1.50, 1.00, 0.00, 0.00, 200.00, 20.00, 10, 3, 2000.00),
('Gold', 4, 16.00, 5.00, 2.00, 1.50, 0.50, 0.00, 250.00, 25.00, 20, 5, 4000.00),
('Platinum', 5, 18.00, 6.00, 2.50, 2.00, 1.00, 0.50, 300.00, 30.00, 40, 7, 8000.00),
('Diamond', 6, 20.00, 7.00, 3.00, 2.50, 1.50, 1.00, 350.00, 35.00, 80, 10, 15000.00),
('Crown', 7, 22.00, 8.00, 3.50, 3.00, 2.00, 1.50, 400.00, 40.00, 150, 12, 25000.00),
('Ambassador', 8, 24.00, 9.00, 4.00, 3.50, 2.50, 2.00, 450.00, 45.00, 250, 15, 40000.00),
('Royal Ambassador', 9, 26.00, 10.00, 4.50, 4.00, 3.00, 2.50, 500.00, 50.00, 400, 18, 60000.00),
('Global Director', 10, 28.00, 11.00, 5.00, 4.50, 3.50, 3.00, 550.00, 55.00, 600, 20, 100000.00);";
executeQuery($conn, $sql_insert_mlm_levels, "Default MLM levels inserted or already exist.", "Error inserting default MLM levels");

// 3. Create mlm_performance table
$sql_create_mlm_performance = "
CREATE TABLE IF NOT EXISTS mlm_performance (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NOT NULL,
    month_year VARCHAR(7) NOT NULL, -- e.g., '2023-10'
    current_rank_id INT(11) NOT NULL,
    total_sales_volume DECIMAL(15,2) DEFAULT 0.00,
    direct_sales_volume DECIMAL(15,2) DEFAULT 0.00,
    team_sales_volume DECIMAL(15,2) DEFAULT 0.00,
    direct_commission DECIMAL(15,2) DEFAULT 0.00,
    team_commission DECIMAL(15,2) DEFAULT 0.00,
    level_commission DECIMAL(15,2) DEFAULT 0.00,
    bonus_commission DECIMAL(15,2) DEFAULT 0.00,
    leadership_bonus DECIMAL(15,2) DEFAULT 0.00,
    performance_bonus DECIMAL(15,2) DEFAULT 0.00,
    rank_name VARCHAR(255) NOT NULL,
    monthly_target DECIMAL(15,2) DEFAULT 0.00,
    target_achieved BOOLEAN DEFAULT FALSE,
    is_rank_promoted BOOLEAN DEFAULT FALSE,
    promotion_date DATE NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY (user_id, month_year),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (current_rank_id) REFERENCES mlm_levels(id) ON DELETE RESTRICT
);";
executeQuery($conn, $sql_create_mlm_performance, "Table 'mlm_performance' created or already exists.", "Error creating table 'mlm_performance'");

// 4. Create mlm_rank_advancements table
$sql_create_mlm_rank_advancements = "
CREATE TABLE IF NOT EXISTS mlm_rank_advancements (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NOT NULL,
    from_rank_id INT(11) NULL,
    to_rank_id INT(11) NOT NULL,
    promotion_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    business_achieved DECIMAL(15,2) DEFAULT 0.00,
    team_size_achieved INT(11) DEFAULT 0,
    direct_referrals_achieved INT(11) DEFAULT 0,
    promotion_bonus DECIMAL(15,2) DEFAULT 0.00,
    is_fast_track BOOLEAN DEFAULT FALSE,
    fast_track_bonus DECIMAL(15,2) DEFAULT 0.00,
    recognition_award VARCHAR(255) NULL,
    certificate_issued BOOLEAN DEFAULT FALSE,
    certificate_date DATE NULL,
    requirements_met JSON NULL, -- Stores JSON of met requirements
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (from_rank_id) REFERENCES mlm_levels(id) ON DELETE SET NULL,
    FOREIGN KEY (to_rank_id) REFERENCES mlm_levels(id) ON DELETE RESTRICT
);";
executeQuery($conn, $sql_create_mlm_rank_advancements, "Table 'mlm_rank_advancements' created or already exists.", "Error creating table 'mlm_rank_advancements'");

// 5. Create mlm_special_bonuses table
$sql_create_mlm_special_bonuses = "
CREATE TABLE IF NOT EXISTS mlm_special_bonuses (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    bonus_name VARCHAR(255) NOT NULL UNIQUE,
    bonus_type ENUM('welcome', 'fast_start', 'leadership', 'loyalty', 'performance', 'seasonal', 'anniversary') NOT NULL,
    description TEXT NULL,
    amount DECIMAL(15,2) DEFAULT 0.00,
    percentage DECIMAL(5,2) DEFAULT 0.00,
    qualification_criteria JSON NULL, -- Stores JSON of criteria
    valid_from DATE NULL,
    valid_to DATE NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);";
executeQuery($conn, $sql_create_mlm_special_bonuses, "Table 'mlm_special_bonuses' created or already exists.", "Error creating table 'mlm_special_bonuses'");

// 6. Insert sample special bonuses
$special_bonuses = [
    ['Welcome Bonus', 'welcome', 'Bonus for new associates upon joining.', 50.00, 0.00, json_encode(['min_sales' => 100, 'within_days' => 30]), 'CURDATE()', 'DATE_ADD(CURDATE(), INTERVAL 1 YEAR)', TRUE],
    ['Fast Start Bonus', 'fast_start', 'Achieve certain sales within first 60 days.', 100.00, 0.00, json_encode(['min_direct_sales' => 500, 'within_days' => 60]), 'CURDATE()', 'DATE_ADD(CURDATE(), INTERVAL 1 YEAR)', TRUE],
    ['Leadership Pool Bonus', 'leadership', 'Share of company profits for top leaders.', 0.00, 1.00, json_encode(['min_rank' => 'Diamond', 'min_team_sales' => 10000]), 'CURDATE()', 'DATE_ADD(CURDATE(), INTERVAL 1 YEAR)', TRUE],
    ['Loyalty Bonus', 'loyalty', 'Bonus for long-term active associates.', 200.00, 0.00, json_encode(['min_active_months' => 12, 'min_sales_per_month' => 200]), 'CURDATE()', 'DATE_ADD(CURDATE(), INTERVAL 5 YEAR)', TRUE],
    ['Performance Bonus Q4', 'performance', 'Quarterly performance bonus based on team sales.', 0.00, 0.75, json_encode(['quarter' => 'Q4', 'min_team_sales' => 20000]), '2023-10-01', '2023-12-31', TRUE],
    ['Summer Seasonal Bonus', 'seasonal', 'Special bonus for sales during summer months.', 75.00, 0.00, json_encode(['month_range' => [6, 7, 8], 'min_direct_sales' => 300]), 'CURDATE()', 'DATE_ADD(CURDATE(), INTERVAL 3 MONTH)', TRUE],
    ['5 Year Anniversary Bonus', 'anniversary', 'Bonus for associates completing 5 years with the company.', 250.00, 0.00, json_encode(['years_with_company' => 5]), 'CURDATE()', 'DATE_ADD(CURDATE(), INTERVAL 1 YEAR)', TRUE]
  ];

  foreach ($special_bonuses as $bonus) {
    $sql_insert_special_bonuses = "
    INSERT IGNORE INTO mlm_special_bonuses (bonus_name, bonus_type, description, amount, percentage, qualification_criteria, valid_from, valid_to, is_active) VALUES
    ('" . $bonus[0] . "', '" . $bonus[1] . "', '" . $bonus[2] . "', " . $bonus[3] . ", " . $bonus[4] . ", '" . $bonus[5] . "', " . $bonus[6] . ", " . $bonus[7] . ", " . ($bonus[8] ? "TRUE" : "FALSE") . ");";
    executeQuery($conn, $sql_insert_special_bonuses, "Sample special bonuses inserted or already exist.", "Error inserting sample special bonuses");
  }

  // 7. Create mlm_rewards_recognition table
  $sql_create_mlm_rewards_recognition = "
  CREATE TABLE IF NOT EXISTS mlm_rewards_recognition (
      id INT(11) AUTO_INCREMENT PRIMARY KEY,
      reward_name VARCHAR(255) NOT NULL UNIQUE,
      reward_type ENUM('gadget', 'cash', 'travel', 'jewelry', 'car', 'house', 'recognition') NOT NULL,
      description TEXT NULL,
      value DECIMAL(15,2) DEFAULT 0.00,
      qualification_criteria JSON NULL, -- Stores JSON of criteria
      image_url VARCHAR(255) NULL,
      is_active BOOLEAN DEFAULT TRUE,
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
  );";
  executeQuery($conn, $sql_create_mlm_rewards_recognition, "Table 'mlm_rewards_recognition' created or already exists.", "Error creating table 'mlm_rewards_recognition'");

  // 8. Insert sample rewards and recognition
  $rewards_recognition = [
    ['Smartphone Reward', 'gadget', 'Latest model smartphone for achieving sales target.', 800.00, json_encode(['min_sales' => 10000, 'min_rank' => 'Gold']), 'https://example.com/smartphone.jpg', TRUE],
    ['Cash Bonus $500', 'cash', 'Direct cash bonus for exceptional performance.', 500.00, json_encode(['min_direct_sales' => 2000, 'within_month' => 1]), NULL, TRUE],
    ['Luxury Travel Voucher', 'travel', 'All-expenses-paid trip to an exotic location.', 5000.00, json_encode(['min_team_sales' => 50000, 'min_rank' => 'Diamond']), 'https://example.com/travel.jpg', TRUE],
    ['Diamond Ring', 'jewelry', 'Exclusive diamond ring for top performers.', 2500.00, json_encode(['min_team_sales' => 30000, 'min_direct_referrals' => 10]), 'https://example.com/diamond_ring.jpg', TRUE],
    ['Car Fund Contribution', 'car', 'Contribution towards a new car purchase.', 10000.00, json_encode(['min_rank' => 'Crown', 'min_consistent_sales' => 30000]), NULL, TRUE],
    ['House Down Payment', 'house', 'Significant contribution towards a house down payment.', 25000.00, json_encode(['min_rank' => 'Global Director', 'min_years_active' => 3]), NULL, TRUE],
    ['Hall of Fame Recognition', 'recognition', 'Public recognition in the company\'s Hall of Fame.', 0.00, json_encode(['lifetime_sales' => 1000000, 'min_rank' => 'Ambassador']), NULL, TRUE]
  ];

  foreach ($rewards_recognition as $reward) {
    $escaped_description = mysqli_real_escape_string($conn, $reward[2]);
    $sql_insert_rewards_recognition = "
    INSERT IGNORE INTO mlm_rewards_recognition (reward_name, reward_type, description, value, qualification_criteria, image_url, is_active) VALUES
    ('" . $reward[0] . "', '" . $reward[1] . "', '" . $escaped_description . "', " . $reward[3] . ", '" . $reward[4] . "', " . ($reward[5] ? "'" . $reward[5] . "'" : "NULL") . ", " . ($reward[6] ? "TRUE" : "FALSE") . ");";
    executeQuery($conn, $sql_insert_rewards_recognition, "Sample rewards and recognition inserted or already exist.", "Error inserting sample rewards and recognition");
  }

echo "MLM database seeding complete.
";

?>