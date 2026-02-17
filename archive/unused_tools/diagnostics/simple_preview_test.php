<?php
/**
 * APS Dream Home - SIMPLE PREVIEW TEST
 * Test if application is working
 */

echo "🏠 APS Dream Home - SIMPLE PREVIEW TEST\n";
echo "====================================\n\n";

// Test 1: Check if we can access the main site
echo "1. 🌐 TESTING MAIN SITE\n";
echo "=====================\n";

$url = 'http://localhost/apsdreamhome/';
$context = stream_context_create([
    'http' => [
        'timeout' => 10,
        'method' => 'GET'
    ]
]);

echo "   🔄 Testing: $url\n";

$startTime = microtime(true);
$response = @file_get_contents($url, false, $context);
$endTime = microtime(true);
$responseTime = round(($endTime - $startTime) * 1000, 2);

if ($response === false) {
    $error = error_get_last();
    echo "   ❌ Failed: " . ($error['message'] ?? 'Unknown error') . "\n";
    
    // Check if it's a 500 error
    if (strpos($error['message'], '500') !== false) {
        echo "   🔍 This is a 500 Internal Server Error\n";
        echo "   💡 Check PHP error logs\n";
    }
} else {
    $responseLength = strlen($response);
    echo "   ✅ Success: $responseLength bytes in {$responseTime}ms\n";
    
    // Check response content
    if (strpos($response, '<html') !== false) {
        echo "   📄 Type: HTML page\n";
        
        // Check for common HTML elements
        if (strpos($response, '<title>') !== false) {
            preg_match('/<title>(.*?)<\/title>/i', $response, $matches);
            if (isset($matches[1])) {
                echo "   📋 Title: " . trim($matches[1]) . "\n";
            }
        }
        
        // Check for error messages
        if (strpos($response, 'error') !== false || strpos($response, 'Error') !== false) {
            echo "   ⚠️  Possible error in response\n";
        }
        
        // Check if it's a login page
        if (strpos($response, 'login') !== false || strpos($response, 'Login') !== false) {
            echo "   🔐 Contains login elements\n";
        }
        
        // Check if it's an admin panel
        if (strpos($response, 'admin') !== false || strpos($response, 'Admin') !== false) {
            echo "   🎛️  Contains admin elements\n";
        }
    } else {
        echo "   📄 Type: Non-HTML content\n";
        // Show first 200 characters
        echo "   📝 Preview: " . substr($response, 0, 200) . "...\n";
    }
}

// Test 2: Check database connection
echo "\n2. 🗄️ DATABASE CONNECTION\n";
echo "======================\n";

try {
    $conn = new mysqli('localhost', 'root', '', 'apsdreamhome');
    if ($conn->connect_error) {
        echo "   ❌ Database: " . $conn->connect_error . "\n";
    } else {
        echo "   ✅ Database: Connected\n";
        $result = $conn->query("SHOW TABLES");
        echo "   ✅ Tables: " . $result->num_rows . "\n";
        $conn->close();
    }
} catch (Exception $e) {
    echo "   ❌ Database: " . $e->getMessage() . "\n";
}

// Test 3: Check if key files exist
echo "\n3. 📁 KEY FILES CHECK\n";
echo "===================\n";

$keyFiles = [
    'index.php' => 'Main entry point',
    '.htaccess' => 'Apache config',
    '.env' => 'Environment config'
];

foreach ($keyFiles as $file => $description) {
    $exists = file_exists($file);
    $status = $exists ? "✅" : "❌";
    echo "   $status $description\n";
}

// Test 4: Check PHP error log
echo "\n4. 📋 PHP ERROR LOG\n";
echo "==================\n";

$errorLog = ini_get('error_log');
if ($errorLog && file_exists($errorLog)) {
    $errors = file_get_contents($errorLog);
    $recentErrors = substr($errors, -1000);
    echo "   📄 Error Log: $errorLog\n";
    echo "   📝 Recent errors:\n";
    echo "   " . substr($recentErrors, -200) . "\n";
} else {
    echo "   ℹ️  No error log found\n";
}

// Test 5: Recommendations
echo "\n5. 🎯 RECOMMENDATIONS\n";
echo "==================\n";

if ($response !== false) {
    echo "   🟢 Main site is accessible\n";
    echo "   🌐 Open browser: http://localhost/apsdreamhome/\n";
    echo "   🎉 Application is working!\n";
} else {
    echo "   🔴 Main site not accessible\n";
    echo "   🔧 Check XAMPP services\n";
    echo "   🔧 Start Apache and MySQL\n";
    echo "   🔧 Check .htaccess configuration\n";
    echo "   🔧 Check PHP error logs\n";
}

echo "\n🎉 SIMPLE PREVIEW TEST COMPLETED!\n";
echo "================================\n";
echo "Status: " . ($response !== false ? "✅ Working" : "❌ Needs setup") . "\n";

?>
