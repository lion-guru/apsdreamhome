<?php
/**
 * API Index - Main API entry point
 * Handles API routing and responses
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    // Get the API endpoint from URL
    $request_uri = $_SERVER['REQUEST_URI'];
    $script_name = $_SERVER['SCRIPT_NAME'];
    $base_path = dirname($script_name);

    // Remove base path from request URI
    $path = substr($request_uri, strlen($base_path));

    // Remove query string
    $path = explode('?', $path)[0];

    // Remove leading slash
    $path = ltrim($path, '/');

    // Split path into segments
    $path_segments = explode('/', $path);

    // Remove empty segments
    $path_segments = array_filter($path_segments);

    // Get API version and endpoint
    $api_version = $path_segments[0] ?? 'v1';
    $endpoint = $path_segments[1] ?? '';

    // Simple API routing
    switch ($endpoint) {
        case 'properties':
            require_once __DIR__ . '/properties.php';
            break;
        case 'property':
            require_once __DIR__ . '/property.php';
            break;
        case 'inquiry':
            require_once __DIR__ . '/inquiry.php';
            break;
        case 'favorites':
            require_once __DIR__ . '/favorites.php';
            break;
        case 'property-types':
            require_once __DIR__ . '/property-types.php';
            break;
        case 'cities':
            require_once __DIR__ . '/cities.php';
            break;
        default:
            sendJsonResponse([
                'success' => false,
                'error' => 'API endpoint not found',
                'available_endpoints' => [
                    'properties',
                    'property/{id}',
                    'inquiry',
                    'favorites',
                    'property-types',
                    'cities'
                ]
            ], 404);
            break;
    }

} catch (Exception $e) {
    error_log('API Error: ' . $e->getMessage());
    sendJsonResponse([
        'success' => false,
        'error' => 'Internal server error',
        'message' => $e->getMessage()
    ], 500);
}

/**
 * Send JSON response
 */
function sendJsonResponse($data, $status_code = 200) {
    http_response_code($status_code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}
