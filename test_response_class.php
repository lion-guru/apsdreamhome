<?php

// Include the Response class
require_once __DIR__ . '/app/Core/Http/Response.php';

// Simple test function
function runTest($name, $test) {
    echo "Test: $name - ";
    try {
        $result = $test();
        if ($result === true) {
            echo "PASSED\n";
            return true;
        } else {
            echo "FAILED: $result\n";
        }
    } catch (Exception $e) {
        echo "ERROR: " . $e->getMessage() . "\n";
    }
    return false;
}

echo "Testing Response class...\n\n";

// Test 1: Create a basic response
runTest('Basic Response', function() {
    $response = new App\Core\Http\Response('Hello, World!');
    return (string)$response === 'Hello, World!';
});

// Test 2: Set status code
runTest('Set Status Code', function() {
    $response = new App\Core\Http\Response();
    $response->setStatusCode(404);
    return $response->getStatusCode() === 404;
});

// Test 3: Set and get header
runTest('Set and Get Header', function() {
    $response = new App\Core\Http\Response();
    $response->setHeader('Content-Type', 'application/json');
    $headers = $response->getHeader('Content-Type');
    
    if (empty($headers)) {
        return 'No headers found';
    }
    
    if (!is_array($headers)) {
        return 'Headers is not an array';
    }
    
    if (!isset($headers[0]) || $headers[0] !== 'application/json') {
        return 'Header value does not match';
    }
    
    return true;
});

// Test 4: Set multiple headers
runTest('Set Multiple Headers', function() {
    $response = new App\Core\Http\Response();
    $response->setHeader('Content-Type', 'application/json');
    $response->setHeader('Cache-Control', 'no-cache');
    
    $contentType = $response->getHeader('Content-Type');
    $cacheControl = $response->getHeader('Cache-Control');
    
    if (empty($contentType) || $contentType[0] !== 'application/json') {
        return 'Content-Type header not set correctly';
    }
    
    if (empty($cacheControl) || $cacheControl[0] !== 'no-cache') {
        return 'Cache-Control header not set correctly';
    }
    
    return true;
});

// Test 5: Set JSON response
runTest('Set JSON Response', function() {
    $data = ['name' => 'Test', 'value' => 123];
    
    // Create a new response using the static json method
    $response = App\Core\Http\Response::json($data);
    
    // Debug: Print all headers
    $headers = $response->getHeaders();
    echo "\nDebug - All Headers (after json()):\n";
    foreach ($headers as $name => $values) {
        echo "- $name: " . implode(', ', (array)$values) . "\n";
    }
    
    // Get all headers and check for Content-Type case-insensitively
    $contentType = null;
    
    foreach ($headers as $name => $values) {
        if (strtolower($name) === 'content-type') {
            $contentType = is_array($values) ? $values[0] : $values;
            break;
        }
    }
    
    if ($contentType === null) {
        return 'Content-Type header not found in response';
    }
    
    echo "\nDebug - Found Content-Type: " . $contentType . "\n";
    
    if (strpos($contentType, 'application/json') === false) {
        return 'Content-Type header does not contain application/json. Actual: ' . $contentType;
    }
    
    // Check if the response content is valid JSON
    $responseContent = (string)$response;
    echo "\nDebug - Response Content: " . $responseContent . "\n";
    
    $decoded = json_decode($responseContent, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        return 'Response is not valid JSON: ' . json_last_error_msg() . ' (Content: ' . $responseContent . ')';
    }
    
    // Check if the decoded content matches the original data
    if ($decoded !== $data) {
        return 'Decoded JSON does not match original data. Expected: ' . 
               json_encode($data) . ', Got: ' . json_encode($decoded);
    }
    
    // Verify the content type is set correctly on the response object
    if ($response->getContentType() !== 'application/json') {
        return 'Response content type is not set to application/json. Got: ' . $response->getContentType();
    }
    
    // Verify the charset is set correctly (case-insensitive comparison)
    if (strcasecmp($response->getCharset(), 'UTF-8') !== 0) {
        return 'Response charset is not set to UTF-8 (case-insensitive). Got: ' . $response->getCharset();
    }
    
    return true;
});

