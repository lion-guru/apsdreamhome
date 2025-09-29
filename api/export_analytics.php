<?php
// API endpoint to export analytics data (admin only)
header('Content-Type: text/csv');
require_once '../config.php';
session_start();
if (!isset($_SESSION['auser'])) {
    echo 'Not authenticated';
    exit;
}
$user_id = $_SESSION['auser'];
// Check if user is admin
$admin_check = $conn->prepare("SELECT ur.user_id FROM user_roles ur JOIN roles r ON ur.role_id=r.id WHERE ur.user_id=? AND r.name='Admin'");
$stmt->bind_param("s", $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($admin_check->num_rows == 0) {
    echo 'Not authorized';
    exit;
}
header('Content-Disposition: attachment; filename="analytics_export_' . date('Ymd_His') . '.csv"');
$out = fopen('php://output', 'w');
fputcsv($out, ['Metric','Value']);
$onboard_count = $conn->query("SELECT COUNT(*) as c FROM audit_log WHERE action='Onboarding' AND created_at >= NOW() - INTERVAL 30 DAY")->fetch_assoc()['c'];
fputcsv($out, ['Onboardings (30d)', $onboard_count]);
$offboard_count = $conn->query("SELECT COUNT(*) as c FROM audit_log WHERE action='Offboarding' AND created_at >= NOW() - INTERVAL 30 DAY")->fetch_assoc()['c'];
fputcsv($out, ['Offboardings (30d)', $offboard_count]);
$perm_usage = $conn->query("SELECT p.action, COUNT(al.id) as usage_count FROM audit_log al JOIN permissions p ON al.details LIKE CONCAT('%', p.action, '%') GROUP BY p.action ORDER BY usage_count DESC LIMIT 10");
while($row = $perm_usage->fetch_assoc()) {
    fputcsv($out, ['Permission Usage: ' . $row['action'], $row['usage_count']]);
}
fclose($out);
exit;
