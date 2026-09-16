<?php

/**
 * Migration: Create user_dashboard_layouts table
 * Run: php scripts/create_dashboard_layouts_table.php
 */

require_once __DIR__ . '/../config/bootstrap.php';

use App\Core\Database\Database;

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();

    // Check if table already exists
    $exists = $pdo->query("
        SELECT COUNT(*) FROM information_schema.tables 
        WHERE table_schema = DATABASE() AND table_name = 'user_dashboard_layouts'
    ")->fetchColumn();

    if ($exists) {
        echo "Table 'user_dashboard_layouts' already exists.\n";
        exit(0);
    }

    $sql = "
    CREATE TABLE `user_dashboard_layouts` (
        `id` bigint unsigned NOT NULL AUTO_INCREMENT,
        `user_id` bigint unsigned NOT NULL,
        `tenant_id` int unsigned NOT NULL DEFAULT 1,
        `layout_name` varchar(100) NOT NULL DEFAULT 'default',
        `layout_data` json NOT NULL,
        `is_active` tinyint(1) NOT NULL DEFAULT 1,
        `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `uk_user_layout` (`user_id`, `layout_name`, `tenant_id`),
        KEY `idx_user_tenant` (`user_id`, `tenant_id`),
        KEY `idx_tenant_active` (`tenant_id`, `is_active`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";

    $pdo->exec($sql);
    echo "Table 'user_dashboard_layouts' created successfully.\n";

    // Insert default widget definitions
    $widgetsSql = "
    INSERT INTO `admin_dashboard_widgets` (`widget_key`, `widget_name`, `widget_type`, `description`, `default_config`, `allowed_roles`, `is_active`, `sort_order`) VALUES
    ('stats_card', 'Stats Card', 'stats', 'Display a single metric with icon and trend', '{\"title\": \"\", \"value\": 0, \"icon\": \"fas fa-chart-bar\", \"color\": \"#3b82f6\", \"trend\": 0, \"trend_label\": \"\"}', '[\"super_admin\",\"admin\",\"manager\",\"ceo\",\"cfo\",\"cto\",\"coo\",\"cmo\",\"chro\",\"sales_director\",\"marketing_director\",\"finance_director\",\"hr_director\",\"operations_director\",\"department_manager\",\"project_manager\",\"sales_manager\",\"hr_manager\",\"marketing_manager\",\"finance_manager\",\"property_manager\",\"it_manager\",\"operations_manager\",\"legal_advisor\",\"chartered_accountant\",\"senior_developer\"]', 1, 1),
    ('chart_widget', 'Chart Widget', 'chart', 'Display Chart.js line/bar/pie chart', '{\"title\": \"\", \"chart_type\": \"line\", \"data_source\": \"\", \"height\": 300}', '[\"super_admin\",\"admin\",\"manager\",\"ceo\",\"cfo\",\"cto\",\"coo\",\"cmo\",\"chro\",\"sales_director\",\"marketing_director\",\"finance_director\",\"hr_director\",\"operations_director\",\"department_manager\",\"project_manager\",\"sales_manager\",\"hr_manager\",\"marketing_manager\",\"finance_manager\",\"property_manager\",\"it_manager\",\"operations_manager\",\"legal_advisor\",\"chartered_accountant\",\"senior_developer\"]', 1, 2),
    ('table_widget', 'Data Table', 'table', 'Display paginated data table', '{\"title\": \"\", \"columns\": [], \"data_source\": \"\", \"page_size\": 10}', '[\"super_admin\",\"admin\",\"manager\",\"ceo\",\"cfo\",\"cto\",\"coo\",\"cmo\",\"chro\",\"sales_director\",\"marketing_director\",\"finance_director\",\"hr_director\",\"operations_director\",\"department_manager\",\"project_manager\",\"sales_manager\",\"hr_manager\",\"marketing_manager\",\"finance_manager\",\"property_manager\",\"it_manager\",\"operations_manager\",\"legal_advisor\",\"chartered_accountant\",\"senior_developer\"]', 1, 3),
    ('quick_actions', 'Quick Actions', 'actions', 'Grid of action buttons', '{\"title\": \"\", \"actions\": []}', '[\"super_admin\",\"admin\",\"manager\",\"associate\",\"agent\",\"employee\",\"telecaller\",\"ceo\",\"cfo\",\"cto\",\"coo\",\"cmo\",\"chro\",\"sales_director\",\"marketing_director\",\"construction_director\",\"finance_director\",\"hr_director\",\"operations_director\",\"legal_head\",\"finance_head\",\"hr_head\",\"operations_head\",\"department_manager\",\"project_manager\",\"sales_manager\",\"hr_manager\",\"marketing_manager\",\"finance_manager\",\"property_manager\",\"it_manager\",\"operations_manager\",\"legal_advisor\",\"chartered_accountant\",\"senior_developer\"]', 1, 4),
    ('recent_activity', 'Recent Activity', 'activity', 'List of recent activities with timestamps', '{\"title\": \"\", \"data_source\": \"\", \"limit\": 10}', '[\"super_admin\",\"admin\",\"manager\",\"associate\",\"agent\",\"employee\",\"telecaller\",\"ceo\",\"cfo\",\"cto\",\"coo\",\"cmo\",\"chro\",\"sales_director\",\"marketing_director\",\"construction_director\",\"finance_director\",\"hr_director\",\"operations_director\",\"legal_head\",\"finance_head\",\"hr_head\",\"operations_head\",\"department_manager\",\"project_manager\",\"sales_manager\",\"hr_manager\",\"marketing_manager\",\"finance_manager\",\"property_manager\",\"it_manager\",\"operations_manager\",\"legal_advisor\",\"chartered_accountant\",\"senior_developer\"]', 1, 5),
    ('kpi_widget', 'KPI Widget', 'kpi', 'Key Performance Indicator with targets', '{\"title\": \"\", \"value\": 0, \"target\": 0, \"unit\": \"\", \"icon\": \"fas fa-bullseye\", \"color\": \"#10b981\", \"format\": \"number\"}', '[\"super_admin\",\"admin\",\"manager\",\"ceo\",\"cfo\",\"cto\",\"coo\",\"cmo\",\"chro\",\"sales_director\",\"marketing_director\",\"finance_director\",\"hr_director\",\"operations_director\",\"department_manager\",\"project_manager\",\"sales_manager\",\"hr_manager\",\"marketing_manager\",\"finance_manager\",\"property_manager\",\"it_manager\",\"operations_manager\",\"legal_advisor\",\"chartered_accountant\",\"senior_developer\"]', 1, 6)
    ON DUPLICATE KEY UPDATE 
        `widget_name` = VALUES(`widget_name`),
        `description` = VALUES(`description`),
        `default_config` = VALUES(`default_config`),
        `allowed_roles` = VALUES(`allowed_roles`),
        `is_active` = VALUES(`is_active`),
        `sort_order` = VALUES(`sort_order`);
    ";

    // Check if admin_dashboard_widgets table exists
    $widgetsTableExists = $pdo->query("
        SELECT COUNT(*) FROM information_schema.tables 
        WHERE table_schema = DATABASE() AND table_name = 'admin_dashboard_widgets'
    ")->fetchColumn();

    if ($widgetsTableExists) {
        $pdo->exec($widgetsSql);
        echo "Default widgets inserted/updated in 'admin_dashboard_widgets'.\n";
    } else {
        echo "Note: 'admin_dashboard_widgets' table doesn't exist yet. Skipping widget definitions.\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}