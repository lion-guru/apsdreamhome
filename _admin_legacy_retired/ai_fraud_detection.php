<?php
session_start();
include 'config.php';
require_role('Admin');
// Placeholder: Simulate fraud detection by flagging high-value or rapid transactions
$frauds = $conn->query("SELECT * FROM payments WHERE amount > 100000 OR (created_at >= NOW() - INTERVAL 1 HOUR AND status='success') ORDER BY created_at DESC LIMIT 20");
?><!DOCTYPE html>
<html lang='en'>
<head><meta charset='UTF-8'><title>Fraud Detection (AI)</title><link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css'></head>
<body><div class='container py-4'><h2>AI Fraud Detection</h2><table class='table table-bordered'><thead><tr><th>Payment ID</th><th>Customer</th><th>Amount</th><th>Status</th><th>Date</th></tr></thead><tbody><?php while($f = $frauds->fetch_assoc()): ?><tr><td><?= $f['id'] ?></td><td><?= $f['customer_id'] ?></td><td>₹<?= number_format($f['amount'],2) ?></td><td><?= htmlspecialchars($f['status']) ?></td><td><?= $f['created_at'] ?></td></tr><?php endwhile; ?></tbody></table></div></body></html>
