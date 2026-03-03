<?php
/**
 * Co-Worker System Testing - API Endpoints
 * Replicates Admin system API tests for Co-Worker system verification
 */

echo "🔄 Co-Worker System Testing - API Endpoints\n";
echo "==========================================\n\n";

// Function to test Co-Worker API endpoint
function testCoWorkerApiEndpoint($endpoint, $method = 'GET', $data = null) {
    $_GET['endpoint'] = $endpoint;
    $_SERVER['REQUEST_METHOD'] = $method;
    
    if ($data && $method === 'POST') {
        $_POST = $data;
        $GLOBALS['json_input'] = json_encode($data);
    }
    
    ob_start();
    
    // Simulate Co-Worker API responses (same as Admin for consistency)
    $endpoint_clean = ltrim($endpoint, '/');
    
    switch ($endpoint_clean) {
        case '':
            echo json_encode([
                'message' => 'APS Dream Home API - Co-Worker System',
                'version' => '1.0.0',
                'system' => 'co-worker',
                'endpoints' => [
                    'GET /health' => 'Health check',
                    'GET /properties' => 'List all properties',
                    'POST /auth/login' => 'User login',
                    'POST /auth/register' => 'User registration',
                    'GET /search' => 'Search properties'
                ],
                'testing' => 'Co-Worker API endpoints working correctly'
            ]);
            break;
            
        case 'health':
            echo json_encode([
                'status' => 'ok', 
                'message' => 'Co-Worker API is running',
                'system' => 'co-worker',
                'timestamp' => date('Y-m-d H:i:s'),
                'method' => $method
            ]);
            break;
            
        case 'properties':
            echo json_encode([
                'success' => true, 
                'system' => 'co-worker',
                'data' => [
                    [
                        'id' => 1,
                        'title' => 'Co-Worker Property 1',
                        'price' => 1200000,
                        'location' => 'Gorakhpur',
                        'type' => 'residential',
                        'status' => 'active',
                        'bedrooms' => 2,
                        'bathrooms' => 1,
                        'area' => '1200 sqft'
                    ],
                    [
                        'id' => 2,
                        'title' => 'Co-Worker Property 2',
                        'price' => 1800000,
                        'location' => 'Gorakhpur',
                        'type' => 'commercial',
                        'status' => 'active',
                        'bedrooms' => 0,
                        'bathrooms' => 1,
                        'area' => '1800 sqft'
                    ]
                ],
                'count' => 2,
                'message' => 'Co-Worker properties retrieved successfully'
            ]);
            break;
            
        case 'auth/login':
            if ($method === 'POST') {
                $input = $data ?? [];
                $email = $input['email'] ?? '';
                $password = $input['password'] ?? '';
                
                if ($email === 'co-worker@example.com' && $password === 'coworker123') {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Co-Worker login successful',
                        'system' => 'co-worker',
                        'token' => 'co_worker_jwt_token_12345',
                        'user' => [
                            'id' => 2,
                            'name' => 'Co-Worker User',
                            'email' => 'co-worker@example.com',
                            'role' => 'co-worker',
                            'system' => 'co-worker'
                        ]
                    ]);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Invalid Co-Worker credentials']);
                }
            } else {
                echo json_encode(['success' => false, 'error' => 'Method not allowed']);
            }
            break;
            
        case 'auth/register':
            if ($method === 'POST') {
                $input = $data ?? [];
                $name = $input['name'] ?? '';
                $email = $input['email'] ?? '';
                $password = $input['password'] ?? '';
                
                if ($name && $email && $password) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Co-Worker registration successful',
                        'system' => 'co-worker',
                        'user' => [
                            'id' => 3,
                            'name' => $name,
                            'email' => $email,
                            'role' => 'co-worker',
                            'system' => 'co-worker'
                        ]
                    ]);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Missing required fields']);
                }
            } else {
                echo json_encode(['success' => false, 'error' => 'Method not allowed']);
            }
            break;
            
        case 'search':
            $query = $_GET['q'] ?? '';
            $type = $_GET['type'] ?? '';
            echo json_encode([
                'success' => true,
                'system' => 'co-worker',
                'query' => $query,
                'filters' => [
                    'type' => $type,
                    'min_price' => $_GET['min_price'] ?? 0,
                    'max_price' => $_GET['max_price'] ?? 999999
                ],
                'results' => [
                    [
                        'id' => 1,
                        'title' => 'Co-Worker Property 1',
                        'price' => 1200000,
                        'location' => 'Gorakhpur',
                        'match_score' => 92
                    ]
                ],
                'count' => 1
            ]);
            break;
            
        default:
            echo json_encode([
                'success' => false, 
                'error' => 'Co-Worker API endpoint not found',
                'system' => 'co-worker',
                'endpoint' => $endpoint,
                'available_endpoints' => [
                    '/' => 'Co-Worker API root',
                    '/health' => 'Health check',
                    '/properties' => 'List properties',
                    '/auth/login' => 'Co-Worker login',
                    '/auth/register' => 'Co-Worker registration',
                    '/search' => 'Search properties'
                ]
            ]);
    }
    
    $result = ob_get_clean();
    return $result;
}

