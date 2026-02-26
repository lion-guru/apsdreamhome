<?php
// Check if routes are actually being loaded
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "🧪 Route Loading Debug\n";
echo "=====================\n";

// Check if routes file exists
if (file_exists('routes/modern.php')) {
    echo "✅ routes/modern.php exists\n";
    
    // Check file size
    $size = filesize('routes/modern.php');
    echo "File size: $size bytes\n";
    
    if ($size > 0) {
        echo "✅ File is not empty\n";
        
        // Check if it contains our homepage route
        $content = file_get_contents('routes/modern.php');
        if (strpos($content, 'APS Dream Home') !== false) {
            echo "✅ Homepage route content found in file\n";
        } else {
            echo "❌ Homepage route content not found\n";
        }
        
        if (strpos($content, "router->get('/',") !== false) {
            echo "✅ Homepage route definition found\n";
        } else {
            echo "❌ Homepage route definition not found\n";
        }
    } else {
        echo "❌ File is empty\n";
    }
} else {
    echo "❌ routes/modern.php does not exist\n";
}

echo "\n";

// Test loading routes
try {
    echo "Loading bootstrap...\n";
    require_once 'bootstrap/app.php';
    
    echo "Getting app instance...\n";
    $app = \App\Core\App::getInstance();
    
    echo "Getting router...\n";
    $router = $app->router();
    
    echo "Router class: " . get_class($router) . "\n";
    
    // Try to manually add a test route
    echo "Adding test route...\n";
    $router->get('/test', function() {
        return 'Test route works!';
    });
    
    echo "✅ Routes loaded successfully\n";
    
} catch (Error $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "❌ EXCEPTION: " . $e->getMessage() . "\n";
}

echo "\n🎯 Debug Complete!\n";
?>
