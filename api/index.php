<?php
/**
 * API Index - Direct API Entry Point
 * Bypasses .htaccess routing issues
 */

// Set content type to JSON
header('Content-Type: application/json');

// Get the request path
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$path = parse_url($requestUri, PHP_URL_PATH);

// Remove /api prefix if present
$path = str_replace('/apsdreamhome/api', '', $path);
$path = rtrim($path, '/');

// Get HTTP method
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// Basic API routing
if ($path === '' || $path === '/') {
    // API root - show available endpoints
    echo json_encode([
        'message' => 'APS Dream Home API - Direct Access',
        'version' => '1.0.0',
        'endpoints' => [
            'GET /health' => 'Health check',
            'GET /properties' => 'List all properties',
            'POST /auth/login' => 'User login',
            'POST /auth/register' => 'User registration',
            'GET /search' => 'Search properties'
        ],
        'testing' => 'API endpoints working correctly'
    ]);
    return;
}

// Health check
if ($path === '/health') {
    echo json_encode([
        'status' => 'ok', 
        'message' => 'API is running',
        'timestamp' => date('Y-m-d H:i:s'),
        'method' => $method
    ]);
    return;
}

// Properties endpoint
if ($path === '/properties') {
    if ($method === 'GET') {
        try {
            // Simulate database query for testing
            $properties = [
                [
                    'id' => 1,
                    'title' => 'Sample Property 1',
                    'price' => 100000,
                    'location' => 'Gorakhpur',
                    'type' => 'residential',
                    'status' => 'active',
                    'bedrooms' => 3,
                    'bathrooms' => 2,
                    'area' => '1500 sqft'
                ],
                [
                    'id' => 2,
                    'title' => 'Sample Property 2',
                    'price' => 150000,
                    'location' => 'Gorakhpur',
                    'type' => 'commercial',
                    'status' => 'active',
                    'bedrooms' => 0,
                    'bathrooms' => 1,
                    'area' => '2000 sqft'
                ],
                [
                    'id' => 3,
                    'title' => 'Sample Property 3',
                    'price' => 200000,
                    'location' => 'Gorakhpur',
                    'type' => 'residential',
                    'status' => 'active',
                    'bedrooms' => 4,
                    'bathrooms' => 3,
                    'area' => '2500 sqft'
                ]
            ];
            echo json_encode([
                'success' => true, 
                'data' => $properties,
                'count' => count($properties),
                'message' => 'Properties retrieved successfully'
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
        }
        return;
    }
}

// Auth endpoints
if (strpos($path, '/auth/') === 0) {
    $authAction = str_replace('/auth/', '', $path);
    
    if ($authAction === 'login' && $method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        $email = $input['email'] ?? '';
        $password = $input['password'] ?? '';
        
        if ($email === 'test@example.com' && $password === 'test123') {
            echo json_encode([
                'success' => true,
                'message' => 'Login successful',
                'token' => 'sample_jwt_token_12345',
                'user' => [
                    'id' => 1,
                    'name' => 'Test User',
                    'email' => 'test@example.com',
                    'role' => 'user'
                ]
            ]);
        } else {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Invalid credentials']);
        }
        return;
    }
    
    if ($authAction === 'register' && $method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        $name = $input['name'] ?? '';
        $email = $input['email'] ?? '';
        $password = $input['password'] ?? '';
        
        if ($name && $email && $password) {
            echo json_encode([
                'success' => true,
                'message' => 'Registration successful',
                'user' => [
                    'id' => 2,
                    'name' => $name,
                    'email' => $email,
                    'role' => 'user'
                ]
            ]);
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Missing required fields']);
        }
        return;
    }
}

// Search endpoint
if ($path === '/search') {
    if ($method === 'GET') {
        $query = $_GET['q'] ?? '';
        $type = $_GET['type'] ?? '';
        $minPrice = $_GET['min_price'] ?? 0;
        $maxPrice = $_GET['max_price'] ?? 999999;
        
        // Simulate search results
        $results = [
            [
                'id' => 1,
                'title' => 'Sample Property 1',
                'price' => 100000,
                'location' => 'Gorakhpur',
                'type' => 'residential',
                'match_score' => 95
            ]
        ];
        
        echo json_encode([
            'success' => true,
            'query' => $query,
            'filters' => [
                'type' => $type,
                'min_price' => $minPrice,
                'max_price' => $maxPrice
            ],
            'results' => $results,
            'count' => count($results)
        ]);
        return;
    }
}

// Property details endpoint
if (preg_match('/^\/properties\/(\d+)$/', $path, $matches)) {
    $propertyId = (int)$matches[1];
    
    if ($method === 'GET') {
        try {
            // Simulate database query
            if ($propertyId <= 3) {
                $property = [
                    'id' => $propertyId,
                    'title' => "Sample Property {$propertyId}",
                    'price' => $propertyId * 100000,
                    'location' => 'Gorakhpur',
                    'type' => 'residential',
                    'status' => 'active',
                    'description' => 'This is a sample property description.',
                    'features' => ['Bedrooms: 3', 'Bathrooms: 2', 'Area: 1500 sqft'],
                    'images' => [
                        'image1.jpg',
                        'image2.jpg',
                        'image3.jpg'
                    ]
                ];
                echo json_encode(['success' => true, 'data' => $property]);
            } else {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Property not found']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Database error']);
        }
        return;
    }
}

// If no route matched, return 404
http_response_code(404);
echo json_encode([
    'success' => false, 
    'error' => 'API endpoint not found',
    'path' => $path,
    'method' => $method,
    'available_endpoints' => [
        '/' => 'API root',
        '/health' => 'Health check',
        '/properties' => 'List properties',
        '/properties/{id}' => 'Property details',
        '/auth/login' => 'User login',
        '/auth/register' => 'User registration',
        '/search' => 'Search properties'
    ]
]);
?>