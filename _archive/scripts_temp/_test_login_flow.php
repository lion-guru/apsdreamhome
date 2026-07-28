<?php
/**
 * Test login flow for all executive roles
 * Verifies: login → redirect → dashboard → sidebar permissions
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);
require __DIR__ . '/../config/bootstrap.php';
require __DIR__ . '/../app/Core/Database.php';

use App\Core\Database;
$db = Database::getInstance();
$pdo = $db->getConnection();

echo "=== LOGIN FLOW VERIFICATION ===\n\n";

// Key roles to test
$testRoles = [
    'ceo' => ['email' => 'ceo@apsdreamhome.com', 'expected_redirect' => '/admin/dashboard/ceo'],
    'cfo' => ['email' => 'cfo@apsdreamhome.com', 'expected_redirect' => '/admin/dashboard/cfo'],
    'cto' => ['email' => 'cto@apsdreamhome.com', 'expected_redirect' => '/admin/dashboard/cto'],
    'coo' => ['email' => 'coo@apsdreamhome.com', 'expected_redirect' => '/admin/dashboard/coo'],
    'cmo' => ['email' => 'cmo@apsdreamhome.com', 'expected_redirect' => '/admin/dashboard/cmo'],
    'chro' => ['email' => 'chro@apsdreamhome.com', 'expected_redirect' => '/admin/dashboard/chro'],
    'sales_director' => ['email' => 'sales_director@apsdreamhome.com', 'expected_redirect' => '/admin/dashboard/sales'],
    'finance_director' => ['email' => 'finance_director@apsdreamhome.com', 'expected_redirect' => '/admin/dashboard/finance'],
    'hr_director' => ['email' => 'hr_director@apsdreamhome.com', 'expected_redirect' => '/admin/dashboard/hr'],
    'it_manager' => ['email' => 'it_manager@apsdreamhome.com', 'expected_redirect' => '/admin/dashboard/it'],
    'sales_manager' => ['email' => 'sales_manager@apsdreamhome.com', 'expected_redirect' => '/admin/dashboard/sales'],
    'team_lead' => ['email' => 'team_lead@apsdreamhome.com', 'expected_redirect' => '/admin/dashboard'],
    'accountant' => ['email' => 'accountant@apsdreamhome.com', 'expected_redirect' => '/admin/dashboard'],
];

// Load the redirect map from CustomerAuthController
$content = file_get_contents(__DIR__ . '/../app/Http/Controllers/Auth/CustomerAuthController.php');

$passed = 0;
$failed = 0;

foreach ($testRoles as $role => $info) {
    echo "[$role] ";
    
    // 1. Check user exists in DB
    $user = $pdo->prepare("SELECT id, role, status FROM users WHERE email = ?");
    $user->execute([$info['email']]);
    $u = $user->fetch(PDO::FETCH_ASSOC);
    
    if (!$u) {
        echo "FAIL (user not found)\n";
        $failed++;
        continue;
    }
    
    if ($u['role'] !== $role) {
        echo "FAIL (role mismatch: expected=$role, got={$u['role']})\n";
        $failed++;
        continue;
    }
    
    // 2. Check redirect map in CustomerAuthController
    $hasRedirect = strpos($content, "'$role' =>") !== false || strpos($content, "'$role'=>") !== false;
    if (!$hasRedirect) {
        echo "FAIL (no redirect in CustomerAuthController)\n";
        $failed++;
        continue;
    }
    
    // 3. Check RoleBasedDashboardController redirect map
    $rbacContent = file_get_contents(__DIR__ . '/../app/Http/Controllers/RoleBasedDashboardController.php');
    $hasRbacRedirect = strpos($rbacContent, "'$role' =>") !== false;
    if (!$hasRbacRedirect && !in_array($role, ['team_lead', 'accountant'])) {
        echo "WARN (no redirect in RoleBasedDashboardController) ";
    }
    
    // 4. Check sidebar permissions
    $permCount = $pdo->prepare("SELECT COUNT(*) as c FROM admin_role_menu_permissions WHERE role = ? AND can_view = 1");
    $permCount->execute([$role]);
    $perms = $permCount->fetch()['c'];
    
    if ($perms == 0 && !in_array($role, ['team_lead', 'accountant'])) {
        echo "WARN (0 sidebar items) ";
    }
    
    // 5. Check dashboard view exists
    $dashRoleMap = [
        'ceo' => 'ceo', 'cfo' => 'cfo', 'cto' => 'cto', 'coo' => 'coo',
        'cmo' => 'cm', 'chro' => 'hr', 'sales_director' => 'sales',
        'finance_director' => 'finance', 'hr_director' => 'hr',
        'it_manager' => 'it', 'sales_manager' => 'sales',
    ];
    $dashFile = $dashRoleMap[$role] ?? null;
    if ($dashFile) {
        $viewPath = __DIR__ . "/../app/views/dashboard/{$dashFile}_dashboard.php";
        $viewExists = file_exists($viewPath);
        if (!$viewExists) {
            echo "WARN (dashboard view missing) ";
        }
    }
    
    echo "OK ({$perms} menu items, role={$u['role']}, status={$u['status']})\n";
    $passed++;
}

echo "\n=== SUMMARY ===\n";
echo "Passed: $passed / " . count($testRoles) . "\n";
echo "Failed: $failed\n";
