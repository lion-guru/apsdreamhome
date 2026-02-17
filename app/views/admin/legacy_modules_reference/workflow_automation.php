<?php
require_once __DIR__ . '/core/init.php';
require_role('Admin');
require_permission('view_workflow_automation');

$db = \App\Core\App::database();
$msg = '';
// Example: Escalate if a user has 3+ permission denials in 24h
$escalations = $db->fetchAll("SELECT e.name, al.user_id, COUNT(*) as denials FROM audit_log al LEFT JOIN employees e ON al.user_id = e.id WHERE al.action='Permission Denied' AND al.created_at >= NOW() - INTERVAL 1 DAY GROUP BY al.user_id HAVING denials >= 3 ORDER BY denials DESC");
?>
<!DOCTYPE html>
<html lang='en'>
<head><meta charset='UTF-8'><title>Workflow Automation & Escalations</title><link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css'></head>
<body><div class='container py-4'><h2>Workflow Automation & Escalations</h2><p>This dashboard highlights users with repeated permission denials (3 or more in the last 24 hours) for potential compliance escalation.</p><table class='table table-bordered'><thead><tr><th>User</th><th>Denials (24h)</th></tr></thead><tbody><?php foreach($escalations as $row): ?><tr><td><?= h($row['name']) ?></td><td><?= $row['denials'] ?></td></tr><?php endforeach; ?></tbody></table></div></body></html>
