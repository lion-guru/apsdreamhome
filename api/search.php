<?php
/**
 * Property Search API Endpoint
 * Handles AJAX property search requests with filtering, sorting, and pagination
 * Standardized to use bootstrap and ResponseTransformer
 */

// Unified bootstrap for autoloading, json helper, timezone, session, and base JSON header
require_once __DIR__ . '/includes/bootstrap.php';

// Set error logging for this endpoint
ini_set('error_log', __DIR__ . '/../logs/search_api_error.log');

// Apply rate limiting
if (class_exists('App\\Common\\Middleware\\RateLimitMiddleware')) {
    $rateLimitMiddleware = new \App\Common\Middleware\RateLimitMiddleware();
    $rateLimitMiddleware->handle('search');
} else {
    // Fallback rate limiting if middleware is not available
    $max_search_requests = 60; // requests per minute
    $time_window = 60; // 1 minute in seconds
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
    
    $rate_limit_key = 'search_rate_limit_' . md5($ip_address);
    $rate_limit_data = $_SESSION[$rate_limit_key] ?? [
        'count' => 0,
        'first_request' => time()
    ];
    
    // Reset counter if time window has passed
    if (time() - $rate_limit_data['first_request'] > $time_window) {
        $rate_limit_data = [
            'count' => 0,
            'first_request' => time()
        ];
    }
    
    // Check rate limit
    if ($rate_limit_data['count'] >= $max_search_requests) {
        $wait_time = ($rate_limit_data['first_request'] + $time_window) - time();
        
        if (class_exists('App\\Common\\Transformers\\ResponseTransformer') && function_exists('json_response')) {
            $out = \App\Common\Transformers\ResponseTransformer::error(
                'Too many requests',
                'RATE_LIMIT_EXCEEDED',
                429,
                ['retry_after' => $wait_time]
            );
            json_response($out, 429);
        } else {
            http_response_code(429);
            header('Retry-After: ' . $wait_time);
            header('Content-Type: application/json');
            echo json_encode([
                'error' => 'Too many requests',
                'message' => 'Rate limit exceeded. Please try again later.',
                'retry_after' => $wait_time,
                'code' => 'RATE_LIMIT_EXCEEDED'
            ]);
        }
        exit();
    }
    
    // Increment request count
    $rate_limit_data['count']++;
    $_SESSION[$rate_limit_key] = $rate_limit_data;
    
    // Store in globals for access in sendSecurityResponse
    $GLOBALS['max_search_requests'] = $max_search_requests;
    $GLOBALS['rate_limit_data'] = $rate_limit_data;
    $GLOBALS['time_window'] = $time_window;
}

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Security event logging function
function logSecurityEvent($event, $context = []) {
    static $logFile = null;

    if ($logFile === null) {
        $logDir = __DIR__ . '/../logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        $logFile = $logDir . '/search_api_security.log';
    }

    $timestamp = date('Y-m-d H:i:s');
    $contextStr = '';

    if (!empty($context)) {
        foreach ($context as $key => $value) {
            try {
                if (is_null($value)) {
                    $strValue = 'NULL';
                } elseif (is_bool($value)) {
                    $strValue = $value ? 'TRUE' : 'FALSE';
                } elseif (is_scalar($value)) {
                    $strValue = (string)$value;
                } elseif (is_array($value) || is_object($value)) {
                    $strValue = json_encode($value, JSON_PARTIAL_OUTPUT_ON_ERROR | JSON_UNESCAPED_UNICODE);
                } else {
                    $strValue = 'UNKNOWN_TYPE';
                }

                $strValue = mb_strlen($strValue) > 500 ? mb_substr($strValue, 0, 500) . '...' : $strValue;
                $contextStr .= " | $key: $strValue";
            } catch (Exception $e) {
                $contextStr .= " | $key: SERIALIZATION_ERROR";
            }
        }
    }

    $logMessage = "[{$timestamp}] {$event}{$contextStr}\n";
    file_put_contents($logFile, $logMessage, FILE_APPEND);
    error_log($logMessage);
}

