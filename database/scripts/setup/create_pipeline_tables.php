<?php
/**
 * Script to create lead pipeline kanban board system tables
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

    // Create pipeline stages table
    $sql = "CREATE TABLE IF NOT EXISTS `pipeline_stages` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `stage_name` VARCHAR(255) NOT NULL,
        `stage_description` TEXT NULL,
        `stage_order` INT NOT NULL,
        `stage_color` VARCHAR(7) DEFAULT '#007bff',
        `is_active` TINYINT(1) DEFAULT 1,
        `is_default` TINYINT(1) DEFAULT 0,
        `probability_percentage` DECIMAL(5,2) DEFAULT 0 COMMENT 'Conversion probability for this stage',
        `avg_days_in_stage` DECIMAL(6,2) NULL COMMENT 'Average days leads spend in this stage',
        `stage_type` ENUM('prospect','qualified','proposal','negotiation','closed_won','closed_lost','nurture') DEFAULT 'prospect',
        `automated_actions` JSON NULL COMMENT 'Actions to trigger when lead enters this stage',
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

        INDEX `idx_stage_order` (`stage_order`),
        INDEX `idx_stage_active` (`is_active`),
        INDEX `idx_stage_type` (`stage_type`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Pipeline stages table created successfully!\n";
    }

    // Create lead pipeline table
    $sql = "CREATE TABLE IF NOT EXISTS `lead_pipeline` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `lead_id` INT NOT NULL,
        `current_stage_id` INT NOT NULL,
        `assigned_to` INT NULL COMMENT 'User ID of assigned agent',
        `assigned_by` INT NULL,
        `assigned_at` TIMESTAMP NULL,
        `entered_stage_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `stage_deadline` DATE NULL COMMENT 'Expected completion date for current stage',
        `priority` ENUM('low','normal','high','urgent') DEFAULT 'normal',
        `tags` JSON NULL COMMENT 'Lead tags for filtering and categorization',
        `deal_value` DECIMAL(15,2) NULL,
        `expected_close_date` DATE NULL,
        `confidence_percentage` DECIMAL(5,2) DEFAULT 0 COMMENT 'Confidence in closing the deal',
        `last_activity` TIMESTAMP NULL,
        `last_activity_type` VARCHAR(100) NULL,
        `is_active` TINYINT(1) DEFAULT 1,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

        UNIQUE KEY `unique_lead_pipeline` (`lead_id`),
        INDEX `idx_pipeline_stage` (`current_stage_id`),
        INDEX `idx_pipeline_assigned` (`assigned_to`),
        INDEX `idx_pipeline_active` (`is_active`),
        INDEX `idx_pipeline_priority` (`priority`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Lead pipeline table created successfully!\n";
    }

    // Create pipeline movement history table
    $sql = "CREATE TABLE IF NOT EXISTS `pipeline_movement_history` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `lead_id` INT NOT NULL,
        `from_stage_id` INT NULL,
        `to_stage_id` INT NOT NULL,
        `moved_by` INT NOT NULL,
        `moved_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `time_in_previous_stage` INT NULL COMMENT 'Time spent in previous stage (minutes)',
        `movement_reason` TEXT NULL,
        `automated` TINYINT(1) DEFAULT 0 COMMENT 'Was this movement automated?',
        `metadata` JSON NULL COMMENT 'Additional movement data',

        INDEX `idx_movement_lead` (`lead_id`),
        INDEX `idx_movement_from` (`from_stage_id`),
        INDEX `idx_movement_to` (`to_stage_id`),
        INDEX `idx_movement_by` (`moved_by`),
        INDEX `idx_movement_at` (`moved_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Pipeline movement history table created successfully!\n";
    }

    // Create pipeline activities table
    $sql = "CREATE TABLE IF NOT EXISTS `pipeline_activities` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `lead_id` INT NOT NULL,
        `activity_type` ENUM('call','email','meeting','site_visit','proposal','followup','note','task','document') NOT NULL,
        `activity_title` VARCHAR(255) NOT NULL,
        `activity_description` TEXT NULL,
        `activity_date` DATETIME NOT NULL,
        `duration_minutes` INT NULL,
        `outcome` ENUM('successful','unsuccessful','pending','scheduled','cancelled') DEFAULT 'pending',
        `outcome_details` TEXT NULL,
        `performed_by` INT NOT NULL,
        `assigned_to` INT NULL,
        `is_completed` TINYINT(1) DEFAULT 0,
        `completed_at` DATETIME NULL,
        `reminder_date` DATETIME NULL,
        `attachments` JSON NULL COMMENT 'Activity attachments',
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

        INDEX `idx_activity_lead` (`lead_id`),
        INDEX `idx_activity_type` (`activity_type`),
        INDEX `idx_activity_date` (`activity_date`),
        INDEX `idx_activity_performed` (`performed_by`),
        INDEX `idx_activity_completed` (`is_completed`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Pipeline activities table created successfully!\n";
    }

    // Create pipeline analytics table
    $sql = "CREATE TABLE IF NOT EXISTS `pipeline_analytics` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `stage_id` INT NULL,
        `date` DATE NOT NULL,
        `leads_in_stage` INT DEFAULT 0,
        `leads_entered` INT DEFAULT 0,
        `leads_exited` INT DEFAULT 0,
        `avg_time_in_stage` DECIMAL(8,2) NULL COMMENT 'Average time in stage (minutes)',
        `conversion_rate` DECIMAL(5,4) NULL COMMENT 'Stage conversion rate',
        `stage_velocity` DECIMAL(6,2) NULL COMMENT 'Average deals per day',
        `revenue_generated` DECIMAL(15,2) DEFAULT 0,
        `deals_closed` INT DEFAULT 0,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

        INDEX `idx_analytics_stage` (`stage_id`),
        INDEX `idx_analytics_date` (`date`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Pipeline analytics table created successfully!\n";
    }

    // Create pipeline filters table
    $sql = "CREATE TABLE IF NOT EXISTS `pipeline_filters` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `user_id` INT NOT NULL,
        `filter_name` VARCHAR(255) NOT NULL,
        `filter_criteria` JSON NOT NULL,
        `is_default` TINYINT(1) DEFAULT 0,
        `is_shared` TINYINT(1) DEFAULT 0,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

        INDEX `idx_filter_user` (`user_id`),
        INDEX `idx_filter_default` (`is_default`),
        INDEX `idx_filter_shared` (`is_shared`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Pipeline filters table created successfully!\n";
    }

    // Insert default pipeline stages
    $defaultStages = [
        ['New Lead', 'Fresh leads that need initial qualification', 1, '#6c757d', 1, 1, 10, null, 'prospect'],
        ['Contact Made', 'Initial contact established with the lead', 2, '#007bff', 1, 0, 25, 2, 'prospect'],
        ['Qualified', 'Lead has been qualified and shows interest', 3, '#28a745', 1, 0, 40, 5, 'qualified'],
        ['Needs Analysis', 'Analyzing lead requirements and budget', 4, '#ffc107', 1, 0, 35, 7, 'qualified'],
        ['Proposal Sent', 'Property proposal has been sent to lead', 5, '#17a2b8', 1, 0, 60, 10, 'proposal'],
        ['Negotiation', 'Negotiating terms and price with lead', 6, '#fd7e14', 1, 0, 70, 14, 'negotiation'],
        ['Closed Won', 'Deal successfully closed', 7, '#28a745', 1, 0, 100, 21, 'closed_won'],
        ['Closed Lost', 'Deal lost or lead not interested', 8, '#dc3545', 1, 0, 0, 0, 'closed_lost'],
        ['Nurture', 'Leads that need long-term nurturing', 9, '#6f42c1', 1, 0, 5, 30, 'nurture']
    ];

    $insertStageSql = "INSERT IGNORE INTO `pipeline_stages` (`stage_name`, `stage_description`, `stage_order`, `stage_color`, `is_active`, `is_default`, `probability_percentage`, `avg_days_in_stage`, `stage_type`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($insertStageSql);

    foreach ($defaultStages as $stage) {
        $stmt->execute($stage);
    }

    echo "✅ Default pipeline stages inserted successfully!\n";

    echo "\n🎉 Lead pipeline kanban board system database setup completed!\n";

} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
