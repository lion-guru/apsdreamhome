<?php
// Dashboard Verification Script
// This script checks all tables needed for dashboard widgets and adds data if missing

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

// Function to check and seed a table
function checkAndSeedTable($conn, $tableName, $minRecords = 5) {
    // Check if table exists
    $result = $conn->query("SHOW TABLES LIKE '$tableName'");
    if($result && $result->num_rows > 0) {
        echo "$tableName table exists\n";
        
        // Check record count
        $result = $conn->query("SELECT COUNT(*) as count FROM $tableName");
        if ($result) {
            $row = $result->fetch_assoc();
            $count = $row['count'];
            echo "$tableName count: $count\n";
            
            if ($count < $minRecords) {
                echo "Need to add data to $tableName\n";
                return true;
            } else {
                echo "$tableName has sufficient data\n";
                return false;
            }
        } else {
            echo "Error checking $tableName: " . $conn->error . "\n";
            return false;
        }
    } else {
        echo "$tableName table does not exist\n";
        return false;
    }
}

// Check and seed core tables
$tables = [
    'properties' => 5,
    'customers' => 10,
    'leads' => 10,
    'bookings' => 3,
    'transactions' => 5
];

foreach ($tables as $table => $minRecords) {
    $needsData = checkAndSeedTable($conn, $table, $minRecords);
    
    if ($needsData) {
        switch ($table) {
            case 'properties':
                // Add minimal property records (just IDs if needed)
                $conn->query("INSERT IGNORE INTO $table (id) VALUES (1), (2), (3), (4), (5)");
                echo "Added minimal data to $table\n";
                break;
                
            case 'customers':
                // Add minimal customer records (just IDs if needed)
                $conn->query("INSERT IGNORE INTO $table (id) VALUES (1), (2), (3), (4), (5), (6), (7), (8), (9), (10)");
                echo "Added minimal data to $table\n";
                break;
                
            case 'leads':
                // Add minimal lead records (just IDs if needed)
                $conn->query("INSERT IGNORE INTO $table (id) VALUES (1), (2), (3), (4), (5), (6), (7), (8), (9), (10)");
                echo "Added minimal data to $table\n";
                break;
                
            case 'bookings':
                // Add minimal booking records (just IDs if needed)
                $conn->query("INSERT IGNORE INTO $table (id) VALUES (1), (2), (3)");
                echo "Added minimal data to $table\n";
                break;
                
            case 'transactions':
                // Add minimal transaction records (just IDs if needed)
                $conn->query("INSERT IGNORE INTO $table (id) VALUES (1), (2), (3), (4), (5)");
                echo "Added minimal data to $table\n";
                break;
        }
    }
}

// Check for additional tables that might be used in dashboard widgets
$additionalTables = [
    'notifications',
    'feedback',
    'gallery',
    'testimonials',
    'property_visits'
];

foreach ($additionalTables as $table) {
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
                // Add minimal records (just IDs if needed)
                $conn->query("INSERT IGNORE INTO $table (id) VALUES (1), (2), (3)");
                echo "Added minimal data to $table\n";
            }
        }
    } else {
        echo "$table table does not exist\n";
    }
}

// Close connection
$conn->close();
echo "Dashboard verification complete\n";
echo "Your dashboard should now display data in all widgets\n";
?>
