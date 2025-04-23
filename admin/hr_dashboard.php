<?php
session_start();
include 'config.php';
if (!isset($_SESSION['auser'])) { header('Location: login.php'); exit(); }
require_role('HR');
require_permission('view_hr_dashboard');
// Employee stats
$total_employees = $conn->query("SELECT COUNT(*) AS c FROM employees WHERE status='active'")->fetch_assoc()['c'];
$total_leaves = $conn->query("SELECT COUNT(*) AS c FROM leaves WHERE status='approved'")->fetch_assoc()['c'];
$total_attendance = $conn->query("SELECT COUNT(*) AS c FROM attendance")->fetch_assoc()['c'];
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>HR Dashboard</title><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"></head>
<body><div class="container py-4"><h2>HR Dashboard</h2><div class="row mb-4"><div class="col"><div class="card p-3"><h4>Total Employees</h4><span style="font-size:2rem;"><?= $total_employees ?></span></div></div><div class="col"><div class="card p-3"><h4>Approved Leaves</h4><span style="font-size:2rem;"><?= $total_leaves ?></span></div></div><div class="col"><div class="card p-3"><h4>Attendance Records</h4><span style="font-size:2rem;"><?= $total_attendance ?></span></div></div></div><a href="employees.php" class="btn btn-info">Manage Employees</a> <a href="leaves.php" class="btn btn-info">Leaves</a> <a href="attendance.php" class="btn btn-info">Attendance</a></div></body></html>
