<?php
/**
 * Properties API Debug Test
 * Tests the properties API endpoint in isolation
 */

require_once 'config/bootstrap.php';

echo "🔍 Properties API Debug Test\n";
echo "============================\n\n";

// Test 1: Check database connection
echo "1. 🗄️  Testing Database Connection...\n";
try {
    global $pdo;
    if ($pdo) {
        echo "   ✅ Database connection successful\n";

        // Check if properties table exists
        $stmt = $pdo->query("SHOW TABLES LIKE 'properties'");
        if ($stmt->rowCount() > 0) {
            echo "   ✅ Properties table exists\n";

            // Check property count
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM properties WHERE status = 'available'");
            $result = $stmt->fetch();
            echo "   ✅ Properties count: {$result['count']} available\n";
        } else {
            echo "   ❌ Properties table not found\n";
        }
    } else {
        echo "   ❌ Database connection not available\n";
    }
} catch (Exception $e) {
    echo "   ❌ Database error: " . $e->getMessage() . "\n";
}

// Test 2: Test direct API call
echo "\n2. 🌐 Testing Direct API Call...\n";
try {
    $url = 'http://localhost/apsdreamhomefinal/api/properties?page=1&limit=5';
    $response = file_get_contents($url);

    if ($response) {
        echo "   ✅ API responded: " . strlen($response) . " bytes\n";

        $data = json_decode($response, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            echo "   ✅ Valid JSON response\n";

            if (isset($data['success']) && $data['success']) {
                echo "   ✅ API success response\n";
                echo "   📊 Properties returned: " . count($data['data']['properties'] ?? []) . "\n";
                echo "   📄 Pagination: " . ($data['data']['pagination']['total_count'] ?? 0) . " total\n";
            } else {
                echo "   ❌ API error response: " . ($data['error'] ?? 'Unknown error') . "\n";
            }
        } else {
            echo "   ❌ Invalid JSON: " . json_last_error_msg() . "\n";
        }
    } else {
        echo "   ❌ No response from API\n";
    }
} catch (Exception $e) {
    echo "   ❌ API call error: " . $e->getMessage() . "\n";
}

// Test 3: Check API endpoint response headers
echo "\n3. 📋 Testing API Response Headers...\n";
try {
    $url = 'http://localhost/apsdreamhomefinal/api/properties';
    $headers = get_headers($url, 1);

    if ($headers) {
        echo "   ✅ Headers received\n";

        $content_type = $headers['Content-Type'] ?? 'Not set';
        echo "   📄 Content-Type: $content_type\n";

        $cors_origin = $headers['Access-Control-Allow-Origin'] ?? 'Not set';
        echo "   🌐 CORS Origin: $cors_origin\n";

        $http_code = $headers[0] ?? 'Unknown';
        echo "   📊 HTTP Code: $http_code\n";
    } else {
        echo "   ❌ No headers received\n";
    }
} catch (Exception $e) {
    echo "   ❌ Headers test error: " . $e->getMessage() . "\n";
}

// Test 4: Check controller method directly
echo "\n4. 🎮 Testing Controller Method Directly...\n";
try {
    $controller = new App\Controllers\MobileApiController();

    if (method_exists($controller, 'getPropertiesWithFilters')) {
        echo "   ✅ getPropertiesWithFilters method exists\n";

        // Test with empty filters
        $properties = $controller->getPropertiesWithFilters([], 5, 0);
        if (is_array($properties)) {
            echo "   ✅ Method returns array: " . count($properties) . " properties\n";
        } else {
            echo "   ❌ Method doesn't return array\n";
        }
    } else {
        echo "   ❌ getPropertiesWithFilters method not found\n";
    }
} catch (Exception $e) {
    echo "   ❌ Controller test error: " . $e->getMessage() . "\n";
}

echo "\n💡 Debug Summary:\n";
echo "================\n";
echo "• Check if the properties API endpoint is being called correctly\n";
echo "• Verify database table structure matches API expectations\n";
echo "• Check if there are any missing columns or relationships\n";
echo "• Test with different parameters\n";
echo "• Check error logs for more details\n";

echo "\n🔧 Troubleshooting Steps:\n";
echo "========================\n";
echo "1. Check .htaccess rewrite rules\n";
echo "2. Verify database table structure\n";
echo "3. Test with different query parameters\n";
echo "4. Check PHP error logs\n";
echo "5. Verify controller method signatures\n";
?>
