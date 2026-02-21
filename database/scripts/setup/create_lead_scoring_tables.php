<?php
/**
 * Script to create lead scoring system tables
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

    // Create lead scoring rules table
    $sql = "CREATE TABLE IF NOT EXISTS `lead_scoring_rules` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `rule_name` VARCHAR(255) NOT NULL,
        `rule_description` TEXT NULL,
        `category` ENUM('demographic','behavioral','engagement','property_preference','budget','timeline','source') DEFAULT 'behavioral',
        `criteria_type` ENUM('exact_match','range','contains','greater_than','less_than','between','not_empty','custom') DEFAULT 'exact_match',
        `field_name` VARCHAR(100) NOT NULL,
        `field_value` TEXT NULL,
        `comparison_operator` VARCHAR(20) NULL,
        `score_points` INT NOT NULL DEFAULT 0,
        `max_occurrences` INT DEFAULT 1 COMMENT 'Maximum times this rule can apply',
        `decay_days` INT NULL COMMENT 'Score decays after this many days',
        `is_active` TINYINT(1) DEFAULT 1,
        `priority` INT DEFAULT 0 COMMENT 'Higher priority rules are applied first',
        `created_by` INT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

        INDEX `idx_rule_category` (`category`),
        INDEX `idx_rule_field` (`field_name`),
        INDEX `idx_rule_active` (`is_active`),
        INDEX `idx_rule_priority` (`priority`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Lead scoring rules table created successfully!\n";
    }

    // Create lead scores table
    $sql = "CREATE TABLE IF NOT EXISTS `lead_scores` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `lead_id` INT NOT NULL,
        `current_score` INT NOT NULL DEFAULT 0,
        `score_breakdown` JSON NULL COMMENT 'Detailed breakdown of score components',
        `grade` ENUM('A+','A','B+','B','C+','C','D','F') DEFAULT 'F',
        `last_calculated` TIMESTAMP NULL,
        `next_calculation` TIMESTAMP NULL,
        `calculation_count` INT DEFAULT 0,
        `is_locked` TINYINT(1) DEFAULT 0 COMMENT 'Prevent automatic recalculation',
        `locked_by` INT NULL,
        `locked_at` TIMESTAMP NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

        UNIQUE KEY `unique_lead_score` (`lead_id`),
        INDEX `idx_score_value` (`current_score`),
        INDEX `idx_score_grade` (`grade`),
        INDEX `idx_score_locked` (`is_locked`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Lead scores table created successfully!\n";
    }

    // Create lead scoring history table
    $sql = "CREATE TABLE IF NOT EXISTS `lead_scoring_history` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `lead_id` INT NOT NULL,
        `rule_id` INT NULL,
        `action` ENUM('scored','decay','manual_adjustment','reset') NOT NULL,
        `points_change` INT NOT NULL,
        `old_score` INT NOT NULL,
        `new_score` INT NOT NULL,
        `reason` TEXT NULL,
        `applied_by` INT NULL COMMENT 'User who applied the scoring',
        `applied_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

        INDEX `idx_history_lead` (`lead_id`),
        INDEX `idx_history_rule` (`rule_id`),
        INDEX `idx_history_action` (`action`),
        INDEX `idx_history_applied` (`applied_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Lead scoring history table created successfully!\n";
    }

    // Create scoring thresholds table
    $sql = "CREATE TABLE IF NOT EXISTS `scoring_thresholds` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `threshold_name` VARCHAR(100) NOT NULL,
        `min_score` INT NOT NULL,
        `max_score` INT NULL,
        `grade` ENUM('A+','A','B+','B','C+','C','D','F') NOT NULL,
        `description` VARCHAR(255) NULL,
        `action_required` ENUM('immediate_followup','schedule_followup','monitor','low_priority','archive') DEFAULT 'monitor',
        `email_alert` TINYINT(1) DEFAULT 0,
        `sms_alert` TINYINT(1) DEFAULT 0,
        `auto_assign` TINYINT(1) DEFAULT 0,
        `assigned_user_id` INT NULL,
        `is_active` TINYINT(1) DEFAULT 1,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

        INDEX `idx_threshold_score` (`min_score`, `max_score`),
        INDEX `idx_threshold_grade` (`grade`),
        INDEX `idx_threshold_active` (`is_active`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Scoring thresholds table created successfully!\n";
    }

    // Create lead engagement metrics table
    $sql = "CREATE TABLE IF NOT EXISTS `lead_engagement_metrics` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `lead_id` INT NOT NULL,
        `metric_type` ENUM('page_views','inquiries','property_views','contact_attempts','meetings','offers','responses') NOT NULL,
        `metric_value` INT DEFAULT 1,
        `metric_date` DATE NOT NULL,
        `source` VARCHAR(100) NULL COMMENT 'Source of the engagement',
        `metadata` JSON NULL COMMENT 'Additional metric data',
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

        UNIQUE KEY `unique_lead_metric` (`lead_id`, `metric_type`, `metric_date`),
        INDEX `idx_metric_lead` (`lead_id`),
        INDEX `idx_metric_type` (`metric_type`),
        INDEX `idx_metric_date` (`metric_date`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Lead engagement metrics table created successfully!\n";
    }

    // Insert default scoring rules
    $defaultRules = [
        ['Demographic - High Budget', 'Lead has budget above ₹2 crores', 'demographic', 'greater_than', 'budget_max', '20000000', null, 25, 1, 30],
        ['Demographic - Medium Budget', 'Lead has budget between ₹50 lakhs to ₹2 crores', 'demographic', 'between', 'budget_max', '5000000,20000000', null, 15, 1, 30],
        ['Demographic - Ready to Buy', 'Lead is ready to buy within 3 months', 'timeline', 'exact_match', 'timeline', 'within_3_months', null, 20, 1, 60],
        ['Behavioral - Multiple Property Views', 'Viewed 5+ properties', 'behavioral', 'greater_than', 'property_views', '4', null, 15, 1, 7],
        ['Engagement - Responded to Email', 'Responded to marketing emails', 'engagement', 'exact_match', 'email_response', 'yes', null, 10, 5, 14],
        ['Engagement - Attended Webinar', 'Attended property webinar', 'engagement', 'exact_match', 'webinar_attendance', 'yes', null, 12, 1, 30],
        ['Property Preference - Luxury Segment', 'Interested in luxury properties', 'property_preference', 'contains', 'property_types', 'penthouse,villa', null, 18, 1, 45],
        ['Source - Referral', 'Lead came through referral', 'source', 'exact_match', 'lead_source', 'referral', null, 22, 1, null],
        ['Behavioral - Return Visitor', 'Visited website multiple times', 'behavioral', 'greater_than', 'visit_count', '3', null, 8, 1, 7],
        ['Engagement - Downloaded Brochure', 'Downloaded property brochure', 'engagement', 'exact_match', 'brochure_download', 'yes', null, 6, 3, 21]
    ];

    $insertRuleSql = "INSERT IGNORE INTO `lead_scoring_rules` (`rule_name`, `rule_description`, `category`, `criteria_type`, `field_name`, `field_value`, `comparison_operator`, `score_points`, `max_occurrences`, `decay_days`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($insertRuleSql);

    foreach ($defaultRules as $rule) {
        $stmt->execute($rule);
    }

    echo "✅ Default scoring rules inserted successfully!\n";

    // Insert default scoring thresholds
    $defaultThresholds = [
        ['Hot Lead - A+', 85, null, 'A+', 'Premium leads requiring immediate attention', 'immediate_followup', 1, 1, 1, null],
        ['Hot Lead - A', 70, 84, 'A', 'High-quality leads for quick follow-up', 'immediate_followup', 1, 0, 1, null],
        ['Warm Lead - B+', 55, 69, 'B+', 'Good leads needing scheduled follow-up', 'schedule_followup', 0, 0, 1, null],
        ['Warm Lead - B', 40, 54, 'B', 'Moderate leads to monitor closely', 'monitor', 0, 0, 0, null],
        ['Cold Lead - C', 20, 39, 'C', 'Low priority leads', 'low_priority', 0, 0, 0, null],
        ['Dead Lead - F', 0, 19, 'F', 'Very low potential leads', 'archive', 0, 0, 0, null]
    ];

    $insertThresholdSql = "INSERT IGNORE INTO `scoring_thresholds` (`threshold_name`, `min_score`, `max_score`, `grade`, `description`, `action_required`, `email_alert`, `sms_alert`, `auto_assign`, `assigned_user_id`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($insertThresholdSql);

    foreach ($defaultThresholds as $threshold) {
        $stmt->execute($threshold);
    }

    echo "✅ Default scoring thresholds inserted successfully!\n";

    echo "\n🎉 Lead scoring system database setup completed!\n";

} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
