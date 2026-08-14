<?php
/**
 * Centralize Employee Menu â€” Add employee items to admin_menu_items + RBAC permissions
 * 
 * This script:
 * 1. Adds employee-specific menu items to admin_menu_items (section='employee')
 * 2. Removes stale admin dashboard permissions for employee sub-roles
 * 3. Adds RBAC permissions per sub-role (common items + department-specific)
 * 4. Clears all menu caches
 * 
 * Run: php scripts/centralize_employee_menu.php
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
    echo "Connected to database.\n\n";
} catch (Exception $e) {
    die("Connection failed: " . $e->getMessage() . "\n");
}

// ============================================================
// STEP 1: Add employee menu items to admin_menu_items
// ============================================================
echo "=== STEP 1: Adding employee menu items ===\n";

// First, remove any existing employee items (idempotent)
$pdo->exec("DELETE FROM admin_role_menu_permissions WHERE menu_item_id IN (SELECT id FROM admin_menu_items WHERE section = 'employee')");
$pdo->exec("DELETE FROM admin_menu_items WHERE section = 'employee'");
echo "  Cleaned existing employee items.\n";

// Employee menu items â€” these replace the hardcoded PortalMenuService::employeeItems()
$employeeItems = [
    // Main section
    ['name' => 'Dashboard',       'url' => '/employee/dashboard',          'icon' => 'fas fa-tachometer-alt',  'section' => 'employee', 'order_index' => 10,  'parent_id' => null],
    ['name' => 'My Tasks',        'url' => '/employee/tasks',             'icon' => 'fas fa-tasks',           'section' => 'employee', 'order_index' => 20,  'parent_id' => null],
    ['name' => 'Attendance',      'url' => '/employee/attendance',        'icon' => 'fas fa-calendar-check',  'section' => 'employee', 'order_index' => 30,  'parent_id' => null],
    ['name' => 'Leaves',          'url' => '/employee/leaves',            'icon' => 'fas fa-umbrella-beach',  'section' => 'employee', 'order_index' => 40,  'parent_id' => null],
    ['name' => 'Payroll',         'url' => '/employee/payroll',           'icon' => 'fas fa-money-check-alt', 'section' => 'employee', 'order_index' => 50,  'parent_id' => null],
    ['name' => 'Performance',     'url' => '/employee/performance',       'icon' => 'fas fa-chart-line',      'section' => 'employee', 'order_index' => 60,  'parent_id' => null],
    ['name' => 'Documents',       'url' => '/employee/documents',         'icon' => 'fas fa-folder-open',     'section' => 'employee', 'order_index' => 70,  'parent_id' => null],
    ['name' => 'My Profile',      'url' => '/employee/profile',           'icon' => 'fas fa-user',            'section' => 'employee', 'order_index' => 80,  'parent_id' => null],
    ['name' => 'Settings',        'url' => '/employee/settings',          'icon' => 'fas fa-cog',             'section' => 'employee', 'order_index' => 90,  'parent_id' => null],
    ['name' => 'Logout',          'url' => '/employee/logout',            'icon' => 'fas fa-sign-out-alt',    'section' => 'employee', 'order_index' => 100, 'parent_id' => null],
];

$insertStmt = $pdo->prepare("
    INSERT INTO admin_menu_items (name, url, icon, section, order_index, parent_id, is_active, permission_key, created_at)
    VALUES (?, ?, ?, ?, ?, ?, 1, 'employee', NOW())
");

$insertedIds = [];
foreach ($employeeItems as $item) {
    $insertStmt->execute([
        $item['name'], $item['url'], $item['icon'],
        $item['section'], $item['order_index'], $item['parent_id']
    ]);
    $id = $pdo->lastInsertId();
    $insertedIds[$item['name']] = $id;
    echo "  Added: {$item['name']} (ID: $id) â†’ {$item['url']}\n";
}

echo "\n  Total employee items added: " . count($insertedIds) . "\n\n";

// ============================================================
// STEP 2: Remove stale admin dashboard permissions for employee sub-roles
// ============================================================
echo "=== STEP 2: Cleaning stale admin dashboard permissions ===\n";

// Employee sub-roles should NOT see admin dashboards (CEO/CFO/Finance/Sales/ERP/Main)
// They should see only their own employee dashboard items
$dashboardsToDelete = $pdo->query("
    SELECT id, name FROM admin_menu_items 
    WHERE section = 'dashboards' AND is_active = 1
")->fetchAll(PDO::FETCH_ASSOC);

$employeeSubRoles = $pdo->query("
    SELECT DISTINCT role FROM admin_role_menu_permissions 
    WHERE role LIKE 'employee_%'
")->fetchAll(PDO::FETCH_COLUMN);

$deleteStmt = $pdo->prepare("
    DELETE FROM admin_role_menu_permissions 
    WHERE role = ? AND menu_item_id = ?
");

$cleanedCount = 0;
foreach ($employeeSubRoles as $subRole) {
    foreach ($dashboardsToDelete as $dash) {
        $deleteStmt->execute([$subRole, $dash['id']]);
        if ($deleteStmt->rowCount() > 0) {
            $cleanedCount++;
        }
    }
}
echo "  Removed $cleanedCount stale admin dashboard permissions for employee sub-roles.\n\n";

// ============================================================
// STEP 3: Add RBAC permissions for employee sub-roles
// ============================================================
echo "=== STEP 3: Adding RBAC permissions ===\n";

$permStmt = $pdo->prepare("
    INSERT IGNORE INTO admin_role_menu_permissions 
    (role, menu_item_id, can_view, can_create, can_edit, can_delete)
    VALUES (?, ?, ?, ?, ?, ?)
");

// Common items all employee sub-roles can see
$commonItemNames = ['Dashboard', 'My Tasks', 'Attendance', 'Leaves', 'Payroll', 'Performance', 'Documents', 'My Profile', 'Settings', 'Logout'];

// Sub-role specific items
$roleItemMap = [
    'employee_hr_manager'          => ['Leaves', 'Payroll', 'Performance'],
    'employee_hr_executive'        => ['Leaves', 'Payroll', 'Performance'],
    'employee_land_manager'        => ['My Tasks', 'Documents'],
    'employee_land_executive'      => ['My Tasks', 'Documents'],
    'employee_legal_manager'       => ['My Tasks', 'Documents'],
    'employee_legal_executive'     => ['My Tasks', 'Documents'],
    'employee_finance_manager'     => ['Payroll', 'Performance'],
    'employee_finance_executive'   => ['Payroll'],
    'employee_marketing_manager'   => ['My Tasks', 'Performance'],
    'employee_marketing_executive' => ['My Tasks', 'Performance'],
    'employee_it_manager'          => ['My Tasks', 'Documents'],
    'employee_it_executive'        => ['My Tasks', 'Documents'],
    'employee_operations_manager'  => ['My Tasks', 'Attendance'],
    'employee_operations_executive'=> ['My Tasks', 'Attendance'],
    'employee_sales_manager'       => ['My Tasks', 'Performance'],
    'employee_sales_executive'     => ['My Tasks', 'Performance'],
    'employee_telecaller'          => ['My Tasks', 'Performance'],
    'employee_general'             => [],
];

$totalPerms = 0;
foreach ($roleItemMap as $subRole => $extraItems) {
    $items = array_unique(array_merge($commonItemNames, $extraItems));
    foreach ($items as $itemName) {
        if (!isset($insertedIds[$itemName])) continue;
        $menuItemId = $insertedIds[$itemName];
        
        // can_view=1 for all, can_create/can_edit for work items, can_delete for logout only
        $canView = 1;
        $canCreate = in_array($itemName, ['My Tasks']) ? 1 : 0;
        $canEdit = in_array($itemName, ['My Tasks', 'Leaves']) ? 1 : 0;
        $canDelete = ($itemName === 'Logout') ? 1 : 0;
        
        $permStmt->execute([$subRole, $menuItemId, $canView, $canCreate, $canEdit, $canDelete]);
        $totalPerms++;
    }
    echo "  Granted " . count($items) . " items to $subRole\n";
}

echo "\n  Total RBAC permissions created: $totalPerms\n\n";

// ============================================================
// STEP 4: Clear all menu caches
// ============================================================
echo "=== STEP 4: Clearing menu caches ===\n";

$cacheDir = $root . '/storage/cache';
if (is_dir($cacheDir)) {
    $files = glob($cacheDir . '/*.cache');
    $deleted = 0;
    foreach ($files as $f) {
        if (unlink($f)) $deleted++;
    }
    echo "  Deleted $deleted cache files.\n";
} else {
    echo "  Cache directory not found (OK for fresh install).\n";
}

// Also try clearing via service
try {
    $svc = new \App\Services\AdminMenuService();
    $svc->clearMenuCache();
    echo "  AdminMenuService cache cleared.\n";
} catch (\Exception $e) {
    echo "  AdminMenuService cache clear skipped: " . $e->getMessage() . "\n";
}

// ============================================================
// STEP 5: Verify
// ============================================================
echo "\n=== VERIFICATION ===\n";

$empItems = $pdo->query("SELECT COUNT(*) FROM admin_menu_items WHERE section = 'employee'")->fetchColumn();
echo "  Employee menu items in admin_menu_items: $empItems\n";

$totalEmpPerms = $pdo->query("SELECT COUNT(*) FROM admin_role_menu_permissions WHERE role LIKE 'employee_%' AND menu_item_id IN (SELECT id FROM admin_menu_items WHERE section = 'employee')")->fetchColumn();
echo "  RBAC permissions for employee items: $totalEmpPerms\n";

$staleDashPerms = $pdo->query("SELECT COUNT(*) FROM admin_role_menu_permissions WHERE role LIKE 'employee_%' AND menu_item_id IN (SELECT id FROM admin_menu_items WHERE section = 'dashboards')")->fetchColumn();
echo "  Stale admin dashboard perms for employees: $staleDashPerms (should be 0)\n";

echo "\n=== DONE ===\n";
echo "Employee sidebar is now database-driven via admin_menu_items + RBAC.\n";
echo "PortalMenuService::employeeItems() will read from DB instead of hardcoded arrays.\n";?>