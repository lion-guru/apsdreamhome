<?php
// Include the database configuration and connection
require_once __DIR__ . '/../includes/config/db_config.php';
require_once __DIR__ . '/../includes/db_connection.php';

// Function to test database connection
function testConnection() {
    try {
        // Get database connection
        $conn = $con;
        
        if ($conn === false) {
            throw new Exception("Failed to get database connection");
        }
        
        // Test query
        $result = $conn->query("SELECT DATABASE() AS dbname");
        if ($result === false) {
            throw new Exception("Query failed: " . $conn->error);
        }
        
        $row = $result->fetch_assoc();
        $dbName = $row['dbname'];
        
        // Get tables count
        $result = $conn->query("SHOW TABLES");
        $tableCount = $result->num_rows;
        
        echo "<h2>Database Connection Test</h2>";
        echo "<p><strong>Status:</strong> <span style='color: green;'>Connected successfully</span></p>";
        echo "<p><strong>Database:</strong> " . htmlspecialchars($dbName) . "</p>";
        echo "<p><strong>Tables found:</strong> " . $tableCount . "</p>";
        
        // Show first 5 tables if any
        if ($tableCount > 0) {
            echo "<h3>Tables in database:</h3>";
            echo "<ul>";
            $tables = [];
            while ($row = $result->fetch_array()) {
                $tables[] = $row[0];
            }
            foreach (array_slice($tables, 0, 5) as $table) {
                echo "<li>" . htmlspecialchars($table) . "</li>";
            }
            if ($tableCount > 5) {
                echo "<li>... and " . ($tableCount - 5) . " more</li>";
            }
            echo "</ul>";
        }
        
        // Close connection
        $conn->close();
        
    } catch (Exception $e) {
        echo "<h2>Database Connection Test</h2>";
        echo "<p><strong>Error:</strong> <span style='color: red;'>" . htmlspecialchars($e->getMessage()) . "</span></p>";
        
        // Show debug info (be careful with this in production)
        echo "<h3>Debug Information:</h3>";
        echo "<pre>";
        echo "MySQL Error: " . (isset($conn) ? $conn->error : 'No connection') . "\n\n";
        echo "PHP Version: " . phpversion() . "\n";
        echo "MySQLi Available: " . (extension_loaded('mysqli') ? 'Yes' : 'No') . "\n";
        echo "</pre>";
    }
}

// Run the test
?>
<!DOCTYPE html>
<html>
<head>
    <title>Database Connection Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
        .container { max-width: 800px; margin: 0 auto; }
        .success { color: green; }
        .error { color: red; }
        pre { background: #f4f4f4; padding: 10px; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <?php testConnection(); ?>
        
        <h3>Connection Details:</h3>
        <ul>
            <?php
            $db_host = defined('DB_HOST') ? DB_HOST : 'Not defined';
            $db_name = defined('DB_NAME') ? DB_NAME : 'Not defined';
            $db_user = defined('DB_USER') ? DB_USER : 'Not defined';
            $db_pass = defined('DB_PASS') ? str_repeat('*', strlen(DB_PASS)) : 'Not defined';
            ?>
            <li><strong>DB Host:</strong> <?php echo htmlspecialchars($db_host); ?></li>
            <li><strong>DB Name:</strong> <?php echo htmlspecialchars($db_name); ?></li>
            <li><strong>DB User:</strong> <?php echo htmlspecialchars($db_user); ?></li>
            <li><strong>DB Password:</strong> <?php echo $db_pass; ?></li>
        </ul>
        
        <p><a href="javascript:window.location.reload()">Test Again</a> | <a href="index.php">Back to Admin</a></p>
    </div>
</body>
</html>
