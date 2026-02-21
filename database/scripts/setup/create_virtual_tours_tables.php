<?php
/**
 * Script to create virtual tours and 360° view system tables
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

    // Create virtual tours table
    $sql = "CREATE TABLE IF NOT EXISTS `virtual_tours` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `property_id` INT NOT NULL,
        `tour_title` VARCHAR(255) NOT NULL,
        `tour_description` TEXT NULL,
        `tour_type` ENUM('360_tour','video_tour','interactive_tour','floor_plan_tour') DEFAULT '360_tour',
        `status` ENUM('draft','published','archived','processing') DEFAULT 'draft',
        `is_featured` TINYINT(1) DEFAULT 0,
        `duration_minutes` INT NULL COMMENT 'Estimated tour duration',
        `view_count` INT DEFAULT 0,
        `like_count` INT DEFAULT 0,
        `share_count` INT DEFAULT 0,
        `completion_rate` DECIMAL(5,2) DEFAULT 0 COMMENT 'Percentage of users who complete the tour',
        `seo_title` VARCHAR(255) NULL,
        `seo_description` TEXT NULL,
        `tour_settings` JSON NULL COMMENT 'Tour configuration settings',
        `created_by` INT NULL,
        `published_at` DATETIME NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

        FOREIGN KEY (`property_id`) REFERENCES `properties`(`id`) ON DELETE CASCADE,
        INDEX `idx_tour_property` (`property_id`),
        INDEX `idx_tour_status` (`status`),
        INDEX `idx_tour_type` (`tour_type`),
        INDEX `idx_tour_featured` (`is_featured`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Virtual tours table created successfully!\n";
    }

    // Create tour scenes table (360° images/panoramas)
    $sql = "CREATE TABLE IF NOT EXISTS `tour_scenes` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `tour_id` INT NOT NULL,
        `scene_title` VARCHAR(255) NOT NULL,
        `scene_description` TEXT NULL,
        `scene_order` INT DEFAULT 0,
        `scene_type` ENUM('panorama','equirectangular','cubemap','flat_image') DEFAULT 'panorama',
        `image_path` VARCHAR(500) NOT NULL,
        `thumbnail_path` VARCHAR(500) NULL,
        `north_offset` DECIMAL(6,2) DEFAULT 0 COMMENT 'North rotation offset in degrees',
        `initial_view` JSON NULL COMMENT 'Initial viewing parameters',
        `audio_path` VARCHAR(500) NULL COMMENT 'Background audio for scene',
        `transition_type` ENUM('fade','slide','instant') DEFAULT 'fade',
        `transition_duration` INT DEFAULT 1000 COMMENT 'Transition duration in milliseconds',
        `hotspots` JSON NULL COMMENT 'Embedded hotspots data',
        `is_start_scene` TINYINT(1) DEFAULT 0,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

        FOREIGN KEY (`tour_id`) REFERENCES `virtual_tours`(`id`) ON DELETE CASCADE,
        INDEX `idx_scene_tour` (`tour_id`),
        INDEX `idx_scene_order` (`scene_order`),
        INDEX `idx_scene_start` (`is_start_scene`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Tour scenes table created successfully!\n";
    }

    // Create tour hotspots table
    $sql = "CREATE TABLE IF NOT EXISTS `tour_hotspots` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `scene_id` INT NOT NULL,
        `hotspot_type` ENUM('info','navigation','image','video','audio','property_info','floor_plan','amenity') DEFAULT 'info',
        `hotspot_title` VARCHAR(255) NOT NULL,
        `hotspot_description` TEXT NULL,
        `position_x` DECIMAL(6,4) NOT NULL COMMENT 'X coordinate (0-1)',
        `position_y` DECIMAL(6,4) NOT NULL COMMENT 'Y coordinate (0-1)',
        `position_z` DECIMAL(6,4) DEFAULT 0 COMMENT 'Z coordinate for 3D positioning',
        `target_scene_id` INT NULL COMMENT 'For navigation hotspots',
        `content_type` ENUM('text','image','video','audio','url','property_detail') DEFAULT 'text',
        `content_data` JSON NULL COMMENT 'Content data based on type',
        `icon_type` VARCHAR(50) DEFAULT 'info',
        `icon_color` VARCHAR(7) DEFAULT '#007bff',
        `is_active` TINYINT(1) DEFAULT 1,
        `click_count` INT DEFAULT 0,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

        FOREIGN KEY (`scene_id`) REFERENCES `tour_scenes`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`target_scene_id`) REFERENCES `tour_scenes`(`id`) ON DELETE SET NULL,
        INDEX `idx_hotspot_scene` (`scene_id`),
        INDEX `idx_hotspot_type` (`hotspot_type`),
        INDEX `idx_hotspot_active` (`is_active`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Tour hotspots table created successfully!\n";
    }

    // Create tour assets table (additional media files)
    $sql = "CREATE TABLE IF NOT EXISTS `tour_assets` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `tour_id` INT NOT NULL,
        `asset_type` ENUM('image','video','audio','document','floor_plan') NOT NULL,
        `asset_title` VARCHAR(255) NOT NULL,
        `asset_description` TEXT NULL,
        `file_path` VARCHAR(500) NOT NULL,
        `file_size` INT NOT NULL,
        `mime_type` VARCHAR(100) NOT NULL,
        `thumbnail_path` VARCHAR(500) NULL,
        `is_featured` TINYINT(1) DEFAULT 0,
        `sort_order` INT DEFAULT 0,
        `metadata` JSON NULL COMMENT 'Additional asset metadata',
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

        FOREIGN KEY (`tour_id`) REFERENCES `virtual_tours`(`id`) ON DELETE CASCADE,
        INDEX `idx_asset_tour` (`tour_id`),
        INDEX `idx_asset_type` (`asset_type`),
        INDEX `idx_asset_featured` (`is_featured`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Tour assets table created successfully!\n";
    }

    // Create tour analytics table
    $sql = "CREATE TABLE IF NOT EXISTS `tour_analytics` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `tour_id` INT NOT NULL,
        `user_id` INT NULL,
        `user_type` ENUM('customer','guest') DEFAULT 'guest',
        `session_id` VARCHAR(255) NULL,
        `event_type` ENUM('view','start','complete','hotspot_click','scene_change','share','like','comment') NOT NULL,
        `event_data` JSON NULL COMMENT 'Additional event data',
        `scene_id` INT NULL,
        `hotspot_id` INT NULL,
        `duration_seconds` INT NULL,
        `device_type` ENUM('desktop','mobile','tablet','vr') DEFAULT 'desktop',
        `browser_info` JSON NULL,
        `ip_address` VARCHAR(45) NULL,
        `referrer_url` VARCHAR(500) NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

        FOREIGN KEY (`tour_id`) REFERENCES `virtual_tours`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`scene_id`) REFERENCES `tour_scenes`(`id`) ON DELETE SET NULL,
        FOREIGN KEY (`hotspot_id`) REFERENCES `tour_hotspots`(`id`) ON DELETE SET NULL,
        INDEX `idx_analytics_tour` (`tour_id`),
        INDEX `idx_analytics_user` (`user_id`, `user_type`),
        INDEX `idx_analytics_event` (`event_type`),
        INDEX `idx_analytics_session` (`session_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Tour analytics table created successfully!\n";
    }

    // Create tour comments table
    $sql = "CREATE TABLE IF NOT EXISTS `tour_comments` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `tour_id` INT NOT NULL,
        `user_id` INT NULL,
        `user_type` ENUM('customer','employee','admin') DEFAULT 'customer',
        `user_name` VARCHAR(255) NULL,
        `user_email` VARCHAR(255) NULL,
        `comment_text` TEXT NOT NULL,
        `rating` DECIMAL(3,2) NULL COMMENT 'Tour rating 1-5',
        `parent_comment_id` INT NULL,
        `is_approved` TINYINT(1) DEFAULT 1,
        `helpful_votes` INT DEFAULT 0,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

        FOREIGN KEY (`tour_id`) REFERENCES `virtual_tours`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`parent_comment_id`) REFERENCES `tour_comments`(`id`) ON DELETE CASCADE,
        INDEX `idx_comment_tour` (`tour_id`),
        INDEX `idx_comment_user` (`user_id`, `user_type`),
        INDEX `idx_comment_approved` (`is_approved`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Tour comments table created successfully!\n";
    }

    // Create tour templates table
    $sql = "CREATE TABLE IF NOT EXISTS `tour_templates` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `template_name` VARCHAR(255) NOT NULL,
        `template_description` TEXT NULL,
        `template_type` ENUM('residential','commercial','apartment','villa','office') DEFAULT 'residential',
        `default_scenes` JSON NOT NULL COMMENT 'Default scene configuration',
        `default_hotspots` JSON NULL COMMENT 'Default hotspot configuration',
        `is_premium` TINYINT(1) DEFAULT 0,
        `usage_count` INT DEFAULT 0,
        `is_active` TINYINT(1) DEFAULT 1,
        `created_by` INT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

        INDEX `idx_template_type` (`template_type`),
        INDEX `idx_template_active` (`is_active`),
        INDEX `idx_template_premium` (`is_premium`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Tour templates table created successfully!\n";
    }

    // Insert default tour templates
    $defaultTemplates = [
        [
            'Apartment Tour Template',
            'Standard 2-3 BHK apartment virtual tour template',
            'apartment',
            '{"scenes": ["Entrance", "Living Room", "Kitchen", "Master Bedroom", "Bathroom", "Balcony"], "duration": 15}',
            '{"hotspots": ["Property Info", "Floor Plan", "Amenities", "Location Map"]}',
            0,
            0,
            1
        ],
        [
            'Villa Tour Template',
            'Luxury villa virtual tour with multiple floors',
            'villa',
            '{"scenes": ["Entrance", "Living Room", "Dining Room", "Kitchen", "Master Suite", "Guest Rooms", "Garden", "Pool"], "duration": 25}',
            '{"hotspots": ["Property Features", "Floor Plans", "Garden Details", "Pool Features"]}',
            1,
            0,
            1
        ],
        [
            'Commercial Space Template',
            'Office/retail space virtual tour template',
            'commercial',
            '{"scenes": ["Entrance", "Reception", "Main Area", "Conference Room", "Restrooms", "Parking"], "duration": 12}',
            '{"hotspots": ["Space Specs", "Lease Terms", "Nearby Amenities", "Transportation"]}',
            0,
            0,
            1
        ]
    ];

    $insertTemplateSql = "INSERT IGNORE INTO `tour_templates` (`template_name`, `template_description`, `template_type`, `default_scenes`, `default_hotspots`, `is_premium`, `usage_count`, `is_active`) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($insertTemplateSql);

    foreach ($defaultTemplates as $template) {
        $stmt->execute($template);
    }

    echo "✅ Default tour templates inserted successfully!\n";

    echo "\n🎉 Virtual Tours and 360° View system database setup completed!\n";

} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
