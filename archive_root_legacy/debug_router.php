<?php
// Simple debug test for router
echo "Debug Router Test\n";

// Simulate the router.php logic
$request_uri = 'api/test';
$routes = array(
    'api/test' => 'api/test.php',
    'api/properties' => 'api/properties.php',
    'api/bookings' => 'api/bookings.php',
    'api/messages' => 'api/messages.php'
);

echo "Request URI: " . $request_uri . "\n";
echo "Routes: " . print_r($routes, true) . "\n";

// Test if route exists
if (isset($routes[$request_uri])) {
    echo "Route found: " . $routes[$request_uri] . "\n";
    if (file_exists($routes[$request_uri])) {
        echo "File exists: " . $routes[$request_uri] . "\n";
    } else {
        echo "File does NOT exist: " . $routes[$request_uri] . "\n";
    }
} else {
    echo "Route NOT found in routes array\n";
}

// Test the actual enhancedAutoRouting function
function enhancedAutoRouting($request_uri, $routes) {
    echo "DEBUG: enhancedAutoRouting called with: " . $request_uri . "\n";
    // First check explicit routes
    if (isset($routes[$request_uri])) {
        $route = $routes[$request_uri];
        echo "DEBUG: Found explicit route: " . $route . "\n";
        if (file_exists($route)) {
            echo "DEBUG: Route file exists: " . $route . "\n";
            return $route;
        } else {
            echo "DEBUG: Route file does not exist: " . $route . "\n";
        }
    } else {
        echo "DEBUG: No explicit route found for: " . $request_uri . "\n";
    }
    return false;
}

$result = enhancedAutoRouting($request_uri, $routes);
echo "Result: " . $result . "\n";