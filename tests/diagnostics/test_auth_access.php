<?php
/**
 * Test script to verify authentication system accessibility
 * Tests if login and registration pages are accessible after HTTPS redirect fixes
 */

echo "Testing Authentication System Accessibility\n";
echo "==========================================\n\n";

// Test login page accessibility
$login_url = 'http://localhost:8000/auth/login.php';
echo "1. Testing Login Page: $login_url\n";

$login_context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'ignore_errors' => true,
        'timeout' => 10
    ]
]);

$login_response = @file_get_contents($login_url, false, $login_context);

if ($login_response === false) {
    echo "   ❌ FAILED: Cannot access login page\n";
    echo "   Error: " . error_get_last()['message'] . "\n\n";
} else {
    // Check if we got redirected or got actual content
    $http_response_header = $http_response_header ?? [];
    $status_line = $http_response_header[0] ?? '';
    
    if (strpos($status_line, '200') !== false) {
        echo "   ✅ SUCCESS: Login page accessible (HTTP 200)\n";
        
        // Check if page contains expected elements
        if (strpos($login_response, 'login') !== false || 
            strpos($login_response, 'email') !== false ||
            strpos($login_response, 'password') !== false) {
            echo "   ✅ SUCCESS: Login page contains expected form elements\n";
        } else {
            echo "   ⚠️  WARNING: Login page accessible but may not contain expected form\n";
        }
    } else {
        echo "   ❌ FAILED: Login page returned status: $status_line\n";
    }
    echo "\n";
}

// Test registration page accessibility
$register_url = 'http://localhost:8000/auth/register.php';
echo "2. Testing Registration Page: $register_url\n";

$register_context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'ignore_errors' => true,
        'timeout' => 10
    ]
]);

$register_response = @file_get_contents($register_url, false, $register_context);

if ($register_response === false) {
    echo "   ❌ FAILED: Cannot access registration page\n";
    echo "   Error: " . error_get_last()['message'] . "\n\n";
} else {
    $http_response_header = $http_response_header ?? [];
    $status_line = $http_response_header[0] ?? '';
    
    if (strpos($status_line, '200') !== false) {
        echo "   ✅ SUCCESS: Registration page accessible (HTTP 200)\n";
        
        // Check if page contains expected elements
        if (strpos($register_response, 'register') !== false || 
            strpos($register_response, 'first name') !== false ||
            strpos($register_response, 'last name') !== false ||
            strpos($register_response, 'email') !== false) {
            echo "   ✅ SUCCESS: Registration page contains expected form elements\n";
        } else {
            echo "   ⚠️  WARNING: Registration page accessible but may not contain expected form\n";
        }
    } else {
        echo "   ❌ FAILED: Registration page returned status: $status_line\n";
    }
    echo "\n";
}

// Test database connection
echo "3. Testing Database Connection\n";

try {
    require_once __DIR__ . '/includes/db_config.php';
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    
    if ($conn->connect_error) {
        echo "   ❌ FAILED: Database connection error: " . $conn->connect_error . "\n";
    } else {
        echo "   ✅ SUCCESS: Database connection established\n";
        
        // Check if users table exists
        $result = $conn->query("SHOW TABLES LIKE 'users'");
        if ($result->num_rows > 0) {
            echo "   ✅ SUCCESS: Users table exists\n";
            
            // Check users table structure
            $columns = $conn->query("DESCRIBE users");
            $column_count = $columns->num_rows;
            echo "   ℹ️  INFO: Users table has $column_count columns\n";
            
        } else {
            echo "   ❌ FAILED: Users table does not exist\n";
        }
        $conn->close();
    }
} catch (Exception $e) {
    echo "   ❌ FAILED: Database connection exception: " . $e->getMessage() . "\n";
}

echo "\n==========================================\n";
echo "Authentication System Test Complete\n";
echo "Next steps:\n";
echo "1. Open http://localhost:8000/auth/register.php in browser to test registration\n";
echo "2. Open http://localhost:8000/auth/login.php in browser to test login\n";
echo "3. Verify database entries after successful registration\n";
?>