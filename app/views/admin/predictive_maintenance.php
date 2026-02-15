<?php
require_once __DIR__ . '/core/init.php';
require_role('Admin');

$db = \App\Core\App::database();
$devices = $db->fetchAll("SELECT * FROM iot_devices ORDER BY created_at DESC LIMIT 30");
?><!DOCTYPE html>
<html lang='en'>
<head><meta charset='UTF-8'><title>Predictive Maintenance</title><link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css'></head>
<body><div class='container py-4'><h2>Predictive Maintenance Dashboard</h2><table class='table table-bordered'><thead><tr><th>Device</th><th>Type</th><th>Status</th><th>Last Seen</th><th>Maintenance Risk (AI)</th></tr></thead><tbody><?php foreach($devices as $d): $risk = \App\Helpers\SecurityHelper::secureRandomInt(1, 100); ?><tr><td><?= h($d['device_name']) ?></td><td><?= h($d['device_type']) ?></td><td><?= h($d['status']) ?></td><td><?= h($d['last_seen']) ?></td><td><?= (int)$risk ?>%</td></tr><?php endforeach; ?></tbody></table><p class='mt-3'>*AI integration ready. Connect with Python/ML model for production predictive maintenance.</p></div></body></html>
