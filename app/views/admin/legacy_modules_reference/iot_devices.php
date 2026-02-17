<?php
require_once __DIR__ . '/core/init.php';
require_role('Admin');

$db = \App\Core\App::database();
// List IoT devices
$devices = $db->fetchAll("SELECT * FROM iot_devices ORDER BY created_at DESC LIMIT 30");
?><!DOCTYPE html>
<html lang='en'>
<head><meta charset='UTF-8'><title>IoT Device Management</title><link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css'></head>
<body><div class='container py-4'><h2>IoT Device Management</h2><p>Manage smart property devices and monitor events. Ready for integration with any IoT platform.</p><table class='table table-bordered'><thead><tr><th>Device</th><th>Type</th><th>Status</th><th>Last Seen</th></tr></thead><tbody><?php foreach($devices as $d): ?><tr><td><?= h($d['device_name']) ?></td><td><?= h($d['device_type']) ?></td><td><?= h($d['status']) ?></td><td><?= $d['last_seen'] ?></td></tr><?php endforeach; ?></tbody></table></div></body></html>
