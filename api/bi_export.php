<?php
// API endpoint to export data for BI tools (admin only)
header('Content-Type: text/csv');
session_start();
require_once '../config.php';
require_role('Admin');
// Example: Export bookings summary
header('Content-Disposition: attachment; filename="bi_export_bookings.csv"');
$out = fopen('php://output', 'w');
fputcsv($out, ['Date','Bookings']);
$res = $conn->query("SELECT DATE(created_at) as d, COUNT(*) as c FROM bookings GROUP BY d ORDER BY d DESC LIMIT 90");
while($row = $res->fetch_assoc()) {
    fputcsv($out, [$row['d'], $row['c']]);
}
fclose($out);
exit;
