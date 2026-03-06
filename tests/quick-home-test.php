<?php

/**
 * APS Dream Home - Quick Home Page Test
 * Quick test to identify home page issues
 */

echo "=== APS Dream Home - Quick Home Page Test ===\n\n";

// Test basic file existence
echo "📁 FILE CHECKS:\n";

$files = [
    'HomeController' => '/app/Http/Controllers/HomeController.php',
    'BaseController' => '/app/Http/Controllers/BaseController.php',
    'Home View' => '/app/views/home/index.php',
    'Base Layout' => '/app/views/layouts/base.php',
    'View Class' => '/app/Core/View/View.php'
];

foreach ($files as $name => $path) {
    $fullPath = __DIR__ . $path;
    $status = file_exists($fullPath) ? '✅' : '❌';
    echo "   $status $name: $path\n";
}

echo "\n🔍 SIMPLE TEST:\n";

// Test if we can include HomeController
try {
    require_once __DIR__ . '/app/Http/Controllers/BaseController.php';
    echo "   ✅ BaseController loaded\n";
} catch (Exception $e) {
    echo "   ❌ BaseController error: " . $e->getMessage() . "\n";
}

try {
    require_once __DIR__ . '/app/Http/Controllers/HomeController.php';
    echo "   ✅ HomeController loaded\n";
} catch (Exception $e) {
    echo "   ❌ HomeController error: " . $e->getMessage() . "\n";
}

// Test if we can create HomeController instance
try {
    $controller = new \App\Http\Controllers\HomeController();
    echo "   ✅ HomeController instance created\n";
} catch (Exception $e) {
    echo "   ❌ HomeController instance error: " . $e->getMessage() . "\n";
}

// Test if we can call index method
try {
    $result = $controller->index();
    echo "   ✅ HomeController::index() called\n";
    if ($result) {
        echo "   📄 Method returned result\n";
    } else {
        echo "   ⚠️ Method returned null\n";
    }
} catch (Exception $e) {
    echo "   ❌ HomeController::index() error: " . $e->getMessage() . "\n";
    echo "   📍 File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "\n💡 COMMON HOME PAGE ISSUES:\n";
echo "1. Missing BaseController methods\n";
echo "2. Missing View class or render method\n";
echo "3. Missing layout files\n";
echo "4. Missing CSS/JS assets\n";
echo "5. Database connection issues\n";

echo "\n🔧 QUICK FIXES:\n";
echo "1. Check browser console for JavaScript errors\n";
echo "2. Check network tab for failed requests\n";
echo "3. Check Apache error logs\n";
echo "4. Try accessing: http://localhost/apsdreamhome/index.php\n";
echo "5. Check if XAMPP Apache is running\n";

echo "\n✨ TEST COMPLETE! ✨\n";
?>
