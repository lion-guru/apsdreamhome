<?php
session_start();
include 'config.php';
require_role('Admin');
// Placeholder: Simulate AI sales prediction using historical booking data
$sales = $conn->query("SELECT DATE(created_at) as d, COUNT(*) as c FROM bookings GROUP BY d ORDER BY d DESC LIMIT 30");
$trend = [];
while($row = $sales->fetch_assoc()) $trend[] = $row;
$predicted = round(array_sum(array_column($trend, 'c')) / max(count($trend),1), 2);
?><!DOCTYPE html>
<html lang='en'>
<head><meta charset='UTF-8'><title>Sales Prediction (AI)</title><link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css'></head>
<body><div class='container py-4'><h2>AI Sales Prediction</h2><div class='alert alert-info'>Predicted average daily bookings (next month): <strong><?= $predicted ?></strong></div><h4>Recent Booking Trend</h4><table class='table table-bordered'><thead><tr><th>Date</th><th>Bookings</th></tr></thead><tbody><?php foreach($trend as $t): ?><tr><td><?= $t['d'] ?></td><td><?= $t['c'] ?></td></tr><?php endforeach; ?></tbody></table></div></body></html>
