<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$conn = new mysqli('localhost', 'root', '', 'apsdreamhome');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
echo "Connected successfully\n";

$res = $conn->query("SELECT COUNT(*) FROM ai_workflows");
if ($res) {
    echo "Workflows count: " . $res->fetch_row()[0] . "\n";
} else {
    echo "Query failed: " . $conn->error . "\n";
}
