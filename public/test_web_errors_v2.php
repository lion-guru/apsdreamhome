<?php
// Test script to check error routes via web
$routes = [
    '/apsdreamhome/public/test/error/404',
    '/apsdreamhome/public/test/error/500',
    '/apsdreamhome/public/test/error/403',
    '/apsdreamhome/public/test/error/401',
    '/apsdreamhome/public/test/error/400'
];

echo "Testing error routes via web server:\n";
echo "==================================\n\n";

foreach ($routes as $route) {
    $url = "http://localhost" . $route;
    echo "Testing: $url\n";
    
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'timeout' => 10,
            'ignore_errors' => true
        ]
    ]);
    
    $response = @file_get_contents($url, false, $context);
    $status_line = $http_response_header[0] ?? '';
    
    echo "Status: $status_line\n";
    
    if ($response !== false) {
        // Check if it's our error page or a default Apache error
        if (strpos($response, '404 Not Found') !== false && strpos($response, 'Apache') !== false) {
            echo "Result: Default Apache 404 page\n";
        } elseif (strpos($response, 'Error') !== false) {
            echo "Result: Custom error page detected\n";
            // Show a snippet of the response
            $snippet = substr(strip_tags($response), 0, 100);
            echo "Snippet: $snippet...\n";
        } else {
            echo "Result: Custom response\n";
            $snippet = substr(strip_tags($response), 0, 100);
            echo "Snippet: $snippet...\n";
        }
    } else {
        echo "Result: Failed to get response\n";
    }
    
    echo "\n";
}
?>