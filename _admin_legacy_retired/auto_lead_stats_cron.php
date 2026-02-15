<?php
// auto_lead_stats_cron.php: Update daily statistics for leads
require_once __DIR__ . '/../includes/config/config.php';
global $con;
$conn = $con;

// Insert daily stats snapshot
$conn->query("INSERT INTO lead_stats_daily (stat_date, total, new, qualified, contacted, converted) SELECT CURDATE(), COUNT(*), SUM(status = 'New'), SUM(status = 'Qualified'), SUM(status = 'Contacted'), SUM(status = 'Converted') FROM leads");
?>
