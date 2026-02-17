<?php

// List of routes to test
$routes = [
    '/',
    '/about',
    '/contact',
    '/properties',
    '/gallery',
    '/resell',
    '/testimonials',
    '/faq',
    '/api/properties', // Corrected from /api/properties/search
    // '/api/search.php', // POST route, skipped for now or need to simulate POST
    '/api/properties/featured', // Need to check if this exists
    '/api/properties/recent'    // Need to check if this exists
];

echo "Starting render tests (isolated processes)...\n";
echo "------------------------------------------------\n";

$passed = 0;
$failed = 0;

foreach ($routes as $route) {
    echo "Testing route: $route ... ";

    // Escape the route for shell argument
    $escapedRoute = escapeshellarg($route);

    // Command to run the single URI test
    // We use 2>&1 to capture stderr as well
    $command = "php test_single_uri.php $escapedRoute 2>&1";

    $output = [];
    $returnVar = 0;

    // Execute command
    exec($command, $output, $returnVar);

    $outputStr = implode("\n", $output);

    // Check for errors
    $isFatal = stripos($outputStr, 'Fatal error') !== false;
    $isException = stripos($outputStr, 'Uncaught Exception') !== false;
    $isWarning = stripos($outputStr, 'Warning') !== false;
    $isNotice = stripos($outputStr, 'Notice') !== false;
    $is404 = stripos($outputStr, '404 Not Found') !== false; // Check for 404 text in title or body

    // Basic validation logic
    if ($returnVar !== 0 || $isFatal || $isException) {
        echo "FAILED (Fatal/Exception)\n";
        echo "Return Code: $returnVar\n";
        echo "Output snippet:\n" . substr($outputStr, 0, 500) . "...\n";
        $failed++;
    } elseif ($is404) {
        echo "FAILED (404 Not Found)\n";
        $failed++;
    } elseif (strlen(trim($outputStr)) == 0) {
        echo "FAILED (Empty Response)\n";
        $failed++;
    } else {
        echo "PASSED\n";
        $passed++;
    }
    echo "------------------------------------------------\n";
}

echo "Test Summary:\n";
echo "Total: " . count($routes) . "\n";
echo "Passed: $passed\n";
echo "Failed: $failed\n";

if ($failed > 0) {
    exit(1);
}
exit(0);
