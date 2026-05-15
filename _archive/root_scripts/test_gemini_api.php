<?php

// Test script for Gemini chatbot API

$url = 'http://localhost/apsdreamhome/api/gemini/chat';

// Create JSON payload
$data = json_encode(['message' => 'hello']);

$opts = [
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/json',
        'content' => $data
    ]
];
$context = stream_context_create($opts);

$result = @file_get_contents($url, false, $context);

if ($result === false) {
    echo "Error: Could not connect to API\n";
    print_r(error_get_last());
} else {
    echo "Raw API Response:\n";
    echo $result . "\n\n";
    
    echo "Decoded Response:\n";
    $decoded = json_decode($result, true);
    print_r($decoded);
}

echo "\n--- Testing with different messages ---\n";

// Test price query
$data2 = json_encode(['message' => 'plot price kitna hai']);
$opts2 = [
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/json',
        'content' => $data2
    ]
];
$context2 = stream_context_create($opts2);
$result2 = @file_get_contents($url, false, $context2);

if ($result2) {
    echo "\nPrice Query Response:\n";
    $decoded2 = json_decode($result2, true);
    print_r($decoded2);
}

echo "\n--- Done ---\n";
