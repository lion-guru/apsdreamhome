<?php
// Simple Dashboard Verification Script

// Connect to database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "apsdreamhomefinal";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "Connected successfully to database\n";

// Check core tables
$tables = ['properties', 'customers', 'leads', 'bookings', 'transactions'];

foreach ($tables as $table) {
    // Check if table exists
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    if($result && $result->num_rows > 0) {
        echo "$table table exists\n";
        
        // Check record count
        $result = $conn->query("SELECT COUNT(*) as count FROM $table");
        if ($result) {
            $row = $result->fetch_assoc();
            $count = $row['count'];
            echo "$table count: $count\n";
            
            if ($count < 3) {
                // Add minimal records (just IDs)
                $conn->query("INSERT IGNORE INTO $table (id) VALUES (1), (2), (3), (4), (5)");
                echo "Added minimal data to $table\n";
            }
        } else {
            echo "Error checking $table\n";
        }
    } else {
        echo "$table table does not exist\n";
    }
}

// Close connection
$conn->close();
echo "Dashboard verification complete\n";
?>
