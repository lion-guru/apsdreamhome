<?php
session_start();
require_once(__DIR__ . '/../app/bootstrap.php');
$db = \App\Core\App::database();
if (!isset($_SESSION['auser'])) {
    header("Location: index.php");
    exit();
}
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int)$_GET['id'];
    $db->execute("DELETE FROM admin WHERE id = :id", ['id' => $id]);
}
header("Location: adminlist.php?msg=Deleted");
exit();
