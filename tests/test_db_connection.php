<?php
// Simple script to test DB connection
require 'includes/config.php';

// Get database config
$config = AppConfig::getInstance()->get('database');
$host = $config['host'];
$user = $config['user'];
$pass = $config['pass'];
$db   = 'apsdreamhome';

echo "Attempting to connect to database...\n";
echo "Host: $host\n";
echo "User: $user\n";
echo "Database: $db\n\n";

// Test connection
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "✅ Successfully connected to database: $db\n";

// Show tables to verify access
$result = $conn->query("SHOW TABLES");
if ($result) {
    echo "\nTables in database:\n";
    while ($row = $result->fetch_array()) {
        echo "- " . $row[0] . "\n";
    }
    echo "\nTotal tables: " . $result->num_rows . "\n";
} else {
    echo "\nNo tables found or error: " . $conn->error . "\n";
}

$conn->close();
?>
