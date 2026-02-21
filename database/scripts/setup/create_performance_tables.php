<?php
/**
 * Script to create employee performance reviews and KPI tracking tables
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

    // Create performance reviews table
    $sql = "CREATE TABLE IF NOT EXISTS `performance_reviews` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `employee_id` INT NOT NULL,
        `reviewer_id` INT NULL,
        `review_period_start` DATE NOT NULL,
        `review_period_end` DATE NOT NULL,
        `review_type` ENUM('monthly','quarterly','annual','probation') DEFAULT 'monthly',
        `overall_rating` DECIMAL(3,2) NULL COMMENT 'Overall rating out of 5',
        `performance_level` ENUM('exceeds_expectations','meets_expectations','below_expectations','needs_improvement') NULL,
        `goals_achievement` TEXT NULL,
        `strengths` TEXT NULL,
        `areas_for_improvement` TEXT NULL,
        `development_plan` TEXT NULL,
        `reviewer_comments` TEXT NULL,
        `employee_comments` TEXT NULL,
        `status` ENUM('draft','submitted','under_review','completed','acknowledged') DEFAULT 'draft',
        `review_date` DATE NULL,
        `next_review_date` DATE NULL,
        `is_self_review` TINYINT(1) DEFAULT 0,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

        INDEX `idx_employee_review` (`employee_id`, `review_period_start`, `review_period_end`),
        INDEX `idx_reviewer` (`reviewer_id`),
        INDEX `idx_review_type` (`review_type`),
        INDEX `idx_review_status` (`status`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Performance reviews table created successfully!\n";
    }

    // Create KPIs table
    $sql = "CREATE TABLE IF NOT EXISTS `kpis` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `name` VARCHAR(255) NOT NULL,
        `description` TEXT NULL,
        `category` ENUM('sales','customer_satisfaction','productivity','quality','financial','operational') DEFAULT 'productivity',
        `unit` VARCHAR(50) NULL COMMENT 'Unit of measurement (%, count, currency, etc.)',
        `target_type` ENUM('fixed','range','percentage') DEFAULT 'fixed',
        `default_target` DECIMAL(15,2) NULL,
        `min_target` DECIMAL(15,2) NULL,
        `max_target` DECIMAL(15,2) NULL,
        `weightage` DECIMAL(5,2) DEFAULT 1.00 COMMENT 'Weight in overall performance score',
        `is_active` TINYINT(1) DEFAULT 1,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

        INDEX `idx_kpi_category` (`category`),
        INDEX `idx_kpi_active` (`is_active`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ KPIs table created successfully!\n";
    }

    // Create employee KPIs table
    $sql = "CREATE TABLE IF NOT EXISTS `employee_kpis` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `employee_id` INT NOT NULL,
        `kpi_id` INT NOT NULL,
        `period_start` DATE NOT NULL,
        `period_end` DATE NOT NULL,
        `target_value` DECIMAL(15,2) NOT NULL,
        `actual_value` DECIMAL(15,2) NULL,
        `achievement_percentage` DECIMAL(5,2) NULL,
        `score` DECIMAL(3,2) NULL COMMENT 'Score out of weightage',
        `status` ENUM('active','completed','cancelled') DEFAULT 'active',
        `comments` TEXT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

        INDEX `idx_emp_kpi_employee` (`employee_id`),
        INDEX `idx_emp_kpi_kpi` (`kpi_id`),
        INDEX `idx_emp_kpi_period` (`period_start`, `period_end`),
        INDEX `idx_emp_kpi_status` (`status`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Employee KPIs table created successfully!\n";
    }

    // Create performance goals table
    $sql = "CREATE TABLE IF NOT EXISTS `performance_goals` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `employee_id` INT NOT NULL,
        `title` VARCHAR(255) NOT NULL,
        `description` TEXT NULL,
        `category` ENUM('individual','team','department','company') DEFAULT 'individual',
        `priority` ENUM('low','medium','high','critical') DEFAULT 'medium',
        `target_date` DATE NOT NULL,
        `status` ENUM('not_started','in_progress','completed','cancelled') DEFAULT 'not_started',
        `progress_percentage` DECIMAL(5,2) DEFAULT 0,
        `assigned_by` INT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

        INDEX `idx_goal_employee` (`employee_id`),
        INDEX `idx_goal_category` (`category`),
        INDEX `idx_goal_status` (`status`),
        INDEX `idx_goal_priority` (`priority`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Performance goals table created successfully!\n";
    }

    // Create performance feedback table
    $sql = "CREATE TABLE IF NOT EXISTS `performance_feedback` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `review_id` INT NOT NULL,
        `feedback_type` ENUM('self','manager','peer','subordinate') DEFAULT 'manager',
        `feedback_by` INT NOT NULL,
        `feedback_for` INT NOT NULL,
        `rating_overall` DECIMAL(3,2) NULL,
        `rating_communication` DECIMAL(3,2) NULL,
        `rating_technical_skills` DECIMAL(3,2) NULL,
        `rating_leadership` DECIMAL(3,2) NULL,
        `rating_teamwork` DECIMAL(3,2) NULL,
        `rating_quality` DECIMAL(3,2) NULL,
        `positive_feedback` TEXT NULL,
        `areas_improvement` TEXT NULL,
        `recommendations` TEXT NULL,
        `is_anonymous` TINYINT(1) DEFAULT 0,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

        INDEX `idx_feedback_review` (`review_id`),
        INDEX `idx_feedback_type` (`feedback_type`),
        INDEX `idx_feedback_by` (`feedback_by`),
        INDEX `idx_feedback_for` (`feedback_for`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Performance feedback table created successfully!\n";
    }

    // Insert default KPIs
    $defaultKPIs = [
        ['Monthly Sales Target', 'Number of properties sold per month', 'sales', 'count', 'fixed', 5, null, null, 25.00],
        ['Customer Satisfaction Score', 'Average customer satisfaction rating', 'customer_satisfaction', 'score', 'percentage', 85, null, null, 20.00],
        ['Task Completion Rate', 'Percentage of assigned tasks completed on time', 'productivity', '%', 'percentage', 90, null, null, 15.00],
        ['Response Time', 'Average response time to customer inquiries (hours)', 'operational', 'hours', 'fixed', 2, null, null, 10.00],
        ['Quality Score', 'Code quality and bug-free delivery score', 'quality', 'score', 'percentage', 95, null, null, 15.00],
        ['Revenue Contribution', 'Monthly revenue contribution', 'financial', '₹', 'fixed', 50000, null, null, 15.00]
    ];

    $insertSql = "INSERT IGNORE INTO `kpis` (`name`, `description`, `category`, `unit`, `target_type`, `default_target`, `min_target`, `max_target`, `weightage`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($insertSql);

    foreach ($defaultKPIs as $kpi) {
        $stmt->execute($kpi);
    }

    echo "✅ Default KPIs inserted successfully!\n";

    echo "\n🎉 Employee performance management system database setup completed!\n";

} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
