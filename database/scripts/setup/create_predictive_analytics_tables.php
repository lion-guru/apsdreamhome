<?php
/**
 * Script to create AI predictive analytics and sales forecasting tables
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

    // Create sales data table (historical sales transactions)
    $sql = "CREATE TABLE IF NOT EXISTS `sales_data` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `property_id` INT NULL,
        `transaction_date` DATE NOT NULL,
        `sale_price` DECIMAL(15,2) NOT NULL,
        `original_price` DECIMAL(15,2) NULL,
        `discount_amount` DECIMAL(15,2) DEFAULT 0,
        `property_type` VARCHAR(100) NULL,
        `location` VARCHAR(255) NULL,
        `city` VARCHAR(100) NULL,
        `area_sqft` DECIMAL(10,2) NULL,
        `bedrooms` INT NULL,
        `bathrooms` INT NULL,
        `age_years` INT NULL,
        `time_to_sell_days` INT NULL,
        `marketing_channel` ENUM('website','agent','direct','advertisement','referral') DEFAULT 'website',
        `buyer_type` ENUM('individual','investor','company','nri','other') DEFAULT 'individual',
        `season` ENUM('winter','summer','monsoon','spring') NULL,
        `economic_indicators` JSON NULL COMMENT 'Interest rates, inflation, etc.',
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

        INDEX `idx_sale_date` (`transaction_date`),
        INDEX `idx_sale_property` (`property_id`),
        INDEX `idx_sale_location` (`city`),
        INDEX `idx_sale_type` (`property_type`),
        INDEX `idx_sale_channel` (`marketing_channel`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Sales data table created successfully!\n";
    }

    // Create market trends table
    $sql = "CREATE TABLE IF NOT EXISTS `market_trends` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `trend_date` DATE NOT NULL,
        `location` VARCHAR(255) NOT NULL,
        `property_type` VARCHAR(100) NULL,
        `avg_price_per_sqft` DECIMAL(10,2) NULL,
        `price_change_percentage` DECIMAL(5,2) NULL COMMENT 'Month-over-month change',
        `inventory_count` INT NULL,
        `days_on_market_avg` DECIMAL(6,2) NULL,
        `demand_index` DECIMAL(5,2) NULL COMMENT 'Demand score 0-100',
        `supply_index` DECIMAL(5,2) NULL COMMENT 'Supply score 0-100',
        `rental_yield_percentage` DECIMAL(5,2) NULL,
        `interest_rate` DECIMAL(4,2) NULL,
        `inflation_rate` DECIMAL(4,2) NULL,
        `gdp_growth` DECIMAL(4,2) NULL,
        `confidence_index` DECIMAL(5,2) NULL COMMENT 'Market confidence 0-100',
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

        UNIQUE KEY `unique_trend_location_date` (`trend_date`, `location`, `property_type`),
        INDEX `idx_trend_date` (`trend_date`),
        INDEX `idx_trend_location` (`location`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Market trends table created successfully!\n";
    }

    // Create predictive models table
    $sql = "CREATE TABLE IF NOT EXISTS `predictive_models` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `model_name` VARCHAR(255) NOT NULL,
        `model_type` ENUM('price_prediction','demand_forecast','sales_forecast','market_trend') NOT NULL,
        `algorithm` ENUM('linear_regression','polynomial_regression','arima','exponential_smoothing','neural_network','random_forest') DEFAULT 'linear_regression',
        `target_variable` VARCHAR(100) NOT NULL,
        `features` JSON NOT NULL COMMENT 'Array of feature names used in the model',
        `parameters` JSON NULL COMMENT 'Model parameters/hyperparameters',
        `accuracy_score` DECIMAL(5,4) NULL COMMENT 'Model accuracy (0-1)',
        `training_data_period` VARCHAR(50) NULL COMMENT 'Period used for training',
        `last_trained` DATETIME NULL,
        `is_active` TINYINT(1) DEFAULT 1,
        `performance_metrics` JSON NULL COMMENT 'Detailed performance metrics',
        `created_by` INT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

        INDEX `idx_model_type` (`model_type`),
        INDEX `idx_model_active` (`is_active`),
        INDEX `idx_model_accuracy` (`accuracy_score`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Predictive models table created successfully!\n";
    }

    // Create forecast results table
    $sql = "CREATE TABLE IF NOT EXISTS `forecast_results` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `model_id` INT NOT NULL,
        `forecast_date` DATE NOT NULL,
        `forecast_period` VARCHAR(50) NOT NULL COMMENT 'e.g., 2024-Q1, 2024-M01',
        `forecast_value` DECIMAL(15,2) NOT NULL,
        `confidence_interval_lower` DECIMAL(15,2) NULL,
        `confidence_interval_upper` DECIMAL(15,2) NULL,
        `actual_value` DECIMAL(15,2) NULL COMMENT 'Filled when actual data becomes available',
        `accuracy_error` DECIMAL(10,2) NULL COMMENT 'Forecast error percentage',
        `forecast_type` ENUM('price','sales_volume','demand','revenue') DEFAULT 'price',
        `location` VARCHAR(255) NULL,
        `property_type` VARCHAR(100) NULL,
        `metadata` JSON NULL COMMENT 'Additional forecast metadata',
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

        FOREIGN KEY (`model_id`) REFERENCES `predictive_models`(`id`) ON DELETE CASCADE,
        INDEX `idx_forecast_model` (`model_id`),
        INDEX `idx_forecast_date` (`forecast_date`),
        INDEX `idx_forecast_period` (`forecast_period`),
        INDEX `idx_forecast_type` (`forecast_type`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Forecast results table created successfully!\n";
    }

    // Create seasonality patterns table
    $sql = "CREATE TABLE IF NOT EXISTS `seasonality_patterns` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `location` VARCHAR(255) NOT NULL,
        `property_type` VARCHAR(100) NULL,
        `metric_type` ENUM('sales_volume','price_index','demand_index') NOT NULL,
        `month` TINYINT NOT NULL COMMENT '1-12',
        `seasonal_multiplier` DECIMAL(5,4) NOT NULL COMMENT 'Seasonal adjustment factor',
        `trend_adjustment` DECIMAL(5,4) DEFAULT 0,
        `confidence_score` DECIMAL(5,4) NULL,
        `last_updated` DATE NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

        UNIQUE KEY `unique_seasonal_pattern` (`location`, `property_type`, `metric_type`, `month`),
        INDEX `idx_seasonal_location` (`location`),
        INDEX `idx_seasonal_metric` (`metric_type`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Seasonality patterns table created successfully!\n";
    }

    // Create analytics cache table
    $sql = "CREATE TABLE IF NOT EXISTS `analytics_cache` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `cache_key` VARCHAR(255) NOT NULL UNIQUE,
        `cache_data` JSON NOT NULL,
        `expires_at` TIMESTAMP NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

        INDEX `idx_cache_key` (`cache_key`),
        INDEX `idx_cache_expires` (`expires_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Analytics cache table created successfully!\n";
    }

    // Insert sample sales data for demonstration
    $sampleSales = [
        ['2023-01-15', 2500000.00, 2800000.00, 300000.00, 'Apartment', 'Sector 62, Noida', 'Noida', 1200.00, 3, 2, 2, 45, 'website', 'individual', 'winter'],
        ['2023-02-20', 3500000.00, 3800000.00, 300000.00, 'Villa', 'DLF Phase 1, Gurgaon', 'Gurgaon', 2500.00, 4, 3, 1, 30, 'agent', 'investor', 'winter'],
        ['2023-03-10', 1800000.00, 2000000.00, 200000.00, 'Apartment', 'Indirapuram, Ghaziabad', 'Ghaziabad', 1000.00, 2, 2, 3, 60, 'direct', 'individual', 'spring'],
        ['2023-04-05', 4200000.00, 4500000.00, 300000.00, 'Penthouse', 'Golf Course Road, Gurgaon', 'Gurgaon', 3000.00, 4, 4, 0, 25, 'website', 'nri', 'spring'],
        ['2023-05-18', 2900000.00, 3200000.00, 300000.00, 'Apartment', 'Sector 18, Noida', 'Noida', 1400.00, 3, 3, 1, 40, 'agent', 'company', 'summer'],
        ['2023-06-12', 2200000.00, 2400000.00, 200000.00, 'Apartment', 'Vaishali, Ghaziabad', 'Ghaziabad', 1100.00, 2, 2, 4, 55, 'advertisement', 'individual', 'summer'],
        ['2023-07-25', 3800000.00, 4100000.00, 300000.00, 'Villa', 'Sushant Lok, Gurgaon', 'Gurgaon', 2800.00, 5, 4, 2, 35, 'referral', 'investor', 'monsoon'],
        ['2023-08-08', 1950000.00, 2100000.00, 150000.00, 'Apartment', 'Crossing Republik, Ghaziabad', 'Ghaziabad', 950.00, 2, 2, 0, 42, 'website', 'individual', 'monsoon'],
        ['2023-09-14', 4600000.00, 4800000.00, 200000.00, 'Penthouse', 'MG Road, Gurgaon', 'Gurgaon', 3500.00, 5, 5, 1, 28, 'agent', 'nri', 'monsoon'],
        ['2023-10-30', 3100000.00, 3400000.00, 300000.00, 'Apartment', 'Sector 137, Noida', 'Noida', 1600.00, 4, 3, 1, 38, 'website', 'individual', 'winter'],
        ['2023-11-22', 2700000.00, 2900000.00, 200000.00, 'Apartment', 'Raj Nagar Extension, Ghaziabad', 'Ghaziabad', 1300.00, 3, 2, 2, 48, 'direct', 'company', 'winter'],
        ['2023-12-10', 5200000.00, 5500000.00, 300000.00, 'Villa', 'Palam Vihar, Gurgaon', 'Gurgaon', 3200.00, 6, 5, 3, 32, 'referral', 'investor', 'winter']
    ];

    $insertSql = "INSERT IGNORE INTO `sales_data` (`transaction_date`, `sale_price`, `original_price`, `discount_amount`, `property_type`, `location`, `city`, `area_sqft`, `bedrooms`, `bathrooms`, `age_years`, `time_to_sell_days`, `marketing_channel`, `buyer_type`, `season`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($insertSql);

    foreach ($sampleSales as $sale) {
        $stmt->execute($sale);
    }

    echo "✅ Sample sales data inserted successfully!\n";

    // Insert default predictive model
    $defaultModel = [
        'Price Prediction Model',
        'price_prediction',
        'linear_regression',
        'sale_price',
        '["area_sqft","bedrooms","bathrooms","age_years","location","property_type"]',
        '{"intercept": 1000000, "coefficients": {"area_sqft": 1500, "bedrooms": 200000, "bathrooms": 100000, "age_years": -25000}}',
        0.85,
        '2023-01-01 to 2023-12-31',
        date('Y-m-d H:i:s'),
        1,
        '{"mse": 250000000, "r2_score": 0.85, "mae": 150000}'
    ];

    $modelSql = "INSERT IGNORE INTO `predictive_models` (`model_name`, `model_type`, `algorithm`, `target_variable`, `features`, `parameters`, `accuracy_score`, `training_data_period`, `last_trained`, `is_active`, `performance_metrics`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($modelSql);
    $stmt->execute($defaultModel);

    echo "✅ Default predictive model inserted successfully!\n";

    echo "\n🎉 AI Predictive Analytics and Sales Forecasting system database setup completed!\n";

} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
