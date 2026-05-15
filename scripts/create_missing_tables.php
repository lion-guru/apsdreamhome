<?php
/**
 * Create 17 missing database tables based on model definitions
 * Run: php scripts/create_missing_tables.php
 */

$host = '127.0.0.1';
$port = 3307;
$dbname = 'apsdreamhome';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO(
        "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4",
        $user, $pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );
    echo "Connected to MySQL successfully\n\n";
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage() . "\n");
}

$tables = [];

// === Timestamp note ===
// Models extending UnifiedModel (App\Core\Database\Model) have $timestamps=true
// Models extending App\Models\Model have timestamps only if fillable includes them

$tables[] = [
    'land_projects',
    "CREATE TABLE IF NOT EXISTS `land_projects` (
        `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `name` VARCHAR(255) NOT NULL DEFAULT '',
        `location` VARCHAR(255) NOT NULL DEFAULT '',
        `description` TEXT DEFAULT NULL,
        `total_area` DECIMAL(12,2) DEFAULT NULL,
        `project_type` VARCHAR(100) DEFAULT NULL,
        `developer_name` VARCHAR(255) DEFAULT NULL,
        `approval_number` VARCHAR(100) DEFAULT NULL,
        `rera_number` VARCHAR(100) DEFAULT NULL,
        `status` VARCHAR(50) NOT NULL DEFAULT 'development',
        `start_date` DATE DEFAULT NULL,
        `completion_date` DATE DEFAULT NULL,
        `estimated_cost` DECIMAL(15,2) DEFAULT NULL,
        `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        INDEX `idx_status` (`status`),
        INDEX `idx_project_type` (`project_type`),
        INDEX `idx_location` (`location`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;"
];

$tables[] = [
    'marketing_leads',
    "CREATE TABLE IF NOT EXISTS `marketing_leads` (
        `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `first_name` VARCHAR(255) NOT NULL DEFAULT '',
        `last_name` VARCHAR(255) NOT NULL DEFAULT '',
        `email` VARCHAR(255) DEFAULT NULL,
        `phone` VARCHAR(50) DEFAULT NULL,
        `company` VARCHAR(255) DEFAULT NULL,
        `position` VARCHAR(255) DEFAULT NULL,
        `source` VARCHAR(100) DEFAULT NULL,
        `status` VARCHAR(50) NOT NULL DEFAULT 'new',
        `status_reason` TEXT DEFAULT NULL,
        `score` INT(11) NOT NULL DEFAULT 0,
        `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        INDEX `idx_status` (`status`),
        INDEX `idx_source` (`source`),
        INDEX `idx_score` (`score`),
        INDEX `idx_email` (`email`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;"
];

$tables[] = [
    'admin_dashboard_stats',
    "CREATE TABLE IF NOT EXISTS `admin_dashboard_stats` (
        `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `stat_type` VARCHAR(100) NOT NULL DEFAULT '',
        `stat_value` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
        `stat_date` DATE NOT NULL,
        `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        INDEX `idx_stat_type` (`stat_type`),
        INDEX `idx_stat_date` (`stat_date`),
        UNIQUE KEY `uk_type_date` (`stat_type`, `stat_date`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;"
];

$tables[] = [
    'area_amenities',
    "CREATE TABLE IF NOT EXISTS `area_amenities` (
        `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `city` VARCHAR(255) NOT NULL DEFAULT '',
        `name` VARCHAR(255) NOT NULL DEFAULT '',
        `type` VARCHAR(100) NOT NULL DEFAULT '',
        `latitude` DECIMAL(10,6) DEFAULT NULL,
        `longitude` DECIMAL(10,6) DEFAULT NULL,
        `address` TEXT DEFAULT NULL,
        `rating` DECIMAL(3,1) DEFAULT NULL,
        `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        INDEX `idx_city` (`city`),
        INDEX `idx_type` (`type`),
        INDEX `idx_city_type` (`city`, `type`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;"
];

$tables[] = [
    'custom_features',
    "CREATE TABLE IF NOT EXISTS `custom_features` (
        `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `feature_type` VARCHAR(100) NOT NULL DEFAULT '',
        `property_id` INT(11) UNSIGNED DEFAULT NULL,
        `user_id` INT(11) UNSIGNED DEFAULT NULL,
        `feature_data` LONGTEXT DEFAULT NULL,
        `status` VARCHAR(50) NOT NULL DEFAULT 'active',
        `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        INDEX `idx_feature_type` (`feature_type`),
        INDEX `idx_property_id` (`property_id`),
        INDEX `idx_user_id` (`user_id`),
        INDEX `idx_status` (`status`),
        INDEX `idx_type_status` (`feature_type`, `status`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;"
];

$tables[] = [
    'email_verifications',
    "CREATE TABLE IF NOT EXISTS `email_verifications` (
        `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `user_id` INT(11) UNSIGNED NOT NULL,
        `token` VARCHAR(255) NOT NULL DEFAULT '',
        `expires_at` DATETIME NOT NULL,
        `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        INDEX `idx_user_id` (`user_id`),
        INDEX `idx_token` (`token`),
        INDEX `idx_expires_at` (`expires_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;"
];

$tables[] = [
    'employee_leaves',
    "CREATE TABLE IF NOT EXISTS `employee_leaves` (
        `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `employee_id` INT(11) UNSIGNED NOT NULL,
        `leave_type` VARCHAR(50) NOT NULL DEFAULT '',
        `start_date` DATE NOT NULL,
        `end_date` DATE NOT NULL,
        `total_days` INT(11) NOT NULL DEFAULT 0,
        `reason` TEXT DEFAULT NULL,
        `attachment` VARCHAR(255) DEFAULT NULL,
        `status` VARCHAR(50) NOT NULL DEFAULT 'pending',
        `approved_by` INT(11) UNSIGNED DEFAULT NULL,
        `approved_at` DATETIME DEFAULT NULL,
        `rejection_reason` TEXT DEFAULT NULL,
        `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        INDEX `idx_employee_id` (`employee_id`),
        INDEX `idx_leave_type` (`leave_type`),
        INDEX `idx_status` (`status`),
        INDEX `idx_approved_by` (`approved_by`),
        INDEX `idx_dates` (`start_date`, `end_date`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;"
];

$tables[] = [
    'event_log',
    "CREATE TABLE IF NOT EXISTS `event_log` (
        `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `event_id` VARCHAR(100) DEFAULT NULL,
        `event_name` VARCHAR(255) NOT NULL DEFAULT '',
        `event_data` LONGTEXT DEFAULT NULL,
        `event_type` VARCHAR(100) NOT NULL DEFAULT '',
        `priority` INT(11) NOT NULL DEFAULT 2,
        `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        INDEX `idx_event_id` (`event_id`),
        INDEX `idx_event_name` (`event_name`),
        INDEX `idx_event_type` (`event_type`),
        INDEX `idx_priority` (`priority`),
        INDEX `idx_created_at` (`created_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;"
];

$tables[] = [
    'leave_requests',
    "CREATE TABLE IF NOT EXISTS `leave_requests` (
        `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `employee_id` INT(11) UNSIGNED NOT NULL,
        `leave_type_id` INT(11) UNSIGNED DEFAULT NULL,
        `start_date` DATE NOT NULL,
        `end_date` DATE NOT NULL,
        `total_days` DECIMAL(5,1) NOT NULL DEFAULT 0.0,
        `reason` TEXT DEFAULT NULL,
        `status` VARCHAR(50) NOT NULL DEFAULT 'pending',
        `approved_by` INT(11) UNSIGNED DEFAULT NULL,
        `approved_at` DATETIME DEFAULT NULL,
        `approved_notes` TEXT DEFAULT NULL,
        `emergency_contact` VARCHAR(255) DEFAULT NULL,
        `work_coverage` VARCHAR(255) DEFAULT NULL,
        `attachment_path` VARCHAR(255) DEFAULT NULL,
        `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        INDEX `idx_employee_id` (`employee_id`),
        INDEX `idx_leave_type_id` (`leave_type_id`),
        INDEX `idx_status` (`status`),
        INDEX `idx_approved_by` (`approved_by`),
        INDEX `idx_dates` (`start_date`, `end_date`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;"
];

$tables[] = [
    'mortgage_inquiries',
    "CREATE TABLE IF NOT EXISTS `mortgage_inquiries` (
        `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `name` VARCHAR(255) NOT NULL DEFAULT '',
        `email` VARCHAR(255) DEFAULT NULL,
        `phone` VARCHAR(50) DEFAULT NULL,
        `property_value` DECIMAL(15,2) DEFAULT NULL,
        `down_payment` DECIMAL(15,2) DEFAULT NULL,
        `loan_amount` DECIMAL(15,2) DEFAULT NULL,
        `loan_tenure` INT(11) DEFAULT NULL,
        `employment_type` VARCHAR(100) DEFAULT NULL,
        `monthly_income` DECIMAL(15,2) DEFAULT NULL,
        `existing_loans` TEXT DEFAULT NULL,
        `property_location` VARCHAR(255) DEFAULT NULL,
        `urgency_level` VARCHAR(50) DEFAULT NULL,
        `additional_info` TEXT DEFAULT NULL,
        `loan_to_value_ratio` DECIMAL(5,2) DEFAULT NULL,
        `status` VARCHAR(50) NOT NULL DEFAULT 'pending',
        `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        INDEX `idx_status` (`status`),
        INDEX `idx_email` (`email`),
        INDEX `idx_phone` (`phone`),
        INDEX `idx_urgency` (`urgency_level`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;"
];

$tables[] = [
    'performance_cache',
    "CREATE TABLE IF NOT EXISTS `performance_cache` (
        `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `cache_key` VARCHAR(255) NOT NULL DEFAULT '',
        `cache_value` LONGTEXT DEFAULT NULL,
        `cache_data` LONGTEXT DEFAULT NULL,
        `expires_at` DATETIME DEFAULT NULL,
        `cache_type` VARCHAR(100) NOT NULL DEFAULT 'general',
        `size_bytes` INT(11) NOT NULL DEFAULT 0,
        `hit_count` INT(11) NOT NULL DEFAULT 0,
        `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        INDEX `idx_cache_key` (`cache_key`),
        INDEX `idx_cache_type` (`cache_type`),
        INDEX `idx_expires_at` (`expires_at`),
        INDEX `idx_hit_count` (`hit_count`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;"
];

$tables[] = [
    'virtual_tours',
    "CREATE TABLE IF NOT EXISTS `virtual_tours` (
        `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `property_id` INT(11) UNSIGNED DEFAULT NULL,
        `tour_title` VARCHAR(255) NOT NULL DEFAULT '',
        `tour_description` TEXT DEFAULT NULL,
        `tour_type` VARCHAR(100) NOT NULL DEFAULT '360_tour',
        `status` VARCHAR(50) NOT NULL DEFAULT 'draft',
        `is_featured` TINYINT(1) NOT NULL DEFAULT 0,
        `duration_minutes` INT(11) DEFAULT NULL,
        `view_count` INT(11) NOT NULL DEFAULT 0,
        `like_count` INT(11) NOT NULL DEFAULT 0,
        `share_count` INT(11) NOT NULL DEFAULT 0,
        `completion_rate` DECIMAL(5,2) DEFAULT NULL,
        `seo_title` VARCHAR(255) DEFAULT NULL,
        `seo_description` TEXT DEFAULT NULL,
        `tour_settings` LONGTEXT DEFAULT NULL,
        `created_by` INT(11) UNSIGNED DEFAULT NULL,
        `published_at` DATETIME DEFAULT NULL,
        `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        INDEX `idx_property_id` (`property_id`),
        INDEX `idx_tour_type` (`tour_type`),
        INDEX `idx_status` (`status`),
        INDEX `idx_is_featured` (`is_featured`),
        INDEX `idx_created_by` (`created_by`),
        INDEX `idx_view_count` (`view_count`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;"
];

$tables[] = [
    'virtual_tour_assets',
    "CREATE TABLE IF NOT EXISTS `virtual_tour_assets` (
        `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `tour_id` INT(11) UNSIGNED NOT NULL,
        `file_path` VARCHAR(255) NOT NULL DEFAULT '',
        `title` VARCHAR(255) DEFAULT NULL,
        `description` TEXT DEFAULT NULL,
        `sort_order` INT(11) NOT NULL DEFAULT 0,
        `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        INDEX `idx_tour_id` (`tour_id`),
        INDEX `idx_sort_order` (`sort_order`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;"
];

$tables[] = [
    'lead_custom_fields',
    "CREATE TABLE IF NOT EXISTS `lead_custom_fields` (
        `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `field_name` VARCHAR(255) NOT NULL DEFAULT '',
        `field_label` VARCHAR(255) NOT NULL DEFAULT '',
        `field_type` VARCHAR(100) NOT NULL DEFAULT 'text',
        `field_group` VARCHAR(100) DEFAULT NULL,
        `field_options` LONGTEXT DEFAULT NULL,
        `default_value` TEXT DEFAULT NULL,
        `is_required` TINYINT(1) NOT NULL DEFAULT 0,
        `is_active` TINYINT(1) NOT NULL DEFAULT 1,
        `validation_rules` LONGTEXT DEFAULT NULL,
        `sort_order` INT(11) NOT NULL DEFAULT 0,
        `created_by` INT(11) UNSIGNED DEFAULT NULL,
        PRIMARY KEY (`id`),
        INDEX `idx_field_type` (`field_type`),
        INDEX `idx_field_group` (`field_group`),
        INDEX `idx_is_active` (`is_active`),
        INDEX `idx_created_by` (`created_by`),
        INDEX `idx_sort_order` (`sort_order`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;"
];

$tables[] = [
    'lead_custom_field_values',
    "CREATE TABLE IF NOT EXISTS `lead_custom_field_values` (
        `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `lead_id` INT(11) UNSIGNED NOT NULL,
        `field_id` INT(11) UNSIGNED NOT NULL,
        `field_value` LONGTEXT DEFAULT NULL,
        `updated_by` INT(11) UNSIGNED DEFAULT NULL,
        PRIMARY KEY (`id`),
        INDEX `idx_lead_id` (`lead_id`),
        INDEX `idx_field_id` (`field_id`),
        UNIQUE KEY `uk_lead_field` (`lead_id`, `field_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;"
];

$tables[] = [
    'mlm_advanced_analytics',
    "CREATE TABLE IF NOT EXISTS `mlm_advanced_analytics` (
        `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `user_id` INT(11) UNSIGNED NOT NULL,
        `mlm_level` INT(11) NOT NULL DEFAULT 0,
        `commission_data` LONGTEXT DEFAULT NULL,
        `performance_metrics` LONGTEXT DEFAULT NULL,
        `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        INDEX `idx_user_id` (`user_id`),
        INDEX `idx_mlm_level` (`mlm_level`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;"
];

$tables[] = [
    'resell_property_images',
    "CREATE TABLE IF NOT EXISTS `resell_property_images` (
        `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `property_id` INT(11) UNSIGNED NOT NULL,
        `image_path` VARCHAR(255) NOT NULL DEFAULT '',
        PRIMARY KEY (`id`),
        INDEX `idx_property_id` (`property_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;"
];

$created = 0;
$failed = 0;

foreach ($tables as [$name, $sql]) {
    try {
        $pdo->exec($sql);
        echo sprintf("  %-30s CREATED\n", "`$name`");
        $created++;
    } catch (PDOException $e) {
        echo sprintf("  %-30s FAILED - %s\n", "`$name`", $e->getMessage());
        $failed++;
    }
}

echo "\n--- Summary ---\n";
echo "Created: $created | Failed: $failed | Total: " . count($tables) . "\n\n";

echo "--- Verifying all 17 tables ---\n";
$verified = 0;
foreach ($tables as [$name, $_]) {
    $stmt = $pdo->query("SHOW TABLES LIKE " . $pdo->quote($name));
    if ($stmt->rowCount() > 0) {
        echo sprintf("  %-30s EXISTS\n", "`$name`");
        $verified++;
    } else {
        echo sprintf("  %-30s MISSING\n", "`$name`");
    }
}
echo "\nVerified: $verified / " . count($tables) . " tables exist\n";
