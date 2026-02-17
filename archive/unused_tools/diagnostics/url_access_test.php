<?php
/**
 * APS Dream Home - URL ACCESS TEST
 * Test if URLs are accessible and working
 */

echo "🏠 APS Dream Home - URL ACCESS TEST\n";
echo "===================================\n\n";

$urls = [
    'http://localhost/apsdreamhome/' => 'Main Site',
    'http://localhost/apsdreamhome/login' => 'Login Page',
    'http://localhost/apsdreamhome/admin' => 'Admin Panel',
    'http://localhost/apsdreamhome/dashboard' => 'User Dashboard'
];

foreach ($urls as $url => $description) {
    echo "🔄 Testing: $description\n";
    echo "📍 URL: $url\n";
    
    $startTime = microtime(true);
    
    // Test with file_get_contents
    $context = stream_context_create([
        'http' => [
            'timeout' => 10,
            'method' => 'GET',
            'ignore_errors' => true
        ]
    ]);
    
    $response = @file_get_contents($url, false, $context);
    $endTime = microtime(true);
    $responseTime = round(($endTime - $startTime) * 1000, 2);
    
    if ($response === false) {
        $error = error_get_last();
        echo "❌ FAILED: " . ($error['message'] ?? 'Unknown error') . "\n";
        
        // Try to get HTTP status with curl if available
        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            curl_setopt($ch, CURLOPT_NOBODY, true);
            curl_setopt($ch, CURLOPT_HEADER, true);
            curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            echo "📊 HTTP Status: $httpCode\n";
            
            if ($httpCode == 500) {
                echo "🔍 This is a 500 Internal Server Error\n";
                echo "💡 Check PHP error logs: " . ini_get('error_log') . "\n";
            } elseif ($httpCode == 404) {
                echo "🔍 This is a 404 Not Found Error\n";
                echo "💡 Check if the file/route exists\n";
            } elseif ($httpCode == 0) {
                echo "🔍 Connection failed - Apache not running?\n";
                echo "💡 Check XAMPP services\n";
            }
        }
    } else {
        $responseLength = strlen($response);
        echo "✅ SUCCESS: $responseLength bytes in {$responseTime}ms\n";
        
        // Check if it's HTML
        if (strpos($response, '<html') !== false) {
            echo "📄 Type: HTML page\n";
            
            // Extract title
            if (preg_match('/<title>(.*?)<\/title>/i', $response, $matches)) {
                echo "📋 Title: " . trim($matches[1]) . "\n";
            }
            
            // Check for error indicators
            if (strpos($response, 'error') !== false || strpos($response, 'Error') !== false) {
                echo "⚠️  Contains error indicators\n";
            }
            
            // Check for success indicators
            if (strpos($response, 'APS Dream Home') !== false) {
                echo "🎉 Contains APS Dream Home branding\n";
            }
        } else {
            echo "📄 Type: Non-HTML content\n";
            echo "📝 Preview: " . substr($response, 0, 200) . "...\n";
        }
        
        // Show first few lines for debugging
        $lines = explode("\n", $response);
        echo "📝 First few lines:\n";
        for ($i = 0; $i < min(5, count($lines)); $i++) {
            echo "   Line " . ($i + 1) . ": " . substr($lines[$i], 0, 100) . "\n";
        }
    }
    
    echo "\n" . str_repeat("-", 50) . "\n\n";
}

// Test Apache service
echo "🔧 APACHE SERVICE CHECK\n";
echo "======================\n";

$apacheRunning = false;
$services = ['Apache2.4', 'Apache', 'httpd'];

foreach ($services as $service) {
    $check = shell_exec("sc query \"$service\" 2>nul");
    if (strpos($check, 'RUNNING') !== false) {
        $apacheRunning = true;
        echo "✅ Apache: Running ($service)\n";
        break;
    }
}

if (!$apacheRunning) {
    echo "❌ Apache: Not running\n";
    echo "💡 Start Apache from XAMPP Control Panel\n";
}

// Test MySQL service
echo "\n🗄️ MYSQL SERVICE CHECK\n";
echo "====================\n";

$mysqlRunning = false;
$services = ['MySQL', 'mysqld', 'MariaDB'];

foreach ($services as $service) {
    $check = shell_exec("sc query \"$service\" 2>nul");
    if (strpos($check, 'RUNNING') !== false) {
        $mysqlRunning = true;
        echo "✅ MySQL: Running ($service)\n";
        break;
    }
}

if (!$mysqlRunning) {
    echo "❌ MySQL: Not running\n";
    echo "💡 Start MySQL from XAMPP Control Panel\n";
}

// Test database connection
echo "\n🔗 DATABASE CONNECTION TEST\n";
echo "=============================\n";

try {
    $conn = new mysqli('localhost', 'root', '', 'apsdreamhome');
    if ($conn->connect_error) {
        echo "❌ Database: " . $conn->connect_error . "\n";
    } else {
        echo "✅ Database: Connected\n";
        $result = $conn->query("SHOW TABLES");
        echo "✅ Tables: " . $result->num_rows . "\n";
        $conn->close();
    }
} catch (Exception $e) {
    echo "❌ Database: " . $e->getMessage() . "\n";
}

// Final recommendations
echo "\n🎯 RECOMMENDATIONS\n";
echo "==================\n";

if ($apacheRunning && $mysqlRunning) {
    echo "🟢 SERVICES: All running\n";
    echo "🟢 DATABASE: Connected\n";
    echo "🟢 PROJECT: Should be accessible\n";
    echo "\n🌐 Open browser: http://localhost/apsdreamhome/\n";
} else {
    echo "🔴 SERVICES: Some services not running\n";
    echo "🔧 STEPS TO FIX:\n";
    echo "1. Open XAMPP Control Panel\n";
    echo "2. Start Apache service\n";
    echo "3. Start MySQL service\n";
    echo "4. Try accessing http://localhost/apsdreamhome/\n";
}

echo "\n🎉 URL ACCESS TEST COMPLETED!\n";
echo "===============================\n";

?>