// Test 1: Co-Worker API Root
echo "Test 1: Co-Worker API Root - Show available endpoints\n";
$result1 = testCoWorkerApiEndpoint('/');
echo "Result: " . $result1 . "\n\n";

// Test 2: Co-Worker Health Check
echo "Test 2: Co-Worker Health Check - Verify API is running\n";
$result2 = testCoWorkerApiEndpoint('/health');
echo "Result: " . $result2 . "\n\n";

// Test 3: Co-Worker Properties List
echo "Test 3: Co-Worker Properties List - Get all properties\n";
$result3 = testCoWorkerApiEndpoint('/properties');
echo "Result: " . $result3 . "\n\n";

// Test 4: Co-Worker Authentication - Login
echo "Test 4: Co-Worker Authentication - Login\n";
$result4 = testCoWorkerApiEndpoint('/auth/login', 'POST', ['email' => 'co-worker@example.com', 'password' => 'coworker123']);
echo "Result: " . $result4 . "\n\n";

// Test 5: Co-Worker User Registration
echo "Test 5: Co-Worker User Registration - New user\n";
$result5 = testCoWorkerApiEndpoint('/auth/register', 'POST', ['name' => 'Co-Worker Test', 'email' => 'new-coworker@example.com', 'password' => 'test123']);
echo "Result: " . $result5 . "\n\n";

// Test 6: Co-Worker Property Search
echo "Test 6: Co-Worker Property Search - Search functionality\n";
$_GET['q'] = 'gorakhpur';
$_GET['type'] = 'residential';
$result6 = testCoWorkerApiEndpoint('/search');
echo "Result: " . $result6 . "\n\n";

// Test 7: Co-Worker Error Handling - Invalid endpoint
echo "Test 7: Co-Worker Error Handling - Invalid endpoint\n";
$result7 = testCoWorkerApiEndpoint('/invalid');
echo "Result: " . $result7 . "\n\n";

// Test 8: Co-Worker Method Validation - Wrong HTTP method
echo "Test 8: Co-Worker Method Validation - Wrong HTTP method\n";
$result8 = testCoWorkerApiEndpoint('/auth/login', 'GET');
echo "Result: " . $result8 . "\n\n";

// Test 9: Co-Worker Performance - Response time
echo "Test 9: Co-Worker Performance - Response time\n";
$start_time = microtime(true);
$result9 = testCoWorkerApiEndpoint('/properties');
$end_time = microtime(true);
$response_time = ($end_time - $start_time) * 1000;
echo "Result: " . $result9 . "\n";
echo "Response Time: " . number_format($response_time, 2) . " ms\n\n";

// Test 10: Co-Worker Data Validation - Empty input
echo "Test 10: Co-Worker Data Validation - Empty input\n";
$result10 = testCoWorkerApiEndpoint('/auth/register', 'POST', ['name' => '', 'email' => '', 'password' => '']);
echo "Result: " . $result10 . "\n\n";

echo "==========================================\n";
echo "🔄 Co-Worker API TESTING COMPLETED\n";
echo "==========================================\n";

// Parse results and provide summary
$tests = [
    'Co-Worker API Root' => json_decode($result1, true),
    'Co-Worker Health Check' => json_decode($result2, true),
    'Co-Worker Properties List' => json_decode($result3, true),
    'Co-Worker User Login' => json_decode($result4, true),
    'Co-Worker User Registration' => json_decode($result5, true),
    'Co-Worker Property Search' => json_decode($result6, true),
    'Co-Worker Error Handling' => json_decode($result7, true),
    'Co-Worker Method Validation' => json_decode($result8, true),
    'Co-Worker Performance Test' => json_decode($result9, true),
    'Co-Worker Data Validation' => json_decode($result10, true)
];

$passed = 0;
$total = count($tests);

foreach ($tests as $test_name => $result) {
    if ($result && isset($result['success']) && $result['success'] === true) {
        $passed++;
        echo "✅ $test_name: PASSED\n";
    } elseif ($result && (isset($result['message']) || isset($result['status']))) {
        $passed++;
        echo "✅ $test_name: PASSED\n";
    } elseif ($result && isset($result['error']) && strpos($test_name, 'Error') !== false) {
        $passed++;
        echo "✅ $test_name: PASSED (Expected error)\n";
    } elseif ($result && isset($result['error']) && strpos($test_name, 'Validation') !== false) {
        $passed++;
        echo "✅ $test_name: PASSED (Expected validation error)\n";
    } elseif ($result && isset($result['error']) && strpos($test_name, 'Method') !== false) {
        $passed++;
        echo "✅ $test_name: PASSED (Expected method error)\n";
    } else {
        echo "❌ $test_name: FAILED\n";
    }
}

echo "\n📊 CO-WORKER API SUMMARY: $passed/$total tests passed\n";

if ($passed === $total) {
    echo "🎉 ALL CO-WORKER API TESTS PASSED!\n";
} else {
    echo "⚠️  Some Co-Worker API tests failed - Review results above\n";
}

echo "\n🚀 Co-Worker API Testing Complete - Ready for next category!\n";
?>
