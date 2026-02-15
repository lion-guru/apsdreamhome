<?php
require_once(__DIR__ . '/../includes/functions/role_helper.php');
enforceRole(['admin','superadmin']);
require_once(__DIR__ . '/../src/Database/Database.php');
$db = new Database();
$con = $db->getConnection();
$sql = "SELECT id, name, email, phone, role, status FROM employees ORDER BY id DESC";
$result = mysqli_query($con, $sql);
$employees = [];
while ($row = mysqli_fetch_assoc($result)) {
    $employees[] = [
        'id' => htmlspecialchars($row['id']),
        'name' => htmlspecialchars($row['name']),
        'email' => htmlspecialchars($row['email']),
        'phone' => $row['phone'],
        'role' => $row['role'],
        'status' => $row['status']
    ];
}
header('Content-Type: application/json');
echo json_encode($employees);
