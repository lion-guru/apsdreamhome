<?php
session_start();
include 'config.php';
if (!isset($_SESSION['auser'])) { header('Location: login.php'); exit(); }
// List user sessions
$sessions = $conn->query("SELECT * FROM user_sessions ORDER BY login_time DESC LIMIT 100");
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>User Sessions</title><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"></head>
<body><div class="container py-4"><h2>User Sessions</h2><table class="table table-bordered"><thead><tr><th>User ID</th><th>Login Time</th><th>Logout Time</th><th>IP Address</th><th>Status</th></tr></thead><tbody><?php while($s = $sessions->fetch_assoc()): ?><tr><td><?= $s['user_id'] ?></td><td><?= $s['login_time'] ?></td><td><?= $s['logout_time'] ?></td><td><?= htmlspecialchars($s['ip_address']) ?></td><td><?= ucfirst($s['status']) ?></td></tr><?php endwhile; ?></tbody></table></div></body></html>
