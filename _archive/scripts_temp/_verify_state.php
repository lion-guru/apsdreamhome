<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require __DIR__ . '/../config/bootstrap.php';
require __DIR__ . '/../app/Core/Database.php';
use App\Core\Database;
$db = Database::getInstance();
$pdo = $db->getConnection();

// 1. Check if CEO can access executive assistant route
echo "=== EXECUTIVE AI ROUTE CHECK ===\n";
$routeExists = $pdo->query("SELECT COUNT(*) as c FROM admin_menu_items WHERE url = '/admin/ai/executive-assistant'")->fetch()['c'];
echo "Sidebar item: " . ($routeExists ? 'YES' : 'NO') . "\n";

// 2. Check if any roles have 0 permissions still
echo "\n=== ROLES WITH 0 PERMISSIONS ===\n";
$empty = $pdo->query("SELECT DISTINCT role FROM admin_role_menu_permissions WHERE can_view=1 GROUP BY role HAVING COUNT(*) = 0")->fetchAll(PDO::FETCH_ASSOC);
if (empty($empty)) echo "  None â€” all roles have permissions\n";
else foreach ($empty as $e) echo "  " . $e['role'] . "\n";

// 3. Check employee_designation_roles
echo "\n=== EMPLOYEE DESIGNATION ROLES ===\n";
$count = $pdo->query("SELECT COUNT(*) as c FROM employee_designation_roles")->fetch()['c'];
echo "  Total entries: $count\n";

// 4. Check if all dashboard views exist
echo "\n=== DASHBOARD VIEWS ===\n";
$roles = ['ceo','cfo','cto','coo','cm','hr','it','marketing','operations','finance','sales','director','superadmin'];
foreach ($roles as $r) {
    $file = __DIR__ . "/../app/views/dashboard/{$r}_dashboard.php";
    $exists = file_exists($file);
    echo "  {$r}_dashboard.php: " . ($exists ? 'OK' : 'MISSING') . "\n";
}

// 5. Check if RoleBasedDashboardController index has all role redirects
echo "\n=== DASHBOARD REDIRECT MAP ===\n";
$content = file_get_contents(__DIR__ . '/../app/Http/Controllers/RoleBasedDashboardController.php');
$roles_in_map = ['ceo','cfo','cto','coo','cmo','chro','sales_director','marketing_director','construction_director','finance_director','hr_director'];
foreach ($roles_in_map as $r) {
    $found = strpos($content, "'$r'") !== false;
    echo "  $r: " . ($found ? 'OK' : 'MISSING') . "\n";
}

echo "\nDone.\n";?>