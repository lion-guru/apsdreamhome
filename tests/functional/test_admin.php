<?php
// Test Admin Login Functionality
require_once __DIR__ . '/includes/db_connection.php';
require_once __DIR__ . '/admin/includes/csrf_protection.php';
require_once __DIR__ . '/admin/includes/session_manager.php';
require_once __DIR__ . '/admin/admin_login_handler.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Test database connection
echo "<h2>Database Connection Test</h2>";
try {
    $conn = getDbConnection();
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
    
    // Check if users table exists
    $result = $conn->query("SHOW TABLES LIKE 'users'");
    if ($result->num_rows > 0) {
        echo "<div>✓ Users table exists</div>";
        
        // Test admin user
        $test_user = 'admin';
        $result = $conn->query("SELECT * FROM users WHERE username = '" . $conn->real_escape_string($test_user) . "'");
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            echo "<div>✓ Found admin user</div>";
            
            // Test login
            echo "<h3>Testing Login</h3>";
            $result = AdminLoginHandler::login($test_user, 'admin123');
            if ($result['status'] === 'success') {
                echo "<div style='color: green;'>✓ Login successful!</div>";
                echo "<pre>" . print_r($result, true) . "</pre>";
            } else {
                echo "<div style='color: red;'>✗ Login failed: " . htmlspecialchars($result['message'] ?? 'Unknown error') . "</div>";
            }
        } else {
            echo "<div style='color: orange;'>⚠ Admin user not found</div>";
        }
    } else {
        echo "<div style='color: red;'>✗ Users table not found</div>";
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

// Show session info
echo "<h3>Session Info:</h3>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

// Show PHP info
if (isset($_GET['phpinfo'])) {
    phpinfo();
} else {
    echo "<p><a href='?phpinfo=1'>Show PHP Info</a></p>";
}
?>
