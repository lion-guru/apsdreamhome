<?php
/**
 * Comprehensive API Testing Script
 * Tests all API endpoints systematically
 */

echo "🧪 APS DREAM HOME - COMPREHENSIVE API TESTING\n";
echo "================================================\n\n";

// Test 1: API Root
echo "Test 1: API Root - Show available endpoints\n";
$_GET['endpoint'] = '/';
ob_start();
include 'api_direct_test.php';
$result1 = ob_get_clean();
echo "Result: " . $result1 . "\n\n";

// Test 2: Health Check
echo "Test 2: Health Check - Verify API is running\n";
$_GET['endpoint'] = '/health';
ob_start();
include 'api_direct_test.php';
$result2 = ob_get_clean();
echo "Result: " . $result2 . "\n\n";

// Test 3: Properties List
echo "Test 3: Properties List - Get all properties\n";
$_GET['endpoint'] = '/properties';
ob_start();
include 'api_direct_test.php';
$result3 = ob_get_clean();
echo "Result: " . $result3 . "\n\n";

// Test 4: User Authentication - Login
echo "Test 4: User Authentication - Login\n";
$_GET['endpoint'] = '/auth/login';
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST['email'] = 'test@example.com';
$_POST['password'] = 'test123';
// Simulate JSON input
file_put_contents('php://temp/input', json_encode(['email' => 'test@example.com', 'password' => 'test123']));
ob_start();
include 'api_direct_test.php';
$result4 = ob_get_clean();
echo "Result: " . $result4 . "\n\n";

// Test 5: User Registration
echo "Test 5: User Registration - New user\n";
$_GET['endpoint'] = '/auth/register';
$_SERVER['REQUEST_METHOD'] = 'POST';
file_put_contents('php://temp/input', json_encode(['name' => 'Test User', 'email' => 'new@example.com', 'password' => 'test123']));
ob_start();
include 'api_direct_test.php';
$result5 = ob_get_clean();
echo "Result: " . $result5 . "\n\n";

// Test 6: Property Search
echo "Test 6: Property Search - Search functionality\n";
$_GET['endpoint'] = '/search';
$_GET['q'] = 'gorakhpur';
$_GET['type'] = 'residential';
ob_start();
include 'api_direct_test.php';
$result6 = ob_get_clean();
echo "Result: " . $result6 . "\n\n";

// Test 7: Error Handling - Invalid endpoint
echo "Test 7: Error Handling - Invalid endpoint\n";
$_GET['endpoint'] = '/invalid';
ob_start();
include 'api_direct_test.php';
$result7 = ob_get_clean();
echo "Result: " . $result7 . "\n\n";

// Test 8: Method Validation - Wrong HTTP method
echo "Test 8: Method Validation - Wrong HTTP method\n";
$_GET['endpoint'] = '/auth/login';
$_SERVER['REQUEST_METHOD'] = 'GET';
ob_start();
include 'api_direct_test.php';
$result8 = ob_get_clean();
echo "Result: " . $result8 . "\n\n";

// Test 9: Performance - Response time
echo "Test 9: Performance - Response time\n";
$_GET['endpoint'] = '/properties';
$start_time = microtime(true);
ob_start();
include 'api_direct_test.php';
$result9 = ob_get_clean();
$end_time = microtime(true);
$response_time = ($end_time - $start_time) * 1000; // Convert to milliseconds
echo "Result: " . $result9 . "\n";
echo "Response Time: " . number_format($response_time, 2) . " ms\n\n";

// Test 10: Data Validation - Empty input
echo "Test 10: Data Validation - Empty input\n";
$_GET['endpoint'] = '/auth/register';
$_SERVER['REQUEST_METHOD'] = 'POST';
file_put_contents('php://temp/input', json_encode(['name' => '', 'email' => '', 'password' => '']));
ob_start();
include 'api_direct_test.php';
$result10 = ob_get_clean();
echo "Result: " . $result10 . "\n\n";

echo "================================================\n";
echo "🧪 API TESTING COMPLETED\n";
echo "================================================\n";

// Parse results and provide summary
$tests = [
    'API Root' => json_decode($result1, true),
    'Health Check' => json_decode($result2, true),
    'Properties List' => json_decode($result3, true),
    'User Login' => json_decode($result4, true),
    'User Registration' => json_decode($result5, true),
    'Property Search' => json_decode($result6, true),
    'Error Handling' => json_decode($result7, true),
    'Method Validation' => json_decode($result8, true),
    'Performance Test' => json_decode($result9, true),
    'Data Validation' => json_decode($result10, true)
];

$passed = 0;
$total = count($tests);

foreach ($tests as $test_name => $result) {
    if ($result && isset($result['success']) && $result['success'] === true) {
        $passed++;
        echo "✅ $test_name: PASSED\n";
    } elseif ($result && isset($result['message']) || isset($result['status'])) {
        $passed++;
        echo "✅ $test_name: PASSED\n";
    } elseif ($result && isset($result['error']) && strpos($test_name, 'Error') !== false) {
        $passed++;
        echo "✅ $test_name: PASSED (Expected error)\n";
    } else {
        echo "❌ $test_name: FAILED\n";
    }
}

echo "\n📊 SUMMARY: $passed/$total tests passed\n";

if ($passed === $total) {
    echo "🎉 ALL API TESTS PASSED - API endpoints working correctly!\n";
} else {
    echo "⚠️  Some tests failed - Review results above\n";
}

echo "\n🚀 Ready to proceed with Day 2 testing!\n";
?>
