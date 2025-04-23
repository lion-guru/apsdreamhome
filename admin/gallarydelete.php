<?php
session_start();
include("config.php");
require_once __DIR__ . '/../includes/log_admin_activity.php';
// Secure authentication check
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}
$aid = $_GET['id'];

// view code//
$sql = "SELECT * FROM images where id='$aid'";
$result = mysqli_query($con, $sql);
while($row = mysqli_fetch_array($result))
    {
      $img=$row["image"];
    }
@unlink('upload/'.$img);

//end view code


$msg="";
$sql = "DELETE FROM images WHERE id = {$aid}";
$result = mysqli_query($con, $sql);
if($result == true)
{
    log_admin_activity('delete_gallery', 'Deleted gallery image ID: ' . $aid);
    $msg="<p class='alert alert-success'>Gallary image delete</p>";
    header("Location:gallaryview.php?msg=$msg");
}
else
{
    $msg="<p class='alert alert-warning'>Gallary Image Not Deleted</p>";
        header("Location:gallaryview.php?msg=$msg");
}

mysqli_close($con);
?>
