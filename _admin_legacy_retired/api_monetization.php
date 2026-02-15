<?php
session_start();
include 'config.php';
require_role('Admin');
$monetizations = $conn->query("SELECT * FROM api_monetization ORDER BY created_at DESC LIMIT 20");
?><!DOCTYPE html>
<html lang='en'>
<head><meta charset='UTF-8'><title>API Monetization Engine</title><link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css'></head>
<body><div class='container py-4'><h2>API Monetization Engine</h2><form method='post'><div class='mb-3'><label>Developer</label><input type='text' name='dev_name' class='form-control'></div><div class='mb-3'><label>API Endpoint</label><input type='text' name='endpoint' class='form-control'></div><div class='mb-3'><label>Price per Call (INR)</label><input type='number' name='price' class='form-control' min='0'></div><button class='btn btn-success'>Set Pricing</button></form><table class='table table-bordered mt-4'><thead><tr><th>Developer</th><th>Endpoint</th><th>Price/Call</th><th>Status</th><th>Created</th></tr></thead><tbody><?php while($m = $monetizations->fetch_assoc()): ?><tr><td><?= htmlspecialchars($m['dev_name']) ?></td><td><?= htmlspecialchars($m['endpoint']) ?></td><td>₹<?= htmlspecialchars($m['price']) ?></td><td><?= htmlspecialchars($m['status']) ?></td><td><?= $m['created_at'] ?></td></tr><?php endwhile; ?></tbody></table><p class='mt-3'>*Let developers set pricing for API usage and handle automated billing for API calls.</p></div></body></html>
