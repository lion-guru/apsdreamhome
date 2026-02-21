<?php
/**
 * Script to create associate MLM gamification system tables
 */

// Database configuration
$host = 'localhost';
$dbname = 'apsdreamhome';
$username = 'root';
$password = '';

try {
    // Create PDO connection
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    echo "Connected to database successfully.\n";

    // Function to execute SQL queries
    function executeQuery($pdo, $sql) {
        try {
            $stmt = $pdo->prepare($sql);
            $result = $stmt->execute();
            if ($result) {
                echo "Query executed successfully\n";
                return true;
            } else {
                echo "Error executing query\n";
                return false;
            }
        } catch (Exception $e) {
            echo "Exception: " . $e->getMessage() . "\n";
            return false;
        }
    }

    // Create gamification points table
    $sql = "CREATE TABLE IF NOT EXISTS `gamification_points` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `user_id` INT NOT NULL,
        `user_type` ENUM('associate','customer','employee') DEFAULT 'associate',
        `points_total` INT DEFAULT 0,
        `points_available` INT DEFAULT 0,
        `points_redeemed` INT DEFAULT 0,
        `current_level` INT DEFAULT 1,
        `experience_points` INT DEFAULT 0,
        `last_activity_date` DATE NULL,
        `streak_days` INT DEFAULT 0,
        `longest_streak` INT DEFAULT 0,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

        UNIQUE KEY `unique_user_points` (`user_id`, `user_type`),
        INDEX `idx_points_level` (`current_level`),
        INDEX `idx_points_activity` (`last_activity_date`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Gamification points table created successfully!\n";
    }

    // Create points transactions table
    $sql = "CREATE TABLE IF NOT EXISTS `points_transactions` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `user_id` INT NOT NULL,
        `user_type` ENUM('associate','customer','employee') DEFAULT 'associate',
        `transaction_type` ENUM('earned','redeemed','bonus','penalty','transfer') NOT NULL,
        `points_amount` INT NOT NULL,
        `balance_before` INT NOT NULL,
        `balance_after` INT NOT NULL,
        `reference_type` ENUM('activity','achievement','challenge','redemption','admin') DEFAULT 'activity',
        `reference_id` INT NULL,
        `description` VARCHAR(255) NOT NULL,
        `metadata` JSON NULL,
        `created_by` INT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

        INDEX `idx_transaction_user` (`user_id`, `user_type`),
        INDEX `idx_transaction_type` (`transaction_type`),
        INDEX `idx_transaction_reference` (`reference_type`, `reference_id`),
        INDEX `idx_transaction_date` (`created_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Points transactions table created successfully!\n";
    }

    // Create badges table
    $sql = "CREATE TABLE IF NOT EXISTS `gamification_badges` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `badge_name` VARCHAR(255) NOT NULL,
        `badge_description` TEXT NULL,
        `badge_icon` VARCHAR(100) NOT NULL,
        `badge_color` VARCHAR(7) DEFAULT '#007bff',
        `badge_type` ENUM('achievement','milestone','special','seasonal') DEFAULT 'achievement',
        `rarity_level` ENUM('common','uncommon','rare','epic','legendary') DEFAULT 'common',
        `points_required` INT DEFAULT 0,
        `criteria_rules` JSON NOT NULL,
        `is_active` TINYINT(1) DEFAULT 1,
        `max_awards` INT NULL COMMENT 'Maximum times this badge can be awarded',
        `valid_from` DATE NULL,
        `valid_until` DATE NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

        INDEX `idx_badge_type` (`badge_type`),
        INDEX `idx_badge_rarity` (`rarity_level`),
        INDEX `idx_badge_active` (`is_active`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Badges table created successfully!\n";
    }

    // Create user badges table
    $sql = "CREATE TABLE IF NOT EXISTS `user_badges` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `user_id` INT NOT NULL,
        `user_type` ENUM('associate','customer','employee') DEFAULT 'associate',
        `badge_id` INT NOT NULL,
        `awarded_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `awarded_by` INT NULL,
        `is_displayed` TINYINT(1) DEFAULT 1,
        `metadata` JSON NULL,

        UNIQUE KEY `unique_user_badge` (`user_id`, `user_type`, `badge_id`),
        INDEX `idx_user_badge_user` (`user_id`, `user_type`),
        INDEX `idx_user_badge_badge` (`badge_id`),
        INDEX `idx_user_badge_displayed` (`is_displayed`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ User badges table created successfully!\n";
    }

    // Create challenges table
    $sql = "CREATE TABLE IF NOT EXISTS `gamification_challenges` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `challenge_name` VARCHAR(255) NOT NULL,
        `challenge_description` TEXT NOT NULL,
        `challenge_type` ENUM('daily','weekly','monthly','seasonal','special') DEFAULT 'daily',
        `target_metric` VARCHAR(100) NOT NULL COMMENT 'What needs to be achieved',
        `target_value` INT NOT NULL,
        `points_reward` INT DEFAULT 0,
        `badge_reward_id` INT NULL,
        `bonus_multiplier` DECIMAL(3,2) DEFAULT 1.00,
        `start_date` DATE NOT NULL,
        `end_date` DATE NOT NULL,
        `max_participants` INT NULL,
        `current_participants` INT DEFAULT 0,
        `is_active` TINYINT(1) DEFAULT 1,
        `difficulty_level` ENUM('easy','medium','hard','expert') DEFAULT 'medium',
        `category` VARCHAR(50) NULL,
        `created_by` INT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

        FOREIGN KEY (`badge_reward_id`) REFERENCES `gamification_badges`(`id`) ON DELETE SET NULL,
        INDEX `idx_challenge_type` (`challenge_type`),
        INDEX `idx_challenge_dates` (`start_date`, `end_date`),
        INDEX `idx_challenge_active` (`is_active`),
        INDEX `idx_challenge_difficulty` (`difficulty_level`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Challenges table created successfully!\n";
    }

    // Create challenge participants table
    $sql = "CREATE TABLE IF NOT EXISTS `challenge_participants` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `challenge_id` INT NOT NULL,
        `user_id` INT NOT NULL,
        `user_type` ENUM('associate','customer','employee') DEFAULT 'associate',
        `current_progress` INT DEFAULT 0,
        `target_value` INT NOT NULL,
        `is_completed` TINYINT(1) DEFAULT 0,
        `completed_at` TIMESTAMP NULL,
        `points_earned` INT DEFAULT 0,
        `joined_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

        UNIQUE KEY `unique_challenge_participant` (`challenge_id`, `user_id`, `user_type`),
        INDEX `idx_participant_challenge` (`challenge_id`),
        INDEX `idx_participant_user` (`user_id`, `user_type`),
        INDEX `idx_participant_completed` (`is_completed`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Challenge participants table created successfully!\n";
    }

    // Create leaderboards table
    $sql = "CREATE TABLE IF NOT EXISTS `leaderboards` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `leaderboard_name` VARCHAR(255) NOT NULL,
        `leaderboard_type` ENUM('points','level','badges','challenge','network') DEFAULT 'points',
        `period_type` ENUM('daily','weekly','monthly','all_time','custom') DEFAULT 'monthly',
        `period_start` DATE NULL,
        `period_end` DATE NULL,
        `metric_field` VARCHAR(50) DEFAULT 'points_total',
        `max_entries` INT DEFAULT 100,
        `is_active` TINYINT(1) DEFAULT 1,
        `last_updated` TIMESTAMP NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

        INDEX `idx_leaderboard_type` (`leaderboard_type`),
        INDEX `idx_leaderboard_period` (`period_type`),
        INDEX `idx_leaderboard_active` (`is_active`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Leaderboards table created successfully!\n";
    }

    // Create leaderboard entries table
    $sql = "CREATE TABLE IF NOT EXISTS `leaderboard_entries` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `leaderboard_id` INT NOT NULL,
        `user_id` INT NOT NULL,
        `user_type` ENUM('associate','customer','employee') DEFAULT 'associate',
        `rank_position` INT NOT NULL,
        `metric_value` DECIMAL(15,2) NOT NULL,
        `achieved_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `metadata` JSON NULL,

        UNIQUE KEY `unique_leaderboard_entry` (`leaderboard_id`, `user_id`, `user_type`),
        INDEX `idx_entry_leaderboard` (`leaderboard_id`),
        INDEX `idx_entry_user` (`user_id`, `user_type`),
        INDEX `idx_entry_rank` (`rank_position`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Leaderboard entries table created successfully!\n";
    }

    // Create achievements table
    $sql = "CREATE TABLE IF NOT EXISTS `achievements` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `achievement_name` VARCHAR(255) NOT NULL,
        `achievement_description` TEXT NOT NULL,
        `achievement_icon` VARCHAR(100) NOT NULL,
        `achievement_type` ENUM('milestone','streak','network','performance') DEFAULT 'milestone',
        `criteria_rules` JSON NOT NULL,
        `points_reward` INT DEFAULT 0,
        `badge_reward_id` INT NULL,
        `unlock_level` INT DEFAULT 1,
        `is_hidden` TINYINT(1) DEFAULT 0,
        `max_unlocks` INT DEFAULT 1,
        `is_active` TINYINT(1) DEFAULT 1,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

        FOREIGN KEY (`badge_reward_id`) REFERENCES `gamification_badges`(`id`) ON DELETE SET NULL,
        INDEX `idx_achievement_type` (`achievement_type`),
        INDEX `idx_achievement_level` (`unlock_level`),
        INDEX `idx_achievement_active` (`is_active`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Achievements table created successfully!\n";
    }

    // Create user achievements table
    $sql = "CREATE TABLE IF NOT EXISTS `user_achievements` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `user_id` INT NOT NULL,
        `user_type` ENUM('associate','customer','employee') DEFAULT 'associate',
        `achievement_id` INT NOT NULL,
        `unlocked_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `progress_value` INT DEFAULT 0,
        `is_completed` TINYINT(1) DEFAULT 1,
        `metadata` JSON NULL,

        UNIQUE KEY `unique_user_achievement` (`user_id`, `user_type`, `achievement_id`),
        INDEX `idx_user_achievement_user` (`user_id`, `user_type`),
        INDEX `idx_user_achievement_achievement` (`achievement_id`),
        INDEX `idx_user_achievement_completed` (`is_completed`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ User achievements table created successfully!\n";
    }

    // Create rewards catalog table
    $sql = "CREATE TABLE IF NOT EXISTS `rewards_catalog` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `reward_name` VARCHAR(255) NOT NULL,
        `reward_description` TEXT NULL,
        `reward_type` ENUM('physical','digital','experience','cashback','discount') DEFAULT 'digital',
        `points_cost` INT NOT NULL,
        `reward_value` DECIMAL(10,2) NULL,
        `image_url` VARCHAR(500) NULL,
        `stock_quantity` INT DEFAULT -1 COMMENT '-1 for unlimited',
        `is_active` TINYINT(1) DEFAULT 1,
        `valid_from` DATE NULL,
        `valid_until` DATE NULL,
        `redemption_instructions` TEXT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

        INDEX `idx_reward_type` (`reward_type`),
        INDEX `idx_reward_cost` (`points_cost`),
        INDEX `idx_reward_active` (`is_active`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Rewards catalog table created successfully!\n";
    }

    // Create reward redemptions table
    $sql = "CREATE TABLE IF NOT EXISTS `reward_redemptions` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `user_id` INT NOT NULL,
        `user_type` ENUM('associate','customer','employee') DEFAULT 'associate',
        `reward_id` INT NOT NULL,
        `points_spent` INT NOT NULL,
        `redemption_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `status` ENUM('pending','approved','shipped','delivered','cancelled') DEFAULT 'pending',
        `tracking_number` VARCHAR(100) NULL,
        `delivery_address` TEXT NULL,
        `notes` TEXT NULL,

        FOREIGN KEY (`reward_id`) REFERENCES `rewards_catalog`(`id`),
        INDEX `idx_redemption_user` (`user_id`, `user_type`),
        INDEX `idx_redemption_reward` (`reward_id`),
        INDEX `idx_redemption_status` (`status`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Reward redemptions table created successfully!\n";
    }

    // Insert default badges
    $defaultBadges = [
        ['First Sale', 'Made your first property sale', 'fas fa-handshake', '#28a745', 'achievement', 'common', 100],
        ['Top Performer', 'Ranked in top 10 for the month', 'fas fa-trophy', '#ffc107', 'milestone', 'rare', 500],
        ['Network Builder', 'Recruited 10 new associates', 'fas fa-users', '#007bff', 'achievement', 'uncommon', 200],
        ['Consistency King', 'Maintained 30-day activity streak', 'fas fa-calendar-check', '#17a2b8', 'achievement', 'rare', 300],
        ['Revenue Champion', 'Generated ₹10 lakhs in revenue', 'fas fa-rupee-sign', '#dc3545', 'milestone', 'epic', 1000],
        ['Mentor', 'Helped 5 associates achieve their first sale', 'fas fa-user-graduate', '#6f42c1', 'achievement', 'uncommon', 250]
    ];

    $badgeCriteria = [
        '{"sales_count": 1}', '{"monthly_rank": 10}', '{"recruits_count": 10}', '{"streak_days": 30}',
        '{"revenue_amount": 1000000}', '{"mentees_success": 5}'
    ];

    for ($i = 0; $i < count($defaultBadges); $i++) {
        $badge = $defaultBadges[$i];
        $badge[] = $badgeCriteria[$i];
        $badge[] = 1; // is_active
        $badge[] = null; // max_awards
        $badge[] = null; // valid_from
        $badge[] = null; // valid_until

        $insertBadgeSql = "INSERT IGNORE INTO `gamification_badges` (`badge_name`, `badge_description`, `badge_icon`, `badge_color`, `badge_type`, `rarity_level`, `points_required`, `criteria_rules`, `is_active`, `max_awards`, `valid_from`, `valid_until`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($insertBadgeSql);
        $stmt->execute($badge);
    }

    echo "✅ Default badges inserted successfully!\n";

    // Insert default rewards
    $defaultRewards = [
        ['₹500 Cashback', 'Get ₹500 credited to your account', 'cashback', 1000, 500.00],
        ['Premium Headphones', 'Wireless premium headphones worth ₹3000', 'physical', 2500, 3000.00],
        ['Movie Tickets', '2 movie tickets for you and a friend', 'experience', 800, 600.00],
        ['Extra Commission', '5% extra commission on next sale', 'digital', 1500, null],
        ['Professional Photo Session', 'Professional headshot photography session', 'experience', 2000, 1500.00],
        ['Brand Merchandise', 'Official company branded merchandise', 'physical', 1200, 800.00]
    ];

    $insertRewardSql = "INSERT IGNORE INTO `rewards_catalog` (`reward_name`, `reward_description`, `reward_type`, `points_cost`, `reward_value`) VALUES (?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($insertRewardSql);

    foreach ($defaultRewards as $reward) {
        $stmt->execute($reward);
    }

    echo "✅ Default rewards inserted successfully!\n";

    // Insert default leaderboards
    $defaultLeaderboards = [
        ['Monthly Points Leaderboard', 'points', 'monthly', null, null, 'points_total'],
        ['All-Time Top Performers', 'points', 'all_time', null, null, 'points_total'],
        ['Network Size Champions', 'network', 'monthly', null, null, 'network_size'],
        ['Badge Collectors', 'badges', 'monthly', null, null, 'badge_count']
    ];

    $insertLeaderboardSql = "INSERT IGNORE INTO `leaderboards` (`leaderboard_name`, `leaderboard_type`, `period_type`, `period_start`, `period_end`, `metric_field`) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($insertLeaderboardSql);

    foreach ($defaultLeaderboards as $leaderboard) {
        $stmt->execute($leaderboard);
    }

    echo "✅ Default leaderboards inserted successfully!\n";

    echo "\n🎉 Associate MLM gamification system database setup completed!\n";

} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
