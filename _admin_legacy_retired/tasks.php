<?php
session_start();
include 'config.php';
if (!isset($_SESSION['auser'])) { header('Location: login.php'); exit(); }
// List tasks
$tasks = $conn->query("SELECT t.*, e.name as assignee FROM tasks t LEFT JOIN employees e ON t.assigned_to = e.id ORDER BY t.due_date ASC, t.status DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Tasks</title><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"></head>
<body><div class="container py-4"><h2>Task Management</h2><a href="add_task.php" class="btn btn-success mb-2">Add Task</a><table class="table table-bordered"><thead><tr><th>Title</th><th>Assigned To</th><th>Due Date</th><th>Status</th></tr></thead><tbody><?php while($t = $tasks->fetch_assoc()): ?><tr><td><?= htmlspecialchars($t['title']) ?></td><td><?= htmlspecialchars($t['assignee']) ?></td><td><?= htmlspecialchars($t['due_date']) ?></td><td><?= ucfirst($t['status']) ?></td></tr><?php endwhile; ?></tbody></table></div></body></html>
