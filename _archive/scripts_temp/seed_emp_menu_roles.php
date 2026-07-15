<?php
/**
 * Seed employee department-specific menu role mappings
 */
$host = '127.0.0.1';
$port = 3307;
$dbname = 'apsdreamhome';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get menu item IDs
    $getIds = function($names) use ($pdo) {
        $placeholders = implode(',', array_fill(0, count($names), '?'));
        $stmt = $pdo->prepare("SELECT id, name FROM admin_menu_items WHERE name IN ($placeholders) AND section='employee'");
        $stmt->execute($names);
        $map = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $map[$row['name']] = $row['id'];
        }
        return $map;
    };

    // Role → menu item names mapping
    $roleMappings = [
        // Finance
        'employee_finance_manager' => ['Dashboard', 'Finance Dashboard', 'Reports', 'TDS & GST', 'My Tasks', 'Attendance', 'Leaves', 'Payroll', 'Performance', 'Documents', 'My Profile', 'Settings', 'Logout'],
        'employee_finance_executive' => ['Dashboard', 'Finance Dashboard', 'Reports', 'TDS & GST', 'My Tasks', 'Attendance', 'Leaves', 'Payroll', 'Performance', 'Documents', 'My Profile', 'Settings', 'Logout'],

        // Sales
        'employee_sales_manager' => ['Dashboard', 'Sales Dashboard', 'My Leads', 'Deals Pipeline', 'My Tasks', 'Attendance', 'Leaves', 'Payroll', 'Performance', 'Documents', 'My Profile', 'Settings', 'Logout'],
        'employee_sales_executive' => ['Dashboard', 'Sales Dashboard', 'My Leads', 'Deals Pipeline', 'My Tasks', 'Attendance', 'Leaves', 'Payroll', 'Performance', 'Documents', 'My Profile', 'Settings', 'Logout'],
        'employee_telecalling_lead' => ['Dashboard', 'Sales Dashboard', 'My Leads', 'My Tasks', 'Attendance', 'Leaves', 'Payroll', 'Performance', 'Documents', 'My Profile', 'Settings', 'Logout'],
        'employee_telecaller' => ['Dashboard', 'Sales Dashboard', 'My Leads', 'My Tasks', 'Attendance', 'Leaves', 'Payroll', 'Performance', 'Documents', 'My Profile', 'Settings', 'Logout'],

        // HR
        'employee_hr_manager' => ['Dashboard', 'HR Dashboard', 'Employees', 'Recruitment', 'My Tasks', 'Attendance', 'Leaves', 'Payroll', 'Performance', 'Documents', 'My Profile', 'Settings', 'Logout'],
        'employee_hr_executive' => ['Dashboard', 'HR Dashboard', 'Employees', 'Recruitment', 'My Tasks', 'Attendance', 'Leaves', 'Payroll', 'Performance', 'Documents', 'My Profile', 'Settings', 'Logout'],

        // IT
        'employee_it_manager' => ['Dashboard', 'IT Dashboard', 'Infrastructure', 'Reports', 'My Tasks', 'Attendance', 'Leaves', 'Payroll', 'Performance', 'Documents', 'My Profile', 'Settings', 'Logout'],
        'employee_it_executive' => ['Dashboard', 'IT Dashboard', 'Infrastructure', 'My Tasks', 'Attendance', 'Leaves', 'Payroll', 'Performance', 'Documents', 'My Profile', 'Settings', 'Logout'],

        // Legal
        'employee_legal_advisor' => ['Dashboard', 'Legal Dashboard', 'Compliance', 'Reports', 'My Tasks', 'Attendance', 'Leaves', 'Payroll', 'Performance', 'Documents', 'My Profile', 'Settings', 'Logout'],
        'employee_legal_executive' => ['Dashboard', 'Legal Dashboard', 'Compliance', 'My Tasks', 'Attendance', 'Leaves', 'Payroll', 'Performance', 'Documents', 'My Profile', 'Settings', 'Logout'],

        // Land
        'employee_land_manager' => ['Dashboard', 'Land Dashboard', 'Site Surveys', 'Reports', 'My Tasks', 'Attendance', 'Leaves', 'Payroll', 'Performance', 'Documents', 'My Profile', 'Settings', 'Logout'],
        'employee_land_executive' => ['Dashboard', 'Land Dashboard', 'Site Surveys', 'My Tasks', 'Attendance', 'Leaves', 'Payroll', 'Performance', 'Documents', 'My Profile', 'Settings', 'Logout'],

        // Construction
        'employee_project_manager' => ['Dashboard', 'Construction Dashboard', 'Projects', 'Quality Control', 'Reports', 'My Tasks', 'Attendance', 'Leaves', 'Payroll', 'Performance', 'Documents', 'My Profile', 'Settings', 'Logout'],
        'employee_site_engineer' => ['Dashboard', 'Construction Dashboard', 'Projects', 'Quality Control', 'My Tasks', 'Attendance', 'Leaves', 'Payroll', 'Performance', 'Documents', 'My Profile', 'Settings', 'Logout'],

        // Marketing
        'employee_marketing_manager' => ['Dashboard', 'Marketing Dashboard', 'Campaigns', 'Reports', 'My Tasks', 'Attendance', 'Leaves', 'Payroll', 'Performance', 'Documents', 'My Profile', 'Settings', 'Logout'],
        'employee_marketing_executive' => ['Dashboard', 'Marketing Dashboard', 'Campaigns', 'My Tasks', 'Attendance', 'Leaves', 'Payroll', 'Performance', 'Documents', 'My Profile', 'Settings', 'Logout'],

        // Operations
        'employee_ops_manager' => ['Dashboard', 'Operations Dashboard', 'Vendors', 'Reports', 'My Tasks', 'Attendance', 'Leaves', 'Payroll', 'Performance', 'Documents', 'My Profile', 'Settings', 'Logout'],
        'employee_ops_executive' => ['Dashboard', 'Operations Dashboard', 'Vendors', 'My Tasks', 'Attendance', 'Leaves', 'Payroll', 'Performance', 'Documents', 'My Profile', 'Settings', 'Logout'],

        // Customer Success
        'employee_cs_manager' => ['Dashboard', 'Customer Success', 'Complaints', 'Reports', 'My Tasks', 'Attendance', 'Leaves', 'Payroll', 'Performance', 'Documents', 'My Profile', 'Settings', 'Logout'],
        'employee_cs_executive' => ['Dashboard', 'Customer Success', 'Complaints', 'My Tasks', 'Attendance', 'Leaves', 'Payroll', 'Performance', 'Documents', 'My Profile', 'Settings', 'Logout'],

        // General (fallback — all items)
        'employee_general' => ['Dashboard', 'My Tasks', 'Attendance', 'Leaves', 'Payroll', 'Performance', 'Documents', 'My Profile', 'Settings', 'Logout'],
    ];

    // First, clear ALL existing employee role permissions (to rebuild cleanly)
    $pdo->exec("DELETE FROM admin_role_menu_permissions WHERE role LIKE 'employee_%'");

    // Insert new mappings
    $stmt = $pdo->prepare("INSERT IGNORE INTO admin_role_menu_permissions (menu_item_id, role, can_view, can_edit, can_create, can_delete) VALUES (?, ?, 1, 0, 0, 0)");
    $totalInserted = 0;

    foreach ($roleMappings as $role => $menuNames) {
        $ids = $getIds($menuNames);
        foreach ($menuNames as $name) {
            if (isset($ids[$name])) {
                $stmt->execute([$ids[$name], $role]);
                $totalInserted++;
            }
        }
    }

    echo "Inserted $totalInserted role-menu mappings for " . count($roleMappings) . " roles.\n";

    // Verify counts per role
    $counts = $pdo->query("SELECT role, COUNT(*) as cnt FROM admin_role_menu_permissions WHERE role LIKE 'employee_%' GROUP BY role ORDER BY role")->fetchAll(PDO::FETCH_ASSOC);
    echo "\nRole menu counts:\n";
    foreach ($counts as $c) {
        echo "  {$c['role']}: {$c['cnt']} items\n";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
