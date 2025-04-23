<?php
session_start();
include 'config.php';
if (!isset($_SESSION['auser'])) { header('Location: login.php'); exit(); }
require_role('HR');
$total_leaves = $conn->query("SELECT COUNT(*) AS c FROM leaves")->fetch_assoc()['c'];
$approved_leaves = $conn->query("SELECT COUNT(*) AS c FROM leaves WHERE status='approved'")->fetch_assoc()['c'];
$pending_leaves = $conn->query("SELECT COUNT(*) AS c FROM leaves WHERE status='pending'")->fetch_assoc()['c'];
$rejected_leaves = $conn->query("SELECT COUNT(*) AS c FROM leaves WHERE status='rejected'")->fetch_assoc()['c'];
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Leaves Dashboard</title><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"></head>
<body><div class="container py-4"><h2>Leaves Dashboard</h2><div class="row mb-4"><div class="col"><div class="card p-3"><h4>Total Leaves</h4><span style="font-size:2rem;"><?= $total_leaves ?></span></div></div><div class="col"><div class="card p-3"><h4>Approved</h4><span style="font-size:2rem;"><?= $approved_leaves ?></span></div></div><div class="col"><div class="card p-3"><h4>Pending</h4><span style="font-size:2rem;"><?= $pending_leaves ?></span></div></div><div class="col"><div class="card p-3"><h4>Rejected</h4><span style="font-size:2rem;"><?= $rejected_leaves ?></span></div></div></div><a href="leaves.php" class="btn btn-info">View Leaves</a> <a href="apply_leave.php" class="btn btn-info">Apply Leave</a></div></body></html>
