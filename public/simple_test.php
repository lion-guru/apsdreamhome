<?php
// Simple test to check web server response
$context = stream_context_create([
    'http' => [
        'timeout' => 5,
        'ignore_errors' => true
    ]
]);

$content = @file_get_contents('http://localhost:8000/test/error/404', false, $context);

echo "HTTP Response Headers:\n";
if (isset($http_response_header)) {
    foreach ($http_response_header as $header) {
        echo $header . "\n";
    }
}

echo "\nResponse Content:\n";
echo $content !== false ? $content : 'Failed to fetch content';
echo "\nDone.\n";