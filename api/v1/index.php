<?php
// Enable error reporting for development
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Set default timezone
date_default_timezone_set('UTC');

// Include CORS and security headers
require_once __DIR__ . '/cors.php';

// Include configuration and functions
$configFile = __DIR__ . '/../../config.php';
$functionsFile = __DIR__ . '/../../includes/functions.php';

if (!file_exists($configFile) || !file_exists($functionsFile)) {
    http_response_code(500);
    die(json_encode([
        'success' => false,
        'error' => 'Server configuration error',
        'code' => 500
    ]));
}

require_once $configFile;
require_once $functionsFile;

// Verify database connection
if (!isset($conn) || !$conn) {
    http_response_code(500);
    die(json_encode([
        'success' => false,
        'error' => 'Database connection failed',
        'code' => 500
    ]));
}

// Include middleware and endpoints
require_once __DIR__ . '/middleware/RateLimiter.php';
require_once __DIR__ . '/endpoints/BaseEndpoint.php';
require_once __DIR__ . '/endpoints/Properties.php';
require_once __DIR__ . '/endpoints/Users.php';
require_once __DIR__ . '/endpoints/Auth.php';

// Get request method and URI
$method = $_SERVER['REQUEST_METHOD'];
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$requestUri = str_replace('/apsdreamhome/api/v1', '', $requestUri); // Adjust for subdirectory

// Get request data
$requestData = [];
if ($method === 'POST' || $method === 'PUT' || $method === 'PATCH') {
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    
    if (strpos($contentType, 'application/json') !== false) {
        $json = file_get_contents('php://input');
        $requestData = json_decode($json, true) ?? [];
    } else {
        $requestData = $_POST;
    }
}

// Simple router
$routes = [
    'POST' => [
        '/auth/login' => ['Auth', 'login', false], // No auth required
        '/auth/refresh' => ['Auth', 'refresh', false], // No auth required
        '/auth/logout' => ['Auth', 'logout', true],
        '/properties' => ['Properties', 'create', true],
        '/users' => ['Users', 'create', true, 'admin']
    ],
    'GET' => [
        '/auth/me' => ['Auth', 'me', true],
        '/properties' => ['Properties', 'list', false],
        '/properties/(\d+)' => ['Properties', 'get', false],
        '/users' => ['Users', 'list', true, 'admin'],
        '/users/(\d+)' => ['Users', 'get', true],
        '/profile' => ['Users', 'getProfile', true],
    ],
    'PUT' => [
        '/properties/(\d+)' => ['Properties', 'update', true],
        '/users/(\d+)' => ['Users', 'update', true, 'admin'],
        '/profile' => ['Users', 'updateProfile', true]
    ],
    'DELETE' => [
        '/properties/(\d+)' => ['Properties', 'delete', true],
        '/users/(\d+)' => ['Users', 'delete', true, 'admin']
    ]
];

// Find matching route
$handler = null;
$params = [];
$requiresAuth = true;
$requiredRole = null;

foreach (($routes[$method] ?? []) as $route => $handlerConfig) {
    $pattern = '#^' . preg_replace('/\{([^\}]+)\}/', '([^/]+)', $route) . '$#';
    if (preg_match($pattern, $requestUri, $matches)) {
        $handler = $handlerConfig;
        $params = array_slice($matches, 1);
        $requiresAuth = $handler[2] ?? true;
        $requiredRole = $handler[3] ?? null;
        break;
    }
}

// If no route found
if (!$handler) {
    http_response_code(404);
    echo json_encode([
        'success' => false,
        'error' => 'Endpoint not found',
        'code' => 404
    ]);
    exit;
}

// Initialize rate limiter
$rateLimiter = new RateLimiter($conn);

// Get API key from header or query parameter
$apiKey = null;
$authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
    $apiKey = $matches[1];
} else {
    $apiKey = $_GET['api_key'] ?? null;
}

// Check if endpoint requires authentication
if ($requiresAuth && !$apiKey) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'error' => 'API key is required',
        'code' => 401
    ]);
    exit;
}

