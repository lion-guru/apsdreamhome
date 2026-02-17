<?php
/**
 * Fetch Users AJAX handler
 */
require_once __DIR__ . '/core/init.php';

// Access control
if (!SecurityUtility::hasRole(['admin', 'superadmin'])) {
    http_response_code(403);
    die(json_encode(['error' => 'Unauthorized access.']));
}

$role = isset($_GET['role']) ? $_GET['role'] : '';
$allowed_roles = ['admin', 'superadmin', 'associate', 'user', 'builder', 'agent', 'employee', 'customer'];

$sql = "SELECT uid as id, uname as name, uemail as email, uphone as phone, utype AS role, 'active' as status FROM user";
$params = [];

if ($role && in_array($role, $allowed_roles)) {
    $sql .= " WHERE utype = ?";
    $params[] = $role;
}

$sql .= " ORDER BY uid DESC";

$users = [];
$results = \App\Core\App::database()->fetchAll($sql, $params);

foreach ($results as $row) {
    $users[] = [
        'id' => $row['id'],
        'name' => h($row['name']),
        'email' => h($row['email']),
        'phone' => h($row['phone']),
        'role' => h($row['role']),
        'status' => h($row['status'])
    ];
}

header('Content-Type: application/json');
echo json_encode($users);
?>
