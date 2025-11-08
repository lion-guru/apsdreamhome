<?php
session_start();
include 'config.php';
require_role('Admin');
// List IoT devices
$devices = $conn->query("SELECT * FROM iot_devices ORDER BY created_at DESC LIMIT 30");
?><!DOCTYPE html>
<html lang='en'>
<head><meta charset='UTF-8'><title>IoT Device Management</title><link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css'></head>
<body><div class='container py-4'><h2>IoT Device Management</h2><p>Manage smart property devices and monitor events. Ready for integration with any IoT platform.</p><table class='table table-bordered'><thead><tr><th>Device</th><th>Type</th><th>Status</th><th>Last Seen</th></tr></thead><tbody><?php while($d = $devices->fetch_assoc()): ?><tr><td><?= htmlspecialchars($d['device_name']) ?></td><td><?= htmlspecialchars($d['device_type']) ?></td><td><?= htmlspecialchars($d['status']) ?></td><td><?= $d['last_seen'] ?></td></tr><?php endwhile; ?></tbody></table></div></body></html>
