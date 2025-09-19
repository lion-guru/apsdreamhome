<?php
// Database configuration
$host = 'localhost';
$user = 'root';
$pass = '';

// Create connection without selecting a database
$conn = new mysqli($host, $user, $pass);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get list of all databases
$result = $conn->query("SHOW DATABASES");

$databases = [];
$target_dbs = ['apsdreamhome', 'apsdreamhomes', 'aps_dream_home'];
$found_dbs = [];

if ($result->num_rows > 0) {
    echo "🔍 Found databases:\n";
    echo "==================\n";
    
    while($row = $result->fetch_array()) {
        $db_name = $row[0];
        $databases[] = $db_name;
        
        // Check if it's one of our target databases
        if (in_array($db_name, $target_dbs)) {
            $found_dbs[] = $db_name;
            echo "✅ " . $db_name . " (Will be checked)\n";
        } else {
            echo "   " . $db_name . "\n";
        }
    }
    
    echo "\n";
    
    // Check target databases
    if (count($found_dbs) > 0) {
        echo "🔎 Checking target databases for tables to migrate...\n";
        echo "==========================================\n";
        
        foreach ($found_dbs as $db) {
            $conn->select_db($db);
            $tables_result = $conn->query("SHOW TABLES");
            $table_count = $tables_result->num_rows;
            
            echo "📊 Database: " . $db . " (" . $table_count . " tables)\n";
            
            // List all tables
            if ($table_count > 0) {
                while ($table = $tables_result->fetch_array()) {
                    $table_name = $table[0];
                    echo "   - " . $table_name . "\n";
                }
            } else {
                echo "   No tables found in this database.\n";
            }
            
            echo "\n";
        }
        
        echo "✅ Found " . count($found_dbs) . " target database(s) to check.\n";
    } else {
        echo "ℹ️ No target databases (apsdreamhome, apsdreamhomes, aps_dream_home) found.\n";
    }
    
} else {
    echo "No databases found.\n";
}

$conn->close();
?>
