<?php
/**
 * Test error pages via web server after route fix
 * This script tests if the error pages are accessible via HTTP requests
 */

// Set HTTP_HOST to avoid warnings
$_SERVER['HTTP_HOST'] = 'localhost';

// Include necessary files
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/core/App.php';

// Test URLs
$testUrls = [
    'http://localhost/test/error/404',
    'http://localhost/test/error/500',
    'http://localhost/test/error/403',
    'http://localhost/test/error/401',
    'http://localhost/test/error/400',
    'http://localhost/test/error/generic',
    'http://localhost/test/error/exception'
];

echo "Testing error pages via web server...\n\n";

foreach ($testUrls as $url) {
    echo "Testing: $url\n";
    
    // Create stream context to follow redirects
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'follow_location' => true,
            'timeout' => 10
        ]
    ]);
    
    // Get the response
    $response = @file_get_contents($url, false, $context);
    
    if ($response === false) {
        $error = error_get_last();
        echo "  ❌ Failed: " . $error['message'] . "\n";
    } else {
        // Get HTTP response code
        $httpCode = null;
        if (isset($http_response_header)) {
            foreach ($http_response_header as $header) {
                if (preg_match('/HTTP\/\d\.\d (\d{3})/', $header, $matches)) {
                    $httpCode = $matches[1];
                    break;
                }
            }
        }
        
        echo "  ✅ Success (HTTP $httpCode)\n";
        
        // Check if response contains expected content
        if (strpos($response, 'error') !== false || strpos($response, 'Error') !== false) {
            echo "  ✓ Contains error-related content\n";
        } else {
            echo "  ⚠ May not contain expected error content\n";
        }
        
        // Show first few lines of response
        $lines = explode("\n", $response);
        echo "  Response preview:\n";
        for ($i = 0; $i < min(3, count($lines)); $i++) {
            echo "    " . trim($lines[$i]) . "\n";
        }
    }
    
    echo "\n";
}

echo "Web server testing complete.\n";