/**
 * Send a standardized JSON response with security headers
 * 
 * @param int $status_code HTTP status code
 * @param string $message Response message
 * @param mixed $data Response data (optional)
 * @param string|null $error_code Error code (required for error responses)
 * @param array $headers Additional headers to include
 */
function sendSecurityResponse($status_code, $message, $data = null, $error_code = null, $headers = []) {
    // Set default security headers
    $security_headers = [
        'X-Content-Type-Options' => 'nosniff',
        'X-Frame-Options' => 'DENY',
        'X-XSS-Protection' => '1; mode=block',
        'Referrer-Policy' => 'strict-origin-when-cross-origin',
        'Content-Security-Policy' => "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline';",
        'Strict-Transport-Security' => 'max-age=31536000; includeSubDomains',
        'Permissions-Policy' => 'geolocation=(), microphone=(), camera=()',
        'X-Permitted-Cross-Domain-Policies' => 'none',
        'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
        'Pragma' => 'no-cache',
        'Expires' => '0',
        'X-Response-Time' => (microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']) * 1000 . 'ms',
        'X-Security-Status' => 'protected',
        'X-Request-ID' => uniqid('search_')
    ];
    
    // Add rate limit headers if available
    if (isset($GLOBALS['max_search_requests']) && isset($GLOBALS['rate_limit_data']) && isset($GLOBALS['time_window'])) {
        $security_headers['X-Rate-Limit-Limit'] = $GLOBALS['max_search_requests'];
        $security_headers['X-Rate-Limit-Remaining'] = $GLOBALS['max_search_requests'] - $GLOBALS['rate_limit_data']['count'];
        $security_headers['X-Rate-Limit-Reset'] = $GLOBALS['rate_limit_data']['first_request'] + $GLOBALS['time_window'];
    }

    // Merge additional headers
    $headers = array_merge($security_headers, $headers);

    // Prepare response data
    $response_data = [
        'success' => $status_code >= 200 && $status_code < 300,
        'message' => $message,
        'timestamp' => date('c'),
        'request_id' => $headers['X-Request-ID']
    ];

    // Add data or error details
    if ($status_code >= 200 && $status_code < 300) {
        if ($data !== null) {
            $response_data['data'] = $data;
        }
    } else {
        $response_data['error'] = [
            'code' => $error_code ?? 'API_ERROR',
            'message' => $message,
            'details' => $data
        ];
    }

    // Use ResponseTransformer if available
    if (class_exists('App\\Common\\Transformers\\ResponseTransformer') && function_exists('json_response')) {
        if ($status_code >= 200 && $status_code < 300) {
            $out = \App\Common\Transformers\ResponseTransformer::success(
                $data,
                $message,
                $status_code,
                ['request_id' => $response_data['request_id']]
            );
        } else {
            $out = \App\Common\Transformers\ResponseTransformer::error(
                $message,
                $error_code ?? 'API_ERROR',
                $status_code,
                $data
            );
        }
        
        // Set headers
        foreach ($headers as $name => $value) {
            header("$name: $value");
        }
        
        json_response($out, $status_code);
    } else {
        // Fallback to manual JSON response
        http_response_code($status_code);
        
        // Set headers
        foreach ($headers as $name => $value) {
            header("$name: $value");
        }
        
        // Ensure JSON content type is set
        if (!headers_sent() && !isset($headers['Content-Type'])) {
            header('Content-Type: application/json');
        }
        
        echo json_encode($response_data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
    
    exit();
}

// Enhanced input validation and sanitization
function validateInput($input, $type = 'string', $max_length = null, $required = false) {
    if ($input === null) {
        if ($required) {
            return false;
        }
        return '';
    }

    $input = trim($input);

    if ($required && empty($input)) {
        return false;
    }

    switch ($type) {
        case 'email':
            $input = filter_var($input, FILTER_SANITIZE_EMAIL);
            if (!filter_var($input, FILTER_VALIDATE_EMAIL)) {
                return false;
            }
            break;
        case 'integer':
            $input = filter_var($input, FILTER_VALIDATE_INT);
            if ($input === false) {
                return false;
            }
            break;
        case 'float':
            $input = filter_var($input, FILTER_VALIDATE_FLOAT);
            if ($input === false) {
                return false;
            }
            break;
        case 'search':
            // Allow alphanumeric, spaces, and common search characters
            if (!preg_match('/^[a-zA-Z0-9\s\-_,.()]+$/', $input)) {
                return false;
            }
            $input = filter_var($input, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
            break;
        case 'string':
        default:
            $input = filter_var($input, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
            break;
    }

    if ($max_length && strlen($input) > $max_length) {
        return false;
    }

    return $input;
}

// Validate request headers
function validateRequestHeaders() {
    // Check User-Agent (basic bot detection)
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    if (empty($user_agent) || strlen($user_agent) < 10) {
        logSecurityEvent('Suspicious User Agent in Search API', [
            'user_agent' => $user_agent,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN'
        ]);
    }

    // Check for suspicious headers
    $suspicious_headers = ['X-Forwarded-For', 'X-Real-IP', 'CF-Connecting-IP'];
    foreach ($suspicious_headers as $header) {
        if (isset($_SERVER[$header])) {
            logSecurityEvent('Suspicious Header Detected', [
                'header' => $header,
                'value' => $_SERVER[$header],
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN'
            ]);
        }
    }

    return true;
}

// Main API logic
try {
    // Validate request method
    if ($_SERVER['REQUEST_METHOD'] !== 'GET' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
        logSecurityEvent('Invalid Request Method in Search API', [
            'method' => $_SERVER['REQUEST_METHOD'],
            'ip_address' => $ip_address,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN'
        ]);
        sendSecurityResponse(405, 'Method not allowed. Only GET and POST requests are accepted.');
    }

    // Validate request headers
    if (!validateRequestHeaders()) {
        sendSecurityResponse(400, 'Invalid request headers.');
    }

    // Load required files with validation
    $required_files = [
        __DIR__ . '/../includes/db_connection.php'
    ];

    foreach ($required_files as $file) {
        if (!file_exists($file) || !is_readable($file)) {
            logSecurityEvent('Required File Missing in Search API', [
                'file_path' => $file,
                'ip_address' => $ip_address
            ]);
            sendSecurityResponse(500, 'System configuration error.');
        }
    }

    require_once $required_files[0];

    // Get database connection
    $conn = getDbConnection();

    if (!$conn) {
        logSecurityEvent('Database Connection Failed in Search API', [
            'ip_address' => $ip_address
        ]);
        sendSecurityResponse(500, 'Database connection error.');
    }

    // Enhanced parameter validation and sanitization
    $params = [
        'keyword' => isset($_GET['keyword']) ? validateInput($_GET['keyword'], 'search', 100) : '',
        'property_type' => isset($_GET['property_type']) ? validateInput($_GET['property_type'], 'string', 50) : '',
        'purpose' => isset($_GET['purpose']) ? validateInput($_GET['purpose'], 'string', 20) : '',
        'price_range' => isset($_GET['price_range']) ? validateInput($_GET['price_range'], 'integer') : 0,
        'bedrooms' => isset($_GET['bedrooms']) ? validateInput($_GET['bedrooms'], 'integer') : 0,
        'bathrooms' => isset($_GET['bathrooms']) ? validateInput($_GET['bathrooms'], 'integer') : 0,
        'page' => isset($_GET['page']) ? max(1, validateInput($_GET['page'], 'integer')) : 1,
        'limit' => isset($_GET['limit']) ? min(50, max(1, validateInput($_GET['limit'], 'integer'))) : 12
    ];

    // Validate all parameters
    foreach ($params as $key => $value) {
        if ($value === false) {
            logSecurityEvent('Invalid Parameter in Search API', [
                'parameter' => $key,
                'value' => $_GET[$key] ?? 'NULL',
                'ip_address' => $ip_address
            ]);
            sendSecurityResponse(400, 'Invalid parameter: ' . $key);
        }
    }

    // Log search request for analytics and security monitoring
    logSecurityEvent('Property Search Request', [
        'ip_address' => $ip_address,
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN',
        'parameters' => json_encode(array_filter($params)), // Only log non-empty parameters
        'page' => $params['page'],
        'limit' => $params['limit']
    ]);

    // Build the base query with enhanced security
    $query = "
        SELECT
            p.id, p.title, p.slug, p.description, p.price, p.bedrooms, p.bathrooms,
            p.area, p.area_unit, p.address, p.city, p.state, p.country, p.postal_code,
            p.latitude, p.longitude, p.is_featured, p.status, p.year_built, p.garage,
            p.garage_size, p.created_at, p.updated_at,
            pt.name as property_type, pt.purpose,
            u.first_name, u.last_name, u.phone as agent_phone, u.email as agent_email,
            (SELECT pi.image_url
             FROM property_images pi
             WHERE pi.property_id = p.id
             ORDER BY pi.is_primary DESC, pi.id ASC
             LIMIT 1) as main_image
        FROM properties p
        LEFT JOIN property_types pt ON p.property_type_id = pt.id
        LEFT JOIN users u ON p.agent_id = u.id
        WHERE p.status = 'available' AND p.is_active = 1
    ";

    $conditions = [];
    $bindParams = [];
    $types = '';

    // Add keyword search condition with enhanced security
    if (!empty($params['keyword'])) {
        $conditions[] = "(
            p.title LIKE CONCAT('%', ?, '%') OR
            p.description LIKE CONCAT('%', ?, '%') OR
            p.address LIKE CONCAT('%', ?, '%') OR
            p.city LIKE CONCAT('%', ?, '%') OR
            p.state LIKE CONCAT('%', ?, '%') OR
            p.country LIKE CONCAT('%', ?, '%') OR
            pt.name LIKE CONCAT('%', ?, '%')
        )";
        $searchTerm = $params['keyword'];
        $bindParams = array_merge($bindParams, array_fill(0, 7, $searchTerm));
        $types .= str_repeat('s', 7);
    }

    // Add property type filter with validation
    if (!empty($params['property_type'])) {
        $conditions[] = "pt.name = ?";
        $bindParams[] = $params['property_type'];
        $types .= 's';
    }

    // Add purpose filter with validation
    if (!empty($params['purpose'])) {
        $allowed_purposes = ['sale', 'rent'];
        if (in_array($params['purpose'], $allowed_purposes)) {
            $conditions[] = "pt.purpose = ?";
            $bindParams[] = $params['purpose'];
            $types .= 's';
        }
    }

    // Add price range filter with validation
    if ($params['price_range'] > 0) {
        $conditions[] = "p.price <= ?";
        $bindParams[] = $params['price_range'];
        $types .= 'i';
    }

    // Add bedrooms filter with validation
    if ($params['bedrooms'] > 0) {
        $conditions[] = "p.bedrooms >= ?";
        $bindParams[] = $params['bedrooms'];
        $types .= 'i';
    }

    // Add bathrooms filter with validation
    if ($params['bathrooms'] > 0) {
        $conditions[] = "p.bathrooms >= ?";
        $bindParams[] = $params['bathrooms'];
        $types .= 'i';
    }

    // Combine all conditions
    if (!empty($conditions)) {
        $query .= " AND " . implode(" AND ", $conditions);
    }

    // Get total count for pagination with enhanced security
    $countQuery = "SELECT COUNT(*) as total FROM (" . str_replace(
        "SELECT p.id, p.title, p.slug, p.description, p.price, p.bedrooms, p.bathrooms,
            p.area, p.area_unit, p.address, p.city, p.state, p.country, p.postal_code,
            p.latitude, p.longitude, p.is_featured, p.status, p.year_built, p.garage,
            p.garage_size, p.created_at, p.updated_at,
            pt.name as property_type, pt.purpose,
            u.first_name, u.last_name, u.phone as agent_phone, u.email as agent_email,
            (SELECT pi.image_url FROM property_images pi WHERE pi.property_id = p.id ORDER BY pi.is_primary DESC, pi.id ASC LIMIT 1) as main_image",
        "SELECT p.id",
        $query
    ) . ") as count_table";

    // Prepare and execute count query
    $countStmt = $conn->prepare($countQuery);

    if (!$countStmt) {
        logSecurityEvent('Count Query Preparation Failed', [
            'error' => $conn->error,
            'ip_address' => $ip_address
        ]);
        sendSecurityResponse(500, 'Database query preparation failed.');
    }

    if (!empty($bindParams)) {
        $countStmt->bind_param($types, ...$bindParams);
    }

    if (!$countStmt->execute()) {
        logSecurityEvent('Count Query Execution Failed', [
            'error' => $countStmt->error,
            'ip_address' => $ip_address
        ]);
        $countStmt->close();
        sendSecurityResponse(500, 'Database query execution failed.');
    }

    $countResult = $countStmt->get_result();
    $total = $countResult->fetch_assoc()['total'];
    $countStmt->close();

    // Add sorting and pagination with security validation
    $offset = ($params['page'] - 1) * $params['limit'];
    $query .= " ORDER BY p.is_featured DESC, p.created_at DESC LIMIT ? OFFSET ?";

    // Add pagination parameters
    $bindParams[] = $params['limit'];
    $bindParams[] = $offset;
    $types .= 'ii';

    // Prepare and execute main query
    $stmt = $conn->prepare($query);

    if (!$stmt) {
        logSecurityEvent('Main Query Preparation Failed', [
            'error' => $conn->error,
            'ip_address' => $ip_address
        ]);
        sendSecurityResponse(500, 'Database query preparation failed.');
    }

    if (!empty($bindParams)) {
        $stmt->bind_param($types, ...$bindParams);
    }

    if (!$stmt->execute()) {
        logSecurityEvent('Main Query Execution Failed', [
            'error' => $stmt->error,
            'ip_address' => $ip_address
        ]);
        $stmt->close();
        sendSecurityResponse(500, 'Database query execution failed.');
    }

    $result = $stmt->get_result();
    $properties = [];

    // Process results with security validation
    while ($row = $result->fetch_assoc()) {
        // Format property data with enhanced security
        $property = [
            'id' => (int)$row['id'],
            'title' => htmlspecialchars($row['title'], ENT_QUOTES, 'UTF-8'),
            'slug' => htmlspecialchars($row['slug'], ENT_QUOTES, 'UTF-8'),
            'description' => htmlspecialchars(substr($row['description'], 0, 200), ENT_QUOTES, 'UTF-8'),
            'price' => (float)$row['price'],
            'price_formatted' => '₹' . number_format($row['price']),
            'bedrooms' => (int)$row['bedrooms'],
            'bathrooms' => (int)$row['bathrooms'],
            'area' => (float)$row['area'],
            'area_unit' => htmlspecialchars($row['area_unit'], ENT_QUOTES, 'UTF-8'),
            'address' => htmlspecialchars($row['address'], ENT_QUOTES, 'UTF-8'),
            'city' => htmlspecialchars($row['city'], ENT_QUOTES, 'UTF-8'),
            'state' => htmlspecialchars($row['state'], ENT_QUOTES, 'UTF-8'),
            'country' => htmlspecialchars($row['country'], ENT_QUOTES, 'UTF-8'),
            'postal_code' => htmlspecialchars($row['postal_code'], ENT_QUOTES, 'UTF-8'),
            'latitude' => (float)$row['latitude'],
            'longitude' => (float)$row['longitude'],
            'property_type' => htmlspecialchars($row['property_type'], ENT_QUOTES, 'UTF-8'),
            'purpose' => htmlspecialchars($row['purpose'], ENT_QUOTES, 'UTF-8'),
            'is_featured' => (bool)$row['is_featured'],
            'status' => htmlspecialchars($row['status'], ENT_QUOTES, 'UTF-8'),
            'year_built' => $row['year_built'] ? (int)$row['year_built'] : null,
            'garage' => $row['garage'] ? (int)$row['garage'] : 0,
            'garage_size' => $row['garage_size'] ? (float)$row['garage_size'] : null,
            'image' => $row['main_image'] ?: '/assets/images/properties/default.jpg',
            'created_at' => $row['created_at'],
            'updated_at' => $row['updated_at'],
            'agent' => [
                'name' => htmlspecialchars(trim($row['first_name'] . ' ' . $row['last_name']), ENT_QUOTES, 'UTF-8'),
                'phone' => htmlspecialchars(substr($row['agent_phone'], 0, 15), ENT_QUOTES, 'UTF-8'), // Limit phone display
                'email' => htmlspecialchars(substr($row['agent_email'], 0, 50), ENT_QUOTES, 'UTF-8') // Limit email display
            ]
        ];

        $properties[] = $property;
    }

    // Get available filters with enhanced security
    $filters = [
        'property_types' => [],
        'purposes' => [
            ['value' => 'sale', 'label' => 'For Sale'],
            ['value' => 'rent', 'label' => 'For Rent']
        ],
        'bedrooms' => [1, 2, 3, 4, 5],
        'bathrooms' => [1, 2, 3, 4],
        'max_price' => 10000000 // Default max price
    ];

    // Get available property types with prepared statement
    $typeQuery = "SELECT DISTINCT name FROM property_types WHERE is_active = 1 ORDER BY name";
    $typeResult = $conn->query($typeQuery);

    if ($typeResult) {
        while ($type = $typeResult->fetch_assoc()) {
            $filters['property_types'][] = [
                'value' => htmlspecialchars($type['name'], ENT_QUOTES, 'UTF-8'),
                'label' => htmlspecialchars(ucfirst($type['name']), ENT_QUOTES, 'UTF-8')
            ];
        }
    }

    // Get max price from database with prepared statement
    $maxPriceQuery = "SELECT MAX(price) as max_price FROM properties WHERE status = 'available' AND is_active = 1";
    $maxPriceResult = $conn->query($maxPriceQuery);
    if ($maxPriceResult && $maxPriceRow = $maxPriceResult->fetch_assoc()) {
        $filters['max_price'] = (int)ceil($maxPriceRow['max_price'] / 1000000) * 1000000; // Round up to nearest million
    }

    // Prepare success response
    $response = [
        'success' => true,
        'message' => count($properties) . ' properties found',
        'total' => (int)$total,
        'page' => $params['page'],
        'limit' => $params['limit'],
        'pages' => (int)ceil($total / $params['limit']),
        'properties' => $properties,
        'filters' => $filters,
        'security_info' => [
            'request_secure' => true,
            'rate_limit_remaining' => $max_search_requests - $rate_limit_data['requests'],
            'search_parameters_validated' => true
        ]
    ];

    $stmt->close();

} catch (Exception $e) {
    // Enhanced error handling without information disclosure
    logSecurityEvent('Search API Exception', [
        'error_message' => $e->getMessage(),
        'error_file' => $e->getFile(),
        'error_line' => $e->getLine(),
        'ip_address' => $ip_address,
        'trace' => $e->getTraceAsString()
    ]);

    // Send generic error response without exposing internal details
    sendSecurityResponse(500, 'An internal error occurred while processing your search request.');
} finally {
    // Ensure database connection is closed
    if (isset($conn) && $conn) {
        $conn->close();
    }
}

// Return JSON response
echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>
