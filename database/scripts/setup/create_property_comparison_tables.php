<?php
/**
 * Script to create property comparison tool tables
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

    // Create property comparison sessions table
    $sql = "CREATE TABLE IF NOT EXISTS `property_comparison_sessions` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `session_key` VARCHAR(100) NOT NULL UNIQUE,
        `user_id` INT NULL,
        `user_type` ENUM('customer','employee','associate','admin','guest') DEFAULT 'guest',
        `session_name` VARCHAR(255) NULL,
        `max_properties` INT DEFAULT 4,
        `comparison_criteria` JSON NOT NULL COMMENT 'Selected comparison criteria',
        `is_active` TINYINT(1) DEFAULT 1,
        `expires_at` TIMESTAMP NULL,
        `device_info` JSON NULL,
        `ip_address` VARCHAR(45) NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

        INDEX `idx_session_user` (`user_id`, `user_type`),
        INDEX `idx_session_key` (`session_key`),
        INDEX `idx_session_active` (`is_active`),
        INDEX `idx_session_expires` (`expires_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Property comparison sessions table created successfully!\n";
    }

    // Create session properties table
    $sql = "CREATE TABLE IF NOT EXISTS `comparison_session_properties` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `session_id` INT NOT NULL,
        `property_id` INT NOT NULL,
        `added_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `sort_order` INT DEFAULT 0,

        FOREIGN KEY (`session_id`) REFERENCES `property_comparison_sessions`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`property_id`) REFERENCES `properties`(`id`) ON DELETE CASCADE,
        UNIQUE KEY `unique_session_property` (`session_id`, `property_id`),
        INDEX `idx_session_prop_session` (`session_id`),
        INDEX `idx_session_prop_property` (`property_id`),
        INDEX `idx_session_prop_order` (`sort_order`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Comparison session properties table created successfully!\n";
    }

    // Create comparison criteria table
    $sql = "CREATE TABLE IF NOT EXISTS `comparison_criteria` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `criteria_key` VARCHAR(100) NOT NULL UNIQUE,
        `criteria_name` VARCHAR(255) NOT NULL,
        `criteria_group` ENUM('basic','pricing','location','features','amenities','legal','other') DEFAULT 'basic',
        `data_type` ENUM('text','number','currency','boolean','date','rating','list') DEFAULT 'text',
        `display_format` VARCHAR(50) NULL,
        `is_default` TINYINT(1) DEFAULT 0,
        `is_active` TINYINT(1) DEFAULT 1,
        `sort_order` INT DEFAULT 0,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

        INDEX `idx_criteria_key` (`criteria_key`),
        INDEX `idx_criteria_group` (`criteria_group`),
        INDEX `idx_criteria_active` (`is_active`),
        INDEX `idx_criteria_order` (`sort_order`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Comparison criteria table created successfully!\n";
    }

    // Create property comparison data table
    $sql = "CREATE TABLE IF NOT EXISTS `property_comparison_data` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `property_id` INT NOT NULL,
        `criteria_id` INT NOT NULL,
        `criteria_value` TEXT NULL,
        `data_source` ENUM('manual','api','calculated','imported') DEFAULT 'manual',
        `confidence_score` DECIMAL(3,2) NULL COMMENT 'Confidence in data accuracy (0-1)',
        `last_updated` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        `updated_by` INT NULL,

        FOREIGN KEY (`property_id`) REFERENCES `properties`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`criteria_id`) REFERENCES `comparison_criteria`(`id`) ON DELETE CASCADE,
        UNIQUE KEY `unique_property_criteria` (`property_id`, `criteria_id`),
        INDEX `idx_comp_data_property` (`property_id`),
        INDEX `idx_comp_data_criteria` (`criteria_id`),
        INDEX `idx_comp_data_source` (`data_source`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Property comparison data table created successfully!\n";
    }

    // Create comparison analytics table
    $sql = "CREATE TABLE IF NOT EXISTS `comparison_analytics` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `session_id` INT NULL,
        `user_id` INT NULL,
        `user_type` ENUM('customer','employee','associate','admin','guest') DEFAULT 'guest',
        `event_type` ENUM('session_created','property_added','property_removed','criteria_changed','comparison_viewed','export_generated','share_generated') NOT NULL,
        `event_data` JSON NULL,
        `properties_compared` JSON NULL COMMENT 'Array of property IDs being compared',
        `criteria_used` JSON NULL COMMENT 'Array of criteria keys used',
        `session_duration` INT NULL COMMENT 'Session duration in seconds',
        `device_type` ENUM('desktop','mobile','tablet') DEFAULT 'desktop',
        `ip_address` VARCHAR(45) NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

        FOREIGN KEY (`session_id`) REFERENCES `property_comparison_sessions`(`id`) ON DELETE SET NULL,
        INDEX `idx_analytics_session` (`session_id`),
        INDEX `idx_analytics_user` (`user_id`, `user_type`),
        INDEX `idx_analytics_event` (`event_type`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Comparison analytics table created successfully!\n";
    }

    // Create saved comparisons table
    $sql = "CREATE TABLE IF NOT EXISTS `saved_comparisons` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `user_id` INT NULL,
        `user_type` ENUM('customer','employee','associate','admin') DEFAULT 'customer',
        `comparison_name` VARCHAR(255) NOT NULL,
        `comparison_description` TEXT NULL,
        `property_ids` JSON NOT NULL COMMENT 'Array of property IDs',
        `comparison_criteria` JSON NOT NULL COMMENT 'Array of criteria keys',
        `is_public` TINYINT(1) DEFAULT 0,
        `view_count` INT DEFAULT 0,
        `share_count` INT DEFAULT 0,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

        INDEX `idx_saved_user` (`user_id`, `user_type`),
        INDEX `idx_saved_public` (`is_public`),
        INDEX `idx_saved_created` (`created_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Saved comparisons table created successfully!\n";
    }

    // Insert default comparison criteria
    $defaultCriteria = [
        ['property_title', 'Property Title', 'basic', 'text', null, 1, 1, 1],
        ['property_type', 'Property Type', 'basic', 'text', null, 1, 1, 2],
        ['price', 'Price', 'pricing', 'currency', '₹{value}', 1, 1, 3],
        ['area_sqft', 'Area (Sq Ft)', 'basic', 'number', '{value} sq ft', 1, 1, 4],
        ['bedrooms', 'Bedrooms', 'features', 'number', null, 1, 1, 5],
        ['bathrooms', 'Bathrooms', 'features', 'number', null, 1, 1, 6],
        ['location', 'Location', 'location', 'text', null, 1, 1, 7],
        ['city', 'City', 'location', 'text', null, 1, 1, 8],
        ['furnishing_status', 'Furnishing Status', 'features', 'text', null, 1, 1, 9],
        ['parking_available', 'Parking Available', 'amenities', 'boolean', null, 1, 1, 10],
        ['age_years', 'Property Age', 'basic', 'number', '{value} years', 1, 1, 11],
        ['floor_number', 'Floor Number', 'features', 'number', null, 0, 1, 12],
        ['total_floors', 'Total Floors', 'features', 'number', null, 0, 1, 13],
        ['possession_status', 'Possession Status', 'legal', 'text', null, 1, 1, 14],
        ['rera_registered', 'RERA Registered', 'legal', 'boolean', null, 1, 1, 15],
        ['maintenance_cost', 'Monthly Maintenance', 'pricing', 'currency', '₹{value}/month', 0, 1, 16],
        ['power_backup', 'Power Backup', 'amenities', 'boolean', null, 0, 1, 17],
        ['water_supply', '24/7 Water Supply', 'amenities', 'boolean', null, 0, 1, 18],
        ['security_features', 'Security Features', 'amenities', 'text', null, 0, 1, 19],
        ['nearby_amenities', 'Nearby Amenities', 'location', 'list', null, 0, 1, 20]
    ];

    $insertCriteriaSql = "INSERT IGNORE INTO `comparison_criteria` (`criteria_key`, `criteria_name`, `criteria_group`, `data_type`, `display_format`, `is_default`, `is_active`, `sort_order`) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($insertCriteriaSql);

    foreach ($defaultCriteria as $criteria) {
        $stmt->execute($criteria);
    }

    echo "✅ Default comparison criteria inserted successfully!\n";

    echo "\n🎉 Property comparison tool database setup completed!\n";

} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
