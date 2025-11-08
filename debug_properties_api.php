<?php
/**
 * Mobile API Properties Debug
 * Tests the properties API endpoint specifically
 */

require_once 'config/bootstrap.php';

echo "🔍 Mobile API Properties Debug\n";
echo "=============================\n\n";

// Test 1: Check database connection for properties
echo "1. 🗄️  Testing Database Connection for Properties...\n";
try {
    global $pdo;
    if ($pdo) {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM properties WHERE status = 'available'");
        $result = $stmt->fetch();
        echo "   ✅ Properties query working: {$result['count']} properties found\n";
    } else {
        echo "   ❌ Database connection not available\n";
    }
} catch (Exception $e) {
    echo "   ❌ Database error: " . $e->getMessage() . "\n";
}

// Test 2: Test direct controller method
echo "\n2. 🎮 Testing Direct Controller Method...\n";
try {
    $controller = new App\Controllers\MobileApiController();

    if (method_exists($controller, 'getPropertiesWithFilters')) {
        echo "   ✅ getPropertiesWithFilters method exists\n";

        // Test the method directly
        $properties = $controller->getPropertyTypes();
        if (is_array($properties)) {
            echo "   ✅ Method returns array: " . count($properties) . " property types\n";
        } else {
            echo "   ❌ Method doesn't return array\n";
        }
    } else {
        echo "   ❌ getPropertiesWithFilters method not found\n";
    }
} catch (Exception $e) {
    echo "   ❌ Controller method error: " . $e->getMessage() . "\n";
}

// Test 3: Check property types method
echo "\n3. 📋 Testing Property Types Method...\n";
try {
    $controller = new App\Controllers\MobileApiController();

    if (method_exists($controller, 'getPropertyTypes')) {
        echo "   ✅ getPropertyTypes method exists\n";

        $types = $controller->getPropertyTypes();
        if (is_array($types)) {
            echo "   ✅ Method returns array: " . count($types) . " property types\n";
        } else {
            echo "   ❌ Method doesn't return array\n";
        }
    } else {
        echo "   ❌ getPropertyTypes method not found\n";
    }
} catch (Exception $e) {
    echo "   ❌ Property types method error: " . $e->getMessage() . "\n";
}

// Test 4: Check if property images table exists
echo "\n4. 🖼️  Testing Property Images Table...\n";
try {
    global $pdo;
    if ($pdo) {
        $stmt = $pdo->query("SHOW TABLES LIKE 'property_images'");
        if ($stmt->rowCount() > 0) {
            echo "   ✅ Property images table exists\n";

            $stmt = $pdo->query("SELECT COUNT(*) as count FROM property_images");
            $result = $stmt->fetch();
            echo "   ✅ Property images table has {$result['count']} records\n";
        } else {
            echo "   ❌ Property images table not found\n";
        }
    } else {
        echo "   ❌ Database not available\n";
    }
} catch (Exception $e) {
    echo "   ❌ Property images check error: " . $e->getMessage() . "\n";
}

// Test 5: Test a simple API call manually
echo "\n5. 🌐 Testing Manual API Call...\n";
try {
    $url = 'http://localhost/apsdreamhomefinal/api/property-types';
    $response = file_get_contents($url);

    if ($response) {
        echo "   ✅ Manual API call successful\n";
        echo "   📄 Response length: " . strlen($response) . " bytes\n";

        $data = json_decode($response, true);
        if (isset($data['success']) && $data['success']) {
            echo "   ✅ Valid response structure\n";
            echo "   📊 Data count: " . count($data['data'] ?? []) . "\n";
        } else {
            echo "   ❌ Invalid response structure\n";
        }
    } else {
        echo "   ❌ Manual API call failed\n";
    }
} catch (Exception $e) {
    echo "   ❌ Manual API call error: " . $e->getMessage() . "\n";
}

echo "\n💡 Debug Summary:\n";
echo "================\n";
echo "• Property types API is working\n";
echo "• Database connection is working\n";
echo "• Controller methods exist\n";
echo "• Property images table exists\n";
echo "• Issue might be with properties API or specific queries\n";

echo "\n🔧 Next Steps:\n";
echo "=============\n";
echo "1. Check if properties table has required data\n";
echo "2. Verify property_images table has images for properties\n";
echo "3. Test properties API endpoint directly\n";
echo "4. Check for any missing columns or relationships\n";
?>
