<?php
session_start();
include 'config.php';
if (!isset($_SESSION['auser'])) { header('Location: login.php'); exit(); }
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['auser'];
    $subject = $_POST['subject'];
    $message = $_POST['message'];
    $stmt = $conn->prepare("INSERT INTO support_tickets (user_id, subject, message) VALUES (?, ?, ?)");
    $stmt->bind_param('iss', $user_id, $subject, $message);
    if ($stmt->execute()) {
        require_once __DIR__ . '/../includes/functions/notification_util.php';
        addNotification($conn, 'Support', 'New support ticket created: ' . $subject, $user_id);
        header('Location: support_tickets.php?msg=' . urlencode('Ticket submitted successfully.'));
        exit();
    } else {
        echo "Error: " . htmlspecialchars($stmt->error);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Add Support Ticket</title><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"></head>
<body><div class="container py-4"><h2>Add Support Ticket</h2><form method="POST"><div class="mb-3"><label>Subject</label><input type="text" name="subject" class="form-control" required></div><div class="mb-3"><label>Message</label><textarea name="message" class="form-control" required></textarea></div><button type="submit" class="btn btn-success">Submit Ticket</button></form></div></body></html>
