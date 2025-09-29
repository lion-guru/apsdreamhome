<?php
/**
 * Get Lead Details API Endpoint
 * 
 * Fetches comprehensive information about a specific lead.
 * 
 * @version 1.0.0
 */

// Unified bootstrap for autoload, helpers, timezone, session, headers
require_once __DIR__ . '/includes/bootstrap.php';

// Include required files with error handling
try {
    require_once __DIR__ . '/../includes/Database.php';
    require_once __DIR__ . '/lead_helpers.php';
} catch (Exception $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'error' => 'Internal server error: ' . $e->getMessage(),
        'code' => 'INITIALIZATION_ERROR'
    ]);
    exit;
}

// Set default request method if not set (e.g., when running from CLI)
if (!isset($_SERVER['REQUEST_METHOD'])) {
    $_SERVER['REQUEST_METHOD'] = 'GET';
}

// Initialize response array
$response = [
    'success' => false,
    'data' => null,
    'error' => null,
    'code' => null,
    'meta' => [
        'request_id' => uniqid('lead_', true),
        'timestamp' => date('c'),
        'version' => '1.0',
        'included' => []
    ]
];

try {
    // Get database connection with error handling
    try {
        // Create a new instance of the Database class
        $db = new Database();
        $conn = $db->getConnection();
        
        // Test connection
        if (!$conn || !$conn->ping()) {
            throw new Exception('Database connection failed');
        }
    } catch (Exception $e) {
        throw new Exception('Database connection error: ' . $e->getMessage());
    }
    
    // Get and validate action from query string
    $action = isset($_GET['action']) ? htmlspecialchars($_GET['action'], ENT_QUOTES, 'UTF-8') : 'get';
    $leadId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    $includeRelated = isset($_GET['include']) ? htmlspecialchars($_GET['include'], ENT_QUOTES, 'UTF-8') : '';
    
    // For testing purposes, if running from CLI
    if (php_sapi_name() === 'cli') {
        // Set some default values for testing
        if (!isset($_SERVER['HTTP_HOST'])) $_SERVER['HTTP_HOST'] = 'localhost';
        if (!isset($_SERVER['REQUEST_URI'])) $_SERVER['REQUEST_URI'] = '/api/get_lead_details.php';
        if (!isset($_SERVER['REMOTE_ADDR'])) $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        
        // If no lead ID is provided, set a default one for testing
        if (!$leadId) {
            $leadId = 1; // Default test lead ID
        }
    }
    
    // Minimal validation using ValidationService (returns 422 on failure)
    $input = ['id' => $_GET['id'] ?? null];
    if (class_exists('App\\Common\\Services\\ValidationService')) {
        $validator = new \App\Common\Services\ValidationService($input, [
            'id' => ['required', 'numeric']
        ]);
        if (!$validator->validate()) {
            http_response_code(422);
            $response['error'] = 'Validation failed';
            $response['code'] = 'VALIDATION_ERROR';
            $response['meta']['validation_errors'] = $validator->getErrors();
            echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            exit;
        }
    }
    
    // Authentication required for lead details (skip in CLI for testing)
    $userId = (isset($_SESSION) && isset($_SESSION['user_id'])) ? $_SESSION['user_id'] : null;
    if (php_sapi_name() !== 'cli' && $userId === null) {
        http_response_code(401);
        if (class_exists('App\\Common\\Transformers\\ResponseTransformer') && function_exists('json_response')) {
            $out = \App\Common\Transformers\ResponseTransformer::error('Authentication required', 'UNAUTHORIZED', 401);
            json_response($out, 401);
        } else {
            $response['error'] = 'Authentication required';
            $response['code'] = 'UNAUTHORIZED';
            echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        }
        exit;
    }
    
    // Process the request based on action
    switch ($action) {
        case 'get':
        default:
            try {
                // Validate lead ID
                if (empty($leadId)) {
                    throw new InvalidArgumentException('Lead ID is required', 400);
                }

                // Get single lead details with comprehensive data
                $lead = getLeadById($conn, $leadId, ['user_id' => $userId]);

                if (!$lead) {
                    throw new RuntimeException('Lead not found or access denied', 404);
                }

                // Prepare success response
                $response = [
                    'success' => true,
                    'data' => $lead,
                    'meta' => [
                        'request_id' => $response['meta']['request_id'] ?? uniqid('req_', true),
                        'timestamp' => date('c'),
                        'version' => '1.0.0'
                    ]
                ];

                http_response_code(200);
            } catch (InvalidArgumentException $e) {
                // Client-side errors (400 Bad Request)
                $response = [
                    'success' => false,
                    'error' => $e->getMessage(),
                    'code' => 'INVALID_INPUT',
                    'meta' => [
                        'request_id' => $response['meta']['request_id'] ?? uniqid('req_', true),
                        'timestamp' => date('c')
                    ]
                ];
                http_response_code($e->getCode() >= 400 ? $e->getCode() : 400);
                
            } catch (RuntimeException $e) {
                // Not found or access denied (404)
                $response = [
                    'success' => false,
                    'error' => $e->getMessage(),
                    'code' => 'LEAD_NOT_FOUND',
                    'meta' => [
                        'request_id' => $response['meta']['request_id'] ?? uniqid('req_', true),
                        'timestamp' => date('c')
                    ]
                ];
                http_response_code(404);
            }
            break;
    }

} catch (Exception $e) {
    // Handle any exceptions
    http_response_code(500);
    if (class_exists('App\\Common\\Transformers\\ResponseTransformer') && function_exists('json_response')) {
        $out = \App\Common\Transformers\ResponseTransformer::error('An error occurred while processing your request', 'INTERNAL_SERVER_ERROR', 500);
        json_response($out, 500);
    } else {
        $response['error'] = 'An error occurred while processing your request';
        $response['code'] = 'INTERNAL_SERVER_ERROR';
        $response['debug'] = [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ];
        echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
}

if (!headers_sent()) {
    header('Content-Type: application/json');
}

$status = http_response_code() ?: ($response['success'] ? 200 : 400);
if (class_exists('\App\Common\Transformers\ResponseTransformer') && function_exists('json_response')) {
    if ($response['success']) {
        $out = \App\Common\Transformers\ResponseTransformer::success($response['data'] ?? null, null, $status);
    } else {
        $out = \App\Common\Transformers\ResponseTransformer::error($response['error'] ?? 'Error', $response['code'] ?? 'error', $status);
    }
    json_response($out, $status);
} else {
    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
}

exit(0);
