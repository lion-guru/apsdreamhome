<?php
session_start();
include 'config.php';
require_role('Admin');
// Placeholder: Simulate OCR by listing uploaded documents for tagging
$docs = $conn->query("SELECT * FROM customer_documents ORDER BY uploaded_at DESC LIMIT 20");
?><!DOCTYPE html>
<html lang='en'>
<head><meta charset='UTF-8'><title>Document OCR/Tagging (AI)</title><link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css'></head>
<body><div class='container py-4'><h2>AI Document OCR/Tagging</h2><table class='table table-bordered'><thead><tr><th>Document</th><th>Customer</th><th>Status</th><th>Uploaded</th></tr></thead><tbody><?php while($d = $docs->fetch_assoc()): ?><tr><td><?= htmlspecialchars($d['doc_name']) ?></td><td><?= $d['customer_id'] ?></td><td><?= htmlspecialchars($d['status']) ?></td><td><?= $d['uploaded_at'] ?></td></tr><?php endwhile; ?></tbody></table><p class='mt-3'>*AI OCR/auto-tagging integration ready. Connect with Tesseract, Google Vision, or AWS Textract for production.</p></div></body></html>
