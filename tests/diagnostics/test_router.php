<?php
// Test the router logic

// Simulate the request
$_SERVER['REQUEST_URI'] = '/api/test';

// Include the router logic
$request_uri = $_SERVER['REQUEST_URI'];

// Remove query string
$query_string_pos = strpos($request_uri, '?');
if ($query_string_pos !== false) {
    $request_uri = substr($request_uri, 0, $query_string_pos);
}

// Remove leading slash and any path issues
$request_uri = trim($request_uri, '/');

// Handle specific path adjustments if needed
if (strpos($request_uri, 'apsdreamhomefinal/') === 0) {
    $request_uri = str_replace('apsdreamhomefinal/', '', $request_uri);
}

// Default to home if no URI
if (empty($request_uri)) {
    $request_uri = 'home';
}

echo "Original REQUEST_URI: " . $_SERVER['REQUEST_URI'] . "\n";
echo "Processed request_uri: " . $request_uri . "\n";

// Test the routes array
$routes = [
    'api/test' => 'api/test.php',
    'api/properties' => 'api/properties.php',
];

echo "\nChecking if 'api/test' exists in routes: " . (isset($routes['api/test']) ? 'YES' : 'NO') . "\n";
echo "Route value: " . ($routes['api/test'] ?? 'NOT FOUND') . "\n";
echo "File exists: " . (file_exists('api/test.php') ? 'YES' : 'NO') . "\n";