<?php
session_start();
include("config.php");

if (!isset($_SESSION['uid'])) {
    header("location:login.php");
    exit();
}

$project_id = $_GET['id'];
$stmt = $conn->prepare("DELETE FROM projects WHERE bid = ?");
$stmt->bind_param("i", $project_id);
$stmt->execute();

header("location:builder_dashboard.php");
exit();
?>
