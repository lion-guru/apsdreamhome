<?php
/**
 * Script to create property portfolio management dashboard tables
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

    // Create property portfolios table
    $sql = "CREATE TABLE IF NOT EXISTS `property_portfolios` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `portfolio_name` VARCHAR(255) NOT NULL,
        `portfolio_description` TEXT NULL,
        `portfolio_type` ENUM('investment','rental','development','mixed') DEFAULT 'investment',
        `owner_id` INT NOT NULL,
        `owner_type` ENUM('associate','employee','company','investor') DEFAULT 'associate',
        `total_properties` INT DEFAULT 0,
        `total_value` DECIMAL(15,2) DEFAULT 0,
        `portfolio_roi` DECIMAL(5,2) DEFAULT 0,
        `monthly_income` DECIMAL(12,2) DEFAULT 0,
        `monthly_expenses` DECIMAL(12,2) DEFAULT 0,
        `net_monthly_income` DECIMAL(12,2) DEFAULT 0,
        `is_active` TINYINT(1) DEFAULT 1,
        `portfolio_goals` JSON NULL COMMENT 'Investment goals and targets',
        `risk_profile` ENUM('conservative','moderate','aggressive') DEFAULT 'moderate',
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

        INDEX `idx_portfolio_owner` (`owner_id`, `owner_type`),
        INDEX `idx_portfolio_type` (`portfolio_type`),
        INDEX `idx_portfolio_active` (`is_active`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Property portfolios table created successfully!\n";
    }

    // Create portfolio properties table
    $sql = "CREATE TABLE IF NOT EXISTS `portfolio_properties` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `portfolio_id` INT NOT NULL,
        `property_id` INT NOT NULL,
        `acquisition_date` DATE NOT NULL,
        `acquisition_cost` DECIMAL(12,2) NOT NULL,
        `current_value` DECIMAL(12,2) NULL,
        `ownership_percentage` DECIMAL(5,2) DEFAULT 100.00,
        `property_status` ENUM('owned','rented','vacant','under_development','sold') DEFAULT 'owned',
        `rental_income` DECIMAL(10,2) DEFAULT 0,
        `monthly_expenses` DECIMAL(10,2) DEFAULT 0,
        `net_income` DECIMAL(10,2) DEFAULT 0,
        `last_valuation_date` DATE NULL,
        `next_valuation_date` DATE NULL,
        `notes` TEXT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

        FOREIGN KEY (`portfolio_id`) REFERENCES `property_portfolios`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`property_id`) REFERENCES `properties`(`id`) ON DELETE CASCADE,
        UNIQUE KEY `unique_portfolio_property` (`portfolio_id`, `property_id`),
        INDEX `idx_portfolio_prop_portfolio` (`portfolio_id`),
        INDEX `idx_portfolio_prop_property` (`property_id`),
        INDEX `idx_portfolio_prop_status` (`property_status`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Portfolio properties table created successfully!\n";
    }

    // Create property valuation history table
    $sql = "CREATE TABLE IF NOT EXISTS `property_valuations` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `property_id` INT NOT NULL,
        `valuation_date` DATE NOT NULL,
        `valuation_amount` DECIMAL(12,2) NOT NULL,
        `valuation_method` ENUM('market_comparison','income_capitalization','cost_approach','automated','manual') DEFAULT 'manual',
        `appraiser_name` VARCHAR(255) NULL,
        `appraiser_company` VARCHAR(255) NULL,
        `valuation_report_url` VARCHAR(500) NULL,
        `market_trends` JSON NULL COMMENT 'Market trend data at time of valuation',
        `confidence_level` DECIMAL(3,1) DEFAULT 85.0 COMMENT 'Confidence in valuation (0-100)',
        `notes` TEXT NULL,
        `created_by` INT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

        FOREIGN KEY (`property_id`) REFERENCES `properties`(`id`) ON DELETE CASCADE,
        INDEX `idx_valuation_property` (`property_id`),
        INDEX `idx_valuation_date` (`valuation_date`),
        INDEX `idx_valuation_method` (`valuation_method`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Property valuations table created successfully!\n";
    }

    // Create portfolio analytics table
    $sql = "CREATE TABLE IF NOT EXISTS `portfolio_analytics` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `portfolio_id` INT NOT NULL,
        `analytics_date` DATE NOT NULL,
        `total_properties` INT DEFAULT 0,
        `total_value` DECIMAL(15,2) DEFAULT 0,
        `portfolio_roi` DECIMAL(5,2) DEFAULT 0,
        `monthly_income` DECIMAL(12,2) DEFAULT 0,
        `monthly_expenses` DECIMAL(12,2) DEFAULT 0,
        `net_income` DECIMAL(12,2) DEFAULT 0,
        `occupancy_rate` DECIMAL(5,2) DEFAULT 0,
        `average_rent_per_sqft` DECIMAL(8,2) DEFAULT 0,
        `property_appreciation` DECIMAL(5,2) DEFAULT 0,
        `diversification_score` DECIMAL(3,1) DEFAULT 0 COMMENT 'Portfolio diversification score (0-10)',
        `risk_score` DECIMAL(3,1) DEFAULT 0 COMMENT 'Portfolio risk score (0-10)',
        `performance_trend` ENUM('improving','stable','declining') DEFAULT 'stable',
        `market_comparison` JSON NULL COMMENT 'Comparison with market indices',
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

        FOREIGN KEY (`portfolio_id`) REFERENCES `property_portfolios`(`id`) ON DELETE CASCADE,
        UNIQUE KEY `unique_portfolio_analytics` (`portfolio_id`, `analytics_date`),
        INDEX `idx_portfolio_analytics_portfolio` (`portfolio_id`),
        INDEX `idx_portfolio_analytics_date` (`analytics_date`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Portfolio analytics table created successfully!\n";
    }

    // Create portfolio alerts table
    $sql = "CREATE TABLE IF NOT EXISTS `portfolio_alerts` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `portfolio_id` INT NOT NULL,
        `alert_type` ENUM('valuation_due','maintenance_needed','lease_expiring','payment_overdue','market_opportunity','risk_warning','goal_achievement') NOT NULL,
        `alert_title` VARCHAR(255) NOT NULL,
        `alert_description` TEXT NOT NULL,
        `severity` ENUM('low','medium','high','critical') DEFAULT 'medium',
        `related_property_id` INT NULL,
        `due_date` DATE NULL,
        `is_acknowledged` TINYINT(1) DEFAULT 0,
        `acknowledged_by` INT NULL,
        `acknowledged_at` TIMESTAMP NULL,
        `auto_generated` TINYINT(1) DEFAULT 1,
        `action_required` TEXT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

        FOREIGN KEY (`portfolio_id`) REFERENCES `property_portfolios`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`related_property_id`) REFERENCES `properties`(`id`) ON DELETE SET NULL,
        INDEX `idx_alert_portfolio` (`portfolio_id`),
        INDEX `idx_alert_type` (`alert_type`),
        INDEX `idx_alert_severity` (`severity`),
        INDEX `idx_alert_acknowledged` (`is_acknowledged`),
        INDEX `idx_alert_due` (`due_date`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Portfolio alerts table created successfully!\n";
    }

    // Create portfolio goals table
    $sql = "CREATE TABLE IF NOT EXISTS `portfolio_goals` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `portfolio_id` INT NOT NULL,
        `goal_name` VARCHAR(255) NOT NULL,
        `goal_type` ENUM('total_value','monthly_income','roi_target','property_count','diversification','risk_reduction') NOT NULL,
        `target_value` DECIMAL(15,2) NOT NULL,
        `current_value` DECIMAL(15,2) DEFAULT 0,
        `target_date` DATE NOT NULL,
        `progress_percentage` DECIMAL(5,2) DEFAULT 0,
        `status` ENUM('active','achieved','overdue','cancelled') DEFAULT 'active',
        `priority` ENUM('low','medium','high','critical') DEFAULT 'medium',
        `milestones` JSON NULL COMMENT 'Goal milestones and checkpoints',
        `achieved_at` TIMESTAMP NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

        FOREIGN KEY (`portfolio_id`) REFERENCES `property_portfolios`(`id`) ON DELETE CASCADE,
        INDEX `idx_goal_portfolio` (`portfolio_id`),
        INDEX `idx_goal_type` (`goal_type`),
        INDEX `idx_goal_status` (`status`),
        INDEX `idx_goal_target_date` (`target_date`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Portfolio goals table created successfully!\n";
    }

    // Create portfolio reports table
    $sql = "CREATE TABLE IF NOT EXISTS `portfolio_reports` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `portfolio_id` INT NOT NULL,
        `report_type` ENUM('monthly_performance','quarterly_review','annual_summary','tax_report','market_analysis','custom') DEFAULT 'monthly_performance',
        `report_title` VARCHAR(255) NOT NULL,
        `report_period_start` DATE NOT NULL,
        `report_period_end` DATE NOT NULL,
        `report_data` JSON NOT NULL COMMENT 'Generated report data',
        `report_summary` TEXT NULL,
        `generated_by` INT NOT NULL,
        `is_scheduled` TINYINT(1) DEFAULT 0,
        `schedule_config` JSON NULL,
        `download_count` INT DEFAULT 0,
        `last_downloaded_at` TIMESTAMP NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

        FOREIGN KEY (`portfolio_id`) REFERENCES `property_portfolios`(`id`) ON DELETE CASCADE,
        INDEX `idx_report_portfolio` (`portfolio_id`),
        INDEX `idx_report_type` (`report_type`),
        INDEX `idx_report_period` (`report_period_start`, `report_period_end`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Portfolio reports table created successfully!\n";
    }

    // Create property market data table
    $sql = "CREATE TABLE IF NOT EXISTS `property_market_data` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `location` VARCHAR(255) NOT NULL,
        `property_type` VARCHAR(100) NOT NULL,
        `data_date` DATE NOT NULL,
        `avg_price_per_sqft` DECIMAL(10,2) NULL,
        `median_price` DECIMAL(12,2) NULL,
        `price_trend_percentage` DECIMAL(5,2) NULL COMMENT 'Price change percentage',
        `days_on_market_avg` DECIMAL(6,1) NULL,
        `inventory_count` INT NULL,
        `sales_volume` DECIMAL(15,2) NULL,
        `rental_yield_avg` DECIMAL(4,2) NULL,
        `market_sentiment` ENUM('bullish','neutral','bearish') DEFAULT 'neutral',
        `confidence_score` DECIMAL(3,1) DEFAULT 75.0,
        `data_source` VARCHAR(100) DEFAULT 'internal',
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

        INDEX `idx_market_location` (`location`),
        INDEX `idx_market_type` (`property_type`),
        INDEX `idx_market_date` (`data_date`),
        INDEX `idx_market_sentiment` (`market_sentiment`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Property market data table created successfully!\n";
    }

    // Insert sample portfolio data
    $samplePortfolios = [
        [
            'Investment Portfolio - Mumbai',
            'Core investment portfolio focused on Mumbai residential properties',
            'investment',
            1,
            'associate',
            0,
            0,
            0,
            0,
            0,
            0,
            1,
            '{"total_value_target": 50000000, "monthly_income_target": 200000}',
            'moderate'
        ],
        [
            'Rental Portfolio - Delhi NCR',
            'Income-generating rental properties in Delhi NCR region',
            'rental',
            1,
            'associate',
            0,
            0,
            0,
            0,
            0,
            0,
            1,
            '{"occupancy_target": 95, "rental_yield_target": 4.5}',
            'conservative'
        ]
    ];

    $insertPortfolioSql = "INSERT IGNORE INTO `property_portfolios` (`portfolio_name`, `portfolio_description`, `portfolio_type`, `owner_id`, `owner_type`, `total_properties`, `total_value`, `portfolio_roi`, `monthly_income`, `monthly_expenses`, `net_monthly_income`, `is_active`, `portfolio_goals`, `risk_profile`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($insertPortfolioSql);

    foreach ($samplePortfolios as $portfolio) {
        $stmt->execute($portfolio);
    }

    echo "✅ Sample portfolios inserted successfully!\n";

    echo "\n🎉 Property portfolio management dashboard database setup completed!\n";

} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
