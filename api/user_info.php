<?php
// Simple API endpoint to fetch user info, roles, and permissions (for mobile or external integration)
header('Content-Type: application/json');
require_once '../config.php';
session_start();
if (!isset($_SESSION['auser'])) {
    echo json_encode(['error'=>'Not authenticated']);
    exit;
}
$user_id = $_SESSION['auser'];
$user = $conn->query("SELECT id, name, email, status FROM employees WHERE id=$user_id")->fetch_assoc();
$roles = [];
$res = $conn->query("SELECT r.name FROM user_roles ur JOIN roles r ON ur.role_id=r.id WHERE ur.user_id=$user_id");
while($row = $res->fetch_assoc()) $roles[] = $row['name'];
$permissions = [];
$res = $conn->query("SELECT p.action FROM user_roles ur JOIN role_permissions rp ON ur.role_id=rp.role_id JOIN permissions p ON rp.permission_id=p.id WHERE ur.user_id=$user_id");
while($row = $res->fetch_assoc()) $permissions[] = $row['action'];
echo json_encode([
    'user'=>$user,
    'roles'=>$roles,
    'permissions'=>$permissions
]);
