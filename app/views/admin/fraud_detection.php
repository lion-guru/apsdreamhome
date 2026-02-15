<?php
require_once __DIR__ . '/core/init.php';
require_role('Admin');

$db = \App\Core\App::database();
$alerts = $db->fetchAll("SELECT * FROM fraud_alerts ORDER BY detected_at DESC LIMIT 30");
?><!DOCTYPE html>
<html lang='en'>
<head><meta charset='UTF-8'><title>AI-Powered Fraud Detection</title><link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css'></head>
<body><div class='container py-4'><h2>AI-Powered Fraud Detection</h2><table class='table table-bordered'><thead><tr><th>Alert Type</th><th>Description</th><th>Severity</th><th>Status</th><th>Detected At</th></tr></thead><tbody><?php foreach($alerts as $a): ?><tr><td><?= h($a['alert_type']) ?></td><td><?= h($a['description']) ?></td><td><?= h($a['severity']) ?></td><td><?= h($a['status']) ?></td><td><?= $a['detected_at'] ?></td></tr><?php endforeach; ?></tbody></table><div class='alert alert-warning mt-3'>AI/ML integration ready. Connect to Python or cloud-based ML models for real-time fraud detection and prevention.</div></div></body></html>
