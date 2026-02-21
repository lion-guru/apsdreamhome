<?php
/**
 * Script to create AI property recommendation system tables
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

    // Create user preferences table
    $sql = "CREATE TABLE IF NOT EXISTS `user_property_preferences` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `user_id` INT NOT NULL,
        `property_type` VARCHAR(100) NULL,
        `min_price` DECIMAL(15,2) NULL,
        `max_price` DECIMAL(15,2) NULL,
        `preferred_locations` JSON NULL COMMENT 'Array of preferred locations',
        `bedrooms` INT NULL,
        `bathrooms` INT NULL,
        `min_area` DECIMAL(10,2) NULL,
        `max_area` DECIMAL(10,2) NULL,
        `amenities` JSON NULL COMMENT 'Preferred amenities',
        `property_age_preference` ENUM('new','under_construction','ready_to_move','resale') NULL,
        `furnishing_preference` ENUM('furnished','semi_furnished','unfurnished') NULL,
        `parking_required` TINYINT(1) DEFAULT 0,
        `budget_flexibility` DECIMAL(5,2) DEFAULT 10 COMMENT 'Budget flexibility percentage',
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

        INDEX `idx_user_preferences` (`user_id`),
        INDEX `idx_property_type_pref` (`property_type`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ User property preferences table created successfully!\n";
    }

    // Create property ratings and reviews table
    $sql = "CREATE TABLE IF NOT EXISTS `property_ratings` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `user_id` INT NOT NULL,
        `property_id` INT NOT NULL,
        `rating` DECIMAL(3,2) NOT NULL COMMENT 'Rating from 1.0 to 5.0',
        `review_text` TEXT NULL,
        `rating_criteria` JSON NULL COMMENT 'Detailed ratings for different aspects',
        `is_verified_viewing` TINYINT(1) DEFAULT 0,
        `helpful_votes` INT DEFAULT 0,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

        UNIQUE KEY `unique_user_property_rating` (`user_id`, `property_id`),
        INDEX `idx_property_rating` (`property_id`),
        INDEX `idx_rating_score` (`rating`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Property ratings table created successfully!\n";
    }

    // Create user browsing history table
    $sql = "CREATE TABLE IF NOT EXISTS `user_browsing_history` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `user_id` INT NOT NULL,
        `property_id` INT NOT NULL,
        `session_id` VARCHAR(255) NULL,
        `action_type` ENUM('view','favorite','contact','share','compare','inquiry') NOT NULL,
        `duration_seconds` INT NULL COMMENT 'Time spent viewing',
        `device_type` ENUM('desktop','mobile','tablet') NULL,
        `ip_address` VARCHAR(45) NULL,
        `user_agent` TEXT NULL,
        `referrer_url` VARCHAR(500) NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

        INDEX `idx_user_history` (`user_id`, `created_at`),
        INDEX `idx_property_history` (`property_id`),
        INDEX `idx_session_history` (`session_id`),
        INDEX `idx_action_history` (`action_type`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ User browsing history table created successfully!\n";
    }

    // Create recommendation engine settings table
    $sql = "CREATE TABLE IF NOT EXISTS `recommendation_settings` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `setting_key` VARCHAR(100) NOT NULL UNIQUE,
        `setting_value` TEXT NULL,
        `description` VARCHAR(255) NULL,
        `algorithm_type` ENUM('collaborative','content_based','hybrid','popularity') DEFAULT 'hybrid',
        `is_active` TINYINT(1) DEFAULT 1,
        `updated_by` INT NULL,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

        INDEX `idx_recommendation_setting` (`setting_key`),
        INDEX `idx_algorithm_type` (`algorithm_type`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Recommendation settings table created successfully!\n";
    }

    // Create property similarity matrix table
    $sql = "CREATE TABLE IF NOT EXISTS `property_similarity` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `property_id_1` INT NOT NULL,
        `property_id_2` INT NOT NULL,
        `similarity_score` DECIMAL(5,4) NOT NULL COMMENT 'Similarity score from 0.0000 to 1.0000',
        `similarity_factors` JSON NULL COMMENT 'Factors contributing to similarity',
        `last_calculated` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

        UNIQUE KEY `unique_property_similarity` (`property_id_1`, `property_id_2`),
        INDEX `idx_property_1` (`property_id_1`),
        INDEX `idx_property_2` (`property_id_2`),
        INDEX `idx_similarity_score` (`similarity_score`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Property similarity table created successfully!\n";
    }

    // Create user similarity matrix table
    $sql = "CREATE TABLE IF NOT EXISTS `user_similarity` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `user_id_1` INT NOT NULL,
        `user_id_2` INT NOT NULL,
        `similarity_score` DECIMAL(5,4) NOT NULL COMMENT 'Similarity score from 0.0000 to 1.0000',
        `common_preferences` JSON NULL,
        `last_calculated` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

        UNIQUE KEY `unique_user_similarity` (`user_id_1`, `user_id_2`),
        INDEX `idx_user_1` (`user_id_1`),
        INDEX `idx_user_2` (`user_id_2`),
        INDEX `idx_user_similarity_score` (`similarity_score`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ User similarity table created successfully!\n";
    }

    // Create recommendation cache table
    $sql = "CREATE TABLE IF NOT EXISTS `recommendation_cache` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `user_id` INT NOT NULL,
        `recommendation_type` ENUM('personalized','similar_users','similar_properties','trending','location_based') NOT NULL,
        `property_ids` JSON NOT NULL COMMENT 'Array of recommended property IDs',
        `scores` JSON NULL COMMENT 'Recommendation scores for each property',
        `cache_expires_at` TIMESTAMP NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

        INDEX `idx_user_cache` (`user_id`),
        INDEX `idx_recommendation_type` (`recommendation_type`),
        INDEX `idx_cache_expires` (`cache_expires_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Recommendation cache table created successfully!\n";
    }

    // Insert default recommendation settings
    $defaultSettings = [
        ['algorithm_type', 'hybrid', 'Primary recommendation algorithm type'],
        ['collaborative_weight', '0.6', 'Weight for collaborative filtering (0-1)'],
        ['content_based_weight', '0.3', 'Weight for content-based filtering (0-1)'],
        ['popularity_weight', '0.1', 'Weight for popularity-based recommendations (0-1)'],
        ['min_ratings_for_similarity', '3', 'Minimum ratings needed to calculate user similarity'],
        ['max_recommendations', '20', 'Maximum number of recommendations to return'],
        ['cache_duration_hours', '24', 'How long to cache recommendations (hours)'],
        ['enable_location_based', '1', 'Enable location-based recommendations'],
        ['enable_price_based', '1', 'Enable price-based recommendations'],
        ['enable_amenity_based', '1', 'Enable amenity-based recommendations'],
        ['new_user_fallback', 'trending', 'Fallback algorithm for new users']
    ];

    $insertSql = "INSERT IGNORE INTO `recommendation_settings` (`setting_key`, `setting_value`, `description`, `algorithm_type`, `is_active`) VALUES (?, ?, ?, 'hybrid', 1)";
    $stmt = $pdo->prepare($insertSql);

    foreach ($defaultSettings as $setting) {
        $stmt->execute($setting);
    }

    echo "✅ Default recommendation settings inserted successfully!\n";

    echo "\n🎉 AI Property Recommendation system database setup completed!\n";

} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
