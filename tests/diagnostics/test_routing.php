<?php
/**
 * Simple routing test
 */

// Simulate the routing logic for test/error/404
$requestedPath = 'test/error/404';

// Define routes (simplified version)
$routes = [
    'test/error/404' => 'test_error.php',
];

echo "Testing routing for: $requestedPath\n";

// Check if route exists
if (isset($routes[$requestedPath])) {
    $targetFile = $routes[$requestedPath];
    echo "Route found! Target file: $targetFile\n";
    
    if (file_exists($targetFile)) {
        echo "Target file exists!\n";
        echo "Routing successful!\n";
    } else {
        echo "ERROR: Target file does not exist!\n";
    }
} else {
    echo "ERROR: Route not found!\n";
}

echo "\nTesting file existence:\n";
echo "test_error.php exists: " . (file_exists('test_error.php') ? 'YES' : 'NO') . "\n";
echo "error_pages/404.php exists: " . (file_exists('error_pages/404.php') ? 'YES' : 'NO') . "\n";
?>