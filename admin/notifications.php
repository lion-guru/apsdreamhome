<?php
session_start();
include 'config.php';

if (!isset($_SESSION['auser'])) {
    header("Location: login.php");
    exit();
}

// Fetch recent notifications (last 20)
$notifications = $conn->query("SELECT * FROM notifications ORDER BY created_at DESC LIMIT 20");

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Notifications</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container py-4">
    <h2 class="mb-4">Recent Notifications</h2>
    <div class="card mb-4">
        <div class="card-header">System & User Alerts</div>
        <div class="card-body p-0">
            <table class="table table-striped mb-0">
                <thead>
                <tr>
                    <th>Type</th>
                    <th>Message</th>
                    <th>Date</th>
                </tr>
                </thead>
                <tbody>
                <?php while($n = $notifications->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($n['type']) ?></td>
                    <td><?= htmlspecialchars($n['message']) ?></td>
                    <td><?= htmlspecialchars($n['created_at']) ?></td>
                </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>
