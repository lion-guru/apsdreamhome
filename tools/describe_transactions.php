<?php
require_once __DIR__ . '/../includes/db_config.php';
$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$res = $conn->query("SHOW CREATE TABLE transactions");
print_r($res->fetch_assoc());
$conn->close();