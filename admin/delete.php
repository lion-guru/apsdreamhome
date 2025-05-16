<?php
session_start();
require_once("config.php");

if (!isset($_SESSION['auser'])) {
    http_response_code(403);
    exit("Unauthorized");
}

$type = $_GET['type'] ?? '';
$id = $_GET['id'] ?? 0;

$allowed_tables = [
    'about' => 'about',
    'admin' => 'admin',
    'gallery' => 'gallery',
    'property' => 'property'
];

if (!isset($allowed_tables[$type]) || !is_numeric($id)) {
    http_response_code(400);
    exit("Invalid request");
}

$table = $allowed_tables[$type];
$stmt = $con->prepare("DELETE FROM $table WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

header("Location: {$type}view.php?msg=Deleted");
exit();
