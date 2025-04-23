<?php
session_start();
include 'config.php';
if (!isset($_SESSION['auser'])) { header('Location: login.php'); exit(); }
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $stmt = $conn->prepare("INSERT INTO roles (name, description) VALUES (?, ?)");
    $stmt->bind_param('ss', $name, $description);
    if ($stmt->execute()) {
        header('Location: roles.php?msg=' . urlencode('Role added successfully.'));
        exit();
    } else {
        echo "Error: " . htmlspecialchars($stmt->error);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Add Role</title><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"></head>
<body><div class="container py-4"><h2>Add Role</h2><form method="POST"><div class="mb-3"><label>Role Name</label><input type="text" name="name" class="form-control" required></div><div class="mb-3"><label>Description</label><input type="text" name="description" class="form-control"></div><button type="submit" class="btn btn-success">Add Role</button></form></div></body></html>
