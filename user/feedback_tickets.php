<?php
session_start();
include '../config.php';
if (!isset($_SESSION['auser'])) { header('Location: ../login.php'); exit(); }
$user_id = $_SESSION['auser'];
// Submit feedback/ticket
if (isset($_POST['message']) && trim($_POST['message']) != '') {
    $msg = $_POST['message'];
    $stmt = $conn->prepare("INSERT INTO feedback_tickets (user_id, message, status, created_at) VALUES (?, ?, 'open', NOW())");
    $stmt->bind_param("ss", $user_id, $msg);
    $stmt->execute();
    $stmt->close();
}
$stmt = $conn->prepare("SELECT * FROM feedback_tickets WHERE user_id=? ORDER BY created_at DESC");
$stmt->bind_param("s", $user_id);
$stmt->execute();
$tickets = $stmt->get_result();
?><!DOCTYPE html>
<html lang='en'>
<head><meta charset='UTF-8'><title>Feedback & Support Tickets</title><link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css'></head>
<body><div class='container py-4'><h2>Feedback & Support Tickets</h2><form method='post' class='mb-3'><textarea name='message' class='form-control' required placeholder='Enter feedback or support request'></textarea><button class='btn btn-primary mt-2'>Submit</button></form><ul class='list-group'><?php while($t = $tickets->fetch_assoc()): ?><li class='list-group-item'><strong><?= htmlspecialchars($t['status']) ?>:</strong> <?= htmlspecialchars($t['message']) ?> <em>(<?= $t['created_at'] ?>)</em></li><?php endwhile; ?></ul></div></body></html>
