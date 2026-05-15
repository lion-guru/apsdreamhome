<?php
/**
 * APS Dream Home - MySQL Quick Test
 * Simple MySQL connection test without bootstrap
 */

echo "=== MYSQL QUICK TEST ===\n\n";

// Test basic MySQL connection using mysqli
$mysqli = new mysqli('127.0.0.1', 'root', '', 'apsdreamhome');

if ($mysqli->connect_error) {
    echo "❌ MySQL Connection: FAILED\n";
    echo "📝 Error: " . $mysqli->connect_error . "\n";
} else {
    echo "✅ MySQL Connection: SUCCESS\n";
    
    // Test basic query
    $result = $mysqli->query("SHOW TABLES");
    if ($result) {
        echo "✅ Tables Found: " . $result->num_rows . "\n";
    }
    
    // Test users table
    $result = $mysqli->query("SELECT COUNT(*) as count FROM users");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "✅ Users Table: " . $row['count'] . " records\n";
    }
    
    $mysqli->close();
}

echo "\n=== TEST COMPLETE ===\n";

?>
