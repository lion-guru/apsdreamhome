<?php
session_start();
include 'config.php';
require_permission('manage_leaves');
if (!isset($_SESSION['auser'])) { header('Location: login.php'); exit(); }
$leaves = $conn->query("SELECT l.id, e.name, l.leave_type, l.from_date, l.to_date, l.status, l.remarks FROM leaves l LEFT JOIN employees e ON l.employee_id = e.id ORDER BY l.from_date DESC, e.name");
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Leaves</title><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"></head>
<body><div class="container py-4"><h2>Leave Applications</h2><a href="apply_leave.php" class="btn btn-success mb-2">Apply Leave</a><table class="table table-bordered"><thead><tr><th>Employee</th><th>Type</th><th>From</th><th>To</th><th>Status</th><th>Remarks</th></tr></thead><tbody><?php while($l = $leaves->fetch_assoc()): ?><tr><td><?= htmlspecialchars($l['name']) ?></td><td><?= htmlspecialchars($l['leave_type']) ?></td><td><?= $l['from_date'] ?></td><td><?= $l['to_date'] ?></td><td><?= ucfirst($l['status']) ?></td><td><?= htmlspecialchars($l['remarks']) ?></td></tr><?php endwhile; ?></tbody></table></div></body></html>
