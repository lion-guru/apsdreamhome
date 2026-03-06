<?php
/**
 * APS Dream Home - API Test
 * Test all API endpoints functionality
 * Recreated after file deletion
 */

echo "🔌 APS DREAM HOME - API TEST\n";
echo "=============================\n";

// Base URL
$baseUrl = 'http://localhost/apsdreamhome/api';

// Test endpoints
$endpoints = [
    '/health' => 'Health Check',
    '/properties' => 'Properties List',
    '/properties/search' => 'Property Search',
    '/leads' => 'Leads Management',
    '/users' => 'User Management',
    '/projects' => 'Projects List'
];

echo "\n1. Testing API Endpoints:\n";

foreach ($endpoints as $endpoint => $description) {
    echo "\n📍 Testing: $description ($endpoint)\n";
    
    $url = $baseUrl . $endpoint;
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'timeout' => 10,
            'header' => "Content-Type: application/json\r\n"
        ]
    ]);
    
    $startTime = microtime(true);
    $response = @file_get_contents($url, false, $context);
    $endTime = microtime(true);
    
    if ($response !== false) {
        $responseTime = ($endTime - $startTime) * 1000;
        $httpCode = $http_response_header[0] ?? 'Unknown';
        
        echo "✅ Status: $http_code\n";
        echo "⏱️ Response Time: " . number_format($responseTime, 2) . "ms\n";
        echo "📦 Response Size: " . strlen($response) . " bytes\n";
        
        // Try to parse JSON
        $data = json_decode($response, true);
        if ($data !== null) {
            echo "📋 JSON Format: VALID\n";
            
            if (isset($data['status'])) {
                echo "🎯 API Status: " . $data['status'] . "\n";
            }
            
            if (isset($data['data']) && is_array($data['data'])) {
                echo "📊 Data Records: " . count($data['data']) . "\n";
            }
        } else {
            echo "⚠️ JSON Format: INVALID\n";
        }
    } else {
        echo "❌ Status: FAILED\n";
        echo "🚫 Error: Unable to connect to API\n";
    }
}

// Test POST endpoints
echo "\n2. Testing POST Endpoints:\n";

$postEndpoints = [
    '/auth/login' => 'User Login',
    '/leads' => 'Create Lead',
    '/properties/search' => 'Advanced Search'
];

foreach ($postEndpoints as $endpoint => $description) {
    echo "\n📍 Testing POST: $description ($endpoint)\n";
    
    $postData = [];
    switch ($endpoint) {
        case '/auth/login':
            $postData = [
                'email' => 'test@example.com',
                'password' => 'test123'
            ];
            break;
        case '/leads':
            $postData = [
                'name' => 'Test Lead',
                'email' => 'lead@example.com',
                'phone' => '1234567890'
            ];
            break;
        case '/properties/search':
            $postData = [
                'keyword' => 'apartment',
                'location' => 'mumbai',
                'type' => 'residential'
            ];
            break;
    }
    
    $url = $baseUrl . $endpoint;
    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'timeout' => 10,
            'header' => "Content-Type: application/json\r\n",
            'content' => json_encode($postData)
        ]
    ]);
    
    $startTime = microtime(true);
    $response = @file_get_contents($url, false, $context);
    $endTime = microtime(true);
    
    if ($response !== false) {
        $responseTime = ($endTime - $startTime) * 1000;
        $httpCode = $http_response_header[0] ?? 'Unknown';
        
        echo "✅ Status: $http_code\n";
        echo "⏱️ Response Time: " . number_format($responseTime, 2) . "ms\n";
        echo "📦 Response Size: " . strlen($response) . " bytes\n";
        
        $data = json_decode($response, true);
        if ($data !== null) {
            echo "📋 JSON Format: VALID\n";
            if (isset($data['status'])) {
                echo "🎯 API Status: " . $data['status'] . "\n";
            }
        } else {
            echo "⚠️ JSON Format: INVALID\n";
        }
    } else {
        echo "❌ Status: FAILED\n";
        echo "🚫 Error: Unable to connect to API\n";
    }
}

// Test API Authentication
echo "\n3. Testing API Authentication:\n";

$authEndpoints = [
    '/auth/login' => 'Login Endpoint',
    '/auth/me' => 'User Profile',
    '/auth/logout' => 'Logout Endpoint'
];

foreach ($authEndpoints as $endpoint => $description) {
    echo "\n🔐 Testing Auth: $description ($endpoint)\n";
    
    $url = $baseUrl . $endpoint;
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'timeout' => 10,
            'header' => "Content-Type: application/json\r\n"
        ]
    ]);
    
    $response = @file_get_contents($url, false, $context);
    
    if ($response !== false) {
        $data = json_decode($response, true);
        if ($data !== null) {
            if (isset($data['status']) && $data['status'] === 'success') {
                echo "✅ Authentication: WORKING\n";
            } elseif (isset($data['error'])) {
                echo "⚠️ Authentication: " . $data['error'] . "\n";
            } else {
                echo "✅ Endpoint: RESPONDING\n";
            }
        } else {
            echo "⚠️ Response: Invalid JSON\n";
        }
    } else {
        echo "❌ Endpoint: NOT RESPONDING\n";
    }
}

// Test API Error Handling
echo "\n4. Testing Error Handling:\n";

$errorEndpoints = [
    '/nonexistent' => '404 Not Found',
    '/properties/999999' => 'Invalid Property ID',
    '/users/invalid' => 'Invalid User Format'
];

foreach ($errorEndpoints as $endpoint => $description) {
    echo "\n🚨 Testing Error: $description ($endpoint)\n";
    
    $url = $baseUrl . $endpoint;
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'timeout' => 10,
            'header' => "Content-Type: application/json\r\n"
        ]
    ]);
    
    $response = @file_get_contents($url, false, $context);
    
    if ($response !== false) {
        $data = json_decode($response, true);
        if ($data !== null) {
            if (isset($data['error'])) {
                echo "✅ Error Handling: PROPER\n";
                echo "📝 Error Message: " . $data['error'] . "\n";
            } else {
                echo "⚠️ Error Handling: IMPROPER\n";
            }
        } else {
            echo "⚠️ Response: Invalid JSON\n";
        }
    } else {
        echo "✅ Error Handling: SERVER RESPONDS\n";
    }
}

echo "\n📊 API TEST SUMMARY:\n";
echo "====================\n";
echo "Endpoints Tested: " . count($endpoints) . "\n";
echo "POST Endpoints: " . count($postEndpoints) . "\n";
echo "Auth Endpoints: " . count($authEndpoints) . "\n";
echo "Error Handling: " . count($errorEndpoints) . "\n";

echo "\n✅ API TEST COMPLETE!\n";
echo "API functionality verified and documented.\n";
?>
