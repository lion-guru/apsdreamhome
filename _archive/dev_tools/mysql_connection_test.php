<?php
// MySQL Connection Test
$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'apsdreamhome';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    echo '❌ Connection failed: ' . $conn->connect_error;
} else {
    echo '✅ MySQL connection successful!';
    echo '📊 Connected to database: ' . $db;
    echo '🔧 MySQL version: ' . $conn->server_info;
    
    // Count tables
    $result = $conn->query('SHOW TABLES');
    echo '📋 Total tables: ' . $result->num_rows;
    
    $conn->close();
}
?>