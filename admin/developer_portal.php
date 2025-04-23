<?php
session_start();
include 'config.php';
require_role('Admin');
$devs = $conn->query("SELECT * FROM api_developers ORDER BY created_at DESC LIMIT 20");
?><!DOCTYPE html>
<html lang='en'>
<head><meta charset='UTF-8'><title>Developer Portal</title><link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css'></head>
<body><div class='container py-4'><h2>Developer Portal</h2><form method='post'><div class='mb-3'><label>Developer Name</label><input type='text' name='dev_name' class='form-control'></div><div class='mb-3'><label>Email</label><input type='email' name='email' class='form-control'></div><button class='btn btn-success'>Register Developer</button></form><table class='table table-bordered mt-4'><thead><tr><th>Name</th><th>Email</th><th>API Key</th><th>Status</th><th>Created</th></tr></thead><tbody><?php while($d = $devs->fetch_assoc()): ?><tr><td><?= htmlspecialchars($d['dev_name']) ?></td><td><?= htmlspecialchars($d['email']) ?></td><td><?= htmlspecialchars($d['api_key']) ?></td><td><?= htmlspecialchars($d['status']) ?></td><td><?= $d['created_at'] ?></td></tr><?php endwhile; ?></tbody></table><p class='mt-3'>*Allow partners to register, get API keys, and build on your platform. Manage API access and documentation for third-party developers.</p></div></body></html>
