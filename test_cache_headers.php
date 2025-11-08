<?php

// Include required classes
require_once __DIR__ . '/app/Core/Support/ParameterBag.php';
require_once __DIR__ . '/app/Core/Http/Request.php';
require_once __DIR__ . '/app/Core/Http/Response.php';

// Create a new response
$response = new App\Core\Http\Response('Cached content');

// Test ETag
$response->setEtag('test-etag');
echo "ETag: " . $response->getEtag() . "\n";

// Test Last-Modified
$date = new DateTime('2023-01-01 12:00:00');
$response->setLastModified($date);
echo "Last-Modified: " . $response->getLastModified()->format('Y-m-d H:i:s') . "\n";

// Test Cache-Control
$response->setPublic();
$response->setMaxAge(3600);
$response->setSharedMaxAge(1800);

$cacheControl = $response->getHeader('Cache-Control');
echo "Cache-Control: " . (!empty($cacheControl) ? $cacheControl[0] : 'Not set') . "\n";

// Create a request with If-None-Match header
$request = new App\Core\Http\Request();
$request->headers['If-None-Match'] = ['"test-etag"'];

// Test isNotModified
if ($response->isNotModified($request)) {
    echo "Response is not modified (304)\n";
    $response->setNotModified();
}

echo "Final Status Code: " . $response->getStatusCode() . "\n";

// Output headers
echo "\nHeaders:\n";
$headers = $response->getHeaders();
if (!empty($headers)) {
    foreach ($headers as $name => $values) {
        if (is_array($values)) {
            echo "$name: " . implode(', ', $values) . "\n";
        } else {
            echo "$name: $values\n";
        }
    }
} else {
    echo "No headers set\n";
}
