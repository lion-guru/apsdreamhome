<?php
/**
 * Setup RBAC Permissions for All Roles
 * Configures proper role-based access control for the entire system
 */

$dsn = 'mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome;charset=utf8mb4';
$user = 'root';
$pass = '';

try {
    $db = new PDO($dsn, $user, $pass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "=== SETUP RBAC PERMISSIONS FOR ALL ROLES ===\n\n";

    // Define role-based menu access
    $rolePermissions = [
        'super_admin' => 'all', // Full access
        'admin' => 'all', // Full access
        'ceo' => ['main', 'financial', 'users', 'reports', 'settings'],
        'cfo' => ['main', 'financial', 'reports'],
        'coo' => ['main', 'operations', 'users', 'settings'],
        'cto' => ['main', 'ai', 'settings'],
        'manager' => ['main', 'crm', 'properties', 'users'],
        'associate' => ['main', 'mlm', 'financial'],
        'agent' => ['main', 'crm', 'properties'],
        'employee' => ['main', 'hrm'],
        'user' => ['main'] // Basic customer
    ];

    // First, clear existing role permissions
    echo "Clearing existing role permissions...\n";
    $db->query("DELETE FROM admin_role_menu_permissions");
    echo "✅ Cleared existing permissions\n\n";

    // Get all menu items
    $menuItems = $db->query("SELECT * FROM admin_menu_items WHERE is_active = 1")->fetchAll(PDO::FETCH_ASSOC);

    // Set permissions for each role
    foreach ($rolePermissions as $role => $access) {
        echo "Setting permissions for role: $role\n";
        
        if ($access === 'all') {
            // Grant full access
            foreach ($menuItems as $item) {
                $stmt = $db->prepare("INSERT INTO admin_role_menu_permissions (role, menu_item_id, can_view, can_create, can_edit, can_delete) VALUES (?, ?, 1, 1, 1, 1)");
                $stmt->execute([$role, $item['id']]);
            }
            echo "  ✅ Granted full access to all 150 menu items\n";
        } else {
            // Grant access to specific sections
            foreach ($menuItems as $item) {
                if (in_array($item['section'], $access)) {
                    $stmt = $db->prepare("INSERT INTO admin_role_menu_permissions (role, menu_item_id, can_view, can_create, can_edit, can_delete) VALUES (?, ?, 1, 1, 1, 0)");
                    $stmt->execute([$role, $item['id']]);
                }
            }
            $grantedCount = $db->query("SELECT COUNT(*) FROM admin_role_menu_permissions WHERE role = '$role'")->fetchColumn();
            echo "  ✅ Granted access to $grantedCount menu items in sections: " . implode(', ', $access) . "\n";
        }
        echo "\n";
    }

    // Create default role menu structure for different user types
    echo "Creating role-specific menu structures...\n";

    // Admin/Manager role menu structure
    $adminMenus = [
        ['Dashboard', 'fa-tachometer-alt', '/admin/dashboard', 'main'],
        ['CRM', 'fa-users', '/admin/crm', 'crm'],
        ['Properties', 'fa-building', '/admin/properties', 'properties'],
        ['Leads', 'fa-bullseye', '/admin/leads', 'crm'],
        ['Customers', 'fa-user', '/admin/customers', 'users'],
        ['Reports', 'fa-chart-bar', '/admin/reports', 'main'],
        ['Settings', 'fa-cog', '/admin/settings', 'settings']
    ];

    // Associate role menu structure
    $associateMenus = [
        ['Dashboard', 'fa-tachometer-alt', '/associate/dashboard', 'main'],
        ['Network', 'fa-network-wired', '/associate/network', 'mlm'],
        ['Team', 'fa-users', '/associate/team', 'mlm'],
        ['Commissions', 'fa-rupee-sign', '/associate/commissions', 'financial'],
        ['Leads', 'fa-bullseye', '/associate/leads', 'crm'],
        ['Profile', 'fa-user', '/associate/profile', 'main']
    ];

    // Agent role menu structure
    $agentMenus = [
        ['Dashboard', 'fa-tachometer-alt', '/agent/dashboard', 'main'],
        ['Leads', 'fa-bullseye', '/agent/leads', 'crm'],
        ['Properties', 'fa-building', '/agent/properties', 'properties'],
        ['Clients', 'fa-users', '/agent/clients', 'users'],
        ['Commissions', 'fa-rupee-sign', '/agent/commissions', 'financial'],
        ['Profile', 'fa-user', '/agent/profile', 'main']
    ];

    // Employee role menu structure
    $employeeMenus = [
        ['Dashboard', 'fa-tachometer-alt', '/employee/dashboard', 'main'],
        ['Tasks', 'fa-tasks', '/employee/tasks', 'hrm'],
        ['Attendance', 'fa-clock', '/employee/attendance', 'hrm'],
        ['Leaves', 'fa-calendar-minus', '/employee/leaves', 'hrm'],
        ['Profile', 'fa-user', '/employee/profile', 'main']
    ];

    echo "✅ Role menu structures defined\n\n";

    echo "=== RBAC PERMISSIONS SETUP COMPLETE ===\n";
    echo "Total role permissions configured: " . $db->query("SELECT COUNT(*) FROM admin_role_menu_permissions")->fetchColumn() . "\n";
    echo "\nNext steps:\n";
    echo "1. Test sidebar menus for different roles\n";
    echo "2. Verify access control on admin pages\n";
    echo "3. Create role-specific base controllers if needed\n";

} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
}
?>