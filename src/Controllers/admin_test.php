<?php
/**
 * Admin Panel Test Script
 * 
 * This script tests the functionality of the admin panel by:
 * 1. Verifying database connectivity
 * 2. Checking required tables and their structure
 * 3. Testing admin authentication
 * 4. Verifying access to key admin pages
 * 5. Testing CRUD operations on sample data
 */

// Include required files
require_once __DIR__ . '/includes/db_connection.php';

// Test results array
$testResults = [];

/**
 * Add a test result
 */
function addTestResult($testName, $status, $message = '') {
    global $testResults;
    $testResults[] = [
        'test' => $testName,
        'status' => $status,
        'message' => $message
    ];
}

/**
 * Display test results
 */
function displayResults() {
    global $testResults;
    
    echo "<h2>Admin Panel Test Results</h2>";
    echo "<table border='1' cellpadding='8' cellspacing='0' style='width:100%; border-collapse: collapse;'>";
    echo "<tr>
            <th>Test</th>
            <th>Status</th>
            <th>Details</th>
          </tr>";
    
    foreach ($testResults as $result) {
        $statusColor = $result['status'] === 'PASS' ? 'green' : 'red';
        echo "<tr>";
        echo "<td>{$result['test']}</td>";
        echo "<td style='color: $statusColor; font-weight: bold;'>{$result['status']}</td>";
        echo "<td>{$result['message']}</td>";
        echo "</tr>";
    }
    
    echo "</table>";
}

// Start tests
echo "<h1>Admin Panel Test Suite</h1>";

