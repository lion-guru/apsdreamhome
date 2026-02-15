<?php
session_start();
include 'config.php';
require_role('Admin');
$apps = $conn->query("SELECT * FROM app_store ORDER BY created_at DESC LIMIT 30");
?><!DOCTYPE html>
<html lang='en'>
<head><meta charset='UTF-8'><title>Enterprise App Store</title><link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css'></head>
<body><div class='container py-4'><h2>Enterprise App Store</h2><form method='post'><div class='mb-3'><label>App Name</label><input type='text' name='app_name' class='form-control'></div><div class='mb-3'><label>Provider</label><input type='text' name='provider' class='form-control'></div><div class='mb-3'><label>App URL</label><input type='text' name='app_url' class='form-control'></div><div class='mb-3'><label>Price (INR)</label><input type='number' name='price' class='form-control' min='0'></div><button class='btn btn-success'>List App</button></form><table class='table table-bordered mt-4'><thead><tr><th>Name</th><th>Provider</th><th>Price</th><th>Reviews</th><th>Created</th></tr></thead><tbody><?php while($a = $apps->fetch_assoc()): ?><tr><td><?= htmlspecialchars($a['app_name']) ?></td><td><?= htmlspecialchars($a['provider']) ?></td><td>₹<?= htmlspecialchars($a['price']) ?></td><td><!-- Placeholder for reviews --></td><td><?= $a['created_at'] ?></td></tr><?php endwhile; ?></tbody></table><p class='mt-3'>*Full marketplace for third-party apps: payments, reviews, and app distribution. Ready for integration with payment gateways and review engines.</p></div></body></html>
