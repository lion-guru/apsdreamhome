<?php
session_start();
include 'config.php';
require_permission('approve_leave');
if (!isset($_SESSION['auser'])) { header('Location: login.php'); exit(); }
if (isset($_GET['id']) && isset($_GET['action'])) {
    $id = intval($_GET['id']);
    $action = ($_GET['action'] === 'approve') ? 'approved' : 'rejected';
    $stmt = $conn->prepare("UPDATE leaves SET status=? WHERE id=?");
    $stmt->bind_param('si', $action, $id);
    if ($stmt->execute()) {
        require_once __DIR__ . '/../includes/functions/notification_util.php';
        addNotification($conn, 'Leave', 'Leave ' . $action . ' for leave ID: ' . $id, $_SESSION['auser']);
        header('Location: leaves.php?msg=' . urlencode('Leave ' . $action . ' successfully.'));
        exit();
    } else {
        echo "Error: " . htmlspecialchars($stmt->error);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Approve/Reject Leave</title></head>
<body><div class="container py-4"><h2>Action in progress...</h2></div></body></html>
