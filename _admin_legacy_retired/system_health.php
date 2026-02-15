<?php
session_start();
include 'config.php';
require_role('Admin');
$health = [];
// Check DB connection
$health['database'] = $conn->ping() ? 'OK' : 'FAIL';
// Check PHP version
$health['php_version'] = phpversion();
// Check disk space
$health['disk_free'] = round(disk_free_space("/") / 1024 / 1024, 2) . ' MB';
$health['disk_total'] = round(disk_total_space("/" ) / 1024 / 1024, 2) . ' MB';
// Check recent failed logins
$failed_logins = $conn->query("SELECT COUNT(*) as c FROM audit_log WHERE action='Login Failed' AND created_at >= NOW() - INTERVAL 1 DAY")->fetch_assoc()['c'];
$health['failed_logins_24h'] = $failed_logins;
?>
<!DOCTYPE html>
<html lang='en'>
<head><meta charset='UTF-8'><title>System Health Monitor</title><link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css'></head>
<body><div class='container py-4'><h2>System Health Monitor</h2><table class='table table-bordered'><tr><th>Component</th><th>Status</th></tr><tr><td>Database Connection</td><td><?= $health['database'] ?></td></tr><tr><td>PHP Version</td><td><?= $health['php_version'] ?></td></tr><tr><td>Disk Free</td><td><?= $health['disk_free'] ?> / <?= $health['disk_total'] ?></td></tr><tr><td>Failed Logins (24h)</td><td><?= $health['failed_logins_24h'] ?></td></tr></table></div></body></html>
