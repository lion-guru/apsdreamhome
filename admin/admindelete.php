<?php
include("config.php");
$aid = intval($_GET['id']);
$sql = "DELETE FROM admin WHERE aid = {$aid}";
$result = mysqli_query($con, $sql);
if($result == true)
{
	$msg="<p class='alert alert-success'>Admin Deleted</p>";
	header("Location:adminlist.php?msg=$msg");
}
else{
	$msg="<p class='alert alert-warning'>Admin Not Deleted</p>";
	header("Location:adminlist.php?msg=$msg");
}
mysqli_close($con);
?>

<html>
<body>
<?php include __DIR__ . '/../includes/templates/dynamic_header.php'; ?>
<?php
include("config.php");
$aid = intval($_GET['id']);
$sql = "DELETE FROM admin WHERE aid = {$aid}";
$result = mysqli_query($con, $sql);
if($result == true)
{
	$msg="<p class='alert alert-success'>Admin Deleted</p>";
	header("Location:adminlist.php?msg=$msg");
}
else{
	$msg="<p class='alert alert-warning'>Admin Not Deleted</p>";
	header("Location:adminlist.php?msg=$msg");
}
mysqli_close($con);
?>
<?php include __DIR__ . '/../includes/templates/new_footer.php'; ?>
</body>
</html>
