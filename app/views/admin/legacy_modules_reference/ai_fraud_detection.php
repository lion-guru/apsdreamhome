<?php
require_once __DIR__ . '/core/init.php';
require_role('Admin');

$db = \App\Core\App::database();
// Placeholder: Simulate fraud detection by flagging high-value or rapid transactions
$frauds = $db->fetchAll("SELECT * FROM payments WHERE amount > 100000 OR (created_at >= NOW() - INTERVAL 1 HOUR AND status='success') ORDER BY created_at DESC LIMIT 20");
?><!DOCTYPE html>
<html lang='en'>
<head><meta charset='UTF-8'><title>Fraud Detection (AI)</title><link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css'></head>
<body><div class='container py-4'><h2>AI Fraud Detection</h2><table class='table table-bordered'><thead><tr><th>Payment ID</th><th>Customer</th><th>Amount</th><th>Status</th><th>Date</th></tr></thead><tbody><?php foreach($frauds as $f): ?><tr><td><?= $f['id'] ?></td><td><?= $f['customer_id'] ?></td><td>₹<?= number_format($f['amount'],2) ?></td><td><?= h($f['status']) ?></td><td><?= $f['created_at'] ?></td></tr><?php endforeach; ?></tbody></table></div></body></html>
