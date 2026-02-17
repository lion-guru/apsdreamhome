<?php
require_once(__DIR__ . '/../includes/functions/role_helper.php');
enforceRole(['admin','superadmin']);

$action = $_POST['action'] ?? '';
$response = ['success' => false, 'message' => 'Invalid request'];
$db = \App\Core\App::database();

if ($action === 'add') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $role = trim($_POST['role'] ?? 'user');
    $password = password_hash(trim($_POST['password'] ?? ''), PASSWORD_DEFAULT);
    if ($name && $email && $phone && $password) {
        $query = "INSERT INTO user (uname, uemail, uphone, utype, upass, is_updated, join_date) VALUES (:name, :email, :phone, :role, :password, 'Y', NOW())";
        if ($db->execute($query, [
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'role' => $role,
            'password' => $password
        ])) {
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
    if ($id && $name && $email && $phone) {
        $query = "UPDATE user SET uname=:name, uemail=:email, uphone=:phone, utype=:role WHERE uid=:id";
        if ($db->execute($query, [
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'role' => $role,
            'id' => $id
        ])) {
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
        $query = "DELETE FROM user WHERE uid=:id";
        if ($db->execute($query, ['id' => $id])) {
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
