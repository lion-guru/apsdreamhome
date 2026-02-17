<?php
require_once(__DIR__ . '/../includes/functions/role_helper.php');
enforceRole(['superadmin']);
$settings = include(__DIR__ . '/../includes/config/ai_settings.php');
header('Content-Type: application/json');
echo json_encode(array_map(function($row) {
    $row['setting_key'] = h($row['setting_key']);
    $row['setting_value'] = h($row['setting_value']);
    return $row;
}, $settings));
