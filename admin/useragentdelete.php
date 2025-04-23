<?php
session_start();
include("config.php");
require_once __DIR__ . '/../includes/log_admin_activity.php';
// Secure authentication check
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}
$uid = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// view code//
$sql = "SELECT * FROM user where uid=$uid";
$result = mysqli_query($con, $sql);
while($row = mysqli_fetch_array($result))
	{
	  $img=$row["uimage"];
	}
@unlink('user/'.$img);

//end view code
$msg="";
$sql = "DELETE FROM user WHERE uid = $uid";
$result = mysqli_query($con, $sql);
if($result == true)
{
    log_admin_activity('delete_agent', 'Deleted agent ID: ' . $uid);
	$msg="<p class='alert alert-success'>Agent Deleted</p>";
	header("Location:useragent.php?msg=$msg");
}
else
{
	$msg="<p class='alert alert-warning'>Agent not Deleted</p>";
		header("Location:useragent.php?msg=$msg");
}

mysqli_close($con);
?>
