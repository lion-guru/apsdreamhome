<?php
// Database configuration
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'apsdreamhomefinal';

// Create connection
$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "✅ Successfully connected to database: " . $db . "\n";

// List all tables in the database
$result = $conn->query("SHOW TABLES");

if ($result->num_rows > 0) {
    echo "\n📊 Tables in the database:\n";
    echo "========================\n";
    while($row = $result->fetch_array()) {
        echo "- " . $row[0] . "\n";
    }
} else {
    echo "No tables found in the database.\n";
}

// Close connection
$conn->close();
?>
