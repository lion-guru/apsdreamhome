<?php
session_start();
include 'config.php';
require_role('Admin');
$docs = $conn->query("SELECT * FROM legal_documents ORDER BY uploaded_at DESC LIMIT 20");
?><!DOCTYPE html>
<html lang='en'>
<head><meta charset='UTF-8'><title>AI Legal/Document Review</title><link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css'></head>
<body><div class='container py-4'><h2>AI Legal/Document Review</h2><form method='post' enctype='multipart/form-data'><div class='mb-3'><label>Upload Legal Document</label><input type='file' name='legal_doc' class='form-control'></div><button class='btn btn-success'>Upload</button></form><table class='table table-bordered mt-4'><thead><tr><th>Document</th><th>Status</th><th>AI Summary</th><th>Flagged Issues</th><th>Uploaded</th></tr></thead><tbody><?php while($d = $docs->fetch_assoc()): ?><tr><td><?= htmlspecialchars($d['file_name']) ?></td><td><?= htmlspecialchars($d['review_status']) ?></td><td><?= htmlspecialchars($d['ai_summary']) ?></td><td><?= htmlspecialchars($d['ai_flags']) ?></td><td><?= $d['uploaded_at'] ?></td></tr><?php endwhile; ?></tbody></table><p class='mt-3'>*AI integration ready. Connect with Python/ML model, Google AI, or AWS for production review and compliance.</p></div></body></html>
