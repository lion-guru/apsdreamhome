<?php
require_once __DIR__ . '/core/init.php';
require_role(['Admin','Customer','Partner']);

$db = \App\Core\App::database();
$feedbacks = $db->fetchAll("SELECT * FROM feedback ORDER BY created_at DESC LIMIT 50");
?><!DOCTYPE html>
<html lang='en'>
<head><meta charset='UTF-8'><title>Feedback & Sentiment Analysis</title><link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css'></head>
<body><div class='container py-4'><h2>Automated Feedback & Sentiment Analysis</h2><form method='post'><div class='mb-3'><label>Your Feedback</label><textarea name='feedback' class='form-control'></textarea></div><button class='btn btn-success'>Submit Feedback</button></form><table class='table table-bordered mt-4'><thead><tr><th>User</th><th>Feedback</th><th>Sentiment*</th><th>Date</th></tr></thead><tbody><?php foreach($feedbacks as $f): ?><tr><td><?= h($f['user_email']) ?></td><td><?= h($f['feedback']) ?></td><td><?= h($f['sentiment']) ?></td><td><?= $f['created_at'] ?></td></tr><?php endforeach; ?></tbody></table><p class='mt-3'>*Sentiment auto-analysis ready. Integrate with AI/ML API for real-time feedback insights.</p></div></body></html>
