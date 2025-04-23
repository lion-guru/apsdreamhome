<?php
session_start();
require_once __DIR__ . '/includes/session_manager.php';
require_once __DIR__ . '/../includes/db_config.php';
// Role-based access control: Only superadmin can delete users
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true || $_SESSION['admin_role'] !== 'superadmin') {
    header('Location: unauthorized.php?error=unauthorized');
    exit();
}
?>

<?php
include("config.php");
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
	$msg="<p class='alert alert-success'>User Deleted</p>";
	header("Location:userlist.php?msg=$msg");
}
else
{
	$msg="<p class='alert alert-warning'>User not Deleted</p>";
		header("Location:userlist.php?msg=$msg");
}

mysqli_close($con);
?>
