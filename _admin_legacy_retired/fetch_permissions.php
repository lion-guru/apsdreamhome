<?php
require_once(__DIR__ . '/../includes/functions/role_helper.php');
enforceRole(['superadmin']);
$permissions = include(__DIR__ . '/../includes/config/role_permissions.php');
header('Content-Type: application/json');
foreach ($permissions as $row) {
    $row['permission_id'] = htmlspecialchars($row['permission_id']);
    $row['permission_name'] = htmlspecialchars($row['permission_name']);
}
echo json_encode($permissions);
