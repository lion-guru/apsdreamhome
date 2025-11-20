<?php
// Test script to check error routes via web - with full response
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
        echo "Full Response:\n";
        echo "------------\n";
        echo $response;
        echo "\n------------\n";
    } else {
        echo "Result: Failed to get response\n";
    }
    
    echo "\n";
}
?>