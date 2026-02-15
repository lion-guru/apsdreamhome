<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set content type first to ensure proper JSON output
header('Content-Type: application/json');

// Check if required files exist
$configFile = __DIR__ . '/../../config.php';
$functionsFile = __DIR__ . '/../../includes/functions.php';

if (!file_exists($configFile) || !file_exists($functionsFile)) {
    http_response_code(500);
    die(json_encode([
        'error' => 'Required files not found',
        'config_exists' => file_exists($configFile),
        'functions_exists' => file_exists($functionsFile)
    ]));
}

// Include required files
require_once $configFile;
require_once $functionsFile;
require_once __DIR__ . '/middleware/RateLimiter.php';

// Verify database connection
if (!isset($conn) || !$conn) {
    http_response_code(500);
    die(json_encode(['error' => 'Database connection failed']));
}

// Get API key from header or query parameter
$apiKey = $_SERVER['HTTP_X_API_KEY'] ?? $_GET['api_key'] ?? null;

if (!$apiKey) {
    http_response_code(401);
    echo json_encode(['error' => 'API key is required']);
    exit;
}

try {
    // Initialize rate limiter (100 requests per minute by default)
    $rateLimiter = new RateLimiter($conn);
    $rateLimitCheck = $rateLimiter->check($apiKey);

    if (!$rateLimitCheck['allowed']) {
        http_response_code($rateLimitCheck['status'] ?? 429);
        header('X-RateLimit-Limit: ' . ($rateLimitCheck['limit'] ?? 100));
        header('X-RateLimit-Remaining: ' . ($rateLimitCheck['remaining'] ?? 0));
        header('X-RateLimit-Reset: ' . ($rateLimitCheck['reset'] ?? time() + 60));
        echo json_encode(['error' => $rateLimitCheck['error']]);
        exit;
    }

    // Add rate limit headers to response
    header('X-RateLimit-Limit: ' . $rateLimitCheck['limit']);
    header('X-RateLimit-Remaining: ' . $rateLimitCheck['remaining']);
    header('X-RateLimit-Reset: ' . $rateLimitCheck['reset']);

    // Validate API key and get user info
    $stmt = $conn->prepare("SELECT u.*, ak.name as api_key_name, ak.permissions 
                         FROM api_keys ak 
                         JOIN users u ON ak.user_id = u.id 
                         WHERE ak.api_key = ? AND ak.status = 'active'");
    $stmt->bind_param('s', $apiKey);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception('Invalid or inactive API key', 403);
    }

    $user = $result->fetch_assoc();

    // Log the API request
    $endpoint = $_SERVER['REQUEST_URI'];
    $ip = $_SERVER['REMOTE_ADDR'];
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';

    $logStmt = $conn->prepare("INSERT INTO api_request_logs (api_key_id, endpoint, ip_address, user_agent) 
                             VALUES (?, ?, ?, ?)");
    $logStmt->bind_param('isss', $user['id'], $endpoint, $ip, $userAgent);
    $logStmt->execute();

    // Return user info (excluding sensitive data)
    unset($user['password']);
    unset($user['api_key']);

    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'API authentication successful',
        'user' => $user,
        'permissions' => json_decode($user['permissions'], true),
        'api_key_name' => $user['api_key_name'],
        'rate_limit' => [
            'limit' => $rateLimitCheck['limit'],
            'remaining' => $rateLimitCheck['remaining'],
            'reset' => $rateLimitCheck['reset']
        ]
    ]);

} catch (Exception $e) {
    $statusCode = $e->getCode() >= 400 ? $e->getCode() : 500;
    http_response_code($statusCode);
    echo json_encode([
        'error' => $e->getMessage(),
        'code' => $e->getCode()
    ]);
}
?>
