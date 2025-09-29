<?php

// Include the autoloader
require_once __DIR__ . '/vendor/autoload.php';

// Import the Response class
use App\Core\Http\Response;

// Test 1: Basic response
echo "Test 1: Basic Response\n";
$response = new Response('Hello, World!');
$output = (string) $response;
echo "Status: " . $response->getStatusCode() . ' ' . $response->getStatusText() . "\n";
echo "Content: " . $output . "\n\n";

// Test 2: JSON response
echo "Test 2: JSON Response\n";
$data = ['status' => 'success', 'message' => 'Operation completed'];
$response = Response::json($data);
$output = (string) $response;
echo "Status: " . $response->getStatusCode() . ' ' . $response->getStatusText() . "\n";
echo "Content-Type: " . $response->getHeader('Content-Type')[0] . "\n";
echo "Content: " . $output . "\n\n";

// Test 3: Response with custom status code
echo "Test 3: Custom Status Code\n";
$response = new Response('Not Found', 404);
echo "Status: " . $response->getStatusCode() . ' ' . $response->getStatusText() . "\n\n";

// Test 4: Response with headers
echo "Test 4: Response with Headers\n";
$response = new Response('With Custom Header');
$response->setHeader('X-Custom-Header', 'Test Value');
echo "Has X-Custom-Header: " . ($response->hasHeader('X-Custom-Header') ? 'Yes' : 'No') . "\n";
$headerValue = $response->getHeader('X-Custom-Header');
echo "Header Value: " . $headerValue[0] . "\n";

// Remove the header and test again
$response->removeHeader('X-Custom-Header');
echo "After removal - Has X-Custom-Header: " . ($response->hasHeader('X-Custom-Header') ? 'Yes' : 'No') . "\n\n";

// Test 5: Response with cookies
echo "Test 5: Response with Cookies\n";
$response = new Response('With Cookie');
$response->setCookie('test_cookie', 'cookie_value', [
    'expires' => time() + 3600,
    'path' => '/',
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Strict'
]);

$cookies = $response->getHeader('Set-Cookie');
if (!empty($cookies)) {
    echo "Set-Cookie Header: " . $cookies[0] . "\n";
} else {
    echo "No Set-Cookie header found\n";
}
echo "\n";

// Test 6: Response content type
echo "Test 6: Response Content Type\n";
$response = new Response();
$response->setContentType('application/xml');
echo "Content-Type: " . $response->getHeader('Content-Type')[0] . "\n";

$response->setContentType('text/plain', 'ISO-8859-1');
echo "Updated Content-Type: " . $response->getHeader('Content-Type')[0] . "\n\n";

// Note: The compression test is skipped as it requires output buffering and headers
// which can't be easily tested in a simple script like this

echo "All tests completed!\n";
