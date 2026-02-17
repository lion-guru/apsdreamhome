<?php
require_once __DIR__ . '/core/init.php';
require_role('Admin');
require_permission('export_audit_logs');

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="audit_logs_export_' . date('Ymd_His') . '.csv"');

$out = fopen('php://output', 'w');
fputcsv($out, ['ID', 'User', 'Action', 'Details', 'IP Address', 'Timestamp']);

$db = \App\Core\App::database();
$sql = "SELECT al.id, e.name as user, al.action, al.details, al.ip_address, al.created_at FROM audit_log al LEFT JOIN employees e ON al.user_id = e.id ORDER BY al.id DESC LIMIT 1000";
$rows = $db->fetchAll($sql);

foreach($rows as $row) {
    fputcsv($out, [$row['id'], $row['user'], $row['action'], $row['details'], $row['ip_address'], $row['created_at']]);
}
fclose($out);
exit;
