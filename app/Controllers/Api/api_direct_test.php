<?php
/**
 * API Test Script - Direct Testing
 * Tests API endpoints directly without Apache routing
 */

// Set content type to JSON
header('Content-Type: application/json');

// Get the endpoint from GET parameter
$endpoint = $_GET['endpoint'] ?? '/';

// Simulate different endpoints based on parameter
switch ($endpoint) {
    case '/':
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
        break;
        
    case '/health':
        echo json_encode([
            'status' => 'ok', 
            'message' => 'API is running',
            'timestamp' => date('Y-m-d H:i:s'),
            'method' => $_SERVER['REQUEST_METHOD'] ?? 'GET'
        ]);
        break;
        
    case '/properties':
        echo json_encode([
            'success' => true, 
            'data' => [
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
                ]
            ],
            'count' => 2,
            'message' => 'Properties retrieved successfully'
        ]);
        break;
        
    case '/auth/login':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
        } else {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Method not allowed']);
        }
        break;
        
    case '/auth/register':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
        } else {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Method not allowed']);
        }
        break;
        
    case '/search':
        $query = $_GET['q'] ?? '';
        $type = $_GET['type'] ?? '';
        echo json_encode([
            'success' => true,
            'query' => $query,
            'filters' => [
                'type' => $type,
                'min_price' => $_GET['min_price'] ?? 0,
                'max_price' => $_GET['max_price'] ?? 999999
            ],
            'results' => [
                [
                    'id' => 1,
                    'title' => 'Sample Property 1',
                    'price' => 100000,
                    'location' => 'Gorakhpur',
                    'match_score' => 95
                ]
            ],
            'count' => 1
        ]);
        break;
        
    default:
        http_response_code(404);
        echo json_encode([
            'success' => false, 
            'error' => 'API endpoint not found',
            'endpoint' => $endpoint,
            'available_endpoints' => [
                '/' => 'API root',
                '/health' => 'Health check',
                '/properties' => 'List properties',
                '/auth/login' => 'User login',
                '/auth/register' => 'User registration',
                '/search' => 'Search properties'
            ]
        ]);
}
?>
