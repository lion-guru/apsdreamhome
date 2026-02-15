<?php
session_start();
require_once __DIR__ . '/../app/bootstrap.php';
$db = \App\Core\App::database();
if (!isset($_SESSION['auser'])) { header('Location: ../login.php'); exit(); }
$user_id = $_SESSION['auser'];
$logs = $db->fetch(
    "SELECT action, details, created_at FROM audit_log WHERE user_id=:user_id ORDER BY created_at DESC LIMIT 50",
    ['user_id' => $user_id]
);
?><!DOCTYPE html>
<html lang='en'>
<head><meta charset='UTF-8'><title>My Activity Timeline</title><link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css'></head>
<body><div class='container py-4'><h2>My Activity Timeline</h2><ul class='list-group'><?php foreach($logs as $l): ?><li class='list-group-item'><strong><?= htmlspecialchars($l['action']) ?>:</strong> <?= htmlspecialchars($l['details']) ?> <em>(<?= $l['created_at'] ?>)</em></li><?php endforeach; ?></ul></div></body></html>
