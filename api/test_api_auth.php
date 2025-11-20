<?php
/**
 * API Key Authentication Test Endpoint
 * 
 * This endpoint specifically tests the API key authentication system with hashed keys.
 * It validates API keys and returns authentication status information.
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-API-Key, Authorization');

// Handle OPTIONS request for CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Include required files
require_once __DIR__ . '/../includes/db_settings.php';
require_once __DIR__ . '/auth/api_keys.php';

// Initialize database connection
$conn = get_db_connection();
if (!$conn) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed',
        'error' => 'Unable to connect to database'
    ]);
    exit;
}

// Get API key from headers or query parameters
$apiKey = null;

// Check for X-API-Key header
$headers = getallheaders();
foreach ($headers as $name => $value) {
    if (strtolower($name) === 'x-api-key') {
        $apiKey = $value;
        break;
    }
}

// Check for Authorization header (Bearer token format)
if (!$apiKey && isset($headers['Authorization'])) {
    $authHeader = $headers['Authorization'];
    if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
        $apiKey = $matches[1];
    }
}

// Check for query parameter
if (!$apiKey && isset($_GET['api_key'])) {
    $apiKey = $_GET['api_key'];
}

if (!$apiKey) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'API key required',
        'details' => [
            'methods' => [
                'X-API-Key header',
                'Authorization: Bearer <token> header', 
                'api_key query parameter'
            ],
            'example_curl' => 'curl -H "X-API-Key: your_api_key_here" http://localhost/apsdreamhome/api/test_api_auth.php'
        ]
    ]);
    exit;
}

// Validate the API key for this endpoint
$validation = validateApiKey($apiKey, '/api/test_api_auth');

if ($validation['valid']) {
    // Authentication successful
    echo json_encode([
        'success' => true,
        'message' => 'API key authentication successful',
        'data' => [
            'user_id' => $validation['user_id'],
            'key_id' => $validation['key_id'],
            'rate_limit' => $validation['rate_limit'],
            'requests_made' => $validation['requests_made'],
            'requests_remaining' => $validation['requests_remaining'],
            'api_key_provided' => substr($apiKey, 0, 8) . '...' . substr($apiKey, -4) . ' (masked)',
            'timestamp' => date('c'),
            'endpoint' => '/api/test_api_auth'
        ]
    ], JSON_PRETTY_PRINT);
} else {
    // Authentication failed
    http_response_code($validation['status_code']);
    echo json_encode([
        'success' => false,
        'message' => 'API key authentication failed',
        'error' => $validation['message'],
        'status_code' => $validation['status_code'],
        'api_key_provided' => substr($apiKey, 0, 8) . '...' . substr($apiKey, -4) . ' (masked)',
        'details' => 'Check that your API key is valid, active, and has permission to access this endpoint'
    ], JSON_PRETTY_PRINT);
}
?>