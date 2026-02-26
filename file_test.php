<?php
// Simple test to check what files are actually being loaded
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "🧪 File Loading Test\n";
echo "===================\n";

// Test 1: Check if files exist
$files = [
    'bootstrap/app.php',
    'app/Core/App.php',
    'app/Http/Request.php',
    'app/Core/Routing/Router.php'
];

echo "File existence check:\n";
foreach ($files as $file) {
    if (file_exists($file)) {
        echo "✅ $file\n";
    } else {
        echo "❌ $file\n";
    }
}

echo "\n";

// Test 2: Try to load bootstrap
try {
    echo "Loading bootstrap...\n";
    require_once 'bootstrap/app.php';
    echo "✅ Bootstrap loaded\n";
} catch (Error $e) {
    echo "❌ Bootstrap error: " . $e->getMessage() . "\n";
}

// Test 3: Check available classes
echo "\nAvailable classes after bootstrap:\n";
$classes = get_declared_classes();
foreach ($classes as $class) {
    if (strpos($class, 'App\\') === 0) {
        echo "  - $class\n";
    }
}

echo "\n🎯 Test Complete!\n";
?>
