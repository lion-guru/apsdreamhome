<?php
session_start();
include '../config.php';
if (!isset($_SESSION['customer_id'])) { header('Location: login.php'); exit(); }
$customer_id = $_SESSION['customer_id'];
// Fetch bookings
$bookings = $conn->query("SELECT * FROM bookings WHERE customer_id=$customer_id ORDER BY created_at DESC");
// Fetch documents
$docs = $conn->query("SELECT * FROM customer_documents WHERE customer_id=$customer_id ORDER BY uploaded_at DESC");
// Fetch payments
$payments = $conn->query("SELECT * FROM payments WHERE customer_id=$customer_id ORDER BY paid_at DESC");
?><!DOCTYPE html>
<html lang='en'>
<head><meta charset='UTF-8'><title>Customer Portal</title><link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css'></head>
<body><div class='container py-4'><h2>Welcome to Your Customer Portal</h2><h4>Your Bookings</h4><table class='table table-bordered'><thead><tr><th>Property</th><th>Status</th><th>Date</th></tr></thead><tbody><?php while($b = $bookings->fetch_assoc()): ?><tr><td><?= htmlspecialchars($b['property_name']) ?></td><td><?= htmlspecialchars($b['status']) ?></td><td><?= $b['created_at'] ?></td></tr><?php endwhile; ?></tbody></table><h4>Your Documents</h4><table class='table table-bordered'><thead><tr><th>Document</th><th>Status</th><th>Uploaded</th></tr></thead><tbody><?php while($d = $docs->fetch_assoc()): ?><tr><td><?= htmlspecialchars($d['doc_name']) ?></td><td><?= htmlspecialchars($d['status']) ?></td><td><?= $d['uploaded_at'] ?></td></tr><?php endwhile; ?></tbody></table><h4>Your Payments</h4><table class='table table-bordered'><thead><tr><th>Amount</th><th>Status</th><th>Date</th></tr></thead><tbody><?php while($p = $payments->fetch_assoc()): ?><tr><td>₹<?= number_format($p['amount'],2) ?></td><td><?= htmlspecialchars($p['status']) ?></td><td><?= $p['paid_at'] ?></td></tr><?php endwhile; ?></tbody></table></div></body></html>
