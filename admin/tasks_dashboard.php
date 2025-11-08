<?php
session_start();
include 'config.php';
if (!isset($_SESSION['auser'])) { header('Location: login.php'); exit(); }
require_role('Manager');
require_permission('view_tasks_dashboard');
$total_tasks = $conn->query("SELECT COUNT(*) AS c FROM tasks")->fetch_assoc()['c'];
$pending_tasks = $conn->query("SELECT COUNT(*) AS c FROM tasks WHERE status='pending'")->fetch_assoc()['c'];
$completed_tasks = $conn->query("SELECT COUNT(*) AS c FROM tasks WHERE status='completed'")->fetch_assoc()['c'];
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Tasks Dashboard</title><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"></head>
<body><div class="container py-4"><h2>Tasks Dashboard</h2><div class="row mb-4"><div class="col"><div class="card p-3"><h4>Total Tasks</h4><span style="font-size:2rem;"><?= $total_tasks ?></span></div></div><div class="col"><div class="card p-3"><h4>Pending Tasks</h4><span style="font-size:2rem;"><?= $pending_tasks ?></span></div></div><div class="col"><div class="card p-3"><h4>Completed Tasks</h4><span style="font-size:2rem;"><?= $completed_tasks ?></span></div></div></div><a href="tasks.php" class="btn btn-info">View Tasks</a> <a href="add_task.php" class="btn btn-info">Add Task</a></div></body></html>
