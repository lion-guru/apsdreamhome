<?php
// Test API endpoints
require_once __DIR__ . '/config/bootstrap.php';

// Test health endpoint
echo "Testing API health endpoint...\n";
$url = 'http://localhost/apsdreamhome/api/health';
$context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'header' => 'Content-Type: application/json'
    ]
]);

$response = file_get_contents($url, false, $context);
echo "Response: " . $response . "\n";
?>
