<?php
session_start();
include 'config.php';
if (!isset($_SESSION['auser'])) { header('Location: login.php'); exit(); }
$roles = $conn->query("SELECT * FROM roles ORDER BY id");
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Roles Management</title><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"></head>
<body><div class="container py-4"><h2>Roles Management</h2><a href="add_role.php" class="btn btn-success mb-2">Add Role</a><table class="table table-bordered"><thead><tr><th>ID</th><th>Role Name</th><th>Description</th></tr></thead><tbody><?php while($r = $roles->fetch_assoc()): ?><tr><td><?= $r['id'] ?></td><td><?= htmlspecialchars($r['name']) ?></td><td><?= htmlspecialchars($r['description']) ?></td></tr><?php endwhile; ?></tbody></table></div></body></html>
