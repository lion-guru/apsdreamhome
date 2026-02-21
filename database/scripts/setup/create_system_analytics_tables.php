<?php
/**
 * Script to create advanced system analytics dashboard tables
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

    // Create system analytics metrics table
    $sql = "CREATE TABLE IF NOT EXISTS `system_analytics_metrics` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `metric_category` ENUM('users','properties','finance','crm','communication','performance','system') NOT NULL,
        `metric_name` VARCHAR(100) NOT NULL,
        `metric_key` VARCHAR(100) NOT NULL UNIQUE,
        `metric_value` DECIMAL(15,2) NULL,
        `metric_count` INT NULL,
        `metric_percentage` DECIMAL(5,2) NULL,
        `period_type` ENUM('daily','weekly','monthly','quarterly','yearly') DEFAULT 'monthly',
        `period_date` DATE NOT NULL,
        `comparison_value` DECIMAL(15,2) NULL,
        `comparison_percentage` DECIMAL(5,2) NULL,
        `metadata` JSON NULL,
        `calculated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

        INDEX `idx_metric_category` (`metric_category`),
        INDEX `idx_metric_key` (`metric_key`),
        INDEX `idx_metric_period` (`period_date`, `period_type`),
        INDEX `idx_metric_calculated` (`calculated_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ System analytics metrics table created successfully!\n";
    }

    // Create analytics dashboards table
    $sql = "CREATE TABLE IF NOT EXISTS `analytics_dashboards` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `dashboard_name` VARCHAR(255) NOT NULL,
        `dashboard_slug` VARCHAR(100) NOT NULL UNIQUE,
        `dashboard_description` TEXT NULL,
        `dashboard_category` ENUM('executive','operational','financial','sales','marketing','hr','technical') DEFAULT 'executive',
        `is_default` TINYINT(1) DEFAULT 0,
        `is_public` TINYINT(1) DEFAULT 0,
        `access_roles` JSON NULL COMMENT 'Array of roles that can access this dashboard',
        `layout_config` JSON NOT NULL COMMENT 'Dashboard layout and widget configuration',
        `filters_config` JSON NULL COMMENT 'Default filters for the dashboard',
        `created_by` INT NULL,
        `is_active` TINYINT(1) DEFAULT 1,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

        INDEX `idx_dashboard_category` (`dashboard_category`),
        INDEX `idx_dashboard_slug` (`dashboard_slug`),
        INDEX `idx_dashboard_active` (`is_active`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Analytics dashboards table created successfully!\n";
    }

    // Create dashboard widgets table
    $sql = "CREATE TABLE IF NOT EXISTS `dashboard_widgets_config` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `widget_name` VARCHAR(255) NOT NULL,
        `widget_type` ENUM('kpi_card','chart','table','gauge','progress','heatmap','timeline','comparison') NOT NULL,
        `widget_category` ENUM('users','properties','finance','crm','communication','performance','system') DEFAULT 'performance',
        `data_source` VARCHAR(100) NOT NULL COMMENT 'Source table or API endpoint',
        `query_config` JSON NOT NULL COMMENT 'Query configuration for data fetching',
        `chart_config` JSON NULL COMMENT 'Chart visualization configuration',
        `default_size` ENUM('small','medium','large','xlarge') DEFAULT 'medium',
        `refresh_interval` INT DEFAULT 300 COMMENT 'Auto-refresh interval in seconds',
        `is_active` TINYINT(1) DEFAULT 1,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

        INDEX `idx_widget_type` (`widget_type`),
        INDEX `idx_widget_category` (`widget_category`),
        INDEX `idx_widget_active` (`is_active`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Dashboard widgets config table created successfully!\n";
    }

    // Create analytics reports table
    $sql = "CREATE TABLE IF NOT EXISTS `analytics_reports` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `report_name` VARCHAR(255) NOT NULL,
        `report_slug` VARCHAR(100) NOT NULL UNIQUE,
        `report_description` TEXT NULL,
        `report_category` ENUM('executive','operational','financial','sales','marketing','hr','technical') DEFAULT 'executive',
        `report_type` ENUM('scheduled','on_demand','real_time') DEFAULT 'on_demand',
        `data_sources` JSON NOT NULL COMMENT 'Array of data sources for the report',
        `filters_config` JSON NULL COMMENT 'Report filters configuration',
        `output_format` ENUM('html','pdf','excel','csv','json') DEFAULT 'html',
        `schedule_config` JSON NULL COMMENT 'Schedule configuration for automated reports',
        `is_scheduled` TINYINT(1) DEFAULT 0,
        `next_run` DATETIME NULL,
        `last_run` DATETIME NULL,
        `is_active` TINYINT(1) DEFAULT 1,
        `created_by` INT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

        INDEX `idx_report_category` (`report_category`),
        INDEX `idx_report_type` (`report_type`),
        INDEX `idx_report_active` (`is_active`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Analytics reports table created successfully!\n";
    }

    // Create report executions table
    $sql = "CREATE TABLE IF NOT EXISTS `report_executions` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `report_id` INT NOT NULL,
        `execution_status` ENUM('pending','running','completed','failed') DEFAULT 'pending',
        `execution_start` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `execution_end` TIMESTAMP NULL,
        `execution_duration` INT NULL COMMENT 'Duration in seconds',
        `parameters` JSON NULL COMMENT 'Execution parameters',
        `result_data` JSON NULL COMMENT 'Report result data',
        `result_file` VARCHAR(500) NULL COMMENT 'Path to generated file',
        `error_message` TEXT NULL,
        `executed_by` INT NULL,
        `is_automated` TINYINT(1) DEFAULT 0,

        FOREIGN KEY (`report_id`) REFERENCES `analytics_reports`(`id`) ON DELETE CASCADE,
        INDEX `idx_execution_report` (`report_id`),
        INDEX `idx_execution_status` (`execution_status`),
        INDEX `idx_execution_start` (`execution_start`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Report executions table created successfully!\n";
    }

    // Create analytics alerts table
    $sql = "CREATE TABLE IF NOT EXISTS `analytics_alerts` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `alert_name` VARCHAR(255) NOT NULL,
        `alert_description` TEXT NULL,
        `alert_type` ENUM('threshold','trend','anomaly','goal','system') DEFAULT 'threshold',
        `metric_key` VARCHAR(100) NOT NULL,
        `condition_operator` ENUM('gt','gte','lt','lte','eq','neq','between') DEFAULT 'gt',
        `condition_value` DECIMAL(15,2) NULL,
        `condition_value2` DECIMAL(15,2) NULL COMMENT 'For between operator',
        `alert_severity` ENUM('low','medium','high','critical') DEFAULT 'medium',
        `check_frequency` ENUM('real_time','hourly','daily','weekly') DEFAULT 'daily',
        `notification_channels` JSON NOT NULL COMMENT 'Array of notification channels',
        `notification_recipients` JSON NOT NULL COMMENT 'Array of recipient IDs or emails',
        `is_active` TINYINT(1) DEFAULT 1,
        `last_triggered` TIMESTAMP NULL,
        `created_by` INT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

        INDEX `idx_alert_type` (`alert_type`),
        INDEX `idx_alert_metric` (`metric_key`),
        INDEX `idx_alert_severity` (`alert_severity`),
        INDEX `idx_alert_active` (`is_active`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Analytics alerts table created successfully!\n";
    }

    // Create alert triggers table
    $sql = "CREATE TABLE IF NOT EXISTS `alert_triggers` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `alert_id` INT NOT NULL,
        `trigger_value` DECIMAL(15,2) NOT NULL,
        `trigger_message` TEXT NOT NULL,
        `triggered_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `acknowledged_by` INT NULL,
        `acknowledged_at` TIMESTAMP NULL,
        `resolution_notes` TEXT NULL,

        FOREIGN KEY (`alert_id`) REFERENCES `analytics_alerts`(`id`) ON DELETE CASCADE,
        INDEX `idx_trigger_alert` (`alert_id`),
        INDEX `idx_trigger_time` (`triggered_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    if (executeQuery($pdo, $sql)) {
        echo "✅ Alert triggers table created successfully!\n";
    }

    // Insert default dashboard widgets
    $defaultWidgets = [
        [
            'Total Users',
            'kpi_card',
            'users',
            'system_analytics_metrics',
            '{"metric_key": "total_users", "period": "monthly"}',
            '{"title": "Total Users", "icon": "users", "color": "blue"}',
            'small',
            300
        ],
        [
            'Active Properties',
            'kpi_card',
            'properties',
            'system_analytics_metrics',
            '{"metric_key": "active_properties", "period": "monthly"}',
            '{"title": "Active Properties", "icon": "building", "color": "green"}',
            'small',
            300
        ],
        [
            'Monthly Revenue',
            'kpi_card',
            'finance',
            'system_analytics_metrics',
            '{"metric_key": "monthly_revenue", "period": "monthly"}',
            '{"title": "Monthly Revenue", "icon": "dollar-sign", "color": "success", "format": "currency"}',
            'medium',
            300
        ],
        [
            'Lead Conversion Rate',
            'gauge',
            'crm',
            'system_analytics_metrics',
            '{"metric_key": "lead_conversion_rate", "period": "monthly"}',
            '{"title": "Lead Conversion Rate", "max": 100, "color": "orange"}',
            'medium',
            300
        ],
        [
            'User Growth Chart',
            'chart',
            'users',
            'system_analytics_metrics',
            '{"metric_key": "user_growth", "period": "6months", "chart_type": "line"}',
            '{"title": "User Growth Trend", "x_axis": "date", "y_axis": "count"}',
            'large',
            600
        ],
        [
            'Revenue Trend',
            'chart',
            'finance',
            'system_analytics_metrics',
            '{"metric_key": "revenue_trend", "period": "12months", "chart_type": "bar"}',
            '{"title": "Revenue Trend", "x_axis": "month", "y_axis": "amount"}',
            'large',
            600
        ],
        [
            'System Performance',
            'table',
            'system',
            'system_performance',
            '{"metrics": ["cpu_usage", "memory_usage", "disk_usage", "response_time"]}',
            '{"title": "System Performance Metrics", "columns": ["Metric", "Value", "Status"]}',
            'medium',
            300
        ]
    ];

    $insertWidgetSql = "INSERT IGNORE INTO `dashboard_widgets_config` (`widget_name`, `widget_type`, `widget_category`, `data_source`, `query_config`, `chart_config`, `default_size`, `refresh_interval`) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($insertWidgetSql);

    foreach ($defaultWidgets as $widget) {
        $stmt->execute($widget);
    }

    echo "✅ Default dashboard widgets inserted successfully!\n";

    // Insert default dashboards
    $defaultDashboards = [
        [
            'Executive Dashboard',
            'executive',
            'High-level overview of key business metrics and KPIs',
            'executive',
            1,
            1,
            '["admin","manager"]',
            '{"layout": "grid", "columns": 4, "widgets": ["total-users", "active-properties", "monthly-revenue", "lead-conversion-rate", "user-growth-chart", "revenue-trend"]}',
            '{"date_range": "last_30_days", "comparison": "previous_period"}'
        ],
        [
            'Operations Dashboard',
            'operational',
            'Operational metrics and system performance monitoring',
            'technical',
            0,
            0,
            '["admin","manager","employee"]',
            '{"layout": "grid", "columns": 3, "widgets": ["system-performance", "user-activity", "error-rates"]}',
            '{"date_range": "last_7_days", "real_time": true}'
        ]
    ];

    $insertDashboardSql = "INSERT IGNORE INTO `analytics_dashboards` (`dashboard_name`, `dashboard_slug`, `dashboard_description`, `dashboard_category`, `is_default`, `is_public`, `access_roles`, `layout_config`, `filters_config`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($insertDashboardSql);

    foreach ($defaultDashboards as $dashboard) {
        $dashboard[1] = strtolower(str_replace(' ', '-', $dashboard[0])); // Create slug
        $stmt->execute($dashboard);
    }

    echo "✅ Default dashboards inserted successfully!\n";

    // Insert sample analytics alerts
    $defaultAlerts = [
        [
            'Low Lead Conversion Rate',
            'Alert when lead conversion rate drops below 15%',
            'threshold',
            'lead_conversion_rate',
            'lt',
            15.00,
            null,
            'medium',
            'daily',
            '["email","dashboard"]',
            '["admin@apsdreamhome.com"]'
        ],
        [
            'High System Response Time',
            'Alert when average response time exceeds 3 seconds',
            'threshold',
            'avg_response_time',
            'gt',
            3.00,
            null,
            'high',
            'real_time',
            '["email","sms"]',
            '["admin@apsdreamhome.com"]'
        ],
        [
            'Revenue Goal Achievement',
            'Alert when monthly revenue reaches 80% of target',
            'goal',
            'monthly_revenue',
            'gte',
            800000.00,
            null,
            'low',
            'weekly',
            '["dashboard"]',
            '["all_managers"]'
        ]
    ];

    $insertAlertSql = "INSERT IGNORE INTO `analytics_alerts` (`alert_name`, `alert_description`, `alert_type`, `metric_key`, `condition_operator`, `condition_value`, `alert_severity`, `check_frequency`, `notification_channels`, `notification_recipients`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($insertAlertSql);

    foreach ($defaultAlerts as $alert) {
        $stmt->execute($alert);
    }

    echo "✅ Default analytics alerts inserted successfully!\n";

    echo "\n🎉 Advanced system analytics dashboard database setup completed!\n";

} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
