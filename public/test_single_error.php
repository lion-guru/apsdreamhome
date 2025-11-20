<?php
// Test a single error route to see the debug output
$url = "http://localhost/apsdreamhome/public/test/error/404";

echo "Testing: $url\n";

try {
    $response = file_get_contents($url);
    echo "Response received:\n";
    echo $response;
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}
?>