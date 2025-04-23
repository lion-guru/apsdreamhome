<?php
session_start();
include 'config.php';
if (!isset($_SESSION['auser'])) { header('Location: login.php'); exit(); }
// Fetch employees for assignment
$employees = $conn->query("SELECT id, name FROM employees WHERE status='active' ORDER BY name");
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $assigned_to = $_POST['assigned_to'];
    $due_date = $_POST['due_date'];
    $assigned_by = $_SESSION['auser'];
    $stmt = $conn->prepare("INSERT INTO tasks (title, description, assigned_to, assigned_by, due_date) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param('ssiis', $title, $description, $assigned_to, $assigned_by, $due_date);
    if ($stmt->execute()) {
        require_once __DIR__ . '/../includes/functions/notification_util.php';
        addNotification($conn, 'Task', 'New task assigned: ' . $title, $assigned_to);
        header('Location: tasks.php?msg=' . urlencode('Task added successfully.'));
        exit();
    } else {
        echo "Error: " . htmlspecialchars($stmt->error);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Add Task</title><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"></head>
<body><div class="container py-4"><h2>Add Task</h2><form method="POST"><div class="mb-3"><label>Title</label><input type="text" name="title" class="form-control" required></div><div class="mb-3"><label>Description</label><textarea name="description" class="form-control" required></textarea></div><div class="mb-3"><label>Assign To</label><select name="assigned_to" class="form-control" required><?php while($e = $employees->fetch_assoc()): ?><option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['name']) ?></option><?php endwhile; ?></select></div><div class="mb-3"><label>Due Date</label><input type="date" name="due_date" class="form-control" required></div><button type="submit" class="btn btn-success">Add Task</button></form></div></body></html>
