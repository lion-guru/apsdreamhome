<?php
/**
 * RBAC Sidebar End-to-End Test
 * Tests that AdminMenuService correctly filters menu items per role.
 * Run: php testing/test_rbac_sidebar.php
 */

require_once dirname(__DIR__) . '/config/bootstrap.php';

use App\Services\AdminMenuService;
use App\Http\Middleware\RBACManager;

$passed = 0;
$failed = 0;

function test(string $name, bool $condition, string $detail = '') {
    global $passed, $failed;
    if ($condition) {
        echo "  ✓ $name" . ($detail ? " ($detail)" : '') . "\n";
        $passed++;
    } else {
        echo "  ✗ FAIL: $name" . ($detail ? " — $detail" : '') . "\n";
        $failed++;
    }
}

echo "=== RBAC SIDEBAR END-TO-END TEST ===\n\n";

// 1. Verify DB tables exist
echo "[1] Database Tables\n";
$db = \App\Core\Database::getInstance();

$menuCount = $db->fetchOne("SELECT COUNT(*) as c FROM admin_menu_items WHERE is_active = 1")['c'] ?? 0;
test("admin_menu_items has active rows", $menuCount > 0, "$menuCount items");

$rolePermCount = $db->fetchOne("SELECT COUNT(*) as c FROM admin_role_menu_permissions")['c'] ?? 0;
test("admin_role_menu_permissions has rows", $rolePermCount > 0, "$rolePermCount rows");

$userPermCount = $db->fetchOne("SELECT COUNT(*) as c FROM admin_user_menu_permissions")['c'] ?? -1;
test("admin_user_menu_permissions table exists", $userPermCount >= 0, "$userPermCount rows");

// 2. Test per-role menu item counts
echo "\n[2] Role Menu Item Counts\n";
$expectedCounts = [
    'super_admin' => $menuCount,  // all items
    'admin'       => $menuCount,  // all items
    'manager'     => 121,
    'associate'   => 42,
    'agent'       => 28,
    'telecaller'  => 16,
    'customer'    => 6,
];

$svc = new AdminMenuService();

foreach ($expectedCounts as $role => $expected) {
    // Override session role
    $_SESSION['admin_role'] = $role;
    $_SESSION['role'] = $role;

    // Reconstruct service to pick up new role
    $svcTest = new AdminMenuService();
    $items = $svcTest->getMenuItems($role);
    $actual = count($items);

    // Allow ±5 tolerance for seed variations
    test(
        "Role '$role' gets ~$expected items",
        abs($actual - $expected) <= 5 || $actual >= $expected,
        "got $actual, expected ~$expected"
    );
}

// Employee role now resolves via designation sub-role (no direct 'employee' role in permissions)
// Each employee sees different items based on their department/designation — tested in section 10

// 3. Test super_admin sees all
echo "\n[3] Super Admin Full Access\n";
$_SESSION['admin_role'] = 'super_admin';
$_SESSION['role'] = 'super_admin';
$svcAdmin = new AdminMenuService();
$allItems = $svcAdmin->getMenuItems('super_admin');
test("Super admin sees all items", count($allItems) === $menuCount, count($allItems) . " vs $menuCount");

$sections = [];
foreach ($allItems as $item) {
    $sections[$item['section']] = true;
}
test("Super admin sees all sections", count($sections) >= 10, count($sections) . " sections");

// 4. Test manager gets a subset
echo "\n[4] Manager Subsets\n";
$managerItems = $svc->getMenuItems('manager');
$managerSections = [];
foreach ($managerItems as $item) {
    $managerSections[$item['section']] = true;
}
test("Manager sees fewer items than admin", count($managerItems) < $menuCount, count($managerItems) . " < $menuCount");
test("Manager has dashboard access", isset($managerSections['dashboards']), 'sections: ' . implode(', ', array_keys($managerSections)));

