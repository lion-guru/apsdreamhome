<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Simple test page
echo "<!DOCTYPE html>";
echo "<html lang='en'>";
echo "<head>";
echo "    <meta charset='UTF-8'>";
echo "    <meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "    <title>Test Page</title>";
echo "</head>";
echo "<body>";
echo "<h1>Test Page is Working!</h1>";
echo "<p>If you can see this, PHP is working correctly.</p>";

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

echo "</body>";
echo "</html>";
?>
