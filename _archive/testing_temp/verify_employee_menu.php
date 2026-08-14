<?php
/**
 * Verify centralized employee menu â€” test RBAC permissions per sub-role
 * Run: php testing/verify_employee_menu.php
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
} catch (Exception $e) {
    die("Connection failed: " . $e->getMessage() . "\n");
}

$pass = 0;
$fail = 0;

function assert_test($name, $condition, $detail = '') {
    global $pass, $fail;
    if ($condition) {
        echo "  âœ“ $name\n";
        $pass++;
    } else {
        echo "  âœ— FAIL: $name" . ($detail ? " â€” $detail" : "") . "\n";
        $fail++;
    }
}

echo "=== STEP 1: Employee menu items in admin_menu_items ===\n";
$empItems = $pdo->query("SELECT id, name, url, icon, section, order_index FROM admin_menu_items WHERE section = 'employee' ORDER BY order_index")->fetchAll(PDO::FETCH_ASSOC);
assert_test("10 employee items exist", count($empItems) === 10, "found " . count($empItems));
foreach ($empItems as $item) {
    echo "    ID={$item['id']} {$item['name']} â†’ {$item['url']}\n";
}

echo "\n=== STEP 2: RBAC permissions per sub-role ===\n";
$subRoles = $pdo->query("SELECT DISTINCT role FROM admin_role_menu_permissions WHERE role LIKE 'employee_%' ORDER BY role")->fetchAll(PDO::FETCH_COLUMN);
assert_test("18 sub-roles have permissions", count($subRoles) === 18, "found " . count($subRoles));

foreach ($subRoles as $subRole) {
    $count = $pdo->query("SELECT COUNT(*) FROM admin_role_menu_permissions WHERE role = '$subRole' AND menu_item_id IN (SELECT id FROM admin_menu_items WHERE section = 'employee')")->fetchColumn();
    echo "    $subRole: $count employee items\n";
}

echo "\n=== STEP 3: No stale admin dashboard permissions for employees ===\n";
$stalePerms = $pdo->query("SELECT COUNT(*) FROM admin_role_menu_permissions WHERE role LIKE 'employee_%' AND menu_item_id IN (SELECT id FROM admin_menu_items WHERE section = 'dashboards')")->fetchColumn();
assert_test("Zero stale admin dashboard permissions", $stalePerms === 0, "found $stalePerms");

echo "\n=== STEP 4: Employee â†’ sub-role mapping ===\n";
$empMap = $pdo->query("
    SELECT e.id, e.user_id, e.designation, e.department, edr.sub_role
    FROM employees e
    LEFT JOIN employee_designation_roles edr 
        ON edr.designation = e.designation AND (edr.department = e.department OR edr.department IS NULL)
    WHERE e.user_id IS NOT NULL
    ORDER BY e.user_id
")->fetchAll(PDO::FETCH_ASSOC);

foreach ($empMap as $emp) {
    $subRole = $emp['sub_role'] ?? 'UNMAPPED';
    $menuCount = 'N/A';
    if ($emp['sub_role']) {
        $menuCount = $pdo->query("SELECT COUNT(*) FROM admin_role_menu_permissions WHERE role = '{$emp['sub_role']}' AND menu_item_id IN (SELECT id FROM admin_menu_items WHERE section = 'employee')")->fetchColumn();
    }
    echo "    User {$emp['user_id']}: {$emp['designation']}/{$emp['department']} â†’ $subRole ($menuCount items)\n";
    assert_test("User {$emp['user_id']} has sub-role", $emp['sub_role'] !== null, "sub_role is null");
}

echo "\n=== STEP 5: Cache cleared ===\n";
$cacheFiles = glob($root . '/storage/cache/*.cache');
assert_test("Cache cleared", count($cacheFiles) === 0, "found " . count($cacheFiles) . " cache files");

echo "\n=== RESULTS ===\n";
echo "PASS: $pass\n";
echo "FAIL: $fail\n";
exit($fail > 0 ? 1 : 0);?>