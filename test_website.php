<?php
// Simple test to check if website loads
echo "🧪 Testing website response...\n";

// Test 1: Check if main page loads
$context = stream_context_create([
    'http' => [
        'timeout' => 10,
        'method' => 'GET'
    ]
]);

$url = 'http://localhost/apsdreamhome/';
echo "Testing: $url\n";

$response = @file_get_contents($url, false, $context);

if ($response === false) {
    echo "❌ FAILED: Could not load website\n";
    $error = error_get_last();
    if ($error) {
        echo "Error: " . $error['message'] . "\n";
    }
} else {
    echo "✅ SUCCESS: Website loaded\n";
    echo "Response size: " . strlen($response) . " bytes\n";
    
    // Check for common error indicators
    if (strpos($response, '500') !== false) {
        echo "⚠️  WARNING: Contains '500' error\n";
    }
    if (strpos($response, 'Error') !== false) {
        echo "⚠️  WARNING: Contains 'Error'\n";
    }
    if (strpos($response, '<title>') !== false) {
        echo "✅ Contains HTML title tag\n";
    }
    
    // Show first 200 characters
    echo "First 200 chars:\n";
    echo substr($response, 0, 200) . "...\n";
}

echo "\n🎯 Test Complete!\n";
?>
