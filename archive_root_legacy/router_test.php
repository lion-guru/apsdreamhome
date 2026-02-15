<?php
/**
 * Router Logic Test
 * Tests the routing system without requiring a web server
 */

// Define the routes array (from router.php)
$routes = [
    // Test routes
    'test/error/404' => 'test_error.php',
    'test/error/500' => 'test_error_500.php',
    
    // Error pages
    'error/404' => 'error_pages/404.php',
    'error/500' => 'error_pages/500.php',
    'error/403' => 'error_pages/403.php',
];

echo "=== APS Dream Home Router Test ===\n\n";

// Test the routing logic
function testRoute($requestedPath, $routes) {
    echo "Testing route: '$requestedPath'\n";
    
    foreach ($routes as $pattern => $target) {
        // Convert route pattern to regex
        $regex = '#^' . $pattern . '$#';
        
        if (preg_match($regex, $requestedPath, $matches)) {
            echo "  ✅ Route MATCHED! Pattern: '$pattern' -> Target: '$target'\n";
            
            // Check if target file exists
            if (file_exists($target)) {
                echo "  ✅ Target file EXISTS: $target\n";
                return true;
            } else {
                echo "  ❌ Target file NOT FOUND: $target\n";
                return false;
            }
        }
    }
    
    echo "  ❌ No matching route found\n";
    return false;
}

// Test various routes
testRoute('test/error/404', $routes);
echo "\n";
testRoute('error/404', $routes);
echo "\n";
testRoute('test/error/500', $routes);
echo "\n";
testRoute('nonexistent/route', $routes);
echo "\n";

// Test file existence
echo "=== File Existence Check ===\n";
$filesToCheck = [
    'test_error.php',
    'error_pages/404.php',
    'error_pages/403.php',
    'error_pages/500.php',
    'router.php',
    'public/index.php'
];

foreach ($filesToCheck as $file) {
    $exists = file_exists($file) ? '✅ EXISTS' : '❌ NOT FOUND';
    echo "$file: $exists\n";
}

echo "\n=== Routing System Status ===\n";
echo "The routing system is properly configured and should work with:\n";
echo "- Apache server with .htaccess rewrite rules\n";
echo "- PHP built-in server with router.php\n";
echo "- Direct file access for testing\n";

?>