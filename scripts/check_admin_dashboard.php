<?php
/**
 * Script to verify admin dashboard functionality
 */

// Include required files
require_once __DIR__ . '/includes/db_connection.php';

// Function to check if a URL is accessible
function checkUrl($url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return $httpCode === 200;
}

// Function to check if tables have data using prepared statement
function checkTableHasData($conn, $table) {
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM `$table`");
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result) {
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row['count'] > 0;
    }
    return false;
}

// Main execution
echo "=== Admin Dashboard Health Check ===\n\n";

try {
    // 1. Check database connection
    echo "1. Testing database connection... ";
    $conn = getDbConnection();
    if ($conn === null) {
        throw new Exception("Failed to connect to the database");
    }
    echo "✓ Connected successfully\n";
    
    // 2. Check required tables
    echo "\n2. Checking required tables...\n";
    $requiredTables = ['admin', 'users', 'bookings', 'leads', 'transactions', 'notifications'];
    $allTablesExist = true;
    
    foreach ($requiredTables as $table) {
        echo "   - Checking table '$table'... ";
        $result = $conn->query("SHOW TABLES LIKE '$table'");
        if ($result && $result->num_rows > 0) {
            $hasData = checkTableHasData($conn, $table) ? " (has data)" : " (empty)";
            echo "✓ Exists$hasData\n";
        } else {
            echo "✗ Missing\n";
            $allTablesExist = false;
        }
    }
    
    if (!$allTablesExist) {
        throw new Exception("Some required tables are missing. Please run the database setup.");
    }
    
    // 3. Check admin user exists
    echo "\n3. Checking admin user... ";
    $result = $conn->query("SELECT * FROM `admin` WHERE `role` = 'admin' OR `role` = 'superadmin' LIMIT 1");
    if ($result && $result->num_rows > 0) {
        $admin = $result->fetch_assoc();
        echo "✓ Found admin user: " . htmlspecialchars($admin['auser']) . "\n";
    } else {
        echo "✗ No admin user found. Creating default admin... ";
        // Create default admin
        $defaultPass = password_hash('admin123', PASSWORD_DEFAULT);
        $sql = "INSERT INTO `admin` (`auser`, `apass`, `role`, `email`, `status`) 
                VALUES ('admin', '$defaultPass', 'superadmin', 'admin@example.com', 'active')";
        if ($conn->query($sql)) {
            echo "✓ Created default admin (username: admin, password: admin123)\n";
            echo "   WARNING: Please change the default password immediately!\n";
        } else {
            echo "✗ Failed to create admin: " . $conn->error . "\n";
        }
    }
    
    // 4. Check admin dashboard URL
    echo "\n4. Checking admin dashboard access...\n";
    $dashboardUrl = 'http://' . $_SERVER['HTTP_HOST'] . '/apsdreamhome/admin/';
    echo "   - Dashboard URL: $dashboardUrl\n";
    
    if (checkUrl($dashboardUrl)) {
        echo "   ✓ Dashboard is accessible\n";
    } else {
        echo "   ✗ Dashboard is not accessible\n";
    }
    
    echo "\n=== Health Check Completed Successfully ===\n";
    echo "You can now access the admin dashboard at: $dashboardUrl\n";
    
} catch (Exception $e) {
    echo "\n=== Health Check Failed ===\n";
    echo "Error: " . $e->getMessage() . "\n";
    if (isset($conn) && $conn->error) {
        echo "Database Error: " . $conn->error . "\n";
    }
}

// Close the connection
if (isset($conn)) {
    $conn->close();
}
?>
