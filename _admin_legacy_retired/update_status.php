<?php
session_start();
require("config.php");

// Secure authentication check
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $status = $_POST['status'];
    $applicantId = $_POST['applicantId'];

    // Sanitize inputs
    $status = mysqli_real_escape_string($con, $status);
    $applicantId = mysqli_real_escape_string($con, $applicantId);

    $query = mysqli_query($con, "UPDATE career_applications SET status = '$status' WHERE id = '$applicantId'");

    if ($query) {
        echo "Status updated successfully.";
    } else {
        echo "Error updating status: " . mysqli_error($con);
    }
} else {
    echo "Invalid request.";
}
?>
