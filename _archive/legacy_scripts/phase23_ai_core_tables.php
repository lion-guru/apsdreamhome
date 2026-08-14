<?php
/**
 * Phase 23 Step 1: Create AI Core tables
 * Self-learning AI - no external API
 */
$root = dirname(__DIR__);
$config = require $root . '/config/database.php';
$pdo = new PDO("mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4",
    $config['username'], $config['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

$tables = [
    'ai_learning_data' => "
        CREATE TABLE IF NOT EXISTS ai_learning_data (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id BIGINT UNSIGNED NULL,
            session_id VARCHAR(100) NULL,
            action_type VARCHAR(50) NOT NULL,
            input_data JSON NULL,
            output_data JSON NULL,
            context JSON NULL,
            feedback_score TINYINT NULL,
            learned_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_user (user_id),
            INDEX idx_action (action_type),
            INDEX idx_session (session_id),
            INDEX idx_learned (learned_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ",

    'ai_intent_patterns' => "
        CREATE TABLE IF NOT EXISTS ai_intent_patterns (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            intent_name VARCHAR(100) NOT NULL,
            language VARCHAR(10) DEFAULT 'en',
            pattern_text TEXT NOT NULL,
            pattern_type ENUM('regex','keyword','phrase','exact') DEFAULT 'keyword',
            weight DECIMAL(5,2) DEFAULT 1.00,
            hit_count INT UNSIGNED DEFAULT 0,
            success_count INT UNSIGNED DEFAULT 0,
            is_active TINYINT(1) DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_intent (intent_name),
            INDEX idx_lang (language),
            INDEX idx_active (is_active)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ",

    'ai_user_profiles' => "
        CREATE TABLE IF NOT EXISTS ai_user_profiles (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id BIGINT UNSIGNED NOT NULL UNIQUE,
            preferred_locations JSON NULL,
            preferred_types JSON NULL,
            budget_min DECIMAL(15,2) NULL,
            budget_max DECIMAL(15,2) NULL,
            preferred_area_min INT NULL,
            preferred_area_max INT NULL,
            buying_stage ENUM('exploring','researching','visiting','negotiating','closing','closed') DEFAULT 'exploring',
            interaction_count INT UNSIGNED DEFAULT 0,
            last_interaction_at DATETIME NULL,
            segment VARCHAR(50) NULL,
            tags JSON NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_user (user_id),
            INDEX idx_stage (buying_stage),
            INDEX idx_segment (segment)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ",

    'ai_recommendations' => "
        CREATE TABLE IF NOT EXISTS ai_recommendations (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id BIGINT UNSIGNED NOT NULL,
            item_type VARCHAR(50) NOT NULL,
            item_id BIGINT UNSIGNED NOT NULL,
            score DECIMAL(5,2) DEFAULT 0.00,
            reason TEXT NULL,
            shown_at DATETIME NULL,
            clicked_at DATETIME NULL,
            converted_at DATETIME NULL,
            dismissed_at DATETIME NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_user (user_id),
            INDEX idx_item (item_type, item_id),
            INDEX idx_score (score)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ",

    'ai_lead_scores' => "
        CREATE TABLE IF NOT EXISTS ai_lead_scores (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            lead_id BIGINT UNSIGNED NOT NULL,
            score INT UNSIGNED DEFAULT 0,
            factors JSON NULL,
            intent_score INT UNSIGNED DEFAULT 0,
            engagement_score INT UNSIGNED DEFAULT 0,
            budget_score INT UNSIGNED DEFAULT 0,
            timing_score INT UNSIGNED DEFAULT 0,
            grade ENUM('A','B','C','D','F') DEFAULT 'C',
            predicted_action VARCHAR(100) NULL,
            confidence DECIMAL(5,2) DEFAULT 0.00,
            scored_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_lead (lead_id),
            INDEX idx_score (score),
            INDEX idx_grade (grade)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ",

    'ai_anomalies' => "
        CREATE TABLE IF NOT EXISTS ai_anomalies (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            entity_type VARCHAR(50) NOT NULL,
            entity_id BIGINT UNSIGNED NOT NULL,
            anomaly_type VARCHAR(50) NOT NULL,
            severity ENUM('low','medium','high','critical') DEFAULT 'low',
            description TEXT NULL,
            data_snapshot JSON NULL,
            detected_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            reviewed_at DATETIME NULL,
            reviewed_by BIGINT UNSIGNED NULL,
            status ENUM('new','reviewed','false_positive','confirmed') DEFAULT 'new',
            INDEX idx_entity (entity_type, entity_id),
            INDEX idx_type (anomaly_type),
            INDEX idx_status (status),
            INDEX idx_severity (severity)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ",

    'ai_price_models' => "
        CREATE TABLE IF NOT EXISTS ai_price_models (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            property_type VARCHAR(50) NOT NULL,
            location_id BIGINT UNSIGNED NULL,
            model_data JSON NULL,
            coefficients JSON NULL,
            r_squared DECIMAL(5,4) DEFAULT 0.0000,
            sample_size INT UNSIGNED DEFAULT 0,
            trained_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            is_active TINYINT(1) DEFAULT 1,
            INDEX idx_type (property_type),
            INDEX idx_location (location_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ",

    'ai_chat_sessions' => "
        CREATE TABLE IF NOT EXISTS ai_chat_sessions (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            session_id VARCHAR(100) NOT NULL UNIQUE,
            user_id BIGINT UNSIGNED NULL,
            channel ENUM('web','whatsapp','sms','voice') DEFAULT 'web',
            language VARCHAR(10) DEFAULT 'en',
            current_intent VARCHAR(100) NULL,
            context JSON NULL,
            status ENUM('active','closed','escalated') DEFAULT 'active',
            satisfaction_score TINYINT NULL,
            started_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            ended_at DATETIME NULL,
            INDEX idx_session (session_id),
            INDEX idx_user (user_id),
            INDEX idx_status (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ",

    'ai_chat_messages' => "
        CREATE TABLE IF NOT EXISTS ai_chat_messages (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            session_id VARCHAR(100) NOT NULL,
            sender ENUM('user','bot','agent') NOT NULL,
            message TEXT NOT NULL,
            detected_intent VARCHAR(100) NULL,
            confidence DECIMAL(5,2) DEFAULT 0.00,
            entities JSON NULL,
            sentiment ENUM('positive','neutral','negative') DEFAULT 'neutral',
            response_time_ms INT UNSIGNED NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_session (session_id),
            INDEX idx_intent (detected_intent),
            INDEX idx_sender (sender)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ",

    'user_behavior_tracking' => "
        CREATE TABLE IF NOT EXISTS user_behavior_tracking (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id BIGINT UNSIGNED NULL,
            session_id VARCHAR(100) NULL,
            page_url VARCHAR(500) NULL,
            action_type VARCHAR(50) NOT NULL,
            target_type VARCHAR(50) NULL,
            target_id BIGINT UNSIGNED NULL,
            referrer VARCHAR(500) NULL,
            device_type VARCHAR(50) NULL,
            metadata JSON NULL,
            duration_ms INT UNSIGNED NULL,
            tracked_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_user (user_id),
            INDEX idx_session (session_id),
            INDEX idx_action (action_type),
            INDEX idx_target (target_type, target_id),
            INDEX idx_tracked (tracked_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ",

    'customer_journeys' => "
        CREATE TABLE IF NOT EXISTS customer_journeys (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id BIGINT UNSIGNED NOT NULL,
            stage ENUM('awareness','consideration','intent','evaluation','purchase','retention','advocacy') DEFAULT 'awareness',
            stage_entered_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            previous_stage VARCHAR(50) NULL,
            touchpoints JSON NULL,
            actions_count INT UNSIGNED DEFAULT 0,
            last_action_at DATETIME NULL,
            stage_duration_hours INT UNSIGNED NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_user (user_id),
            INDEX idx_stage (stage)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ",

    'customer_behavior_analysis' => "
        CREATE TABLE IF NOT EXISTS customer_behavior_analysis (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id BIGINT UNSIGNED NOT NULL,
            analysis_date DATE NOT NULL,
            recency_days INT UNSIGNED DEFAULT 0,
            frequency_count INT UNSIGNED DEFAULT 0,
            monetary_value DECIMAL(15,2) DEFAULT 0.00,
            rfm_segment VARCHAR(20) NULL,
            engagement_score INT UNSIGNED DEFAULT 0,
            preferred_categories JSON NULL,
            preferred_locations JSON NULL,
            predicted_ltv DECIMAL(15,2) NULL,
            churn_risk DECIMAL(5,2) DEFAULT 0.00,
            next_best_action VARCHAR(200) NULL,
            UNIQUE KEY uniq_user_date (user_id, analysis_date),
            INDEX idx_rfm (rfm_segment),
            INDEX idx_churn (churn_risk)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ",
];

$created = 0;
$exists = 0;
foreach ($tables as $name => $sql) {
    try {
        $pdo->exec($sql);
        $created++;
        echo "  âœ“ Created $name" . PHP_EOL;
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'already exists') !== false) {
            $exists++;
            echo "  - Exists $name" . PHP_EOL;
        } else {
            echo "  âœ— Failed $name: " . $e->getMessage() . PHP_EOL;
        }
    }
}

echo PHP_EOL . "=== PHASE 23 STEP 1 COMPLETE ===" . PHP_EOL;
echo "Created: $created, Already exists: $exists" . PHP_EOL;
echo "Total AI tables: " . count($tables) . PHP_EOL;?>