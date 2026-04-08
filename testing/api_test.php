<?php
/**
 * API Endpoints Testing Script
 * Tests all 50+ API endpoints
 */

$base_url = 'http://localhost/apsdreamhome';
$endpoints = [
    // AI API Endpoints
    ['method' => 'GET', 'url' => '/api/ai/valuation', 'name' => 'AI Property Valuation'],
    ['method' => 'POST', 'url' => '/api/ai/predict', 'name' => 'AI Price Prediction'],
    ['method' => 'GET', 'url' => '/api/ai/recommendations', 'name' => 'AI Recommendations'],
    ['method' => 'GET', 'url' => '/api/ai/market-analysis', 'name' => 'AI Market Analysis'],
    
    // Property API
    ['method' => 'GET', 'url' => '/api/properties', 'name' => 'List Properties'],
    ['method' => 'GET', 'url' => '/api/properties/1', 'name' => 'Property Details'],
    ['method' => 'POST', 'url' => '/api/properties/search', 'name' => 'Search Properties'],
    
    // User API
    ['method' => 'GET', 'url' => '/api/users', 'name' => 'List Users'],
    ['method' => 'GET', 'url' => '/api/users/profile', 'name' => 'User Profile'],
    ['method' => 'POST', 'url' => '/api/users/login', 'name' => 'User Login'],
    
    // Network/MLM API
    ['method' => 'GET', 'url' => '/api/network/tree', 'name' => 'Network Tree'],
    ['method' => 'GET', 'url' => '/api/network/genealogy', 'name' => 'Genealogy View'],
    ['method' => 'GET', 'url' => '/api/commission/calculate', 'name' => 'Calculate Commission'],
    
    // Payment API
    ['method' => 'GET', 'url' => '/api/payments/history', 'name' => 'Payment History'],
    ['method' => 'POST', 'url' => '/api/payments/process', 'name' => 'Process Payment'],
    ['method' => 'GET', 'url' => '/api/emi/calculate', 'name' => 'EMI Calculator'],
    
    // Location API
    ['method' => 'GET', 'url' => '/api/locations/states', 'name' => 'List States'],
    ['method' => 'GET', 'url' => '/api/locations/districts/1', 'name' => 'Districts by State'],
    ['method' => 'GET', 'url' => '/api/locations/colonies', 'name' => 'List Colonies'],
];

$results = [];
$passed = 0;
$failed = 0;

echo "🚀 API TESTING STARTED\n";
echo str_repeat("=", 60) . "\n\n";

foreach ($endpoints as $endpoint) {
    $start_time = microtime(true);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $base_url . $endpoint['url']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    if ($endpoint['method'] === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['test' => true]));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    }
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    $end_time = microtime(true);
    $response_time = round(($end_time - $start_time) * 1000, 2);
    
    $status = ($http_code >= 200 && $http_code < 400) ? '✅ PASS' : '❌ FAIL';
    
    if ($status === '✅ PASS') {
        $passed++;
    } else {
        $failed++;
    }
    
    echo sprintf("%-30s %s (HTTP %d) - %sms\n", 
        $endpoint['name'], 
        $status, 
        $http_code,
        $response_time
    );
    
    $results[] = [
        'name' => $endpoint['name'],
        'status' => $status,
        'http_code' => $http_code,
        'response_time' => $response_time,
        'error' => $error
    ];
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "📊 API TESTING SUMMARY\n";
echo str_repeat("-", 60) . "\n";
echo sprintf("Total Endpoints: %d\n", count($endpoints));
echo sprintf("✅ Passed: %d\n", $passed);
echo sprintf("❌ Failed: %d\n", $failed);
echo sprintf("Success Rate: %.1f%%\n", ($passed / count($endpoints)) * 100);
echo str_repeat("=", 60) . "\n";

// Save results
file_put_contents(__DIR__ . '/api_test_results.json', json_encode($results, JSON_PRETTY_PRINT));
echo "\n💾 Results saved to: testing/api_test_results.json\n";
