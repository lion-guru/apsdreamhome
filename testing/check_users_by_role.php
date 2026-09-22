<?php
require_once __DIR__ . '/../config/bootstrap.php';
use App\Core\Database\Database;

$db = Database::getInstance();
$roles = ['super_admin', 'admin', 'associate', 'agent', 'employee', 'customer', 'farmer'];

echo "=== CHECKING ACTIVE USERS BY ROLE ===\n";
foreach ($roles as $role) {
    $user = $db->fetchOne("SELECT id, name, email, role, status FROM users WHERE role = ? AND status = 'active' LIMIT 1", [$role]);
    if ($user) {
        echo sprintf("[%s] ID: %d | Name: %s | Email: %s\n", $role, $user['id'], $user['name'], $user['email']);
    } else {
        echo sprintf("[%s] NO ACTIVE USER FOUND\n", $role);
    }
}
