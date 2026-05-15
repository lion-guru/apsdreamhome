<?php
/**
 * Setup RBAC Menu System
 * Creates admin_menu_items table and seeds default menu structure
 */

require __DIR__ . '/../config/bootstrap.php';

$pdo = \App\Core\Database::getInstance()->getConnection();

echo "=== Setting up RBAC Menu System ===\n";

// Create admin_menu_items table
$sql = "CREATE TABLE IF NOT EXISTS admin_menu_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    parent_id INT NULL,
    name VARCHAR(100) NOT NULL,
    url VARCHAR(255) DEFAULT '',
    icon VARCHAR(50) DEFAULT 'fa-circle',
    order_index INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    section VARCHAR(50) DEFAULT 'main',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_id) REFERENCES admin_menu_items(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

$pdo->exec($sql);
echo "✓ admin_menu_items table created\n";

// Create role permissions table
$sql2 = "CREATE TABLE IF NOT EXISTS admin_role_menu_permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role VARCHAR(50) NOT NULL,
    menu_item_id INT NOT NULL,
    can_view TINYINT(1) DEFAULT 1,
    can_create TINYINT(1) DEFAULT 0,
    can_edit TINYINT(1) DEFAULT 0,
    can_delete TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_role_menu (role, menu_item_id),
    FOREIGN KEY (menu_item_id) REFERENCES admin_menu_items(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

$pdo->exec($sql2);
echo "✓ admin_role_menu_permissions table created\n";

// Create user permissions table  
$sql3 = "CREATE TABLE IF NOT EXISTS admin_user_menu_permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    menu_item_id INT NOT NULL,
    can_view TINYINT(1) DEFAULT 1,
    can_create TINYINT(1) DEFAULT 0,
    can_edit TINYINT(1) DEFAULT 0,
    can_delete TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_user_menu (user_id, menu_item_id),
    FOREIGN KEY (menu_item_id) REFERENCES admin_menu_items(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

$pdo->exec($sql3);
echo "✓ admin_user_menu_permissions table created\n";

// Seed default menu items
$menuItems = [
    // Main Section
    ['name' => 'Dashboard', 'url' => '/admin/dashboard', 'icon' => 'fa-chart-pie', 'order_index' => 1, 'section' => 'main'],
    ['name' => 'Analytics', 'url' => '/admin/analytics', 'icon' => 'fa-chart-bar', 'order_index' => 2, 'section' => 'main'],
    
    // CRM Section
    ['name' => 'CRM & Sales', 'url' => '#', 'icon' => 'fa-bullseye', 'order_index' => 10, 'section' => 'crm'],
    ['name' => 'Leads', 'url' => '/admin/leads', 'icon' => 'fa-bullseye', 'order_index' => 11, 'section' => 'crm', 'parent_id' => null],
    ['name' => 'Lead Scoring', 'url' => '/admin/leads/scoring', 'icon' => 'fa-chart-line', 'order_index' => 12, 'section' => 'crm'],
    ['name' => 'Customers', 'url' => '/admin/customers', 'icon' => 'fa-users', 'order_index' => 13, 'section' => 'crm'],
    ['name' => 'Deals', 'url' => '/admin/deals', 'icon' => 'fa-handshake', 'order_index' => 14, 'section' => 'crm'],
    ['name' => 'Sales', 'url' => '/admin/sales', 'icon' => 'fa-rupee-sign', 'order_index' => 15, 'section' => 'crm'],
    ['name' => 'Campaigns', 'url' => '/admin/campaigns', 'icon' => 'fa-bullhorn', 'order_index' => 16, 'section' => 'crm'],
    ['name' => 'Bookings', 'url' => '/admin/bookings', 'icon' => 'fa-file-contract', 'order_index' => 17, 'section' => 'crm'],
    
    // Properties Section
    ['name' => 'Properties', 'url' => '#', 'icon' => 'fa-building', 'order_index' => 20, 'section' => 'properties'],
    ['name' => 'All Properties', 'url' => '/admin/properties', 'icon' => 'fa-building', 'order_index' => 21, 'section' => 'properties'],
    ['name' => 'Projects', 'url' => '/admin/projects', 'icon' => 'fa-project-diagram', 'order_index' => 22, 'section' => 'properties'],
    ['name' => 'Plots / Land', 'url' => '/admin/plots', 'icon' => 'fa-map', 'order_index' => 23, 'section' => 'properties'],
    ['name' => 'Sites', 'url' => '/admin/sites', 'icon' => 'fa-map-marker-alt', 'order_index' => 24, 'section' => 'properties'],
    ['name' => 'Resell Properties', 'url' => '/admin/resell-properties', 'icon' => 'fa-exchange-alt', 'order_index' => 25, 'section' => 'properties'],
    
    // MLM Section
    ['name' => 'MLM Network', 'url' => '#', 'icon' => 'fa-sitemap', 'order_index' => 30, 'section' => 'mlm'],
    ['name' => 'Network Tree', 'url' => '/admin/mlm/network', 'icon' => 'fa-sitemap', 'order_index' => 31, 'section' => 'mlm'],
    ['name' => 'Associates', 'url' => '/admin/mlm/associates', 'icon' => 'fa-handshake', 'order_index' => 32, 'section' => 'mlm'],
    ['name' => 'Commissions', 'url' => '/admin/mlm/commission', 'icon' => 'fa-percentage', 'order_index' => 33, 'section' => 'mlm'],
    ['name' => 'Payouts', 'url' => '/admin/mlm/payouts', 'icon' => 'fa-money-bill-wave', 'order_index' => 34, 'section' => 'mlm'],
    
    // Operations Section
    ['name' => 'Operations', 'url' => '#', 'icon' => 'fa-tasks', 'order_index' => 40, 'section' => 'operations'],
    ['name' => 'Site Visits', 'url' => '/admin/visits', 'icon' => 'fa-car', 'order_index' => 41, 'section' => 'operations'],
    ['name' => 'Support Tickets', 'url' => '/admin/support-tickets', 'icon' => 'fa-ticket-alt', 'order_index' => 42, 'section' => 'operations'],
    ['name' => 'Tasks', 'url' => '/admin/tasks', 'icon' => 'fa-check-square', 'order_index' => 43, 'section' => 'operations'],
    
    // Marketing Section
    ['name' => 'Marketing', 'url' => '#', 'icon' => 'fa-bullhorn', 'order_index' => 50, 'section' => 'marketing'],
    ['name' => 'Gallery', 'url' => '/admin/gallery', 'icon' => 'fa-images', 'order_index' => 51, 'section' => 'marketing'],
    ['name' => 'Testimonials', 'url' => '/admin/testimonials', 'icon' => 'fa-quote-right', 'order_index' => 52, 'section' => 'marketing'],
    ['name' => 'News', 'url' => '/admin/news', 'icon' => 'fa-newspaper', 'order_index' => 53, 'section' => 'marketing'],
    
    // AI Section
    ['name' => 'AI & Technology', 'url' => '#', 'icon' => 'fa-robot', 'order_index' => 60, 'section' => 'ai'],
    ['name' => 'AI Hub', 'url' => '/admin/ai-settings', 'icon' => 'fa-robot', 'order_index' => 61, 'section' => 'ai'],
    ['name' => 'AI Analytics', 'url' => '/admin/ai-settings', 'icon' => 'fa-brain', 'order_index' => 62, 'section' => 'ai'],
    
    // Users Section
    ['name' => 'Users & Team', 'url' => '#', 'icon' => 'fa-users-cog', 'order_index' => 70, 'section' => 'users'],
    ['name' => 'All Users', 'url' => '/admin/users', 'icon' => 'fa-users', 'order_index' => 71, 'section' => 'users'],
    ['name' => 'Employees', 'url' => '/employee/dashboard', 'icon' => 'fa-user-tie', 'order_index' => 72, 'section' => 'users'],
    
    // Locations Section
    ['name' => 'Locations', 'url' => '/admin/locations/states', 'icon' => 'fa-map-marked-alt', 'order_index' => 80, 'section' => 'locations'],
    
    // Settings Section
    ['name' => 'Settings', 'url' => '#', 'icon' => 'fa-cog', 'order_index' => 90, 'section' => 'settings'],
    ['name' => 'Site Settings', 'url' => '/admin/settings', 'icon' => 'fa-cog', 'order_index' => 91, 'section' => 'settings'],
    ['name' => 'Legal Pages', 'url' => '/admin/legal-pages', 'icon' => 'fa-file-contract', 'order_index' => 92, 'section' => 'settings'],
    ['name' => 'API Keys', 'url' => '/admin/api-keys', 'icon' => 'fa-key', 'order_index' => 93, 'section' => 'settings'],
    ['name' => 'Menu Permissions', 'url' => '/admin/menu-permissions', 'icon' => 'fa-lock', 'order_index' => 94, 'section' => 'settings'],
];

// Clear existing data
$pdo->exec("DELETE FROM admin_role_menu_permissions");
$pdo->exec("DELETE FROM admin_user_menu_permissions");
$pdo->exec("DELETE FROM admin_menu_items");

// Insert menu items
$stmt = $pdo->prepare("INSERT INTO admin_menu_items (name, url, icon, order_index, section, parent_id) VALUES (?, ?, ?, ?, ?, ?)");

foreach ($menuItems as $item) {
    $stmt->execute([
        $item['name'],
        $item['url'],
        $item['icon'],
        $item['order_index'],
        $item['section'],
        $item['parent_id'] ?? null
    ]);
}

echo "✓ Seeded " . count($menuItems) . " menu items\n";

// Grant all permissions to admin and super_admin roles
$menuIds = $pdo->query("SELECT id FROM admin_menu_items")->fetchAll(PDO::FETCH_COLUMN);
$roles = ['super_admin', 'admin', 'manager', 'agent', 'associate'];

$permStmt = $pdo->prepare("INSERT INTO admin_role_menu_permissions (role, menu_item_id, can_view, can_create, can_edit, can_delete) VALUES (?, ?, ?, ?, ?, ?)");

foreach ($roles as $role) {
    foreach ($menuIds as $menuId) {
        // Admin and super_admin get full permissions
        if ($role === 'super_admin' || $role === 'admin') {
            $permStmt->execute([$role, $menuId, 1, 1, 1, 1]);
        } else {
            // Others get view only by default
            $permStmt->execute([$role, $menuId, 1, 0, 0, 0]);
        }
    }
}

echo "✓ Set up permissions for " . count($roles) . " roles\n";

echo "\n=== RBAC Menu System Setup Complete ===\n";
echo "All admin pages will now use the unified RBAC sidebar.\n";
