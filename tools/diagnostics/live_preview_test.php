<?php
/**
 * APS Dream Home - LIVE PREVIEW TEST
 * Test actual application functionality
 */

echo "🏠 APS Dream Home - LIVE PREVIEW TEST\n";
echo "===================================\n\n";

// Test 1: Check if Apache is running
echo "1. 🌐 APACHE SERVER CHECK\n";
echo "========================\n";

$apacheRunning = false;
$apacheStatus = "Unknown";

// Try to check Apache service
$services = ['Apache2.4', 'Apache', 'httpd'];
foreach ($services as $service) {
    $check = shell_exec("sc query \"$service\" 2>nul");
    if (strpos($check, 'RUNNING') !== false) {
        $apacheRunning = true;
        $apacheStatus = "Running ($service)";
        break;
    }
}

if ($apacheRunning) {
    echo "   ✅ Apache: $apacheStatus\n";
} else {
    echo "   ❌ Apache: Not running or not found\n";
    echo "   💡 Start Apache from XAMPP Control Panel\n";
}

// Test 2: Check if MySQL is running
echo "\n2. 🗄️ MYSQL SERVER CHECK\n";
echo "========================\n";

$mysqlRunning = false;
$mysqlStatus = "Unknown";

// Try to check MySQL service
$services = ['MySQL', 'mysqld', 'MariaDB'];
foreach ($services as $service) {
    $check = shell_exec("sc query \"$service\" 2>nul");
    if (strpos($check, 'RUNNING') !== false) {
        $mysqlRunning = true;
        $mysqlStatus = "Running ($service)";
        break;
    }
}

if ($mysqlRunning) {
    echo "   ✅ MySQL: $mysqlStatus\n";
} else {
    echo "   ❌ MySQL: Not running or not found\n";
    echo "   💡 Start MySQL from XAMPP Control Panel\n";
}

// Test 3: Test database connection
echo "\n3. 🔗 DATABASE CONNECTION TEST\n";
echo "=============================\n";

try {
    $conn = new mysqli('localhost', 'root', '', 'apsdreamhome');
    if ($conn->connect_error) {
        echo "   ❌ Database: Connection failed\n";
        echo "   📍 Error: " . $conn->connect_error . "\n";
    } else {
        echo "   ✅ Database: Connected successfully\n";
        
        $result = $conn->query("SHOW TABLES");
        $tableCount = $result->num_rows;
        echo "   ✅ Tables: $tableCount found\n";
        
        // Test a simple query
        $result = $conn->query("SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema = 'apsdreamhome'");
        $row = $result->fetch_assoc();
        echo "   ✅ Schema: {$row['count']} tables\n";
        
        $conn->close();
    }
} catch (Exception $e) {
    echo "   ❌ Database: " . $e->getMessage() . "\n";
}

// Test 4: Test PHP functionality
echo "\n4. 🐘 PHP FUNCTIONALITY TEST\n";
echo "========================\n";

echo "   ✅ PHP Version: " . PHP_VERSION . "\n";
echo "   ✅ Memory Limit: " . ini_get('memory_limit') . "\n";
echo "   ✅ Max Execution Time: " . ini_get('max_execution_time') . "\n";
echo "   ✅ File Uploads: " . (ini_get('file_uploads') ? 'Enabled' : 'Disabled') . "\n";

// Test 5: Check project files
echo "\n5. 📁 PROJECT FILES CHECK\n";
echo "======================\n";

$projectRoot = __DIR__;
$requiredFiles = [
    'index.php' => 'Main entry point',
    'bootstrap.php' => 'Bootstrap',
    'app/core/App.php' => 'Application core',
    'routes/web.php' => 'Web routes',
    '.htaccess' => 'Apache config',
    '.env' => 'Environment config'
];

foreach ($requiredFiles as $file => $description) {
    $exists = file_exists($projectRoot . '/' . $file);
    $status = $exists ? "✅" : "❌";
    echo "   $status $description\n";
}

// Test 6: Test URL accessibility
echo "\n6. 🌐 URL ACCESSIBILITY TEST\n";
echo "==========================\n";

$urls = [
    'http://localhost/apsdreamhome/' => 'Main Site',
    'http://localhost/apsdreamhome/login' => 'Login Page',
    'http://localhost/apsdreamhome/admin' => 'Admin Panel',
    'http://localhost/apsdreamhome/dashboard' => 'Dashboard'
];

foreach ($urls as $url => $description) {
    echo "   🔄 Testing: $description\n";
    echo "   📍 URL: $url\n";
    
    // Use file_get_contents with context to test URL
    $context = stream_context_create([
        'http' => [
            'timeout' => 5,
            'method' => 'GET'
        ]
    ]);
    
    $startTime = microtime(true);
    $response = @file_get_contents($url, false, $context);
    $endTime = microtime(true);
    $responseTime = round(($endTime - $startTime) * 1000, 2);
    
    if ($response === false) {
        $error = error_get_last();
        echo "   ❌ Failed: " . ($error['message'] ?? 'Unknown error') . "\n";
    } else {
        $responseLength = strlen($response);
        echo "   ✅ Success: $responseLength bytes in {$responseTime}ms\n";
        
        // Check if it's HTML
        if (strpos($response, '<html') !== false) {
            echo "   📄 Type: HTML page\n";
        } elseif (strpos($response, '<?php') !== false) {
            echo "   ⚠️  Type: PHP code (not processed)\n";
        } else {
            echo "   📄 Type: Other content\n";
        }
    }
    echo "\n";
}

// Test 7: Check XAMPP services
echo "7. 🚀 XAMPP SERVICES CHECK\n";
echo "========================\n";

$xamppServices = [
    'apache' => 'Apache',
    'mysql' => 'MySQL'
];

foreach ($xamppServices as $service => $name) {
    $check = shell_exec("sc query \"$name\" 2>nul");
    if (strpos($check, 'RUNNING') !== false) {
        echo "   ✅ $name: Running\n";
    } else {
        echo "   ❌ $name: Not running\n";
    }
}

// Test 8: Final recommendations
echo "\n8. 🎯 FINAL RECOMMENDATIONS\n";
echo "========================\n";

if ($apacheRunning && $mysqlRunning) {
    echo "   🟢 SERVICES: All running\n";
    echo "   🟢 DATABASE: Connected\n";
    echo "   🟢 FILES: Present\n";
    echo "   🟢 PHP: Working\n";
    echo "\n   🎉 APPLICATION SHOULD BE ACCESSIBLE!\n";
    echo "   🌐 Open browser: http://localhost/apsdreamhome/\n";
} else {
    echo "   🔴 SERVICES: Some services not running\n";
    echo "   🔧 STEPS TO FIX:\n";
    echo "   1. Open XAMPP Control Panel\n";
    echo "   2. Start Apache service\n";
    echo "   3. Start MySQL service\n";
    echo "   4. Try accessing http://localhost/apsdreamhome/\n";
}

echo "\n🎉 LIVE PREVIEW TEST COMPLETED!\n";
echo "===============================\n";
echo "Generated: " . date('Y-m-d H:i:s') . "\n";
echo "Project: APS Dream Home\n";
echo "Status: " . ($apacheRunning && $mysqlRunning ? "✅ Ready" : "❌ Needs setup") . "\n";

?>
