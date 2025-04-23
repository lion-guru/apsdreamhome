<?php
session_start();
require("config.php");

if (!isset($_SESSION['auser'])) {
    header("location:index.php");
    exit();
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $query = mysqli_query($con, "SELECT * FROM kisaan_land_management WHERE id = $id");
    $record = mysqli_fetch_assoc($query);
    echo json_encode($record);
}
?>
