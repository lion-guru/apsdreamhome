<?php
/**
 * Test web server error pages via PHP
 */

echo "Testing web server error pages...\n\n";

// Test error pages via web server
$testUrls = [
    'http://localhost/test/error/404',
    'http://localhost/test/error/500',
    'http://localhost/test/error/403',
    'http://localhost/test/error/401',
    'http://localhost/test/error/400',
    'http://localhost/test/error/generic',
];

foreach ($testUrls as $url) {
    echo "Testing: $url\n";
    
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'timeout' => 10,
            'ignore_errors' => true,
        ]
    ]);
    
    $response = @file_get_contents($url, false, $context);
    $httpCode = null;
    
    if (isset($http_response_header)) {
        foreach ($http_response_header as $header) {
            if (preg_match('/HTTP\/\d\.\d (\d{3})/', $header, $matches)) {
                $httpCode = $matches[1];
                break;
            }
        }
    }
    
    if ($response === false) {
        echo "  ✗ Failed to connect (server might be down)\n";
    } else {
        echo "  HTTP Code: " . ($httpCode ?? 'unknown') . "\n";
        echo "  Response length: " . strlen($response) . " bytes\n";
        
        // Check if response contains expected content
        if (strpos($response, 'Error') !== false || strpos($response, 'error') !== false) {
            echo "  ✓ Contains error-related content\n";
        } else {
            echo "  ? No obvious error content found\n";
        }
        
        // Check if response uses modern layout
        if (strpos($response, 'modern.php') !== false || strpos($response, 'layout') !== false) {
            echo "  ✓ Uses layout system\n";
        } else {
            echo "  ? Layout system not detected\n";
        }
    }
    
    echo "\n";
}

echo "Web server test completed.\n";