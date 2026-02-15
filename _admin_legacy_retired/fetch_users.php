<?php
require_once(__DIR__ . '/../includes/functions/role_helper.php');
enforceRole(['admin','superadmin']);
require_once(__DIR__ . '/../src/Database/Database.php');
$db = new Database();
$con = $db->getConnection();
$role = isset($_GET['role']) ? $_GET['role'] : '';
$sql = "SELECT id, name, email, phone, utype AS role, status FROM users";
if ($role && in_array($role, ['admin','superadmin','associate','user','builder','agent','employee','customer'])) {
    $sql .= " WHERE utype = '" . mysqli_real_escape_string($con, $role) . "'";
}
$sql .= " ORDER BY id DESC";
$result = mysqli_query($con, $sql);
$users = [];
while ($row = mysqli_fetch_assoc($result)) {
    $users[] = [
        'id' => htmlspecialchars($row['id']),
        'name' => htmlspecialchars($row['name']),
        'email' => htmlspecialchars($row['email']),
        'phone' => $row['phone'],
        'role' => $row['role'],
        'status' => $row['status']
    ];
}
header('Content-Type: application/json');
echo json_encode($users);
