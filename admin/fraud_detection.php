<?php
session_start();
include 'config.php';
require_role('Admin');
$alerts = $conn->query("SELECT * FROM fraud_alerts ORDER BY detected_at DESC LIMIT 30");
?><!DOCTYPE html>
<html lang='en'>
<head><meta charset='UTF-8'><title>AI-Powered Fraud Detection</title><link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css'></head>
<body><div class='container py-4'><h2>AI-Powered Fraud Detection</h2><table class='table table-bordered'><thead><tr><th>Alert Type</th><th>Description</th><th>Severity</th><th>Status</th><th>Detected At</th></tr></thead><tbody><?php while($a = $alerts->fetch_assoc()): ?><tr><td><?= htmlspecialchars($a['alert_type']) ?></td><td><?= htmlspecialchars($a['description']) ?></td><td><?= htmlspecialchars($a['severity']) ?></td><td><?= htmlspecialchars($a['status']) ?></td><td><?= $a['detected_at'] ?></td></tr><?php endwhile; ?></tbody></table><div class='alert alert-warning mt-3'>AI/ML integration ready. Connect to Python or cloud-based ML models for real-time fraud detection and prevention.</div></div></body></html>
