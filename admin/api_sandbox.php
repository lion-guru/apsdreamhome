<?php
session_start();
include 'config.php';
require_role('Admin');
$sandboxes = $conn->query("SELECT * FROM api_sandbox ORDER BY created_at DESC LIMIT 20");
?><!DOCTYPE html>
<html lang='en'>
<head><meta charset='UTF-8'><title>API Sandbox/Test Environment</title><link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css'></head>
<body><div class='container py-4'><h2>API Sandbox/Test Environment</h2><form method='post'><div class='mb-3'><label>Developer</label><input type='text' name='dev_name' class='form-control'></div><div class='mb-3'><label>API Endpoint</label><input type='text' name='endpoint' class='form-control'></div><div class='mb-3'><label>Request Payload</label><textarea name='payload' class='form-control'></textarea></div><button class='btn btn-success'>Test API</button></form><table class='table table-bordered mt-4'><thead><tr><th>Developer</th><th>Endpoint</th><th>Payload</th><th>Status</th><th>Created</th></tr></thead><tbody><?php while($s = $sandboxes->fetch_assoc()): ?><tr><td><?= htmlspecialchars($s['dev_name']) ?></td><td><?= htmlspecialchars($s['endpoint']) ?></td><td><?= htmlspecialchars($s['payload']) ?></td><td><?= htmlspecialchars($s['status']) ?></td><td><?= $s['created_at'] ?></td></tr><?php endwhile; ?></tbody></table><p class='mt-3'>*Allow developers to safely test API integrations in a sandboxed environment.</p></div></body></html>
