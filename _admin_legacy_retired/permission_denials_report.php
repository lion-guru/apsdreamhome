<?php
session_start();
include 'config.php';
require_role('Admin');
require_permission('view_permission_denials');
$msg = '';
// Assume permission denials are logged in audit_log as action='Permission Denied'
$filter_user = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
$where = $filter_user ? ("WHERE al.user_id = $filter_user AND al.action='Permission Denied'") : "WHERE al.action='Permission Denied'";
$sql = "SELECT al.*, e.name as user FROM audit_log al LEFT JOIN employees e ON al.user_id = e.id $where ORDER BY al.id DESC LIMIT 200";
$denials = $conn->query($sql);
$users = $conn->query("SELECT id, name FROM employees ORDER BY name");
?>
<!DOCTYPE html>
<html lang='en'>
<head><meta charset='UTF-8'><title>Permission Denials Report</title><link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css'></head>
<body><div class='container py-4'><h2>Permission Denials Report</h2><form method='GET' class='row g-3 mb-4'><div class='col-md-4'><label>User</label><select name='user_id' class='form-control'><option value=''>All</option><?php while($u = $users->fetch_assoc()): ?><option value='<?= $u['id'] ?>' <?= ($filter_user==$u['id'])?'selected':'' ?>><?= htmlspecialchars($u['name']) ?></option><?php endwhile; ?></select></div><div class='col-md-4 d-flex align-items-end'><button type='submit' class='btn btn-primary'>Filter</button></div></form><table class='table table-bordered'><thead><tr><th>ID</th><th>User</th><th>Details</th><th>IP Address</th><th>Timestamp</th></tr></thead><tbody><?php while($row = $denials->fetch_assoc()): ?><tr><td><?= $row['id'] ?></td><td><?= htmlspecialchars($row['user']) ?></td><td><?= htmlspecialchars($row['details']) ?></td><td><?= htmlspecialchars($row['ip_address']) ?></td><td><?= htmlspecialchars($row['created_at']) ?></td></tr><?php endwhile; ?></tbody></table></div></body></html>
