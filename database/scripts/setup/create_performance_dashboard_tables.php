<?php
/**
 * Script to create associate MLM performance dashboard tables
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

    // Create performance metrics table
    $sql = "CREATE TABLE IF NOT EXISTS `performance_metrics` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `user_id` INT NOT NULL,
        `user_type` ENUM('associate','employee','admin') DEFAULT 'associate',
        `metric_type` ENUM('sales_volume','commission_earned','network_size','recruitments','property_views','leads_generated','conversions','training_completed','gamification_points','rank_achieved') NOT NULL,
        `metric_value` DECIMAL(15,2) NOT NULL,
        `metric_date` DATE NOT NULL,
        `period_type` ENUM('daily','weekly','monthly','quarterly','yearly') DEFAULT 'monthly',
        `comparison_value` DECIMAL(15,2) NULL COMMENT 'Value to compare against (previous period)',
        `growth_percentage` DECIMAL(8,4) NULL COMMENT 'Growth compared to previous period',
        `rank_position` INT NULL COMMENT 'Position in leaderboard for this metric',
        `metadata` JSON NULL COMMENT 'Additional metric data',
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

        INDEX `idx_metric_user` (`user_id`, `user_type`),
        INDEX `idx_metric_type` (`metric_type`),
        INDEX `idx_metric_date` (`metric_date`),
        INDEX `idx_metric_period` (`period_type`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Performance metrics table created successfully!\n";
    }

    // Create dashboard widgets table
    $sql = "CREATE TABLE IF NOT EXISTS `dashboard_widgets` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `widget_name` VARCHAR(255) NOT NULL,
        `widget_type` ENUM('metric_card','chart','table','progress_bar','leaderboard','timeline','comparison','goal_tracker') NOT NULL,
        `widget_category` ENUM('sales','network','finance','training','gamification','overview') DEFAULT 'overview',
        `data_source` VARCHAR(100) NOT NULL COMMENT 'Source table or API endpoint',
        `configuration` JSON NOT NULL COMMENT 'Widget configuration (charts, filters, etc.)',
        `default_size` ENUM('small','medium','large','full_width') DEFAULT 'medium',
        `is_default` TINYINT(1) DEFAULT 0,
        `is_active` TINYINT(1) DEFAULT 1,
        `sort_order` INT DEFAULT 0,
        `created_by` INT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

        INDEX `idx_widget_type` (`widget_type`),
        INDEX `idx_widget_category` (`widget_category`),
        INDEX `idx_widget_active` (`is_active`),
        INDEX `idx_widget_order` (`sort_order`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Dashboard widgets table created successfully!\n";
    }

    // Create user dashboard configurations table
    $sql = "CREATE TABLE IF NOT EXISTS `user_dashboard_configs` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `user_id` INT NOT NULL,
        `user_type` ENUM('associate','employee','admin') DEFAULT 'associate',
        `config_name` VARCHAR(255) NOT NULL,
        `widgets_configuration` JSON NOT NULL COMMENT 'Array of widget configurations with positions',
        `layout_settings` JSON NULL COMMENT 'Dashboard layout settings',
        `date_filters` JSON NULL COMMENT 'Default date range filters',
        `is_default` TINYINT(1) DEFAULT 0,
        `is_active` TINYINT(1) DEFAULT 1,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

        UNIQUE KEY `unique_user_config` (`user_id`, `user_type`, `config_name`),
        INDEX `idx_config_user` (`user_id`, `user_type`),
        INDEX `idx_config_active` (`is_active`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ User dashboard configurations table created successfully!\n";
    }

    // Create performance goals table
    $sql = "CREATE TABLE IF NOT EXISTS `performance_goals` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `user_id` INT NOT NULL,
        `user_type` ENUM('associate','employee','admin') DEFAULT 'associate',
        `goal_name` VARCHAR(255) NOT NULL,
        `goal_type` ENUM('sales_target','recruitment_target','commission_target','training_completion','network_growth','rank_achievement') NOT NULL,
        `target_value` DECIMAL(15,2) NOT NULL,
        `current_value` DECIMAL(15,2) DEFAULT 0,
        `start_date` DATE NOT NULL,
        `end_date` DATE NOT NULL,
        `status` ENUM('active','completed','failed','cancelled') DEFAULT 'active',
        `progress_percentage` DECIMAL(5,2) DEFAULT 0,
        `reward_points` INT DEFAULT 0,
        `reward_badge_id` INT NULL,
        `auto_calculate` TINYINT(1) DEFAULT 1 COMMENT 'Automatically calculate progress',
        `created_by` INT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

        INDEX `idx_goal_user` (`user_id`, `user_type`),
        INDEX `idx_goal_type` (`goal_type`),
        INDEX `idx_goal_status` (`status`),
        INDEX `idx_goal_dates` (`start_date`, `end_date`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Performance goals table created successfully!\n";
    }

    // Create performance reports table
    $sql = "CREATE TABLE IF NOT EXISTS `performance_reports` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `report_name` VARCHAR(255) NOT NULL,
        `report_type` ENUM('individual','team','network','company_wide') DEFAULT 'individual',
        `date_range` JSON NOT NULL COMMENT 'Date range for the report',
        `filters` JSON NULL COMMENT 'Additional filters applied',
        `metrics_included` JSON NOT NULL COMMENT 'Array of metrics included in report',
        `generated_data` JSON NULL COMMENT 'Generated report data',
        `generated_by` INT NOT NULL,
        `generated_for` INT NULL COMMENT 'User the report is generated for',
        `is_scheduled` TINYINT(1) DEFAULT 0,
        `schedule_config` JSON NULL COMMENT 'Schedule configuration for recurring reports',
        `last_generated` TIMESTAMP NULL,
        `next_generation` TIMESTAMP NULL,
        `is_active` TINYINT(1) DEFAULT 1,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

        INDEX `idx_report_type` (`report_type`),
        INDEX `idx_report_generated` (`generated_by`),
        INDEX `idx_report_active` (`is_active`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Performance reports table created successfully!\n";
    }

    // Create network analytics table
    $sql = "CREATE TABLE IF NOT EXISTS `network_analytics` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `user_id` INT NOT NULL,
        `user_type` ENUM('associate','employee','admin') DEFAULT 'associate',
        `network_level` INT DEFAULT 0 COMMENT 'MLM network level/depth',
        `direct_recruits` INT DEFAULT 0,
        `total_network_size` INT DEFAULT 0,
        `active_members` INT DEFAULT 0,
        `inactive_members` INT DEFAULT 0,
        `network_growth_rate` DECIMAL(5,2) DEFAULT 0 COMMENT 'Monthly growth percentage',
        `average_commission_per_member` DECIMAL(10,2) DEFAULT 0,
        `total_network_commission` DECIMAL(15,2) DEFAULT 0,
        `analytics_date` DATE NOT NULL,
        `calculated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

        UNIQUE KEY `unique_network_analytics` (`user_id`, `user_type`, `analytics_date`),
        INDEX `idx_network_user` (`user_id`, `user_type`),
        INDEX `idx_network_date` (`analytics_date`),
        INDEX `idx_network_level` (`network_level`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Network analytics table created successfully!\n";
    }

    // Create rank achievements table
    $sql = "CREATE TABLE IF NOT EXISTS `rank_achievements` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `user_id` INT NOT NULL,
        `user_type` ENUM('associate','employee','admin') DEFAULT 'associate',
        `rank_name` VARCHAR(100) NOT NULL,
        `rank_level` INT NOT NULL,
        `requirements_met` JSON NOT NULL COMMENT 'Requirements that were met for this rank',
        `achieved_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `reward_points` INT DEFAULT 0,
        `reward_badge_id` INT NULL,
        `is_current_rank` TINYINT(1) DEFAULT 1,
        `valid_from` DATE NOT NULL,
        `valid_until` DATE NULL,

        INDEX `idx_rank_user` (`user_id`, `user_type`),
        INDEX `idx_rank_level` (`rank_level`),
        INDEX `idx_rank_current` (`is_current_rank`),
        INDEX `idx_rank_achieved` (`achieved_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Rank achievements table created successfully!\n";
    }

    // Insert default dashboard widgets
    $defaultWidgets = [
        [
            'Sales Performance Card',
            'metric_card',
            'sales',
            'performance_metrics',
            '{"metric": "sales_volume", "period": "monthly", "show_trend": true, "target_line": true}',
            'small',
            1,
            1,
            1
        ],
        [
            'Commission Tracker',
            'metric_card',
            'finance',
            'performance_metrics',
            '{"metric": "commission_earned", "period": "monthly", "show_comparison": true}',
            'small',
            1,
            1,
            2
        ],
        [
            'Network Growth Chart',
            'chart',
            'network',
            'network_analytics',
            '{"chart_type": "line", "metrics": ["total_network_size", "active_members"], "period": "6months"}',
            'medium',
            1,
            1,
            3
        ],
        [
            'Monthly Goals Progress',
            'progress_bar',
            'overview',
            'performance_goals',
            '{"show_active_goals": true, "group_by_type": true}',
            'large',
            1,
            1,
            4
        ],
        [
            'Top Performers Leaderboard',
            'leaderboard',
            'overview',
            'performance_metrics',
            '{"metric": "sales_volume", "period": "monthly", "limit": 10}',
            'medium',
            1,
            1,
            5
        ],
        [
            'Training Progress',
            'progress_bar',
            'training',
            'user_course_enrollments',
            '{"show_completion_percentage": true, "include_quiz_scores": true}',
            'medium',
            0,
            1,
            6
        ],
        [
            'Gamification Points',
            'metric_card',
            'gamification',
            'gamification_points',
            '{"show_level_progress": true, "show_badges_count": true}',
            'small',
            0,
            1,
            7
        ]
    ];

    $insertWidgetSql = "INSERT IGNORE INTO `dashboard_widgets` (`widget_name`, `widget_type`, `widget_category`, `data_source`, `configuration`, `default_size`, `is_default`, `is_active`, `sort_order`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($insertWidgetSql);

    foreach ($defaultWidgets as $widget) {
        $stmt->execute($widget);
    }

    echo "✅ Default dashboard widgets inserted successfully!\n";

    // Insert default performance goals for associates
    $defaultGoals = [
        [
            1, 'associate', 'Monthly Sales Target', 'sales_target', 500000.00, '2024-01-01', '2024-01-31', 'active', 0, 500
        ],
        [
            1, 'associate', 'Recruit 5 New Associates', 'recruitment_target', 5.00, '2024-01-01', '2024-03-31', 'active', 0, 300
        ],
        [
            1, 'associate', 'Complete Sales Training', 'training_completion', 1.00, '2024-01-01', '2024-02-28', 'active', 0, 200
        ]
    ];

    $insertGoalSql = "INSERT IGNORE INTO `performance_goals` (`user_id`, `user_type`, `goal_name`, `goal_type`, `target_value`, `start_date`, `end_date`, `status`, `current_value`, `reward_points`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($insertGoalSql);

    foreach ($defaultGoals as $goal) {
        $stmt->execute($goal);
    }

    echo "✅ Default performance goals inserted successfully!\n";

    echo "\n🎉 Associate MLM performance dashboard database setup completed!\n";

} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
