<?php
// Automated Scheduled Report Generator (demo)
// Usage: schedule this script via cron/task scheduler for daily/weekly reports
$db = new mysqli('localhost', 'root', '', 'apsdreamhomes');
if ($db->connect_error) die('DB Error');
$res = $db->query("SELECT COUNT(*) as bookings, SUM(amount) as revenue FROM bookings WHERE DATE(created_at) = CURDATE()");
$row = $res->fetch_assoc();
$report = "Today's Bookings: {$row['bookings']}\nToday's Revenue: ₹{$row['revenue']}\n";
file_put_contents(__DIR__ . '/../reports/daily_report_' . date('Ymd') . '.txt', $report);
// Optionally, email the report using send_admin_alert from auto-email.php
// require_once 'auto-email.php';
// send_admin_alert('Daily Report', $report);
