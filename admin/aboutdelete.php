<?php
include("config.php");
$id = intval($_GET['id']);

// view code//
$sql = "SELECT * FROM about where id=$id";
$result = mysqli_query($con, $sql);
while($row = mysqli_fetch_array($result))
	{
	  $img=$row["image"];
	}
@unlink('upload/'.$img);

//end view code


$msg="";
$sql = "DELETE FROM about WHERE id = $id";
$result = mysqli_query($con, $sql);
if($result == true)
{
	$msg="<p class='alert alert-success'>About Deleted</p>";
	header("Location:aboutview.php?msg=".urlencode($msg));
	exit();
}
else
{
	$error = "<p class='alert alert-warning'>* Error: " . htmlspecialchars(mysqli_error($con)) . "</p>";
	header("Location:aboutview.php?msg=".urlencode($error));
	exit();
}

mysqli_close($con);
?>
