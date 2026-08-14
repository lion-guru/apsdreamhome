<?php
/**
 * RBAC Migration: Create admin_role_menu_permissions + admin_user_menu_permissions
 * 
 * These tables support the AdminMenuService for role-based sidebar filtering.
 * AdminMenuService.php already references these tables â€” they were dropped
 * during a DB cleanup session.
 * 
 * Run: php scripts/migrate_rbac_tables.php
 */

$root = dirname(__DIR__);
$config = require $root . '/config/database.php';

try {
    $pdo = new PDO(
        "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4",
        $config['username'],
        $config['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    echo "=== RBAC TABLE MIGRATION ===\n\n";

    // 1. admin_role_menu_permissions â€” maps role â†’ menu_item access
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `admin_role_menu_permissions` (
            `id` INT NOT NULL AUTO_INCREMENT,
            `role` VARCHAR(50) NOT NULL,
            `menu_item_id` INT NOT NULL,
            `can_view` TINYINT(1) NOT NULL DEFAULT 1,
            `can_create` TINYINT(1) NOT NULL DEFAULT 0,
            `can_edit` TINYINT(1) NOT NULL DEFAULT 0,
            `can_delete` TINYINT(1) NOT NULL DEFAULT 0,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uniq_role_menu` (`role`, `menu_item_id`),
            KEY `idx_role` (`role`),
            KEY `idx_menu_item` (`menu_item_id`),
            CONSTRAINT `fk_role_perm_menu` FOREIGN KEY (`menu_item_id`)
                REFERENCES `admin_menu_items`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "  [OK] admin_role_menu_permissions table created\n";

    // 2. admin_user_menu_permissions â€” per-user overrides
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `admin_user_menu_permissions` (
            `id` INT NOT NULL AUTO_INCREMENT,
            `user_id` BIGINT(20) UNSIGNED NOT NULL,
            `menu_item_id` INT NOT NULL,
            `can_view` TINYINT(1) NOT NULL DEFAULT 1,
            `can_create` TINYINT(1) NOT NULL DEFAULT 0,
            `can_edit` TINYINT(1) NOT NULL DEFAULT 0,
            `can_delete` TINYINT(1) NOT NULL DEFAULT 0,
            `granted_by` BIGINT(20) UNSIGNED DEFAULT NULL,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uniq_user_menu` (`user_id`, `menu_item_id`),
            KEY `idx_user` (`user_id`),
            KEY `idx_menu_item` (`menu_item_id`),
            CONSTRAINT `fk_user_perm_menu` FOREIGN KEY (`menu_item_id`)
                REFERENCES `admin_menu_items`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "  [OK] admin_user_menu_permissions table created\n";

    // Count menu items
    $count = $pdo->query("SELECT COUNT(*) FROM admin_menu_items")->fetchColumn();
    echo "\n  Menu items available: $count\n";

    echo "\n=== MIGRATION COMPLETE ===\n";

} catch (Exception $e) {
    echo "  [ERROR] " . $e->getMessage() . "\n";
    exit(1);
}?>