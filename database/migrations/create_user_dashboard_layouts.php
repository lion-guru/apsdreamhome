<?php

require_once __DIR__ . '/../../config/bootstrap.php';

use App\Core\Database\Database;

try {
    $db = Database::getInstance()->getConnection();

    $sql = "
    CREATE TABLE IF NOT EXISTS `user_dashboard_layouts` (
        `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        `user_id` bigint(20) unsigned NOT NULL,
        `layout_name` varchar(100) NOT NULL,
        `layout_config` json NOT NULL,
        `widgets_order` json DEFAULT NULL,
        `is_default` tinyint(1) NOT NULL DEFAULT 0,
        `tenant_id` int(10) unsigned NOT NULL DEFAULT 1,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `idx_user_default` (`user_id`, `is_default`),
        KEY `idx_tenant` (`tenant_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";

    $db->exec($sql);
    echo "Table 'user_dashboard_layouts' created successfully.\n";

    // Add foreign key if users table exists
    try {
        $db->exec("
            ALTER TABLE `user_dashboard_layouts`
            ADD CONSTRAINT `fk_dashboard_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
        ");
        echo "Foreign key added.\n";
    } catch (Exception $e) {
        echo "Foreign key skipped (users table may not have id column): " . $e->getMessage() . "\n";
    }

} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
    exit(1);
}