<?php
session_start();
include 'config.php';
if (!isset($_SESSION['auser'])) { header('Location: login.php'); exit(); }
// List audit log
$audit = $conn->query("SELECT * FROM audit_log ORDER BY created_at DESC LIMIT 100");
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Audit Log</title><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"></head>
<body><div class="container py-4"><h2>Audit Log</h2><table class="table table-bordered"><thead><tr><th>User ID</th><th>Action</th><th>Details</th><th>IP Address</th><th>Date</th></tr></thead><tbody><?php while($a = $audit->fetch_assoc()): ?><tr><td><?= $a['user_id'] ?></td><td><?= htmlspecialchars($a['action']) ?></td><td><?= htmlspecialchars($a['details']) ?></td><td><?= htmlspecialchars($a['ip_address']) ?></td><td><?= $a['created_at'] ?></td></tr><?php endwhile; ?></tbody></table></div></body></html>
