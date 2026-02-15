<?php
session_start();
include 'config.php';
require_role('Admin');
$partners = $conn->query("SELECT * FROM marketplace_apps ORDER BY created_at DESC LIMIT 30");
?><!DOCTYPE html>
<html lang='en'>
<head><meta charset='UTF-8'><title>AI-Powered Partner Matching</title><link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css'></head>
<body><div class='container py-4'><h2>AI-Powered Partner Matching</h2><form method='post'><div class='mb-3'><label>Client Needs/Keywords</label><input type='text' name='keywords' class='form-control' placeholder='e.g., CRM, analytics, payments'></div><button class='btn btn-success'>Find Best Matches</button></form><table class='table table-bordered mt-4'><thead><tr><th>App Name</th><th>Provider</th><th>URL</th><th>Created</th></tr></thead><tbody><?php while($p = $partners->fetch_assoc()): ?><tr><td><?= htmlspecialchars($p['app_name']) ?></td><td><?= htmlspecialchars($p['provider']) ?></td><td><?= htmlspecialchars($p['app_url']) ?></td><td><?= $p['created_at'] ?></td></tr><?php endwhile; ?></tbody></table><p class='mt-3'>*AI integration ready. Connect to NLP/ML model for intelligent partner/app matching based on client needs.</p></div></body></html>
