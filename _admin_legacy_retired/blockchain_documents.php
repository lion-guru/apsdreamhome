<?php
session_start();
include 'config.php';
require_role('Admin');
// List uploaded documents and their blockchain status
$docs = $conn->query("SELECT * FROM customer_documents ORDER BY uploaded_at DESC LIMIT 20");
?><!DOCTYPE html>
<html lang='en'>
<head><meta charset='UTF-8'><title>Blockchain Document Management</title><link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css'></head>
<body><div class='container py-4'><h2>Blockchain Document Management</h2><p>This module is ready for integration with Ethereum, Polygon, or any blockchain provider. Store document hashes for tamper-proof verification.</p><table class='table table-bordered'><thead><tr><th>Document</th><th>Customer</th><th>Status</th><th>Uploaded</th><th>Blockchain Hash</th></tr></thead><tbody><?php while($d = $docs->fetch_assoc()): ?><tr><td><?= htmlspecialchars($d['doc_name']) ?></td><td><?= $d['customer_id'] ?></td><td><?= htmlspecialchars($d['status']) ?></td><td><?= $d['uploaded_at'] ?></td><td><?= htmlspecialchars($d['blockchain_hash'] ?? '') ?></td></tr><?php endwhile; ?></tbody></table></div></body></html>
