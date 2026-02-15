<?php
// Simple test file to check if PHP is working
echo "<h1>PHP Test Page</h1>";
echo "<p>This is a test page to check if PHP is working correctly.</p>";
echo "<p>Current time: " . date('Y-m-d H:i:s') . "</p>";
echo "<p>PHP Version: " . phpversion() . "</p>";

// Check database connection
echo "<h2>Database Connection Test</h2>";
try {
    $conn = new PDO("mysql:host=localhost;dbname=apsdreamhome", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p style='color:green'>Database connection successful!</p>";
    
    // Try to get some data
    $stmt = $conn->query("SELECT COUNT(*) as count FROM properties");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p>Number of properties in database: " . $result['count'] . "</p>";
    
} catch(PDOException $e) {
    echo "<p style='color:red'>Database connection failed: " . $e->getMessage() . "</p>";
}
?>