<?php
// Test database connection
require_once __DIR__ . '/includes/db_connection.php';

echo "<h2>Database Connection Test</h2>";

// Test connection using the main function
try {
    $conn = getMysqliConnection();
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    echo "<div style='color: green;'>✓ Database connection successful!</div>";
    
    // Test query
    $result = $conn->query("SELECT DATABASE() as db");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "<div>Connected to database: <strong>" . htmlspecialchars($row['db']) . "</strong></div>";
    }
    
    // Check if admin table exists
    $result = $conn->query("SHOW TABLES LIKE 'users'");
    if ($result->num_rows > 0) {
        echo "<div>✓ Users table exists</div>";
    } else {
        echo "<div style='color: orange;'>⚠ Users table not found</div>";
    }
    
    $conn->close();
    
} catch (Exception $e) {
    echo "<div style='color: red;'>✗ Error: " . htmlspecialchars($e->getMessage()) . "</div>";
    
    // Show configuration being used
    echo "<h3>Configuration:</h3>";
    echo "<pre>DB_HOST: " . (defined('DB_HOST') ? DB_HOST : 'not defined') . "</pre>";
    echo "<pre>DB_USER: " . (defined('DB_USER') ? DB_USER : 'not defined') . "</pre>";
    echo "<pre>DB_NAME: " . (defined('DB_NAME') ? DB_NAME : 'not defined') . "</pre>";
}

// Test if we can write to the directory
$testFile = __DIR__ . '/test_write.txt';
if (@file_put_contents($testFile, 'test') !== false) {
    unlink($testFile);
    echo "<div>✓ Directory is writable</div>";
} else {
    echo "<div style='color: orange;'>⚠ Directory is not writable</div>";
}

// Show PHP info
if (isset($_GET['phpinfo'])) {
    phpinfo();
} else {
    echo "<p><a href='?phpinfo=1'>Show PHP Info</a></p>";
}
?>
