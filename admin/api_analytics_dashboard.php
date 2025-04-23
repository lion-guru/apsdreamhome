<?php
session_start();
include 'config.php';
require_role('Admin');
$usage = $conn->query("SELECT * FROM api_usage ORDER BY timestamp DESC LIMIT 30");
?><!DOCTYPE html>
<html lang='en'>
<head><meta charset='UTF-8'><title>API Analytics & Rate Limiting</title><link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css'></head>
<body><div class='container py-4'><h2>API Analytics & Rate Limiting</h2><table class='table table-bordered'><thead><tr><th>Developer</th><th>API Key</th><th>Endpoint</th><th>Usage Count</th><th>Timestamp</th></tr></thead><tbody><?php while($u = $usage->fetch_assoc()): ?><tr><td><?= htmlspecialchars($u['dev_name']) ?></td><td><?= htmlspecialchars($u['api_key']) ?></td><td><?= htmlspecialchars($u['endpoint']) ?></td><td><?= htmlspecialchars($u['usage_count']) ?></td><td><?= $u['timestamp'] ?></td></tr><?php endwhile; ?></tbody></table><p class='mt-3'>*Monitor API usage, set quotas, and provide analytics to developers. Rate limiting integration ready.</p></div></body></html>
