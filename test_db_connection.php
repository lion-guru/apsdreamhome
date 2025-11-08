<?php
// Simple database connection test
require_once 'config.php';

echo "Testing Database Connection...\n";
echo "Database Host: " . DB_HOST . "\n";
echo "Database Name: " . DB_NAME . "\n";
echo "Database User: " . DB_USER . "\n";

// Test the connection
if (isset($con) && $con->connect_error) {
    echo "❌ Connection FAILED: " . $con->connect_error . "\n";
} elseif (isset($con)) {
    echo "✅ Connection SUCCESSFUL!\n";
    
    // Test a simple query
    $result = $con->query("SELECT COUNT(*) as count FROM users");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "📊 Users table has: " . $row['count'] . " records\n";
    } else {
        echo "❌ Query failed: " . $con->error . "\n";
    }
    
    // Test properties table
    $result = $con->query("SELECT COUNT(*) as count FROM properties");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "🏠 Properties table has: " . $row['count'] . " records\n";
    }
} else {
    echo "❌ Database connection object not found. Check config.php\n";
}

echo "\nDatabase connection test completed!\n";
?>