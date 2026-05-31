<?php

// Test script for AI chatbot API

$url = 'http://localhost/apsdreamhome/api/ai/chat';
$data = http_build_query(['message' => 'hello', 'session_id' => 'test123']);
$opts = [
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/x-www-form-urlencoded',
        'content' => $data
    ]
];
$context = stream_context_create($opts);

$result = @file_get_contents($url, false, $context);

if ($result === false) {
    echo "Error: Could not connect to API\n";
    print_r(error_get_last());
} else {
    echo "API Response:\n";
    $decoded = json_decode($result, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        print_r($decoded);
    } else {
        echo $result;
    }
}

echo "\n--- Testing Price Query ---\n";

$data2 = http_build_query(['message' => 'plot price kitna hai', 'session_id' => 'test124']);
$opts2 = [
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/x-www-form-urlencoded',
        'content' => $data2
    ]
];
$context2 = stream_context_create($opts2);
$result2 = @file_get_contents($url, false, $context2);

if ($result2) {
    $decoded2 = json_decode($result2, true);
    echo "Price Query Response:\n";
    print_r($decoded2);
}

echo "\n--- Testing Location Query ---\n";

$data3 = http_build_query(['message' => 'location kaha hai', 'session_id' => 'test125']);
$opts3 = [
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/x-www-form-urlencoded',
        'content' => $data3
    ]
];
$context3 = stream_context_create($opts3);
$result3 = @file_get_contents($url, false, $context3);

if ($result3) {
    $decoded3 = json_decode($result3, true);
    echo "Location Query Response:\n";
    print_r($decoded3);
}

echo "\n--- Done ---\n";
