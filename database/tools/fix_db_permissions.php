<?php
// Run this script ONCE and then delete it for security.
// It will attempt to grant all privileges to root@localhost for apsdreamhome DB with no password.

$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'apsdreamhome';

// Connect to MySQL (no DB selected yet)
$conn = new mysqli($host, $user, $pass);
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

// Create DB if not exists (optional)
$conn->query("CREATE DATABASE IF NOT EXISTS `$db`");

// Grant privileges
$sql = "GRANT ALL PRIVILEGES ON `$db`.* TO 'root'@'localhost' IDENTIFIED BY ''";
if ($conn->query($sql)) {
    echo "Privileges granted successfully.<br>";
} else {
    echo "Error granting privileges: " . $conn->error . "<br>";
}

// Flush privileges
if ($conn->query("FLUSH PRIVILEGES;")) {
    echo "Privileges flushed.<br>";
} else {
    echo "Error flushing privileges: " . $conn->error . "<br>";
}

$conn->close();
echo "<b>Done. Please delete this script for security.</b>";
