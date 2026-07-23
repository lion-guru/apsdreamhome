<?php
require_once 'C:/xampp/htdocs/apsdreamhome/vendor/autoload.php';
$db = \App\Core\Database\Database::getInstance();

echo "=== ALL USER ROLES + COUNTS ===\n";
$rows = $db->fetchAll("SELECT role, COUNT(*) as cnt FROM users GROUP BY role ORDER BY role");
foreach($rows as $r) {
    echo $r['role'] . ': ' . $r['cnt'] . " users\n";
}

echo "\n=== TEST USERS (one per role) ===\n";
$rows = $db->fetchAll("SELECT id, name, email, role, status FROM users WHERE status='active' ORDER BY role, id");
$seen = [];
foreach($rows as $r) {
    if (!isset($seen[$r['role']])) {
        echo $r['role'] . ' | id=' . $r['id'] . ' | ' . $r['email'] . ' | ' . $r['name'] . ' | status=' . $r['status'] . "\n";
        $seen[$r['role']] = true;
    }
}

echo "\n=== EMPLOYEE DESIGNATIONS + USER LINKS ===\n";
$rows = $db->fetchAll("SELECT e.user_id, e.designation, e.department, e.status, u.email, u.role 
    FROM employees e JOIN users u ON e.user_id = u.id 
    WHERE e.status='active' ORDER BY e.department, e.designation LIMIT 30");
foreach($rows as $r) {
    echo $r['department'] . ' | ' . $r['designation'] . ' | user_role=' . $r['role'] . ' | ' . $r['email'] . "\n";
}

echo "\n=== ROLES IN RBAC PERMISSIONS BUT NOT IN USERS ===\n";
$rbacRoles = $db->fetchAll("SELECT DISTINCT role FROM admin_role_menu_permissions");
$userRoles = $db->fetchAll("SELECT DISTINCT role FROM users");
$userRoleList = array_column($userRoles, 'role');
foreach($rbacRoles as $r) {
    if (!in_array($r['role'], $userRoleList)) {
        echo $r['role'] . " (no users with this role)\n";
    }
}

echo "\n=== USER ROLES BUT NOT IN RBAC PERMISSIONS ===\n";
foreach($userRoles as $r) {
    $found = false;
    foreach($rbacRoles as $rb) {
        if ($rb['role'] === $r['role']) { $found = true; break; }
    }
    if (!$found) {
        echo $r['role'] . " (no RBAC menu permissions)\n";
    }
}

echo "\n=== EXISTING TEST PASSWORDS CHECK ===\n";
$rows = $db->fetchAll("SELECT id, name, email, role, password FROM users WHERE status='active' ORDER BY role LIMIT 20");
foreach($rows as $r) {
    $verify = password_verify('Aps@2026', $r['password']);
    echo $r['role'] . ' | ' . $r['email'] . ' | Aps@206: ' . ($verify ? 'YES' : 'NO') . "\n";
}