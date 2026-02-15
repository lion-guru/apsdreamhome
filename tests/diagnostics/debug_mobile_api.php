<?php
/**
 * Simple Mobile API Debug Test
 * Tests basic connectivity to mobile API endpoints
 */

$base_url = 'http://localhost/apsdreamhomefinal';

echo "🔍 Mobile API Debug Test\n";
echo "=======================\n\n";

// Test 1: Basic connectivity
echo "1. 🌐 Testing Basic Connectivity...\n";
try {
    $url = $base_url . '/api/property-types';
    $response = file_get_contents($url);

    if ($response !== false) {
        echo "   ✅ Server responding: " . strlen($response) . " bytes\n";
        echo "   📄 Response preview: " . substr($response, 0, 100) . "...\n";
    } else {
        echo "   ❌ No response from server\n";
    }
} catch (Exception $e) {
    echo "   ❌ Connection error: " . $e->getMessage() . "\n";
}

// Test 2: Check if API routes are registered
echo "\n2. 🛣️  Testing Route Registration...\n";
try {
    // Check if we can access the router
    $router_file = __DIR__ . '/app/core/Router.php';
    if (file_exists($router_file)) {
        echo "   ✅ Router file exists\n";

        $router_content = file_get_contents($router_file);
        if (strpos($router_content, 'MobileApiController') !== false) {
            echo "   ✅ MobileApiController routes registered\n";
        } else {
            echo "   ❌ MobileApiController routes not found in router\n";
        }
    } else {
        echo "   ❌ Router file not found\n";
    }
} catch (Exception $e) {
    echo "   ❌ Router check error: " . $e->getMessage() . "\n";
}

// Test 3: Check controller file
echo "\n3. 🎮 Testing Controller File...\n";
try {
    $controller_file = __DIR__ . '/app/controllers/MobileApiController.php';
    if (file_exists($controller_file)) {
        echo "   ✅ MobileApiController.php exists\n";

        $controller_content = file_get_contents($controller_file);
        if (strpos($controller_content, 'class MobileApiController') !== false) {
            echo "   ✅ MobileApiController class defined\n";
        } else {
            echo "   ❌ MobileApiController class not found\n";
        }
    } else {
        echo "   ❌ MobileApiController.php not found\n";
    }
} catch (Exception $e) {
    echo "   ❌ Controller check error: " . $e->getMessage() . "\n";
}

// Test 4: Test direct controller instantiation
echo "\n4. ⚙️  Testing Direct Controller Access...\n";
try {
    require_once __DIR__ . '/config/bootstrap.php';

    if (class_exists('App\Controllers\MobileApiController')) {
        echo "   ✅ MobileApiController class available\n";

        $controller = new App\Controllers\MobileApiController();
        echo "   ✅ MobileApiController instantiated\n";

        // Test a simple method
        if (method_exists($controller, 'propertyTypes')) {
            echo "   ✅ propertyTypes method exists\n";
        } else {
            echo "   ❌ propertyTypes method not found\n";
        }
    } else {
        echo "   ❌ MobileApiController class not found after bootstrap\n";
    }
} catch (Exception $e) {
    echo "   ❌ Controller instantiation error: " . $e->getMessage() . "\n";
}

// Test 5: Check URL rewriting
echo "\n5. 🔄 Testing URL Rewriting...\n";
try {
    $test_urls = [
        $base_url . '/api/property-types',
        $base_url . '/api/properties',
        $base_url . '/api/cities'
    ];

    foreach ($test_urls as $url) {
        $response = @file_get_contents($url);
        if ($response !== false) {
            $data = json_decode($response, true);
            if (isset($data['success'])) {
                echo "   ✅ {$url} - Working\n";
            } else {
                echo "   ⚠️  {$url} - Response received but not success\n";
            }
        } else {
            echo "   ❌ {$url} - No response\n";
        }
    }
} catch (Exception $e) {
    echo "   ❌ URL test error: " . $e->getMessage() . "\n";
}

echo "\n📋 Debug Summary:\n";
echo "================\n";
echo "• Check if .htaccess is properly configured\n";
echo "• Verify Apache mod_rewrite is enabled\n";
echo "• Check if PHP files are being executed\n";
echo "• Ensure database connection is working\n";
echo "• Verify autoloader is functioning\n";

echo "\n🔧 Troubleshooting Steps:\n";
echo "========================\n";
echo "1. Check .htaccess file for URL rewriting\n";
echo "2. Verify Apache configuration\n";
echo "3. Test basic PHP execution\n";
echo "4. Check error logs\n";
echo "5. Test database connectivity\n";
?>
