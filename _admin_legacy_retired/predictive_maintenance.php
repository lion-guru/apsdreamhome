<?php
session_start();
include 'config.php';
require_role('Admin');
$devices = $conn->query("SELECT * FROM iot_devices ORDER BY created_at DESC LIMIT 30");
?><!DOCTYPE html>
<html lang='en'>
<head><meta charset='UTF-8'><title>Predictive Maintenance</title><link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css'></head>
<body><div class='container py-4'><h2>Predictive Maintenance Dashboard</h2><table class='table table-bordered'><thead><tr><th>Device</th><th>Type</th><th>Status</th><th>Last Seen</th><th>Maintenance Risk (AI)</th></tr></thead><tbody><?php while($d = $devices->fetch_assoc()): $risk = rand(1,100); ?><tr><td><?= htmlspecialchars($d['device_name']) ?></td><td><?= htmlspecialchars($d['device_type']) ?></td><td><?= htmlspecialchars($d['status']) ?></td><td><?= $d['last_seen'] ?></td><td><?= $risk ?>%</td></tr><?php endwhile; ?></tbody></table><p class='mt-3'>*AI integration ready. Connect with Python/ML model for production predictive maintenance.</p></div></body></html>
