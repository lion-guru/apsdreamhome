<?php
session_start();
require_once(__DIR__ . '/../app/bootstrap.php');
$db = \App\Core\App::database();

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
$db->execute("DELETE FROM $table WHERE id = :id", ['id' => $id]);

header("Location: {$type}view.php?msg=Deleted");
exit();