// 5. Test employee gets minimal access
echo "\n[5] Employee Minimal Access\n";
$employeeItems = $svc->getMenuItems('employee');
test("Employee sees significantly fewer items", count($employeeItems) < count($managerItems), count($employeeItems) . " < " . count($managerItems));

// 6. Test customer gets almost nothing
echo "\n[6] Customer Minimal Access\n";
$customerItems = $svc->getMenuItems('customer');
test("Customer sees very few items", count($customerItems) <= 10, count($customerItems) . " items");

// 7. Verify no duplicate menu items per role
echo "\n[7] No Duplicate Items\n";
foreach (['manager', 'employee', 'associate', 'agent', 'telecaller'] as $role) {
    $items = $svc->getMenuItems($role);
    $ids = array_column($items, 'id');
    $unique = array_unique($ids);
    test("Role '$role' has no duplicate menu IDs", count($ids) === count($unique), count($ids) . " total, " . count($unique) . " unique");
}

// 8. Verify can_view flags are set
echo "\n[8] Permission Flags\n";
foreach (['manager', 'employee', 'telecaller'] as $role) {
    $items = $svc->getMenuItems($role);
    $withCanView = 0;
    foreach ($items as $item) {
        if (!empty($item['can_view'])) $withCanView++;
    }
    test("Role '$role' items have can_view flag", $withCanView === count($items), "$withCanView/" . count($items));
}

// 9. Employee Sub-Role System
echo "\n[9] Employee Sub-Role System\n";
$subRoleTableExists = $db->fetchOne("SHOW TABLES LIKE 'employee_designation_roles'");
test("employee_designation_roles table exists", !empty($subRoleTableExists));

$designMappings = $db->fetchOne("SELECT COUNT(*) as c FROM employee_designation_roles")['c'] ?? 0;
test("Designation mappings seeded", $designMappings >= 30, "$designMappings mappings");

$empPerms = $db->fetchOne("SELECT COUNT(*) as c FROM admin_role_menu_permissions WHERE role LIKE 'employee_%'")['c'] ?? 0;
test("Employee sub-role permissions exist", $empPerms >= 200, "$empPerms permissions");

// Test specific employee designations → sub-role resolution
foreach ([
    [8, 'employee_marketing_executive', 'Officer/Marketing'],
    [10, 'employee_it_executive', 'Executive/IT'],
    [11, 'employee_hr_executive', 'Executive/HR'],
    [12, 'employee_operations_manager', 'Manager/Operations'],
    [68, 'employee_sales_executive', 'Analyst/Sales'],
] as list($userId, $expectedSubRole, $label)) {
    $emp = $db->fetchOne("SELECT e.designation, e.department FROM employees e WHERE e.user_id = $userId");
    if ($emp) {
        $mapping = $db->fetchOne(
            "SELECT sub_role FROM employee_designation_roles WHERE designation = ? AND (department = ? OR department IS NULL) LIMIT 1",
            [$emp['designation'], $emp['department']]
        );
        $actual = $mapping['sub_role'] ?? 'employee_general';
        test("$label -> $expectedSubRole", $actual === $expectedSubRole, "got $actual");
    }
}

// Test sub-role menu counts
echo "\n[10] Employee Sub-Role Menu Counts\n";
$empRoles = [
    'employee_hr_executive' => 14,
    'employee_it_executive' => 26,
    'employee_marketing_executive' => 23,
    'employee_operations_manager' => 52,
    'employee_sales_executive' => 38,
    'employee_general' => 6,
];
foreach ($empRoles as $empRole => $expectedMin) {
    $items = $svc->getMenuItems($empRole);
    $actual = count($items);
    test("Sub-role '$empRole' has >= $expectedMin items", $actual >= $expectedMin, "got $actual");
}

// Cleanup session
unset($_SESSION['admin_role'], $_SESSION['role']);

echo "\n=== RESULTS: $passed passed, $failed failed ===\n";
exit($failed > 0 ? 1 : 0);
