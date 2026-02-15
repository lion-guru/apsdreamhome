<?php
session_start();
include 'config.php';
require_role('Admin');
$payments = $conn->query("SELECT * FROM global_payments ORDER BY created_at DESC LIMIT 30");
?><!DOCTYPE html>
<html lang='en'>
<head><meta charset='UTF-8'><title>Global Payments & In-App Purchases</title><link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css'></head>
<body><div class='container py-4'><h2>Global Payments & In-App Purchases</h2><form method='post'><div class='mb-3'><label>Client/User</label><input type='text' name='client' class='form-control'></div><div class='mb-3'><label>Amount</label><input type='number' name='amount' class='form-control'></div><div class='mb-3'><label>Currency</label><select name='currency' class='form-control'><option>INR</option><option>USD</option><option>EUR</option><option>GBP</option><option>JPY</option></select></div><div class='mb-3'><label>Purpose</label><input type='text' name='purpose' class='form-control'></div><button class='btn btn-success'>Record Payment</button></form><table class='table table-bordered mt-4'><thead><tr><th>Client</th><th>Amount</th><th>Currency</th><th>Purpose</th><th>Status</th><th>Created</th></tr></thead><tbody><?php while($p = $payments->fetch_assoc()): ?><tr><td><?= htmlspecialchars($p['client']) ?></td><td><?= htmlspecialchars($p['amount']) ?></td><td><?= htmlspecialchars($p['currency']) ?></td><td><?= htmlspecialchars($p['purpose']) ?></td><td><?= htmlspecialchars($p['status']) ?></td><td><?= $p['created_at'] ?></td></tr><?php endwhile; ?></tbody></table><p class='mt-3'>*Ready for integration with Stripe, PayPal, Razorpay, or any global/local payment gateway. Supports in-app purchases and multi-currency payments.</p></div></body></html>
