<?php
/**
 * Database Connection Test
 * Tests the admin database connection
 */

// Include the same config as admin system
require_once __DIR__ . '/includes/config/config.php';

echo "<h1>Database Connection Test</h1>";

// Test database connection
try {
    if (isset($conn) && $conn instanceof mysqli) {
        echo "<div style='color: green; font-weight: bold;'>✅ Database Connection: SUCCESS</div>";
        echo "<p>Connection type: " . get_class($conn) . "</p>";

        // Test a simple query
        $result = $conn->query("SELECT COUNT(*) as count FROM admin");
        if ($result) {
            $row = $result->fetch_assoc();
            echo "<p>Admin users count: " . $row['count'] . "</p>";
            $result->free();
        }

        echo "<div style='color: green; font-weight: bold;'>✅ Admin Login System: READY</div>";
        echo "<p><a href='admin/'>Go to Admin Panel</a></p>";

    } else {
        echo "<div style='color: red; font-weight: bold;'>❌ Database Connection: FAILED</div>";
        echo "<p>Connection variable not found or invalid type</p>";
    }

} catch (Exception $e) {
    echo "<div style='color: red; font-weight: bold;'>❌ Database Error: " . $e->getMessage() . "</div>";
}

echo "<hr>";
echo "<h2>Available Database Functions:</h2>";
echo "<ul>";

// Check if functions exist
$functions = ['getDbConnection', 'getMysqliConnection', 'mysqli_connect'];
foreach ($functions as $func) {
    if (function_exists($func)) {
        echo "<li style='color: green;'>✅ $func</li>";
    } else {
        echo "<li style='color: red;'>❌ $func</li>";
    }
}

echo "</ul>";
echo "<p><strong>Current time:</strong> " . date('Y-m-d H:i:s') . "</p>";
?>
