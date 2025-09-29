<?php
// Simple API endpoint to fetch user info, roles, and permissions (for mobile or external integration)
header('Content-Type: application/json');
require_once '../config.php';
session_start();

if (!isset($_SESSION['auser'])) {
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

$user_id = $_SESSION['auser'];

// Get user info using prepared statement
$stmt = $conn->prepare("SELECT id, name, email, status FROM employees WHERE id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Get user roles using prepared statement
$roles = [];
$stmt = $conn->prepare("SELECT r.name FROM user_roles ur JOIN roles r ON ur.role_id=r.id WHERE ur.user_id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $roles[] = $row['name'];
}
$stmt->close();

// Get user permissions using prepared statement
$permissions = [];
$stmt = $conn->prepare("SELECT p.action FROM user_roles ur JOIN role_permissions rp ON ur.role_id=rp.role_id JOIN permissions p ON rp.permission_id=p.id WHERE ur.user_id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $permissions[] = $row['action'];
}
$stmt->close();

echo json_encode([
    'user' => $user,
    'roles' => $roles,
    'permissions' => $permissions
]);
?>
