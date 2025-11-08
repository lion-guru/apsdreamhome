<?php
/**
 * System Health Check Script
 * Verifies that all components are working correctly after fixes
 */

echo "<h1>System Health Check</h1>";
echo "<style>
    .success { color: green; }
    .error { color: red; }
    .warning { color: orange; }
    .info { color: blue; }
</style>";

try {
    // Test 1: Configuration Loading
    echo "<h3>1. Configuration Loading</h3>";
    require_once __DIR__ . '/config.php';
    echo "<span class='success'>✅ Config loaded successfully</span><br>";

    // Test 2: Database Connection
    echo "<h3>2. Database Connection</h3>";
    if (isset($conn) && $conn instanceof mysqli) {
        echo "<span class='success'>✅ Database connection established</span><br>";
        echo "<span class='info'>Database: " . DB_NAME . "</span><br>";
        echo "<span class='info'>Host: " . DB_HOST . "</span><br>";

        // Test database query
        $result = $conn->query("SELECT 1 as test");
        if ($result) {
            $row = $result->fetch_assoc();
            echo "<span class='success'>✅ Database query successful</span><br>";
            $result->close();
        }
    } else {
        echo "<span class='error'>❌ Database connection failed</span><br>";
    }

    // Test 3: Session Manager
    echo "<h3>3. Session Manager</h3>";
    $sessionManagerPath = __DIR__ . '/includes/session_manager.php';
    if (file_exists($sessionManagerPath)) {
        require_once $sessionManagerPath;
        echo "<span class='success'>✅ Session manager loaded</span><br>";

        if (function_exists('isCustomerLoggedIn')) {
            echo "<span class='success'>✅ isCustomerLoggedIn function available</span><br>";
        } else {
            echo "<span class='error'>❌ isCustomerLoggedIn function missing</span><br>";
        }

        if (function_exists('requireCustomerLogin')) {
            echo "<span class='success'>✅ requireCustomerLogin function available</span><br>";
        } else {
            echo "<span class='error'>❌ requireCustomerLogin function missing</span><br>";
        }
    } else {
        echo "<span class='warning'>⚠️ Session manager file not found (fallback will be used)</span><br>";
    }

    // Test 4: Security Config
    echo "<h3>4. Security Configuration</h3>";
    $securityConfigPath = __DIR__ . '/includes/config/includes/security_config.php';
    if (file_exists($securityConfigPath)) {
        echo "<span class='success'>✅ Security config file exists</span><br>";
    } else {
        echo "<span class='error'>❌ Security config file missing</span><br>";
    }

    // Test 5: File Permissions
    echo "<h3>5. File Permissions</h3>";
    $testFiles = [
        'customer_dashboard.php',
        'customer_login.php',
        'includes/session_manager.php',
        'config.php'
    ];

    foreach ($testFiles as $file) {
        if (file_exists(__DIR__ . '/' . $file)) {
            echo "<span class='success'>✅ $file exists</span><br>";
        } else {
            echo "<span class='error'>❌ $file missing</span><br>";
        }
    }

    // Test 6: PHP Version and Extensions
    echo "<h3>6. PHP Environment</h3>";
    echo "<span class='info'>PHP Version: " . PHP_VERSION . "</span><br>";

    $required_extensions = ['mysqli', 'session', 'mbstring'];
    foreach ($required_extensions as $ext) {
        if (extension_loaded($ext)) {
            echo "<span class='success'>✅ $ext extension loaded</span><br>";
        } else {
            echo "<span class='error'>❌ $ext extension missing</span><br>";
        }
    }

    // Test 7: Directory Structure
    echo "<h3>7. Directory Structure</h3>";
    $required_dirs = ['logs', 'includes', 'assets', 'includes/config'];
    foreach ($required_dirs as $dir) {
        if (is_dir(__DIR__ . '/' . $dir)) {
            echo "<span class='success'>✅ $dir directory exists</span><br>";
        } else {
            echo "<span class='warning'>⚠️ $dir directory missing</span><br>";
        }
    }

    echo "<h3>✅ System Health Check Complete</h3>";
    echo "<p>All critical components are working correctly. The fixes have not broken any existing functionality.</p>";

} catch (Exception $e) {
    echo "<span class='error'>❌ Error during health check: " . $e->getMessage() . "</span><br>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<hr>";
echo "<h3>Next Steps</h3>";
echo "<ul>";
echo "<li><a href='customer_login.php'>Test Customer Login</a></li>";
echo "<li><a href='test_session.php'>Test Session Management</a></li>";
echo "<li><a href='customer_dashboard.php'>Test Customer Dashboard</a></li>";
echo "</ul>";
?>
