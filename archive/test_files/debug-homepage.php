<?php

/**
 * APS Dream Home - Home Page Debug
 * Debug home page routing and rendering issues
 */

echo "=== APS Dream Home - Home Page Debug ===\n\n";

echo "🔍 Debugging home page issues...\n\n";

// Test 1: Check if HomeController exists
$homeController = __DIR__ . '/app/Http/Controllers/HomeController.php';
echo "1. 📋 HomeController Check:\n";
if (file_exists($homeController)) {
    echo "   ✅ HomeController.php exists\n";
} else {
    echo "   ❌ HomeController.php not found\n";
}

// Test 2: Check if home view exists
$homeView = __DIR__ . '/app/views/home/index.php';
echo "\n2. 📋 Home View Check:\n";
if (file_exists($homeView)) {
    echo "   ✅ home/index.php exists\n";
} else {
    echo "   ❌ home/index.php not found\n";
}

// Test 3: Check if layout exists
$layoutView = __DIR__ . '/app/views/layouts/base.php';
echo "\n3. 📋 Layout Check:\n";
if (file_exists($layoutView)) {
    echo "   ✅ layouts/base.php exists\n";
} else {
    echo "   ❌ layouts/base.php not found\n";
}

// Test 4: Simulate home page request
echo "\n4. 🚀 Simulating Home Page Request:\n";

// Simulate the request
$_SERVER["REQUEST_URI"] = "/";
$_SERVER["REQUEST_METHOD"] = "GET";

try {
    // Load App class
    require_once __DIR__ . '/app/core/App.php';
    
    // Initialize app
    $app = \App\Core\App::getInstance(__DIR__);
    
    // Handle request
    $response = $app->handle();
    
    if ($response) {
        echo "   ✅ App handled request successfully\n";
        echo "   📄 Response length: " . strlen($response) . " characters\n";
        
        // Check if response contains expected content
        if (strpos($response, 'APS Dream Home') !== false) {
            echo "   ✅ Response contains 'APS Dream Home'\n";
        } else {
            echo "   ⚠️ Response missing 'APS Dream Home'\n";
        }
        
        if (strpos($response, '<html') !== false) {
            echo "   ✅ Response contains HTML\n";
        } else {
            echo "   ⚠️ Response missing HTML\n";
        }
    } else {
        echo "   ❌ App returned empty response\n";
    }
    
} catch (Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
    echo "   📍 File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

// Test 5: Check BaseController
$baseController = __DIR__ . '/app/Http/Controllers/BaseController.php';
echo "\n5. 📋 BaseController Check:\n";
if (file_exists($baseController)) {
    echo "   ✅ BaseController.php exists\n";
} else {
    echo "   ❌ BaseController.php not found\n";
}

// Test 6: Check View class
$viewClass = __DIR__ . '/app/core/View/View.php';
echo "\n6. 📋 View Class Check:\n";
if (file_exists($viewClass)) {
    echo "   ✅ View/View.php exists\n";
} else {
    echo "   ❌ View/View.php not found\n";
}

echo "\n📊 DEBUG SUMMARY:\n";
echo str_repeat("=", 50) . "\n";

echo "🎯 POTENTIAL ISSUES:\n";
echo "1. Missing view files\n";
echo "2. Missing layout files\n";
echo "3. BaseController issues\n";
echo "4. View class issues\n";
echo "5. Routing problems\n\n";

echo "💡 SOLUTIONS:\n";
echo "1. Check if all required files exist\n";
echo "2. Verify BaseController functionality\n";
echo "3. Check View class implementation\n";
echo "4. Test routing logic\n";
echo "5. Check error logs for specific errors\n\n";

echo "🔧 NEXT STEPS:\n";
echo "1. Open browser: http://localhost/apsdreamhome/\n";
echo "2. Check browser console for errors\n";
echo "3. Check XAMPP error logs\n";
echo "4. Test with different URLs\n";
echo "5. Check Apache/Nginx configuration\n\n";

echo "✨ DEBUG COMPLETE! ✨\n";
?>
