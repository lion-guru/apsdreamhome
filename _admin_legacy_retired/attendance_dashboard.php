<?php
session_start();
include 'config.php';
if (!isset($_SESSION['auser'])) { header('Location: login.php'); exit(); }
require_role('HR');
$total_attendance = $conn->query("SELECT COUNT(*) AS c FROM attendance")->fetch_assoc()['c'];
$present_days = $conn->query("SELECT COUNT(*) AS c FROM attendance WHERE status='present'")->fetch_assoc()['c'];
$absent_days = $conn->query("SELECT COUNT(*) AS c FROM attendance WHERE status='absent'")->fetch_assoc()['c'];
$leave_days = $conn->query("SELECT COUNT(*) AS c FROM attendance WHERE status='leave'")->fetch_assoc()['c'];
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Attendance Dashboard</title><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"></head>
<body><div class="container py-4"><h2>Attendance Dashboard</h2><div class="row mb-4"><div class="col"><div class="card p-3"><h4>Total Attendance Records</h4><span style="font-size:2rem;"><?= $total_attendance ?></span></div></div><div class="col"><div class="card p-3"><h4>Present Days</h4><span style="font-size:2rem;"><?= $present_days ?></span></div></div><div class="col"><div class="card p-3"><h4>Absent Days</h4><span style="font-size:2rem;"><?= $absent_days ?></span></div></div><div class="col"><div class="card p-3"><h4>Leave Days</h4><span style="font-size:2rem;"><?= $leave_days ?></span></div></div></div><a href="attendance.php" class="btn btn-info">View Attendance</a> <a href="mark_attendance.php" class="btn btn-info">Mark Attendance</a></div></body></html>
