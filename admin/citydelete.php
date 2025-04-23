<?php
session_start();
include("config.php");
require_once __DIR__ . '/../includes/log_admin_activity.php';
// Secure authentication check
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}
$cid = $_GET['id'];
$sql = "DELETE FROM city WHERE cid = {$cid}";
$result = mysqli_query($con, $sql);
if($result == true)
{
    log_admin_activity('delete_city', 'Deleted city ID: ' . $cid);
	$msg="<p class='alert alert-success'>City Deleted</p>";
	header("Location:cityadd.php?msg=$msg");
}
else{
	$msg="<p class='alert alert-warning'>City Not Deleted</p>";
	header("Location:cityadd.php?msg=$msg");
}
mysqli_close($con);
?>