// Test 6: Set and get cookies
runTest('Set and Get Cookies', function() {
    $response = new App\Core\Http\Response();
    $response->setCookie('test_cookie', 'test_value', [
        'expires' => time() + 3600,
        'path' => '/',
        'domain' => 'example.com',
        'secure' => true,
        'httponly' => true,
        'samesite' => 'Strict'
    ]);
    
    $cookies = $response->getCookies();
    
    if (!isset($cookies['test_cookie'])) {
        return 'Cookie not found in response';
    }
    
    if ($cookies['test_cookie']['value'] !== 'test_value') {
        return 'Cookie value does not match';
    }
    
    if (!isset($cookies['test_cookie']['options']['path']) || $cookies['test_cookie']['options']['path'] !== '/') {
        return 'Cookie path does not match';
    }
    
    return true;
});

// Test 7: Remove cookies
runTest('Remove Cookies', function() {
    $response = new App\Core\Http\Response();
    $response->setCookie('test_cookie', 'test_value');
    $response->removeCookie('test_cookie');
    
    $cookies = $response->getCookies();
    
    if (isset($cookies['test_cookie']) && $cookies['test_cookie']['value'] !== '') {
        return 'Cookie was not properly removed';
    }
    
    return true;
});

// Test 8: Check cookie existence
runTest('Check Cookie Existence', function() {
    $response = new App\Core\Http\Response();
    $response->setCookie('test_cookie', 'test_value');
    
    if (!$response->hasCookie('test_cookie')) {
        return 'hasCookie() should return true for existing cookie';
    }
    
    if ($response->hasCookie('non_existent_cookie')) {
        return 'hasCookie() should return false for non-existent cookie';
    }
    
    return true;
});

// Test 9: Test compression
runTest('Compression', function() {
    $response = new App\Core\Http\Response();
    $response->enableCompression(6, 1024);
    
    $reflection = new ReflectionClass($response);
    $compressionEnabled = $reflection->getProperty('compressionEnabled');
    $compressionEnabled->setAccessible(true);
    
    $compressionLevel = $reflection->getProperty('compressionLevel');
    $compressionLevel->setAccessible(true);
    
    $minCompressionSize = $reflection->getProperty('minCompressionSize');
    $minCompressionSize->setAccessible(true);
    
    if ($compressionEnabled->getValue($response) !== true) {
        return 'Compression should be enabled';
    }
    
    if ($compressionLevel->getValue($response) !== 6) {
        return 'Compression level should be 6';
    }
    
    if ($minCompressionSize->getValue($response) !== 1024) {
        return 'Minimum compression size should be 1024';
    }
    
    $response->disableCompression();
    if ($compressionEnabled->getValue($response) !== false) {
        return 'Compression should be disabled';
    }
    
    return true;
});

// Test 10: Test content type helpers
runTest('Content Type Helpers', function() {
    $response = new App\Core\Http\Response();
    
    // Test HTML helper
    $response->html();
    if ($response->getContentType() !== 'text/html') {
        return 'HTML content type not set correctly';
    }
    
    // Test text helper
    $response->text();
    if ($response->getContentType() !== 'text/plain') {
        return 'Text content type not set correctly';
    }
    
    // Test XML helper
    $response->xml();
    if ($response->getContentType() !== 'application/xml') {
        return 'XML content type not set correctly';
    }
    
    // Test toJson
    $data = ['test' => 'value'];
    $response->setContent($data);
    $json = $response->toJson();
    $decoded = json_decode($json, true);
    
    if ($decoded !== $data) {
        return 'toJson() did not encode data correctly';
    }
    
    return true;
});

// Test 11: Test Content Security Policy
runTest('Content Security Policy', function() {
    $response = new App\Core\Http\Response();
    
    // Set a basic CSP
    $response->setContentSecurityPolicy([
        'default-src' => "'self'",
        'script-src' => ["'self'", 'trusted.cdn.com'],
    ]);
    
    // Add another directive
    $response->addContentSecurityPolicyDirective('style-src', ["'self'", 'cdn.example.com']);
    
    // Get headers (case-insensitive check)
    $headers = array_change_key_case($response->getHeaders(), CASE_LOWER);
    
    if (!isset($headers['content-security-policy'])) {
        return 'CSP header not set';
    }
    
    $csp = is_array($headers['content-security-policy']) ? $headers['content-security-policy'][0] : $headers['content-security-policy'];
    
    if (strpos($csp, "default-src 'self'") === false) {
        return 'Default source not set correctly in CSP';
    }
    
    if (strpos($csp, "script-src 'self' trusted.cdn.com") === false) {
        return 'Script source not set correctly in CSP';
    }
    
    if (strpos($csp, "style-src 'self' cdn.example.com") === false) {
        return 'Style source not added correctly to CSP';
    }
    
    // Test removing a directive
    $response->removeContentSecurityPolicyDirective('style-src');
    $headers = $response->getHeaders();
    $csp = $headers['content-security-policy'][0];
    
    if (strpos($csp, 'style-src') !== false) {
        return 'Style source not removed from CSP';
    }
    
    return true;
});

