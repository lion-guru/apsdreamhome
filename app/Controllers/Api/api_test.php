<?php
/**
 * Simple API Test Script
 * Tests API functionality independently of the main application
 */

// Set content type to JSON
header('Content-Type: application/json');

// Get the request URI and method
$uri = $_SERVER["REQUEST_URI"] ?? "/";
$method = $_SERVER["REQUEST_METHOD"] ?? "GET";

// Parse URI to get clean path
$path = parse_url($uri, PHP_URL_PATH);
$path = rtrim($path, '/');

// Remove /api prefix
$endpoint = str_replace('/api', '', $path);

// Basic API routing
if ($endpoint === '' || $endpoint === '/') {
    // API root - show available endpoints
    echo json_encode([
        'message' => 'APS Dream Home API - Test Mode',
        'version' => '1.0.0',
        'endpoints' => [
            'GET /health' => 'Health check',
            'GET /properties' => 'List all properties',
            'POST /auth/login' => 'User login',
            'GET /search' => 'Search properties'
        ]
    ]);
    return;
}

// Health check
if ($endpoint === '/health') {
    echo json_encode(['status' => 'ok', 'message' => 'API is running']);
    return;
}

// Properties endpoint
if ($endpoint === '/properties') {
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
                    'status' => 'active'
                ],
                [
                    'id' => 2,
                    'title' => 'Sample Property 2',
                    'price' => 150000,
                    'location' => 'Gorakhpur',
                    'type' => 'commercial',
                    'status' => 'active'
                ]
            ];
            echo json_encode(['success' => true, 'data' => $properties]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Database error']);
        }
        return;
    }
}

// Auth endpoints
if (strpos($endpoint, '/auth/') === 0) {
    $authAction = str_replace('/auth/', '', $endpoint);
    
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
                    'email' => 'test@example.com'
                ]
            ]);
        } else {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Invalid credentials']);
        }
        return;
    }
}

// Search endpoint
if ($endpoint === '/search') {
    if ($method === 'GET') {
        $query = $_GET['q'] ?? '';
        echo json_encode([
            'success' => true,
            'query' => $query,
            'results' => [
                [
                    'id' => 1,
                    'title' => 'Sample Property 1',
                    'price' => 100000,
                    'location' => 'Gorakhpur'
                ]
            ]
        ]);
        return;
    }
}

// If no route matched, return 404
http_response_code(404);
echo json_encode(['success' => false, 'error' => 'API endpoint not found']);
?>
