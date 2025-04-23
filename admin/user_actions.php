<?php
require_once(__DIR__ . '/../includes/functions/role_helper.php');
enforceRole(['admin','superadmin']);
require_once(__DIR__ . '/../includes/classes/Database.php');
$db = new Database();
$con = $db->getConnection();

$action = $_POST['action'] ?? '';
$response = ['success' => false, 'message' => 'Invalid request'];

if ($action === 'add') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $role = trim($_POST['role'] ?? 'user');
    $password = password_hash(trim($_POST['password'] ?? ''), PASSWORD_DEFAULT);
    $status = trim($_POST['status'] ?? 'active');
    if ($name && $email && $phone && $password) {
        $stmt = $con->prepare("INSERT INTO users (name, email, phone, utype, password, status) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $name, $email, $phone, $role, $password, $status);
        if ($stmt->execute()) {
            $response = ['success' => true, 'message' => 'User added successfully'];
        } else {
            $response['message'] = 'Failed to add user.';
        }
    } else {
        $response['message'] = 'All fields are required.';
    }
} elseif ($action === 'edit') {
    $id = intval($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $role = trim($_POST['role'] ?? 'user');
    $status = trim($_POST['status'] ?? 'active');
    if ($id && $name && $email && $phone) {
        $stmt = $con->prepare("UPDATE users SET name=?, email=?, phone=?, utype=?, status=? WHERE id=?");
        $stmt->bind_param("sssssi", $name, $email, $phone, $role, $status, $id);
        if ($stmt->execute()) {
            $response = ['success' => true, 'message' => 'User updated successfully'];
        } else {
            $response['message'] = 'Failed to update user.';
        }
    } else {
        $response['message'] = 'All fields are required.';
    }
} elseif ($action === 'delete') {
    $id = intval($_POST['id'] ?? 0);
    if ($id) {
        $stmt = $con->prepare("DELETE FROM users WHERE id=?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $response = ['success' => true, 'message' => 'User deleted successfully'];
        } else {
            $response['message'] = 'Failed to delete user.';
        }
    } else {
        $response['message'] = 'Invalid user ID.';
    }
}

header('Content-Type: application/json');
echo json_encode($response);
