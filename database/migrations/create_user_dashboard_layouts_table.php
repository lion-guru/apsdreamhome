<?php

/**
 * Migration: Create user_dashboard_layouts table
 * Run this to create the table for storing custom dashboard layouts per user
 */

require_once __DIR__ . '/../../config/bootstrap.php';

use App\Core\Database\Database;

try {
    $pdo = Database::getInstance()->getConnection();
    
    $sql = "
    CREATE TABLE IF NOT EXISTS `user_dashboard_layouts` (
        `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        `user_id` BIGINT UNSIGNED NOT NULL COMMENT 'User who owns this layout',
        `layout_name` VARCHAR(100) NOT NULL DEFAULT 'default' COMMENT 'Layout identifier (default, custom, etc.)',
        `layout_data` JSON NOT NULL COMMENT 'GridStack layout configuration with widgets',
        `is_active` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'Whether this layout is active',
        `tenant_id` INT UNSIGNED NOT NULL DEFAULT 1 COMMENT 'Tenant ID for multi-tenancy',
        `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `uk_user_layout` (`user_id`, `layout_name`, `tenant_id`),
        KEY `idx_user_id` (`user_id`),
        KEY `idx_tenant_id` (`tenant_id`),
        KEY `idx_is_active` (`is_active`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Custom dashboard layouts per user';
    ";
    
    $pdo->exec($sql);
    echo "✅ Table 'user_dashboard_layouts' created successfully\n";
    
    // Verify table structure
    $stmt = $pdo->query("DESCRIBE `user_dashboard_layouts`");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "\nTable structure:\n";
    foreach ($columns as $col) {
        echo "  {$col['Field']} - {$col['Type']} - {$col['Null']} - {$col['Key']} - {$col['Default']} - {$col['Extra']}\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}