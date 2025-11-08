<?php

// Include the Response class directly
require_once __DIR__ . '/app/Core/Http/Response.php';

// Import the Response class
use App\Core\Http\Response;

// Helper function to capture output
function captureOutput(callable $callback) {
    ob_start();
    $callback();
    return ob_get_clean();
}

// Test 1: Basic response
echo "=== Test 1: Basic Response ===\n";
$response = new Response('Hello, World!');
$output = $response->getContent();
echo "Status: " . $response->getStatusCode() . ' ' . $response->getStatusText() . "\n";
echo "Content: " . $output . "\n\n";

// Test 2: JSON response
echo "=== Test 2: JSON Response ===\n";
$data = ['status' => 'success', 'message' => 'Operation completed'];
$response = Response::json($data);
$output = $response->getContent();
echo "Status: " . $response->getStatusCode() . ' ' . $response->getStatusText() . "\n";
$contentType = $response->getHeader('Content-Type');
echo "Content-Type: " . (!empty($contentType) ? $contentType[0] : 'Not set') . "\n";
echo "Content: " . $output . "\n\n";

// Test 3: Response with custom status code
echo "=== Test 3: Custom Status Code ===\n";
$response = new Response('Not Found', 404);
echo "Status: " . $response->getStatusCode() . ' ' . $response->getStatusText() . "\n\n";

// Test 4: Response with headers
echo "=== Test 4: Response with Headers ===\n";
$response = new Response('With Custom Header');
$response->setHeader('X-Custom-Header', 'Test Value');
echo "Has X-Custom-Header: " . ($response->hasHeader('X-Custom-Header') ? 'Yes' : 'No') . "\n";
$headerValue = $response->getHeader('X-Custom-Header');
echo "Header Value: " . (!empty($headerValue) ? $headerValue[0] : 'Not found') . "\n";

// Remove the header and test again
$response->removeHeader('X-Custom-Header');
echo "After removal - Has X-Custom-Header: " . ($response->hasHeader('X-Custom-Header') ? 'Yes' : 'No') . "\n\n";

// Test 5: Response content type
echo "=== Test 5: Response Content Type ===\n";
$response = new Response();
$response->setContentType('application/xml');
$contentType = $response->getHeader('Content-Type');
echo "Content-Type: " . (!empty($contentType) ? $contentType[0] : 'Not set') . "\n";

$response->setContentType('text/plain', 'ISO-8859-1');
$contentType = $response->getHeader('Content-Type');
echo "Updated Content-Type: " . (!empty($contentType) ? $contentType[0] : 'Not set') . "\n\n";

echo "=== All tests completed! ===\n";
