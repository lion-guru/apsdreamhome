<?php

/**
 * Migration: Create RBAC Menu System
 * 
 * This migration creates tables for a unified RBAC-based sidebar menu system
 * where menu items can be shown/hidden based on user role, and Admin/Super Admin
 * can grant extra permissions to users.
 */

$host = 'localhost';
$port = '3307';
$dbname = 'apsdreamhome';
$username = 'root';
$password = '';

try {
    $conn = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Creating RBAC Menu System...\n";

    // ============================================
    // 1. Create admin_menu_items table
    // ============================================
    $conn->exec("CREATE TABLE IF NOT EXISTS `admin_menu_items` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `name` VARCHAR(100) NOT NULL,
        `icon` VARCHAR(50) DEFAULT NULL,
        `url` VARCHAR(255) NOT NULL,
        `parent_id` INT DEFAULT NULL,
        `order_index` INT DEFAULT 0,
        `permission_key` VARCHAR(100) DEFAULT NULL,
        `is_active` TINYINT(1) DEFAULT 1,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX `idx_parent_id` (`parent_id`),
        INDEX `idx_permission_key` (`permission_key`),
        INDEX `idx_is_active` (`is_active`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "✓ Created admin_menu_items table\n";

    // ============================================
    // 2. Create admin_role_menu_permissions table
    // ============================================
    $conn->exec("CREATE TABLE IF NOT EXISTS `admin_role_menu_permissions` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `role` VARCHAR(50) NOT NULL,
        `menu_item_id` INT NOT NULL,
        `can_view` TINYINT(1) DEFAULT 1,
        `can_create` TINYINT(1) DEFAULT 0,
        `can_edit` TINYINT(1) DEFAULT 0,
        `can_delete` TINYINT(1) DEFAULT 0,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY `unique_role_menu` (`role`, `menu_item_id`),
        INDEX `idx_role` (`role`),
        INDEX `idx_menu_item_id` (`menu_item_id`),
        FOREIGN KEY (`menu_item_id`) REFERENCES `admin_menu_items`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "✓ Created admin_role_menu_permissions table\n";

    // ============================================
    // 3. Create admin_user_menu_permissions table (for custom overrides)
    // ============================================
    $conn->exec("CREATE TABLE IF NOT EXISTS `admin_user_menu_permissions` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `user_id` INT NOT NULL,
        `menu_item_id` INT NOT NULL,
        `can_view` TINYINT(1) DEFAULT 1,
        `can_create` TINYINT(1) DEFAULT 0,
        `can_edit` TINYINT(1) DEFAULT 0,
        `can_delete` TINYINT(1) DEFAULT 0,
        `granted_by` INT DEFAULT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY `unique_user_menu` (`user_id`, `menu_item_id`),
        INDEX `idx_user_id` (`user_id`),
        INDEX `idx_menu_item_id` (`menu_item_id`),
        FOREIGN KEY (`menu_item_id`) REFERENCES `admin_menu_items`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "✓ Created admin_user_menu_permissions table\n";

    // ============================================
    // 4. Insert default menu items
    // ============================================
    echo "\nInserting default menu items...\n";

    $menuItems = [
        // Main Dashboard
        ['name' => 'Dashboard', 'icon' => 'fa-home', 'url' => '/admin/dashboard', 'parent_id' => null, 'order_index' => 1, 'permission_key' => 'dashboard.view'],
        
        // Dashboard Submenu (Role Dashboards)
        ['name' => 'Role Dashboards', 'icon' => 'fa-chart-line', 'url' => '#', 'parent_id' => null, 'order_index' => 2, 'permission_key' => 'dashboard.view'],
        ['name' => 'Agent Dashboard', 'icon' => 'fa-user-tie', 'url' => '/admin/dashboard/agent', 'parent_id' => 2, 'order_index' => 1, 'permission_key' => 'dashboard.agent'],
        ['name' => 'Builder Dashboard', 'icon' => 'fa-hard-hat', 'url' => '/admin/dashboard/builder', 'parent_id' => 2, 'order_index' => 2, 'permission_key' => 'dashboard.builder'],
        ['name' => 'CEO Dashboard', 'icon' => 'fa-crown', 'url' => '/admin/dashboard/ceo', 'parent_id' => 2, 'order_index' => 3, 'permission_key' => 'dashboard.ceo'],
        ['name' => 'CFO Dashboard', 'icon' => 'fa-calculator', 'url' => '/admin/dashboard/cfo', 'parent_id' => 2, 'order_index' => 4, 'permission_key' => 'dashboard.cfo'],
        ['name' => 'CM Dashboard', 'icon' => 'fa-user', 'url' => '/admin/dashboard/cm', 'parent_id' => 2, 'order_index' => 5, 'permission_key' => 'dashboard.cm'],
        ['name' => 'COO Dashboard', 'icon' => 'fa-cogs', 'url' => '/admin/dashboard/coo', 'parent_id' => 2, 'order_index' => 6, 'permission_key' => 'dashboard.coo'],
        ['name' => 'CTO Dashboard', 'icon' => 'fa-laptop-code', 'url' => '/admin/dashboard/cto', 'parent_id' => 2, 'order_index' => 7, 'permission_key' => 'dashboard.cto'],
        ['name' => 'Director Dashboard', 'icon' => 'fa-user-tie', 'url' => '/admin/dashboard/director', 'parent_id' => 2, 'order_index' => 8, 'permission_key' => 'dashboard.director'],
        ['name' => 'Finance Dashboard', 'icon' => 'fa-money-bill-wave', 'url' => '/admin/dashboard/finance', 'parent_id' => 2, 'order_index' => 9, 'permission_key' => 'dashboard.finance'],
        ['name' => 'HR Dashboard', 'icon' => 'fa-users-cog', 'url' => '/admin/dashboard/hr', 'parent_id' => 2, 'order_index' => 10, 'permission_key' => 'dashboard.hr'],
        ['name' => 'IT Dashboard', 'icon' => 'fa-server', 'url' => '/admin/dashboard/it', 'parent_id' => 2, 'order_index' => 11, 'permission_key' => 'dashboard.it'],
        ['name' => 'Marketing Dashboard', 'icon' => 'fa-bullhorn', 'url' => '/admin/dashboard/marketing', 'parent_id' => 2, 'order_index' => 12, 'permission_key' => 'dashboard.marketing'],
        ['name' => 'Operations Dashboard', 'icon' => 'fa-tasks', 'url' => '/admin/dashboard/operations', 'parent_id' => 2, 'order_index' => 13, 'permission_key' => 'dashboard.operations'],
        ['name' => 'Sales Dashboard', 'icon' => 'fa-chart-bar', 'url' => '/admin/dashboard/sales', 'parent_id' => 2, 'order_index' => 14, 'permission_key' => 'dashboard.sales'],
        ['name' => 'Super Admin Dashboard', 'icon' => 'fa-user-shield', 'url' => '/admin/dashboard/superadmin', 'parent_id' => 2, 'order_index' => 15, 'permission_key' => 'dashboard.superadmin'],
        
        // Properties
        ['name' => 'Properties', 'icon' => 'fa-building', 'url' => '/admin/properties', 'parent_id' => null, 'order_index' => 3, 'permission_key' => 'property.view'],
        
        // Users
        ['name' => 'Users', 'icon' => 'fa-users', 'url' => '/admin/users', 'parent_id' => null, 'order_index' => 4, 'permission_key' => 'users.view'],
        
        // Leads
        ['name' => 'Leads', 'icon' => 'fa-user-plus', 'url' => '/admin/leads', 'parent_id' => null, 'order_index' => 5, 'permission_key' => 'leads.view'],
        
        // Bookings
        ['name' => 'Bookings', 'icon' => 'fa-calendar-check', 'url' => '/admin/bookings', 'parent_id' => null, 'order_index' => 6, 'permission_key' => 'bookings.view'],
        
        // Sites
        ['name' => 'Sites', 'icon' => 'fa-map-marker-alt', 'url' => '/admin/sites', 'parent_id' => null, 'order_index' => 7, 'permission_key' => 'sites.view'],
        
        // Inquiries
        ['name' => 'Inquiries', 'icon' => 'fa-envelope', 'url' => '/admin/inquiries', 'parent_id' => null, 'order_index' => 8, 'permission_key' => 'inquiries.view'],
        
        // Plots
        ['name' => 'Plots', 'icon' => 'fa-th', 'url' => '/admin/plots', 'parent_id' => null, 'order_index' => 9, 'permission_key' => 'plots.view'],
        
        // Locations
        ['name' => 'Locations', 'icon' => 'fa-map', 'url' => '/admin/locations/states', 'parent_id' => null, 'order_index' => 10, 'permission_key' => 'locations.view'],
        
        // News
        ['name' => 'News', 'icon' => 'fa-newspaper', 'url' => '/admin/news', 'parent_id' => null, 'order_index' => 11, 'permission_key' => 'news.view'],
        
        // Campaigns
        ['name' => 'Campaigns', 'icon' => 'fa-bullhorn', 'url' => '/admin/campaigns', 'parent_id' => null, 'order_index' => 12, 'permission_key' => 'campaigns.view'],
        
        // Visits
        ['name' => 'Visits', 'icon' => 'fa-calendar', 'url' => '/admin/visits', 'parent_id' => null, 'order_index' => 13, 'permission_key' => 'visits.view'],
        
        // Deals
        ['name' => 'Deals', 'icon' => 'fa-handshake', 'url' => '/admin/deals', 'parent_id' => null, 'order_index' => 14, 'permission_key' => 'deals.view'],
        
        // Testimonials
        ['name' => 'Testimonials', 'icon' => 'fa-quote-left', 'url' => '/admin/testimonials', 'parent_id' => null, 'order_index' => 15, 'permission_key' => 'testimonials.view'],
        
        // API Keys
        ['name' => 'API Keys', 'icon' => 'fa-key', 'url' => '/admin/api-keys', 'parent_id' => null, 'order_index' => 16, 'permission_key' => 'api.keys'],
        
        // Services
        ['name' => 'Services', 'icon' => 'fa-concierge-bell', 'url' => '/admin/services', 'parent_id' => null, 'order_index' => 17, 'permission_key' => 'services.view'],
        
        // User Properties
        ['name' => 'User Properties', 'icon' => 'fa-building', 'url' => '/admin/user-properties', 'parent_id' => null, 'order_index' => 18, 'permission_key' => 'user.properties.view'],
        
        // Plot Costs
        ['name' => 'Plot Costs', 'icon' => 'fa-calculator', 'url' => '/admin/plot-costs', 'parent_id' => null, 'order_index' => 19, 'permission_key' => 'plot.costs.view'],
        
        // AI Settings
        ['name' => 'AI Settings', 'icon' => 'fa-robot', 'url' => '/admin/ai_settings', 'parent_id' => null, 'order_index' => 20, 'permission_key' => 'ai.settings'],
        
        // Analytics
        ['name' => 'Analytics', 'icon' => 'fa-chart-line', 'url' => '/admin/analytics', 'parent_id' => null, 'order_index' => 21, 'permission_key' => 'analytics.view'],
        
        // MLM
        ['name' => 'MLM', 'icon' => 'fa-project-diagram', 'url' => '/admin/mlm', 'parent_id' => null, 'order_index' => 22, 'permission_key' => 'mlm.view'],
        
        // Commission
        ['name' => 'Commission', 'icon' => 'fa-coins', 'url' => '/admin/commission', 'parent_id' => null, 'order_index' => 23, 'permission_key' => 'commission.view'],
        
        // Payouts
        ['name' => 'Payouts', 'icon' => 'fa-money-bill', 'url' => '/admin/payouts', 'parent_id' => null, 'order_index' => 24, 'permission_key' => 'payouts.view'],
        
        // Reports
        ['name' => 'Reports', 'icon' => 'fa-file-alt', 'url' => '/admin/reports', 'parent_id' => null, 'order_index' => 25, 'permission_key' => 'reports.view'],
        
        // Settings
        ['name' => 'Settings', 'icon' => 'fa-cog', 'url' => '/admin/settings', 'parent_id' => null, 'order_index' => 26, 'permission_key' => 'settings.view'],
    ];

    $stmt = $conn->prepare("INSERT INTO `admin_menu_items` (name, icon, url, parent_id, order_index, permission_key) VALUES (?, ?, ?, ?, ?, ?)");
    
    foreach ($menuItems as $item) {
        try {
            $stmt->execute([$item['name'], $item['icon'], $item['url'], $item['parent_id'], $item['order_index'], $item['permission_key']]);
            echo "✓ Inserted: {$item['name']}\n";
        } catch (PDOException $e) {
            echo "✗ Skipped (already exists): {$item['name']}\n";
        }
    }

    // ============================================
    // 5. Grant full access to super_admin and admin
    // ============================================
    echo "\nGranting full access to super_admin and admin...\n";
    
    $menuIds = $conn->query("SELECT id FROM admin_menu_items")->fetchAll(PDO::FETCH_COLUMN);
    
    $roleStmt = $conn->prepare("INSERT IGNORE INTO `admin_role_menu_permissions` (role, menu_item_id, can_view, can_create, can_edit, can_delete) VALUES (?, ?, 1, 1, 1, 1)");
    
    foreach (['super_admin', 'admin'] as $role) {
        foreach ($menuIds as $menuId) {
            $roleStmt->execute([$role, $menuId]);
        }
        echo "✓ Granted full access to $role\n";
    }

    echo "\n✓ RBAC Menu System created successfully!\n";

} catch (PDOException $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
