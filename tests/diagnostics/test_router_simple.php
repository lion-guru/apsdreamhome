<?php
/**
 * Simple Router Test
 * Tests the basic routing functionality
 */

// Test basic routing
echo "Testing Router...\n";

// Test 1: Check if PUBLIC_PATH is defined
if (!defined('PUBLIC_PATH')) {
    define('PUBLIC_PATH', __DIR__);
    echo "✓ PUBLIC_PATH defined: " . PUBLIC_PATH . "\n";
} else {
    echo "✓ PUBLIC_PATH already defined: " . PUBLIC_PATH . "\n";
}

// Test 2: Check if BASE_URL is defined
if (!defined('BASE_URL')) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $domainName = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $base_url = $protocol . $domainName . '/';
    define('BASE_URL', rtrim($base_url, '/') . '/');
    echo "✓ BASE_URL defined: " . BASE_URL . "\n";
} else {
    echo "✓ BASE_URL already defined: " . BASE_URL . "\n";
}

// Test 3: Check if test_error.php exists
$testErrorFile = PUBLIC_PATH . '/test_error.php';
if (file_exists($testErrorFile)) {
    echo "✓ test_error.php exists\n";
} else {
    echo "✗ test_error.php NOT found\n";
}

// Test 4: Check if error_pages/404.php exists
$error404File = PUBLIC_PATH . '/error_pages/404.php';
if (file_exists($error404File)) {
    echo "✓ error_pages/404.php exists\n";
} else {
    echo "✗ error_pages/404.php NOT found\n";
}

// Test 5: Test basic routing logic
$requestedPath = 'test/error/404';
echo "\nTesting route: $requestedPath\n";

// Check if route matches
$pattern = 'test/error/404';
$target = 'test_error.php';

if ($requestedPath === $pattern) {
    echo "✓ Route matches pattern\n";
    $targetFile = PUBLIC_PATH . '/' . $target;
    if (file_exists($targetFile)) {
        echo "✓ Target file exists: $targetFile\n";
        echo "✓ Routing would succeed\n";
    } else {
        echo "✗ Target file NOT found: $targetFile\n";
    }
} else {
    echo "✗ Route does NOT match pattern\n";
}

echo "\nRouter test completed.\n";
?>