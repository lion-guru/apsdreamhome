<?php
session_start();
require_once("config.php");
if (!isset($_SESSION['auser'])) {
    header("Location: index.php");
    exit();
}
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $con->prepare("DELETE FROM property WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
}
header("Location: propertyview.php?msg=Deleted");
exit();
