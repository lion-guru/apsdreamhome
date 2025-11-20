<?php
// Test script to capture the full error response
$url = "http://localhost/apsdreamhome/public/test/error/404";

echo "Testing URL: $url\n\n";

// Create a stream context to capture the full response
$context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'ignore_errors' => true, // Don't fail on HTTP errors
        'timeout' => 30
    ]
]);

try {
    $response = file_get_contents($url, false, $context);
    
    // Get the HTTP response code
    $httpCode = null;
    if (isset($http_response_header)) {
        foreach ($http_response_header as $header) {
            if (preg_match('/HTTP\/\d\.\d (\d+)/', $header, $matches)) {
                $httpCode = $matches[1];
                break;
            }
        }
    }
    
    echo "HTTP Response Code: $httpCode\n";
    echo "Response Length: " . strlen($response) . " bytes\n\n";
    echo "Response Content:\n";
    echo "================\n";
    echo $response;
    echo "\n================\n";
    
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}

echo "\nDone.\n";
?>