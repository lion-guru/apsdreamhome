<?php
// Run via: php scripts/create_ai_tables.php
$root = dirname(__DIR__);
$config = require $root . '/config/database.php';
try {
    $pdo = new PDO("mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4",
        $config['username'], $config['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    
    $queries = [
        // 1. ai_market_trends (Missing)
        "CREATE TABLE IF NOT EXISTS `ai_market_trends` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `location` varchar(255) NOT NULL,
            `property_type` enum('plot','house','flat','shop','farmhouse','commercial') NOT NULL,
            `trend_direction` enum('up','down','stable') DEFAULT 'stable',
            `price_change_percent` decimal(5,2) DEFAULT 0.00,
            `forecast_next_month` decimal(15,2) DEFAULT NULL,
            `transactions_count` int(11) DEFAULT 0,
            `demand_index` int(11) DEFAULT 0,
            `supply_index` int(11) DEFAULT 0,
            `month` date NOT NULL,
            `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_trends_loc_type` (`location`,`property_type`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
        
        // 2. ai_property_valuations (Missing)
        "CREATE TABLE IF NOT EXISTS `ai_property_valuations` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `property_id` bigint(20) unsigned DEFAULT NULL,
            `location` varchar(255) NOT NULL,
            `property_type` varchar(50) NOT NULL,
            `area_sqft` decimal(10,2) NOT NULL,
            `bedrooms` int(11) DEFAULT 0,
            `bathrooms` int(11) DEFAULT 0,
            `age_years` int(11) DEFAULT 0,
            `amenities` text DEFAULT NULL,
            `nearby_facilities` text DEFAULT NULL,
            `predicted_price` decimal(15,2) NOT NULL,
            `price_per_sqft` decimal(12,2) NOT NULL,
            `confidence_score` decimal(3,2) NOT NULL,
            `price_range_low` decimal(15,2) NOT NULL,
            `price_range_high` decimal(15,2) NOT NULL,
            `comparable_properties` text DEFAULT NULL,
            `market_analysis` text DEFAULT NULL,
            `prediction_factors` text DEFAULT NULL,
            `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
        
        // 3. ai_generated_content (Missing)
        "CREATE TABLE IF NOT EXISTS `ai_generated_content` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `content_type` varchar(50) NOT NULL,
            `title` varchar(255) DEFAULT NULL,
            `content` text NOT NULL,
            `prompt` text NOT NULL,
            `model_used` varchar(50) DEFAULT 'gemini-1.5-flash',
            `tokens_used` int(11) DEFAULT 0,
            `user_id` int(11) DEFAULT NULL,
            `property_id` int(11) DEFAULT NULL,
            `is_published` tinyint(1) DEFAULT 0,
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_user` (`user_id`),
            KEY `idx_property` (`property_id`),
            KEY `idx_published` (`is_published`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

        // 4. ai_user_preferences (Missing)
        "CREATE TABLE IF NOT EXISTS `ai_user_preferences` (
            `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            `user_id` bigint(20) unsigned NOT NULL,
            `user_type` varchar(20) NOT NULL DEFAULT 'customer',
            `preferred_locations` text DEFAULT NULL,
            `preferred_property_types` text DEFAULT NULL,
            `budget_min` decimal(15,2) DEFAULT NULL,
            `budget_max` decimal(15,2) DEFAULT NULL,
            `preferred_amenities` text DEFAULT NULL,
            `must_have_features` text DEFAULT NULL,
            `family_size` int(11) DEFAULT NULL,
            `purpose` varchar(50) DEFAULT NULL,
            `urgency_level` varchar(20) DEFAULT 'medium',
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uniq_user_type` (`user_id`,`user_type`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

        // 5. ai_user_behavior (Missing)
        "CREATE TABLE IF NOT EXISTS `ai_user_behavior` (
            `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            `user_id` bigint(20) unsigned DEFAULT NULL,
            `action_type` varchar(50) NOT NULL,
            `property_id` bigint(20) unsigned DEFAULT NULL,
            `search_keywords` text DEFAULT NULL,
            `filters_used` text DEFAULT NULL,
            `time_spent_seconds` int(11) DEFAULT NULL,
            `session_id` varchar(100) DEFAULT NULL,
            `device_type` varchar(50) DEFAULT NULL,
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_ub_user` (`user_id`),
            KEY `idx_ub_property` (`property_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

        // 6. ai_agent_personality (Missing)
        "CREATE TABLE IF NOT EXISTS `ai_agent_personality` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `agent_name` varchar(100) NOT NULL,
            `personality_traits` text DEFAULT NULL,
            `communication_style` text DEFAULT NULL,
            `expertise_areas` text DEFAULT NULL,
            `behavior_rules` text DEFAULT NULL,
            `mood_state` text DEFAULT NULL,
            `learning_progress` text DEFAULT NULL,
            `active` tinyint(1) DEFAULT 1,
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

        // 7. ai_user_interactions (Missing)
        "CREATE TABLE IF NOT EXISTS `ai_user_interactions` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `user_id` int(11) NOT NULL,
            `session_id` varchar(100) NOT NULL,
            `interaction_type` varchar(50) NOT NULL DEFAULT 'question',
            `user_input` text NOT NULL,
            `ai_response` text DEFAULT NULL,
            `context_data` text DEFAULT NULL,
            `success_rating` enum('excellent','good','average','poor') DEFAULT NULL,
            `interaction_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_ui_user` (`user_id`),
            KEY `idx_ui_session` (`session_id`),
            KEY `idx_ui_type` (`interaction_type`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

        // 8. ai_context_memory (Missing)
        "CREATE TABLE IF NOT EXISTS `ai_context_memory` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `user_id` int(11) NOT NULL,
            `context_type` varchar(50) NOT NULL,
            `context_key` varchar(100) NOT NULL,
            `context_value` text NOT NULL,
            `importance_level` enum('low','medium','high') DEFAULT 'medium',
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_cm_user` (`user_id`),
            KEY `idx_cm_type` (`context_type`),
            KEY `idx_cm_key` (`context_key`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

        // 9. ai_workflow_patterns (Missing)
        "CREATE TABLE IF NOT EXISTS `ai_workflow_patterns` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `pattern_name` varchar(255) NOT NULL,
            `pattern_category` varchar(100) NOT NULL,
            `trigger_conditions` text NOT NULL,
            `action_sequence` text NOT NULL,
            `frequency_count` int(11) DEFAULT 1,
            `last_used` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_wp_name` (`pattern_name`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;"
    ];
    
    foreach ($queries as $q) {
        $pdo->exec($q);
    }
    echo "âœ“ All 9 missing AI tables created successfully!\n";
} catch (Exception $e) {
    echo "âœ— Error creating tables: " . $e->getMessage() . "\n";
}?>