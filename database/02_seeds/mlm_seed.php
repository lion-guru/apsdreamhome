<?php
/**
 * Advanced MLM Seed Data - Professional MLM Company System
 * Comprehensive seeding for Multi-Level Marketing with international standards
 */

// Database connection
$host = 'localhost';
$dbname = 'apsdreamhome';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "🚀 Starting Professional MLM Company seeding process...\n";
    
    // Disable foreign key checks
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    
    // Clear existing MLM data
    $pdo->exec("TRUNCATE TABLE mlm_tree");
    $pdo->exec("TRUNCATE TABLE associate_levels");
    $pdo->exec("TRUNCATE TABLE mlm_commissions");
    $pdo->exec("DROP TABLE IF EXISTS `mlm_performance`;"); // Drop table to ensure fresh creation
    $pdo->exec("TRUNCATE TABLE mlm_rank_advancements");
    $pdo->exec("TRUNCATE TABLE associate_mlm");
    $pdo->exec("TRUNCATE TABLE mlm_commission_ledger");
    $pdo->exec("TRUNCATE TABLE mlm_rank_advancements");
    
    echo "✅ Existing MLM data cleaned successfully.\n";
    
    // Get existing users and associates
    $users = $pdo->query("SELECT id, name, email FROM users WHERE type IN ('agent', 'associate') ORDER BY id LIMIT 20")->fetchAll(PDO::FETCH_ASSOC);
    // Fetch associates
    $associates = $pdo->query("SELECT id, user_id, company_name, commission_rate FROM associates ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($users) < 5) {
        echo "❌ Not enough agents/associates found. Please run master_seed.php first.\n";
        exit;
    }
    
    echo "📊 Found " . count($users) . " users and " . count($associates) . " associates\n";
    
    // Define Professional MLM levels with international standards
    $mlm_levels = [
        1 => ['name' => 'Associate', 'commission' => 10.00, 'target' => 1000000, 'direct_bonus' => 5.00, 'level_bonus' => 0.00, 'description' => 'Entry level - Focus on personal sales and team building', 'min_business' => 0.00, 'max_business' => 500000.00, 'joining_fee' => 5000, 'monthly_maintenance' => 1000, 'team_size_required' => 0, 'direct_referrals' => 0],
        2 => ['name' => 'Senior Associate', 'commission' => 12.00, 'target' => 3500000, 'direct_bonus' => 7.00, 'level_bonus' => 2.00, 'description' => 'Mid level - Start building your team and earn team commissions', 'min_business' => 1000000.00, 'max_business' => 2000000.00, 'joining_fee' => 10000, 'monthly_maintenance' => 2000, 'team_size_required' => 5, 'direct_referrals' => 3],
        3 => ['name' => 'Team Leader', 'commission' => 15.00, 'target' => 7000000, 'direct_bonus' => 10.00, 'level_bonus' => 3.00, 'description' => 'Leadership level - Focus on team development and leadership', 'min_business' => 3500000.00, 'max_business' => 5000000.00, 'joining_fee' => 20000, 'monthly_maintenance' => 3000, 'team_size_required' => 15, 'direct_referrals' => 5],
        4 => ['name' => 'Manager', 'commission' => 18.00, 'target' => 15000000, 'direct_bonus' => 12.00, 'level_bonus' => 4.00, 'description' => 'Management level - Advanced team management and strategic planning', 'min_business' => 7000000.00, 'max_business' => 12000000.00, 'joining_fee' => 35000, 'monthly_maintenance' => 5000, 'team_size_required' => 30, 'direct_referrals' => 8],
        5 => ['name' => 'Senior Manager', 'commission' => 20.00, 'target' => 30000000, 'direct_bonus' => 15.00, 'level_bonus' => 5.00, 'description' => 'Senior leadership - Regional management and expansion', 'min_business' => 15000000.00, 'max_business' => 25000000.00, 'joining_fee' => 50000, 'monthly_maintenance' => 8000, 'team_size_required' => 60, 'direct_referrals' => 12],
        6 => ['name' => 'Vice President', 'commission' => 22.00, 'target' => 50000000, 'direct_bonus' => 18.00, 'level_bonus' => 6.00, 'description' => 'Executive level - Strategic leadership and business development', 'min_business' => 30000000.00, 'max_business' => 45000000.00, 'joining_fee' => 75000, 'monthly_maintenance' => 12000, 'team_size_required' => 100, 'direct_referrals' => 15],
        7 => ['name' => 'Director', 'commission' => 25.00, 'target' => 75000000, 'direct_bonus' => 20.00, 'level_bonus' => 7.00, 'description' => 'Top leadership - Company strategy and major decisions', 'min_business' => 50000000.00, 'max_business' => 70000000.00, 'joining_fee' => 100000, 'monthly_maintenance' => 15000, 'team_size_required' => 150, 'direct_referrals' => 20],
        8 => ['name' => 'Senior Director', 'commission' => 27.00, 'target' => 100000000, 'direct_bonus' => 22.00, 'level_bonus' => 8.00, 'description' => 'Elite leadership - National expansion and major partnerships', 'min_business' => 75000000.00, 'max_business' => 95000000.00, 'joining_fee' => 150000, 'monthly_maintenance' => 20000, 'team_size_required' => 250, 'direct_referrals' => 25],
        9 => ['name' => 'Executive Director', 'commission' => 30.00, 'target' => 150000000, 'direct_bonus' => 25.00, 'level_bonus' => 10.00, 'description' => 'Executive board - International business and global strategy', 'min_business' => 100000000.00, 'max_business' => 140000000.00, 'joining_fee' => 200000, 'monthly_maintenance' => 25000, 'team_size_required' => 400, 'direct_referrals' => 30],
        10 => ['name' => 'Global Director', 'commission' => 35.00, 'target' => 250000000, 'direct_bonus' => 30.00, 'level_bonus' => 15.00, 'description' => 'Global leadership - International operations and global expansion', 'min_business' => 150000000.00, 'max_business' => 999999999.00, 'joining_fee' => 300000, 'monthly_maintenance' => 30000, 'team_size_required' => 600, 'direct_referrals' => 40]
    ];
    
    // Create enhanced MLM Tree Structure with professional features
    echo "🌳 Creating MLM tree structure...\n";
    $mlm_tree_data = [];
    $associate_mlm_data = [];
    $mlm_commissions_data = [];
    
    // Level 1 - Root/CEO
    $root_user = $users[0];
    $root_associate = $associates[0];
    
    $mlm_tree_data[] = [
        'user_id' => $root_user['id'],
        'parent_id' => null,
        'level' => 1,
        'created_at' => date('Y-m-d H:i:s', strtotime('-6 months')),
        'activation_date' => date('Y-m-d H:i:s', strtotime('-6 months')),
        'status' => 'active',
        'is_paid' => 1,
        'total_earnings' => rand(50000, 500000) * 10,
        'monthly_target' => $mlm_levels[10]['target'],
        'current_month_sales' => rand(500000, 2000000) * 10
    ];
    
    $associate_mlm_data[] = [
        'associate_id' => $root_associate['id'],
        'level' => 1,
        'commission_rate' => 5,
        'total_earnings' => 150000,
        'current_balance' => 75000,
        'total_sales' => 3000000,
        'team_size' => 45,
        'rank' => 'Global Director',
        'status' => 'active',
        'join_date' => date('Y-m-d', strtotime('-6 months')),
        'achievement_date' => date('Y-m-d', strtotime('-2 months'))
    ];
    
    // Create 10-level hierarchy
    $current_parent_index = 0;
    $users_per_level = [1, 2, 4, 8, 16, 32, 64, 128, 256, 512];
    
    for ($level = 2; $level <= 10; $level++) {
        $users_in_level = min($users_per_level[$level-1], count($users) - 1);
        
        for ($i = 0; $i < $users_in_level && $current_parent_index < count($users); $i++) {
            $user_index = ($level - 2) * $users_in_level + $i + 1;
            if ($user_index >= count($users)) break;
            
            $user = $users[$user_index];
            $parent = $mlm_tree_data[$current_parent_index];
            
            $mlm_tree_data[] = [
                'user_id' => $user['id'],
                'parent_id' => $parent['user_id'],
                'level' => $level,
                'created_at' => date('Y-m-d H:i:s', strtotime('-' . (10-$level) . ' months'))
            ];
            
            // Create associate MLM data
            if ($user_index < count($associates)) {
                $associate = $associates[$user_index];
                $commission_rate = $mlm_levels[$level]['commission'];
                $total_earnings = rand(10000, 500000);
                $total_sales = $total_earnings * 20; // Assuming 5% average commission
                
                $associate_mlm_data[] = [
                    'associate_id' => $associate['id'],
                    'level' => $level,
                    'commission_rate' => $commission_rate,
                    'total_earnings' => $total_earnings,
                    'current_balance' => $total_earnings * 0.4, // 40% balance
                    'total_sales' => $total_sales,
                    'team_size' => rand(0, 50),
                    'rank' => $mlm_levels[$level]['name'],
                    'status' => 'active',
                    'join_date' => date('Y-m-d', strtotime('-' . (10-$level) . ' months')),
                    'achievement_date' => date('Y-m-d', strtotime('-' . rand(1, 3) . ' months'))
                ];
            }
            
            $current_parent_index++;
        }
    }
    
    // Insert MLM Tree data
    $mlm_tree_stmt = $pdo->prepare("INSERT INTO mlm_tree (user_id, parent_id, level, join_date) VALUES (?, ?, ?, ?)");
    
    foreach ($mlm_tree_data as $data) {
        $mlm_tree_stmt->execute([
            $data['user_id'], $data['parent_id'], $data['level'], $data['created_at']
        ]);
    }
    
    echo "✅ MLM Tree data inserted: " . count($mlm_tree_data) . " records\n";
    
    // Insert Associate Levels data (MLM levels)
    $associate_levels_stmt = $pdo->prepare("INSERT INTO associate_levels (name, commission_percent, direct_referral_bonus, level_bonus, reward_description, min_team_size, status, min_business, max_business) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    foreach ($mlm_levels as $level => $data) {
        $associate_levels_stmt->execute([
            $data['name'], $data['commission'], $data['direct_bonus'], 
            $data['level_bonus'], $data['description'], $data['target'],
            'active', $data['min_business'], $data['max_business']
        ]);
    }
    
    echo "✅ Associate Levels data inserted: " . count($mlm_levels) . " records\n";
    
    // Create sample commission data
    $commission_types = ['direct_sale', 'team_bonus', 'level_bonus', 'rank_bonus', 'achievement_bonus'];
    $commission_status = ['pending', 'approved', 'paid'];
    
    $commissions_count = 0;
    foreach ($associates as $associate) {
        $num_commissions = rand(3, 15);
        $associate_level = rand(1, 10);
        
        for ($i = 0; $i < $num_commissions; $i++) {
            $commission_amount = rand(1000, 50000);
            $commission_type = $commission_types[array_rand($commission_types)];
            $status = $commission_status[array_rand($commission_status)];
            
            $mlm_commissions_data[] = [
                'associate_id' => $associate['id'],
                'level' => $associate_level,
                'commission_amount' => $commission_amount,
                'payout_id' => null,
                'status' => $status,
                'created_at' => date('Y-m-d H:i:s', strtotime('-' . rand(1, 30) . ' days'))
            ];
            
            $commissions_count++;
        }
    }
    
    // Insert MLM Commissions data
    $commissions_stmt = $pdo->prepare("INSERT INTO mlm_commissions (associate_id, level, commission_amount, payout_id, status, created_at) VALUES (?, ?, ?, ?, ?, ?)");
    
    foreach ($mlm_commissions_data as $data) {
        $commissions_stmt->execute([
            $data['associate_id'], $data['level'], $data['commission_amount'],
            $data['payout_id'], $data['status'], $data['created_at']
        ]);
    }
    
    echo "✅ MLM Commissions data inserted: " . $commissions_count . " records\n";
    
    // Create enhanced mlm_performance table with professional features
    echo "📈 Creating MLM performance table...\n";
    $pdo->exec("CREATE TABLE IF NOT EXISTS `mlm_performance` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `associate_id` INT(11) NOT NULL,
        `user_id` INT(11) NOT NULL,
        `associate_name` VARCHAR(100) NOT NULL,
        `month_year` VARCHAR(7) NOT NULL,
        `commission_rate` DECIMAL(5,2) NOT NULL,
        `status` ENUM('active','inactive','suspended','terminated') NOT NULL DEFAULT 'active',
        `total_referrals` BIGINT(21) NOT NULL DEFAULT 0,
        `total_sales` BIGINT(21) NOT NULL DEFAULT 0,
        `total_sales_amount` DECIMAL(37,2) NOT NULL DEFAULT 0.00,
        `direct_commission` DECIMAL(15,2) DEFAULT 0.00,
        `team_commission` DECIMAL(15,2) DEFAULT 0.00,
        `level_commission` DECIMAL(15,2) DEFAULT 0.00,
        `bonus_commission` DECIMAL(15,2) DEFAULT 0.00,
        `leadership_bonus` DECIMAL(15,2) DEFAULT 0.00,
        `performance_bonus` DECIMAL(15,2) DEFAULT 0.00,
        `estimated_commission` DECIMAL(46,8) NOT NULL DEFAULT 0.00,
        `rank_id` INT(11) DEFAULT 1,
        `rank_name` VARCHAR(100) DEFAULT 'Associate',
        `achievement_points` INT(11) DEFAULT 0,
        `monthly_target` DECIMAL(15,2) DEFAULT 0.00,
        `target_achieved` DECIMAL(5,2) DEFAULT 0.00,
        `is_rank_promoted` BOOLEAN DEFAULT FALSE,
        `promotion_date` TIMESTAMP NULL,
        `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (`associate_id`) REFERENCES `associates`(`id`),
        FOREIGN KEY (`user_id`) REFERENCES `users`(`id`),
        INDEX `idx_associate_month` (`associate_id`, `month_year`),
        INDEX `idx_user_performance` (`user_id`, `month_year`),
        INDEX `idx_rank_achievement` (`rank_id`, `achievement_points`)
    );");
    echo "✅ mlm_performance table ensured to exist.\n";
    
    // Check if mlm_performance table exists
    $stmt = $pdo->prepare("SHOW TABLES LIKE 'mlm_performance';");
    $stmt->execute();
    $table_exists = $stmt->rowCount() > 0;

    if (!$table_exists) {
        echo "❌ mlm_performance table does not exist after CREATE TABLE attempt.\n";
        exit();
    }
    echo "✅ mlm_performance table confirmed to exist.\n";

    // Create MLM Performance data
    $performance_data = [];
    foreach ($associates as $associate) {
        $performance_data[] = [
            'associate_id' => $associate['id'],
            'associate_name' => 'Associate ' . $associate['id'], // Placeholder, ideally fetch from users table
            'commission_rate' => $associate['commission_rate'],
            'status' => 'active',
            'total_referrals' => rand(0, 100),
            'total_sales' => rand(50000, 500000),
            'total_sales_amount' => rand(100000, 1000000) / 100,
            'estimated_commission' => rand(5000, 50000) / 100,
            'created_at' => date('Y-m-d H:i:s')
        ];
    }
    $performance_stmt = $pdo->prepare("INSERT INTO mlm_performance (associate_id, associate_name, commission_rate, status, total_referrals, total_sales, total_sales_amount, estimated_commission, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

    foreach ($performance_data as $data) {
        $performance_stmt->execute([
            $data['associate_id'], $data['associate_name'], $data['commission_rate'],
            $data['status'], $data['total_referrals'], $data['total_sales'],
            $data['total_sales_amount'], $data['estimated_commission'], $data['created_at']
        ]);
    }
    echo "✅ MLM Performance data inserted: " . count($performance_data) . " records\n";

    // Create enhanced mlm_rank_advancements table with professional features
    echo "🏆 Creating MLM rank advancements table...\n";
    $pdo->exec("DROP TABLE IF EXISTS `mlm_rank_advancements`;");
    $pdo->exec("CREATE TABLE IF NOT EXISTS `mlm_rank_advancements` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `associate_id` INT(11) NOT NULL,
        `user_id` INT(11) NOT NULL,
        `previous_rank` VARCHAR(100) NOT NULL,
        `new_rank` VARCHAR(100) NOT NULL,
        `from_rank_id` INT NOT NULL,
        `to_rank_id` INT NOT NULL,
        `advancement_date` DATE NOT NULL,
        `requirements_met` TEXT,
        `business_achieved` DECIMAL(15,2) DEFAULT 0,
        `team_size_achieved` INT DEFAULT 0,
        `direct_referrals_achieved` INT DEFAULT 0,
        `promotion_bonus` DECIMAL(15,2) DEFAULT 0,
        `is_fast_track` BOOLEAN DEFAULT FALSE,
        `fast_track_bonus` DECIMAL(15,2) DEFAULT 0,
        `recognition_award` VARCHAR(100) DEFAULT NULL,
        `certificate_issued` BOOLEAN DEFAULT FALSE,
        `certificate_date` TIMESTAMP NULL,
        `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (`associate_id`) REFERENCES `associates`(`id`),
        FOREIGN KEY (`user_id`) REFERENCES `users`(`id`),
        INDEX `idx_associate_advancement` (`associate_id`, `advancement_date`),
        INDEX `idx_rank_promotions` (`from_rank_id`, `to_rank_id`),
        INDEX `idx_fast_track` (`is_fast_track`)
    );");
    echo "✅ mlm_rank_advancements table ensured to exist.\n";

    // Create Rank Advancements
    $advancements_data = [];
    foreach ($associates as $associate) {
        $current_level = rand(2, 10);
        if ($current_level > 1) {
            $advancements_data[] = [
                'associate_id' => $associate['id'],
                'previous_rank' => $mlm_levels[$current_level-1]['name'],
                'new_rank' => $mlm_levels[$current_level]['name'],
                'advancement_date' => date('Y-m-d', strtotime('-' . rand(1, 6) . ' months')),
                'points_required' => rand(1000, 50000),
                'created_at' => date('Y-m-d H:i:s')
            ];
        }
    }
    $advancements_stmt = $pdo->prepare("INSERT INTO mlm_rank_advancements (associate_id, previous_rank, new_rank, advancement_date, points_required, created_at) VALUES (?, ?, ?, ?, ?, ?)");
    
    foreach ($advancements_data as $data) {
        $advancements_stmt->execute([
            $data['associate_id'], $data['previous_rank'], $data['new_rank'],
            $data['advancement_date'], $data['points_required'], $data['created_at']
        ]);
    }
    echo "✅ MLM Rank Advancements data inserted: " . count($advancements_data) . " records\n";
    
    // Re-enable foreign key checks
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    
    echo "\n🎉 MLM Database Seeding Completed Successfully!\n";
    echo "📊 Summary:\n";
    echo "   • MLM Tree: " . count($mlm_tree_data) . " records\n";
    echo "   • Associate Levels: " . count($mlm_levels) . " records\n";
    echo "   • MLM Commissions: " . $commissions_count . " records\n";
    echo "   • MLM Performance: " . count($performance_data) . " records\n";
    echo "   • Rank Advancements: " . count($advancements_data) . " records\n";
    echo "   • Total Levels: 10\n";
    echo "   • Commission Range: 5% - 30%\n";
    
} catch (PDOException $e) {
    echo "❌ Database Error: " . $e->getMessage() . "\n";
    exit;
}
?>
// MLM Tree structure is already inserted above using prepared statements with all required fields
// Function to execute a query and fetch results
function executeQuery($pdo, $sql, $params = []) {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Describe mlm_performance table
echo "Describing mlm_performance table...\n";
$mlmPerformanceSchema = executeQuery($pdo, "DESCRIBE mlm_performance;");
print_r($mlmPerformanceSchema);
echo "\n";

// Create MLM Performance data
$performance_data = [];
foreach ($associates as $associate) {
    $performance_data[] = [
        'associate_id' => $associate['id'],
        'month' => date('Y-m'),
        'total_sales' => rand(50000, 500000), // Random sales amount
        'new_recruits' => rand(0, 10),
        'active_downlines' => rand(0, 50),
        'commission_earned' => rand(5000, 50000), // Random commission
        'rank_points' => rand(100, 10000),
        'created_at' => date('Y-m-d H:i:s')
    ];
}
$performance_stmt = $pdo->prepare("INSERT INTO mlm_performance (associate_id, month, total_sales, new_recruits, active_downlines, commission_earned, rank_points, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

foreach ($performance_data as $data) {
    $performance_stmt->execute([
        $data['associate_id'], $data['month'], $data['total_sales'],
        $data['new_recruits'], $data['active_downlines'], $data['commission_earned'],
        $data['rank_points'], $data['created_at']
    ]);
}
echo "✅ MLM Performance data inserted: " . count($performance_data) . " records\n";

// Create professional rank advancement data with bonuses
$advancements_data = [];
foreach ($associates as $index => $associate) {
    if ($index > 0) {
        $from_rank_id = ($index % 9) + 1;
        $to_rank_id = $from_rank_id + 1;
        $is_fast_track = (rand(1, 10) <= 3) ? 1 : 0; // 30% fast track promotions
        $promotion_bonus = $mlm_levels[$to_rank_id - 1]['joining_fee'] * 0.1; // 10% of joining fee
        $fast_track_bonus = $is_fast_track ? $promotion_bonus * 0.5 : 0; // 50% extra for fast track
        
        $advancements_data[] = [
            'associate_id' => $associate['id'],
            'user_id' => $users[$index % count($users)]['id'],
            'previous_rank' => $mlm_levels[$from_rank_id - 1]['name'],
            'new_rank' => $mlm_levels[$to_rank_id - 1]['name'],
            'from_rank_id' => $from_rank_id,
            'to_rank_id' => $to_rank_id,
            'advancement_date' => date('Y-m-d', strtotime("-" . (5 - $index % 5) . " months")),
            'requirements_met' => json_encode([
                'business_achieved' => rand(1000000, 5000000),
                'team_size' => rand(10, 50),
                'direct_referrals' => rand(5, 15),
                'training_completed' => true,
                'certification_done' => true
            ]),
            'business_achieved' => rand(1000000, 5000000),
            'team_size_achieved' => rand(10, 50),
            'direct_referrals_achieved' => rand(5, 15),
            'promotion_bonus' => $promotion_bonus,
            'is_fast_track' => $is_fast_track,
            'fast_track_bonus' => $fast_track_bonus,
            'recognition_award' => $mlm_levels[$to_rank_id - 1]['name'] . ' Achievement Award',
            'certificate_issued' => 1,
            'certificate_date' => date('Y-m-d H:i:s', strtotime("-" . (5 - $index % 5) . " months")),
            'created_at' => date('Y-m-d H:i:s')
        ];
    }
}
$advancements_stmt = $pdo->prepare("INSERT INTO mlm_rank_advancements (associate_id, user_id, previous_rank, new_rank, from_rank_id, to_rank_id, advancement_date, requirements_met, business_achieved, team_size_achieved, direct_referrals_achieved, promotion_bonus, is_fast_track, fast_track_bonus, recognition_award, certificate_issued, certificate_date, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

foreach ($advancements_data as $data) {
    $advancements_stmt->execute([
        $data['associate_id'], $data['user_id'], $data['previous_rank'], $data['new_rank'],
        $data['from_rank_id'], $data['to_rank_id'], $data['advancement_date'], $data['requirements_met'],
        $data['business_achieved'], $data['team_size_achieved'], $data['direct_referrals_achieved'],
        $data['promotion_bonus'], $data['is_fast_track'], $data['fast_track_bonus'], $data['recognition_award'],
        $data['certificate_issued'], $data['certificate_date'], $data['created_at']
    ]);
}]}
echo "✅ MLM Rank Advancements data inserted: " . count($advancements_data) . " records\n";

// Create special bonus and reward systems for professional MLM
echo "💎 Creating special bonus and reward systems...\n";

// Create special bonuses table
$pdo->exec("CREATE TABLE IF NOT EXISTS mlm_special_bonuses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    bonus_name VARCHAR(100) NOT NULL,
    bonus_type ENUM('welcome','fast_start','leadership','loyalty','performance','seasonal','anniversary') NOT NULL,
    bonus_amount DECIMAL(15,2) NOT NULL,
    bonus_percentage DECIMAL(5,2) DEFAULT 0,
    qualifying_rank INT DEFAULT 1,
    qualifying_sales DECIMAL(15,2) DEFAULT 0,
    qualifying_team_size INT DEFAULT 0,
    valid_from DATE NOT NULL,
    valid_to DATE NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_bonus_type (bonus_type),
    INDEX idx_bonus_active (is_active, valid_from, valid_to)
)");

// Create rewards and recognition table
$pdo->exec("CREATE TABLE IF NOT EXISTS mlm_rewards_recognition (
    id INT AUTO_INCREMENT PRIMARY KEY,
    associate_id INT NOT NULL,
    user_id INT NOT NULL,
    reward_type ENUM('car','house','travel','gadget','cash','jewelry','certificate','trophy') NOT NULL,
    reward_name VARCHAR(200) NOT NULL,
    reward_value DECIMAL(15,2) NOT NULL,
    qualifying_rank INT NOT NULL,
    achievement_date DATE NOT NULL,
    delivery_status ENUM('pending','processing','delivered','cancelled') DEFAULT 'pending',
    delivery_date TIMESTAMP NULL,
    is_special_reward BOOLEAN DEFAULT FALSE,
    reward_image VARCHAR(500) DEFAULT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (associate_id) REFERENCES associates(id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    INDEX idx_reward_type (reward_type),
    INDEX idx_delivery_status (delivery_status),
    INDEX idx_special_rewards (is_special_reward)
)");

// Insert special bonus data
$special_bonuses = [
    ['Welcome Bonus', 'welcome', 5000, 0, 1, 0, 0, '2024-01-01', '2024-12-31', 'Welcome bonus for new associates'],
    ['Fast Start Bonus', 'fast_start', 10000, 10, 1, 100000, 5, '2024-01-01', '2024-12-31', 'Bonus for achieving first month target'],
    ['Leadership Excellence', 'leadership', 25000, 15, 5, 1000000, 50, '2024-01-01', '2024-12-31', 'Leadership bonus for senior managers'],
    ['Loyalty Bonus', 'loyalty', 15000, 5, 3, 500000, 20, '2024-01-01', '2024-12-31', 'Loyalty bonus for consistent performers'],
    ['Performance Champion', 'performance', 50000, 20, 7, 2000000, 100, '2024-01-01', '2024-12-31', 'Top performance bonus for directors'],
    ['Festival Special', 'seasonal', 25000, 25, 2, 300000, 10, '2024-01-01', '2024-12-31', 'Special festival season bonus'],
    ['Anniversary Reward', 'anniversary', 30000, 30, 4, 1500000, 75, '2024-01-01', '2024-12-31', 'Company anniversary special bonus']
];

$bonus_stmt = $pdo->prepare("INSERT INTO mlm_special_bonuses (bonus_name, bonus_type, bonus_amount, bonus_percentage, qualifying_rank, qualifying_sales, qualifying_team_size, valid_from, valid_to, description) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
foreach ($special_bonuses as $bonus) {
    $bonus_stmt->execute($bonus);
}

// Insert reward and recognition data
$rewards = [
    ['Smartphone', 'gadget', 25000, 2, '2024-02-15', 'Latest smartphone for team leaders'],
    ['Laptop', 'gadget', 50000, 3, '2024-03-01', 'Professional laptop for managers'],
    ['Car Fund', 'cash', 500000, 6, '2024-04-01', 'Car purchase fund for vice presidents'],
    ['International Trip', 'travel', 150000, 5, '2024-05-01', 'International travel package'],
    ['Gold Jewelry', 'jewelry', 100000, 4, '2024-06-01', 'Gold jewelry set for senior managers'],
    ['Home Appliances', 'gadget', 75000, 3, '2024-07-01', 'Premium home appliances package'],
    ['Luxury Watch', 'jewelry', 200000, 7, '2024-08-01', 'Luxury watch for directors'],
    ['House Down Payment', 'house', 2000000, 9, '2024-09-01', 'House down payment support for executive directors'],
    ['World Tour', 'travel', 500000, 10, '2024-10-01', 'World tour package for global directors']
];

$reward_stmt = $pdo->prepare("INSERT INTO mlm_rewards_recognition (reward_name, reward_type, reward_value, qualifying_rank, achievement_date, description) VALUES (?, ?, ?, ?, ?, ?)");
foreach ($rewards as $reward) {
    $reward_stmt->execute($reward);
}

// Re-enable foreign key checks
echo "🔒 Re-enabling foreign key checks...\n";
$pdo->exec("SET FOREIGN_KEY_CHECKS=1");

echo "🎉 🏆 Professional MLM seeding completed successfully!\n";
echo "📊 Summary:\n";
echo "├── " . count($mlm_levels) . " Professional MLM levels created\n";
echo "├── " . count($mlm_tree_data) . " Enhanced MLM tree records inserted\n";
echo "├── " . count($mlm_levels) . " Associate level records inserted\n";
echo "├── " . $commissions_count . " Commission records inserted\n";
echo "├── " . count($performance_data) . " Performance records inserted\n";
echo "├── " . count($advancements_data) . " Rank advancement records inserted\n";
echo "├── " . count($special_bonuses) . " Special bonus programs created\n";
echo "└── " . count($rewards) . " Rewards & recognition programs created\n";
echo "\n✨ Your MLM system is now ready for professional use!\n";
echo "💼 Features: Advanced commission structure, bonus systems, rewards & recognition\n";
echo "🌍 Standards: International MLM compliance with professional grade features\n";

} catch (PDOException $e) {
    echo "❌ Database Error: " . $e->getMessage() . "\n";
    exit;
}
?>