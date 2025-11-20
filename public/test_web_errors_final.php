<?php
// Test script to check error routes via web - with better error handling
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
    
    if ($response === false) {
        echo "Failed to get response\n";
        if (isset($http_response_header)) {
            echo "Headers: " . implode("\n", $http_response_header) . "\n";
        }
    } else {
        $status_line = $http_response_header[0] ?? '';
        echo "Status: $status_line\n";
        
        // Check if it's our custom error page
        if (strpos($response, 'Error 404') !== false || strpos($response, 'Page Not Found') !== false) {
            echo "Result: Custom 404 error page detected!\n";
        } elseif (strpos($response, 'Error 500') !== false || strpos($response, 'Internal Server Error') !== false) {
            echo "Result: Custom 500 error page detected!\n";
        } elseif (strpos($response, 'Error 403') !== false || strpos($response, 'Forbidden') !== false) {
            echo "Result: Custom 403 error page detected!\n";
        } elseif (strpos($response, 'Error 401') !== false || strpos($response, 'Unauthorized') !== false) {
            echo "Result: Custom 401 error page detected!\n";
        } elseif (strpos($response, 'Error 400') !== false || strpos($response, 'Bad Request') !== false) {
            echo "Result: Custom 400 error page detected!\n";
        } elseif (strpos($response, 'Apache') !== false && strpos($response, '404') !== false) {
            echo "Result: Default Apache 404 page\n";
        } elseif (strpos($response, 'Apache') !== false && strpos($response, '500') !== false) {
            echo "Result: Default Apache 500 page\n";
        } else {
            echo "Result: Custom response\n";
            $snippet = substr(strip_tags($response), 0, 200);
            echo "Snippet: $snippet...\n";
        }
    }
    
    echo "\n";
}
?>