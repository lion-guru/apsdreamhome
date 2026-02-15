<?php
session_start();
include 'config.php';
if (!isset($_SESSION['auser'])) { header('Location: login.php'); exit(); }
// List support tickets
$tickets = $conn->query("SELECT * FROM support_tickets ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Support Tickets</title><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"></head>
<body><div class="container py-4"><h2>Support Tickets</h2><table class="table table-bordered"><thead><tr><th>ID</th><th>User ID</th><th>Subject</th><th>Status</th><th>Created At</th></tr></thead><tbody><?php while($t = $tickets->fetch_assoc()): ?><tr><td><?= $t['id'] ?></td><td><?= $t['user_id'] ?></td><td><?= htmlspecialchars($t['subject']) ?></td><td><?= ucfirst($t['status']) ?></td><td><?= $t['created_at'] ?></td></tr><?php endwhile; ?></tbody></table></div></body></html>
