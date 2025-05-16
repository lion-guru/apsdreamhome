<?php
session_start();
include '../config.php';
if (!isset($_SESSION['auser'])) { header('Location: ../login.php'); exit(); }
$user_id = $_SESSION['auser'];
$user = $conn->query("SELECT name, email, status FROM employees WHERE id=$user_id")->fetch_assoc();
$roles = [];
$res = $conn->query("SELECT r.name FROM user_roles ur JOIN roles r ON ur.role_id=r.id WHERE ur.user_id=$user_id");
while($row = $res->fetch_assoc()) $roles[] = $row['name'];
$permissions = [];
$res = $conn->query("SELECT p.action FROM user_roles ur JOIN role_permissions rp ON ur.role_id=rp.role_id JOIN permissions p ON rp.permission_id=p.id WHERE ur.user_id=$user_id");
while($row = $res->fetch_assoc()) $permissions[] = $row['action'];
$notifications = $conn->query("SELECT type, message, created_at FROM notifications WHERE user_id=$user_id ORDER BY created_at DESC LIMIT 20");
?>
<!DOCTYPE html>
<html lang='en'>
<head><meta charset='UTF-8'><title>User Self-Service Portal</title><link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css'></head>
<body><div class='container py-4'><h2>Welcome, <?= htmlspecialchars($user['name']) ?></h2><div class='mb-3'><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?><br><strong>Status:</strong> <?= htmlspecialchars($user['status']) ?></div><div class='mb-3'><strong>Your Roles:</strong> <?= implode(', ', $roles) ?></div><div class='mb-3'><strong>Your Permissions:</strong> <?= implode(', ', $permissions) ?></div><div class='mb-3'><h5>Recent Notifications</h5><ul><?php while($n = $notifications->fetch_assoc()): ?><li><strong><?= htmlspecialchars($n['type']) ?>:</strong> <?= htmlspecialchars($n['message']) ?> <em>(<?= $n['created_at'] ?>)</em></li><?php endwhile; ?></ul></div></div></body></html>
