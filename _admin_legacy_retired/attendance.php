<?php
session_start();
include 'config.php';
if (!isset($_SESSION['auser'])) { header('Location: login.php'); exit(); }
require_permission('manage_attendance');
$attendance = $conn->query("SELECT a.id, e.name, a.date, a.status, a.remarks FROM attendance a LEFT JOIN employees e ON a.employee_id = e.id ORDER BY a.date DESC, e.name");
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Attendance</title><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"></head>
<body><div class="container py-4"><h2>Attendance</h2><a href="mark_attendance.php" class="btn btn-success mb-2">Mark Attendance</a><table class="table table-bordered"><thead><tr><th>Employee</th><th>Date</th><th>Status</th><th>Remarks</th></tr></thead><tbody><?php while($a = $attendance->fetch_assoc()): ?><tr><td><?= htmlspecialchars($a['name']) ?></td><td><?= $a['date'] ?></td><td><?= ucfirst($a['status']) ?></td><td><?= htmlspecialchars($a['remarks']) ?></td></tr><?php endwhile; ?></tbody></table></div></body></html>
