<?php
// Automated Scheduled Report Generator (demo)
// Usage: schedule this script via cron/task scheduler for daily/weekly reports
require_once __DIR__ . '/core/init.php';
$db = \App\Core\App::database();
if (!$db) die('DB Error');
$row = $db->fetch("SELECT COUNT(*) as bookings, SUM(amount) as revenue FROM bookings WHERE DATE(created_at) = CURDATE()");
$report = "Today's Bookings: " . ($row['bookings'] ?? 0) . "\nToday's Revenue: ₹" . ($row['revenue'] ?? 0) . "\n";
file_put_contents(__DIR__ . '/../reports/daily_report_' . date('Ymd') . '.txt', $report);
// Optionally, email the report using send_admin_alert from auto-email.php
// require_once 'auto-email.php';
// send_admin_alert('Daily Report', $report);
