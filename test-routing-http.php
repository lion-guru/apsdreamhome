<?php

/**
 * Test the complete routing system with dispatcher
 * This script tests the routing system through HTTP requests
 */

// No config needed for this test - we'll use direct URLs

// Test configuration
$baseUrl = 'http://localhost/apsdreamhomefinal';
$testRoutes = [
    'GET' => [
        '/' => 'Home page',
        '/about' => 'About page',
        '/contact' => 'Contact page',
        '/services' => 'Services page',
        '/blog' => 'Blog page',
        '/projects' => 'Projects page',
        '/api/test' => 'API test endpoint',
        '/api/test/users' => 'API users endpoint',
        '/api/test/123' => 'API show endpoint with parameter'
    ],
    'POST' => [
        '/api/test/create' => 'API create endpoint'
    ],
    'PUT' => [
        '/api/test/update/123' => 'API update endpoint'
    ],
    'DELETE' => [
        '/api/test/delete/123' => 'API delete endpoint'
    ]
];

echo "=== APS Dream Home Routing System Test ===\n\n";

// Test GET routes
echo "Testing GET routes:\n";
echo str_repeat("-", 60) . "\n";

foreach ($testRoutes['GET'] as $route => $description) {
    $url = $baseUrl . $route;
    echo "Testing: $description\n";
    echo "URL: $url\n";
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $body = substr($response, $headerSize);
    
    curl_close($ch);
    
    echo "HTTP Code: $httpCode\n";
    
    if ($httpCode >= 200 && $httpCode < 300) {
        echo "✅ SUCCESS\n";
        
        // Check if it's JSON response
        $jsonData = json_decode($body, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            echo "Response Type: JSON\n";
            echo "Response: " . json_encode($jsonData, JSON_PRETTY_PRINT) . "\n";
        } else {
            echo "Response Type: HTML\n";
            echo "Response Length: " . strlen($body) . " characters\n";
        }
    } else {
        echo "❌ FAILED\n";
        echo "Response: " . substr($body, 0, 200) . "...\n";
    }
    
    echo str_repeat("-", 60) . "\n";
}

// Test POST route
echo "\nTesting POST route:\n";
echo str_repeat("-", 60) . "\n";

$postRoute = '/api/test/create';
$postUrl = $baseUrl . $postRoute;
echo "Testing: API create endpoint\n";
echo "URL: $postUrl\n";

$postData = json_encode(['name' => 'Test Resource']);

$ch = curl_init($postUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Content-Length: ' . strlen($postData)
]);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$body = substr($response, $headerSize);

curl_close($ch);

echo "HTTP Code: $httpCode\n";

if ($httpCode >= 200 && $httpCode < 300) {
    echo "✅ SUCCESS\n";
    $jsonData = json_decode($body, true);
    echo "Response: " . json_encode($jsonData, JSON_PRETTY_PRINT) . "\n";
} else {
    echo "❌ FAILED\n";
    echo "Response: " . $body . "\n";
}

echo str_repeat("-", 60) . "\n";

// Test error handling
echo "\nTesting error handling:\n";
echo str_repeat("-", 60) . "\n";

$errorUrl = $baseUrl . '/api/test/error';
echo "Testing: Error handling endpoint\n";
echo "URL: $errorUrl\n";

$ch = curl_init($errorUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $httpCode\n";
if ($httpCode >= 500) {
    echo "✅ Error handling working correctly (returned 5xx error)\n";
} else {
    echo "❌ Error handling may not be working correctly\n";
}

echo str_repeat("-", 60) . "\n";

// Test non-existent route (should return 404)
echo "\nTesting 404 handling:\n";
echo str_repeat("-", 60) . "\n";

$notFoundUrl = $baseUrl . '/this-route-does-not-exist';
echo "Testing: Non-existent route\n";
echo "URL: $notFoundUrl\n";

$ch = curl_init($notFoundUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $httpCode\n";
if ($httpCode === 404) {
    echo "✅ 404 handling working correctly\n";
} else {
    echo "❌ 404 handling may not be working correctly\n";
}

echo str_repeat("-", 60) . "\n";

echo "\n=== Routing System Test Complete ===\n";
echo "Check the results above to verify the enhanced routing system is working correctly.\n";