<?php
session_start();
require("config.php");
require_once __DIR__ . '/../includes/log_admin_activity.php';

// Secure authentication check
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

// Check if user is logged in
if (!isset($_SESSION['auser'])) {
    header("location:index.php");
    exit();
}

// Check if ID is set
if (isset($_POST['id'])) {
    $delete_id = intval($_POST['id']);
    
    // Prepare the SQL statement
    $stmt = $con->prepare("DELETE FROM kisaan_land_management WHERE id = ?");
    if (!$stmt) {
        echo json_encode(['status' => 'error', 'message' => 'Prepare failed: ' . $con->error]);
        exit();
    }

    $stmt->bind_param("i", $delete_id);

    // Execute the statement
    if ($stmt->execute()) {
        log_admin_activity('delete_land_record', 'Deleted land record ID: ' . $delete_id);
        echo json_encode(['status' => 'success', 'message' => 'Record deleted successfully.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error deleting record: ' . $stmt->error]);
    }
    
    $stmt->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
}

$con->close();
?>