// Check rate limit if API key is provided
if ($apiKey) {
    $rateLimitCheck = $rateLimiter->check($apiKey);
    if (!$rateLimitCheck['allowed']) {
        http_response_code($rateLimitCheck['status'] ?? 429);
        header('X-RateLimit-Limit: ' . ($rateLimitCheck['limit'] ?? 100));
        header('X-RateLimit-Remaining: ' . ($rateLimitCheck['remaining'] ?? 0));
        header('X-RateLimit-Reset: ' . ($rateLimitCheck['reset'] ?? time() + 60));
        echo json_encode([
            'success' => false,
            'error' => $rateLimitCheck['error'],
            'code' => $rateLimitCheck['status'] ?? 429
        ]);
        exit;
    }

    // Add rate limit headers to response
    header('X-RateLimit-Limit: ' . $rateLimitCheck['limit']);
    header('X-RateLimit-Remaining: ' . $rateLimitCheck['remaining']);
    header('X-RateLimit-Reset: ' . $rateLimitCheck['reset']);
}

// Get user info if API key is provided
$userId = null;
$userRole = null;

if ($apiKey) {
    // Get user info from API key
    $stmt = $conn->prepare("
        SELECT u.id, u.role, u.status, ak.id as api_key_id 
        FROM api_keys ak 
        JOIN users u ON ak.user_id = u.id 
        WHERE ak.api_key = ? 
        AND (ak.expires_at IS NULL OR ak.expires_at > NOW()) 
        AND (ak.revoked IS NULL OR ak.revoked = 0)
        AND u.status = 'active'
    ");
    
    $hashedKey = hash('sha256', $apiKey);
    $stmt->bind_param('s', $hashedKey);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $userId = $user['id'];
        $userRole = $user['role'];
        $apiKeyId = $user['api_key_id'];
        
        // Log the API request
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        
        $logStmt = $conn->prepare("
            INSERT INTO api_request_logs (api_key_id, endpoint, method, ip_address, user_agent) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $logStmt->bind_param('issss', $apiKeyId, $requestUri, $method, $ip, $userAgent);
        $logStmt->execute();
    } elseif ($requiresAuth) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'error' => 'Invalid or expired API key',
            'code' => 401
        ]);
        exit;
    }
}

// Check if user has required role
if ($requiredRole && $userRole !== $requiredRole) {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'error' => 'Insufficient permissions',
        'code' => 403
    ]);
    exit;
}

// Initialize the endpoint
$endpointClass = $handler[0];
$methodName = $handler[1];
$endpoint = new $endpointClass($conn, $userId, $userRole);

// Call the endpoint method
try {
    // Prepare parameters
    $methodParams = [];
    
    // For auth/login, pass email and password
    if ($requestUri === '/auth/login' && $methodName === 'login') {
        $methodParams = [
            $requestData['email'] ?? '',
            $requestData['password'] ?? ''
        ];
    } 
    // For auth/logout, pass the API key
    elseif ($requestUri === '/auth/logout' && $methodName === 'logout') {
        $methodParams = [$apiKey];
    }
    // For other endpoints, pass request data and URL parameters
    else {
        $methodParams = array_merge([$requestData], $params);
    }
    
    // Call the method
    $response = call_user_func_array([$endpoint, $methodName], $methodParams);
    
    // Send the response
    if (is_array($response) && isset($response['success'])) {
        http_response_code($response['status'] ?? 200);
        header('Content-Type: application/json');
        echo json_encode($response, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    } else {
        http_response_code(200);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'data' => $response
        ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }
    
} catch (Exception $e) {
    $code = $e->getCode() >= 400 ? $e->getCode() : 500;
    http_response_code($code);
    
    $error = [
        'success' => false,
        'error' => $e->getMessage(),
        'code' => $code
    ];
    
    // Add more details in development
    if (ini_get('display_errors')) {
        $error['file'] = $e->getFile();
        $error['line'] = $e->getLine();
        $error['trace'] = $e->getTraceAsString();
    }
    
    echo json_encode($error, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
}
?>