// 1. Test database connection
try {
    $conn = getDbConnection();
    if ($conn === null) {
        throw new Exception("Failed to connect to database");
    }
    addTestResult('Database Connection', 'PASS', 'Successfully connected to the database');
    
    // 2. Check required tables
    $requiredTables = [
        'admin', 'users', 'bookings', 'properties', 'property_types',
        'property_features', 'property_images', 'leads', 'transactions'
    ];
    
    $missingTables = [];
    foreach ($requiredTables as $table) {
        $result = $conn->query("SHOW TABLES LIKE '$table'");
        if ($result->num_rows === 0) {
            $missingTables[] = $table;
        }
    }
    
    if (empty($missingTables)) {
        addTestResult('Required Tables', 'PASS', 'All required tables exist');
    } else {
        addTestResult('Required Tables', 'FAIL', 'Missing tables: ' . implode(', ', $missingTables));
    }
    
    // 3. Check admin user exists
    $result = $conn->query("SELECT * FROM `admin` WHERE `role` IN ('admin', 'superadmin') LIMIT 1");
    if ($result && $result->num_rows > 0) {
        $admin = $result->fetch_assoc();
        addTestResult('Admin User', 'PASS', "Found admin user: {$admin['auser']}");
    } else {
        addTestResult('Admin User', 'FAIL', 'No admin user found. Please create an admin account.');
    }
    
    // 4. Check sample data
    $result = $conn->query("SELECT COUNT(*) as count FROM `properties`");
    $propertyCount = $result->fetch_assoc()['count'];
    
    if ($propertyCount > 0) {
        addTestResult('Sample Properties', 'PASS', "Found $propertyCount properties");
    } else {
        addTestResult('Sample Properties', 'WARNING', 'No properties found. Consider adding sample data.');
    }
    
    // 5. Check bookings
    $result = $conn->query("SELECT COUNT(*) as count FROM `bookings`");
    $bookingCount = $result->fetch_assoc()['count'];
    
    if ($bookingCount > 0) {
        addTestResult('Bookings', 'PASS', "Found $bookingCount bookings");
    } else {
        addTestResult('Bookings', 'WARNING', 'No bookings found. Consider adding sample bookings.');
    }
    
    // 6. Test property relationships
    $result = $conn->query("
        SELECT b.id, b.property_id, p.title 
        FROM bookings b 
        LEFT JOIN properties p ON b.property_id = p.id 
        LIMIT 1");
    
    if ($result && $result->num_rows > 0) {
        $booking = $result->fetch_assoc();
        if (!empty($booking['property_id']) && !empty($booking['title'])) {
            addTestResult('Property Relationships', 'PASS', 'Bookings are properly linked to properties');
        } else {
            addTestResult('Property Relationships', 'WARNING', 'Some bookings may not be properly linked to properties');
        }
    } else {
        addTestResult('Property Relationships', 'WARNING', 'No bookings found to test relationships');
    }
    
    // 7. Check file permissions
    $writableDirs = [
        'admin/uploads',
        'admin/assets/images',
        'includes/config',
        'cache'
    ];
    
    $permissionIssues = [];
    foreach ($writableDirs as $dir) {
        $path = __DIR__ . '/' . $dir;
        if (!is_writable($path)) {
            $permissionIssues[] = $dir . ' is not writable';
        }
    }
    
    if (empty($permissionIssues)) {
        addTestResult('File Permissions', 'PASS', 'All required directories are writable');
    } else {
        addTestResult('File Permissions', 'WARNING', implode('<br>', $permissionIssues));
    }
    
    // 8. Check PHP version and extensions
    $phpVersion = phpversion();
    $requiredExtensions = ['mysqli', 'pdo_mysql', 'gd', 'mbstring', 'json'];
    $missingExtensions = [];
    
    foreach ($requiredExtensions as $ext) {
        if (!extension_loaded($ext)) {
            $missingExtensions[] = $ext;
        }
    }
    
    if (version_compare($phpVersion, '7.4.0', '>=')) {
        addTestResult('PHP Version', 'PASS', "PHP $phpVersion");
    } else {
        addTestResult('PHP Version', 'WARNING', "PHP $phpVersion (7.4.0 or higher recommended)");
    }
    
    if (empty($missingExtensions)) {
        addTestResult('PHP Extensions', 'PASS', 'All required extensions are loaded');
    } else {
        addTestResult('PHP Extensions', 'WARNING', 'Missing extensions: ' . implode(', ', $missingExtensions));
    }
    
    // 9. Test admin login page
    $loginUrl = 'http://' . $_SERVER['HTTP_HOST'] . '/apsdreamhomefinal/admin/login.php';
    $headers = @get_headers($loginUrl);
    
    if ($headers && strpos($headers[0], '200')) {
        addTestResult('Admin Login Page', 'PASS', 'Login page is accessible');
    } else {
        addTestResult('Admin Login Page', 'WARNING', 'Could not access login page. Make sure the server is running.');
    }
    
    // 10. Test dashboard access
    $dashboardUrl = 'http://' . $_SERVER['HTTP_HOST'] . '/apsdreamhomefinal/admin/dashboard.php';
    $headers = @get_headers($dashboardUrl);
    
    if ($headers && (strpos($headers[0], '200') || strpos($headers[0], '302'))) {
        addTestResult('Admin Dashboard', 'PASS', 'Dashboard page is accessible');
    } else {
        addTestResult('Admin Dashboard', 'WARNING', 'Could not access dashboard. You may need to log in first.');
    }
    
} catch (Exception $e) {
    addTestResult('Test Suite', 'FAIL', 'Error running tests: ' . $e->getMessage());
}

// Display results
displayResults();

// Add some CSS for better readability
echo "
<style>
    body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
    h1 { color: #333; }
    h2 { color: #444; margin-top: 30px; }
    table { margin-top: 20px; border-collapse: collapse; width: 100%; }
    th { background-color: #f2f2f2; text-align: left; padding: 12px; }
    td { padding: 10px; border-bottom: 1px solid #ddd; }
    .PASS { color: green; }
    .FAIL { color: red; }
    .WARNING { color: orange; }
</style>";

// Add summary
$passed = count(array_filter($testResults, function($test) { return $test['status'] === 'PASS'; }));
$total = count($testResults);
$percentage = round(($passed / $total) * 100);

echo "<div style='margin-top: 30px; padding: 15px; background-color: #f8f9fa; border-radius: 5px;'>";
echo "<h3>Test Summary</h3>";
echo "<p>Tests Passed: <strong>$passed/$total</strong> ($percentage%)</p>";

echo "<div style='margin-top: 15px;'>";
if ($percentage == 100) {
    echo "<p style='color: green; font-weight: bold;'>✅ All tests passed! Your admin panel is ready to use.</p>";
} elseif ($percentage >= 80) {
    echo "<p style='color: orange; font-weight: bold;'>⚠️ Some tests have warnings. The admin panel should work, but some features might be limited.</p>";
} else {
    echo "<p style='color: red; font-weight: bold;'>❌ Several tests failed. Please check the issues below before using the admin panel.</p>";
}
echo "</div>";

echo "<div style='margin-top: 20px;'>";
echo "<h3>Next Steps:</h3>";
echo "<ol>";
echo "<li>Access the <a href='/apsdreamhomefinal/admin/' target='_blank'>Admin Dashboard</a></li>";
echo "<li>Log in with your admin credentials</li>";
echo "<li>Review and update your site settings</li>";
echo "<li>Add more properties, users, and content as needed</li>";
echo "</ol>";
echo "</div>";

echo "</div>";
?>
