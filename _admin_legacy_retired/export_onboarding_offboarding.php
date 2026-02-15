<?php
session_start();
include 'config.php';
require_role('Admin');
require_permission('export_onboarding_offboarding');
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="onboarding_offboarding_export_' . date('Ymd_His') . '.csv"');
$out = fopen('php://output', 'w');
fputcsv($out, ['ID', 'User', 'Action', 'Details', 'IP Address', 'Timestamp']);
$sql = "SELECT al.id, e.name as user, al.action, al.details, al.ip_address, al.created_at FROM audit_log al LEFT JOIN employees e ON al.user_id = e.id WHERE al.action IN ('Onboarding','Offboarding') ORDER BY al.id DESC LIMIT 1000";
$res = $conn->query($sql);
while($row = $res->fetch_assoc()) {
    fputcsv($out, [$row['id'], $row['user'], $row['action'], $row['details'], $row['ip_address'], $row['created_at']]);
}
fclose($out);
exit;
