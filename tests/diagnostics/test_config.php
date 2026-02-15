<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Test basic PHP functionality
echo "<h1>PHP Test Page</h1>";
echo "<p>PHP Version: " . phpversion() . "</p>";

// Test session
session_start();
echo "<p>Session ID: " . session_id() . "</p>";

// Test database connection
try {
    $conn = new mysqli('localhost', 'root', '', 'apsdreamhome');
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    echo "<p style='color:green;'>✅ Database connection successful!</p>";
    $conn->close();
} catch (Exception $e) {
    echo "<p style='color:red;'>❌ Database error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Display PHP info
// Uncomment the next line if you want to see detailed PHP configuration
// phpinfo();
?>
