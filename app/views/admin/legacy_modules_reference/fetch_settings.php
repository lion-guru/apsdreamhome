<?php
require_once(__DIR__ . '/../includes/functions/role_helper.php');
enforceRole(['superadmin']);
$settings = include(__DIR__ . '/../includes/config/site_settings.php');
header('Content-Type: application/json');
foreach ($settings as $row) {
    $row['setting_key'] = h($row['setting_key']);
    $row['setting_value'] = h($row['setting_value']);
}
echo json_encode($settings);
