<?php
// Automated Health Check & Self-Healing
header('Content-Type: application/json');
$checks = [];
$checks['php_version'] = phpversion();
$checks['mysql'] = function_exists('mysqli_connect');
$checks['disk_free'] = disk_free_space("/") > 1024*1024*100;
$checks['error_log'] = file_exists(__DIR__.'/../error_log') ? filesize(__DIR__.'/../error_log') : 0;
$checks['last_backup'] = file_exists(__DIR__.'/../database/backup.sql') ? date('Y-m-d H:i:s', filemtime(__DIR__.'/../database/backup.sql')) : 'Never';
$checks['status'] = ($checks['php_version'] && $checks['mysql'] && $checks['disk_free']);
// Simple self-heal: clear error log if > 10MB
if($checks['error_log'] > 10485760) @unlink(__DIR__.'/../error_log');
echo json_encode($checks);