// Test 12: Test CORS
runTest('CORS', function() {
    // Save the original server variables
    $originalServer = $_SERVER;
    
    // Set the necessary server variables for testing
    $_SERVER['HTTP_ORIGIN'] = 'http://example.com';
    $_SERVER['REQUEST_METHOD'] = 'OPTIONS';
    $_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'] = 'GET';
    
    $response = new App\Core\Http\Response();
    
    $response->enableCors([
        'allowedOrigins' => ['http://example.com'],
        'allowedMethods' => ['GET', 'POST'],
        'allowedHeaders' => ['Content-Type', 'Authorization'],
    ]);
    
    // Restore the original server variables
    $_SERVER = $originalServer;
    
    // Get headers (case-insensitive check)
    $headers = array_change_key_case($response->getHeaders(), CASE_LOWER);
    
    if (!isset($headers['access-control-allow-origin'])) {
        return 'CORS headers not set';
    }
    
    $origin = is_array($headers['access-control-allow-origin']) ? $headers['access-control-allow-origin'][0] : $headers['access-control-allow-origin'];
    if ($origin !== 'http://example.com') {
        return 'Allowed origin not set correctly. Expected: http://example.com, Got: ' . $origin;
    }
    
    if (!isset($headers['access-control-allow-methods'])) {
        return 'Allowed methods not set';
    }
    
    $methods = is_array($headers['access-control-allow-methods']) ? $headers['access-control-allow-methods'][0] : $headers['access-control-allow-methods'];
    if (strpos($methods, 'GET') === false || strpos($methods, 'POST') === false) {
        return 'Allowed methods not set correctly';
    }
    
    return true;
});

// Test 13: Test caching
runTest('Caching', function() {
    $response = new App\Core\Http\Response();
    
    // Test setting cache with ETag and Last-Modified
    $lastModified = new DateTime('2023-01-01');
    $response->setCache([
        'etag' => 'test-etag',
        'last_modified' => $lastModified,
        'max_age' => 3600,
        's_maxage' => 86400,
        'public' => true,
        'must_revalidate' => true,
    ]);
    
    $headers = $response->getHeaders();
    
    if (!isset($headers['etag'])) {
        return 'ETag header not set';
    }
    
    if ($headers['etag'][0] !== '"test-etag"') {
        return 'ETag value not set correctly';
    }
    
    if (!isset($headers['last-modified'])) {
        return 'Last-Modified header not set';
    }
    
    if (!isset($headers['cache-control'])) {
        return 'Cache-Control header not set';
    }
    
    $cacheControl = $headers['cache-control'][0];
    if (strpos($cacheControl, 'public') === false || 
        strpos($cacheControl, 'max-age=3600') === false ||
        strpos($cacheControl, 's-maxage=86400') === false ||
        strpos($cacheControl, 'must-revalidate') === false) {
        return 'Cache-Control header not set correctly';
    }
    
    // Test no-cache
    $response->setNoCache();
    $headers = $response->getHeaders();
    $cacheControl = $headers['cache-control'][0];
    
    if (strpos($cacheControl, 'no-cache') === false || 
        strpos($cacheControl, 'no-store') === false) {
        return 'No-cache headers not set correctly';
    }
    
    // Test expire
    $response->expire();
    $headers = $response->getHeaders();
    
    if (!isset($headers['expires'])) {
        return 'Expires header not set';
    }
    
    if (!isset($headers['pragma']) || $headers['pragma'][0] !== 'no-cache') {
        return 'Pragma header not set correctly';
    }
    
    return true;
});

echo "\nAll tests completed.\n";
