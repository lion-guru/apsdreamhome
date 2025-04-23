<?php
session_start();
include 'config.php';
require_role('Admin');
require_permission('view_audit_log');
$msg = '';
$filter_user = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
$filter_action = isset($_GET['action']) ? $_GET['action'] : '';
$where = [];
if ($filter_user) $where[] = "al.user_id = $filter_user";
if ($filter_action) $where[] = "al.action = '" . $conn->real_escape_string($filter_action) . "'";
$where_sql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';
$sql = "SELECT al.*, e.name as user FROM audit_log al LEFT JOIN employees e ON al.user_id = e.id $where_sql ORDER BY al.id DESC LIMIT 200";
$audit_logs = $conn->query($sql);
$users = $conn->query("SELECT id, name FROM employees ORDER BY name");
$actions = $conn->query("SELECT DISTINCT action FROM audit_log ORDER BY action");
?>
<!DOCTYPE html>
<html lang='en'>
<head><meta charset='UTF-8'><title>Audit Log Report</title><link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css'></head>
<body><div class='container py-4'><h2>Audit Log Report</h2><form method='GET' class='row g-3 mb-4'><div class='col-md-4'><label>User</label><select name='user_id' class='form-control'><option value=''>All</option><?php while($u = $users->fetch_assoc()): ?><option value='<?= $u['id'] ?>' <?= ($filter_user==$u['id'])?'selected':'' ?>><?= htmlspecialchars($u['name']) ?></option><?php endwhile; ?></select></div><div class='col-md-4'><label>Action</label><select name='action' class='form-control'><option value=''>All</option><?php while($a = $actions->fetch_assoc()): ?><option value='<?= htmlspecialchars($a['action']) ?>' <?= ($filter_action==$a['action'])?'selected':'' ?>><?= htmlspecialchars($a['action']) ?></option><?php endwhile; ?></select></div><div class='col-md-4 d-flex align-items-end'><button type='submit' class='btn btn-primary'>Filter</button></div></form><table class='table table-bordered'><thead><tr><th>ID</th><th>User</th><th>Action</th><th>Details</th><th>IP Address</th><th>Timestamp</th></tr></thead><tbody><?php while($row = $audit_logs->fetch_assoc()): ?><tr><td><?= $row['id'] ?></td><td><?= htmlspecialchars($row['user']) ?></td><td><?= htmlspecialchars($row['action']) ?></td><td><?= htmlspecialchars($row['details']) ?></td><td><?= htmlspecialchars($row['ip_address']) ?></td><td><?= htmlspecialchars($row['created_at']) ?></td></tr><?php endwhile; ?></tbody></table></div></body></html>
