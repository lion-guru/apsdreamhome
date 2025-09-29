<?php
/**
 * Property Visit Scheduling API
 * Handles property visit scheduling with comprehensive security measures
 * Standardized to use bootstrap and ResponseTransformer
 */

// Unified bootstrap for autoloading, json helper, timezone, session, and base JSON header
require_once __DIR__ . '/includes/bootstrap.php';

// Set error logging for this endpoint
ini_set('error_log', __DIR__ . '/../logs/visit_scheduling_api_error.log');

// Apply rate limiting
if (class_exists('App\\Common\\Middleware\\RateLimitMiddleware')) {
    $rateLimitMiddleware = new \App\Common\Middleware\RateLimitMiddleware();
    $rateLimitMiddleware->handle('schedule_visit');
} else {
    // Fallback rate limiting if middleware is not available
    $max_visit_requests = 10; // visits per hour
    $time_window = 3600; // 1 hour in seconds
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
    
    $rate_limit_key = 'visit_rate_limit_' . md5($ip_address);
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
    if ($rate_limit_data['count'] >= $max_visit_requests) {
        $wait_time = ($rate_limit_data['first_request'] + $time_window) - time();
        $response = [
            'error' => 'Too many requests',
            'message' => 'Rate limit exceeded. Please try again later.',
            'retry_after' => $wait_time,
            'code' => 'RATE_LIMIT_EXCEEDED'
        ];
        
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
            echo json_encode($response);
        }
        exit();
    }
    
    // Increment request count
    $rate_limit_data['count']++;
    $_SESSION[$rate_limit_key] = $rate_limit_data;
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    if (class_exists('App\\Common\\Transformers\\ResponseTransformer') && function_exists('json_response')) {
        $out = \App\Common\Transformers\ResponseTransformer::error(
            'Method not allowed',
            'METHOD_NOT_ALLOWED',
            405,
            ['allowed_methods' => ['POST']]
        );
        json_response($out, 405);
    } else {
        http_response_code(405);
        header('Allow: POST');
        echo json_encode([
            'success' => false,
            'error' => 'Method not allowed. Only POST requests are accepted.',
            'code' => 'METHOD_NOT_ALLOWED'
        ]);
    }
    exit();
}

// Start secure session for API
$session_name = 'secure_visit_session';
$secure = true; // Only send cookies over HTTPS
$httponly = true; // Prevent JavaScript access to session cookie
$samesite = 'Strict';

if (PHP_VERSION_ID < 70300) {
    session_set_cookie_params([
        'lifetime' => 3600, // 1 hour
        'path' => '/',
        'domain' => $_SERVER['HTTP_HOST'],
        'secure' => $secure,
        'httponly' => $httponly,
        'samesite' => $samesite
    ]);
} else {
    session_set_cookie_params([
        'lifetime' => 3600, // 1 hour
        'path' => '/',
        'domain' => $_SERVER['HTTP_HOST'],
        'secure' => $secure,
        'httponly' => $httponly,
        'samesite' => $samesite
    ]);
}

session_name($session_name);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Regenerate session ID to prevent session fixation
if (!isset($_SESSION['last_regeneration'])) {
    session_regenerate_id(true);
    $_SESSION['last_regeneration'] = time();
} elseif (time() - $_SESSION['last_regeneration'] > 3600) { // 1 hour
    session_regenerate_id(true);
    $_SESSION['last_regeneration'] = time();
}

// Session timeout check
if (isset($_SESSION['last_activity']) &&
    (time() - $_SESSION['last_activity']) > 3600) { // 1 hour timeout
    session_unset();
    session_destroy();
    logSecurityEvent('Visit Scheduling API Session Timeout', [
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN'
    ]);
    sendSecurityResponse(401, 'Session expired. Please try again.');
}

// Update last activity timestamp
$_SESSION['last_activity'] = time();

// Generate CSRF token if not exists
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Clean up old rate limit data
if (!isset($_SESSION['rate_limit_cleanup'])) {
    $_SESSION['rate_limit_cleanup'] = time();
} elseif (time() - $_SESSION['rate_limit_cleanup'] > 3600) { // Clean up hourly
    foreach ($_SESSION as $key => $value) {
        if (strpos($key, 'visit_attempts_') === 0 && is_array($value)) {
            if (time() - $value['last_request'] > 3600) {
                unset($_SESSION[$key]);
            }
        }
    }
    $_SESSION['rate_limit_cleanup'] = time();
}

// Check rate limiting
$rate_limit_key = 'visit_attempts_' . md5($ip_address);
if (!isset($_SESSION[$rate_limit_key])) {
    $_SESSION[$rate_limit_key] = [
        'requests' => 0,
        'first_request' => $current_time,
        'last_request' => $current_time
    ];
}

$rate_limit_data = &$_SESSION[$rate_limit_key];

if ($current_time - $rate_limit_data['first_request'] < $time_window) {
    $rate_limit_data['requests']++;
    if ($rate_limit_data['requests'] > $max_visit_requests) {
        logSecurityEvent('Visit Scheduling API Rate Limit Exceeded', [
            'ip_address' => $ip_address,
            'requests' => $rate_limit_data['requests'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN'
        ]);
        sendSecurityResponse(429, 'Too many visit scheduling requests. Please try again later.');
    }
} else {
    $rate_limit_data['requests'] = 1;
    $rate_limit_data['first_request'] = $current_time;
}

$rate_limit_data['last_request'] = $current_time;

// Security event logging function
function logSecurityEvent($event, $context = []) {
    static $logFile = null;

    if ($logFile === null) {
        $logDir = __DIR__ . '/../logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        $logFile = $logDir . '/visit_scheduling_api_security.log';
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
        'X-Request-ID' => uniqid('req_')
    ];

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
function validateInput($input, $type = 'string', $max_length = null, $required = true) {
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
        case 'integer':
            $input = filter_var($input, FILTER_VALIDATE_INT);
            if ($input === false || $input < 1) {
                return false;
            }
            break;
        case 'email':
            $input = filter_var($input, FILTER_SANITIZE_EMAIL);
            if (!filter_var($input, FILTER_VALIDATE_EMAIL)) {
                return false;
            }
            break;
        case 'phone':
            $input = filter_var($input, FILTER_SANITIZE_STRING);
            // Remove all non-digit characters except + and spaces
            $input = preg_replace('/[^\d+\s]/', '', $input);
            if (strlen($input) < 10 || strlen($input) > 15) {
                return false;
            }
            break;
        case 'date':
            // Validate date format YYYY-MM-DD
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $input)) {
                return false;
            }
            // Validate if it's a valid date
            $date = DateTime::createFromFormat('Y-m-d', $input);
            if (!$date || $date->format('Y-m-d') !== $input) {
                return false;
            }
            // Check if date is not in the past
            $today = new DateTime();
            $today->setTime(0, 0, 0);
            $input_date = new DateTime($input);
            if ($input_date < $today) {
                return false;
            }
            break;
        case 'time':
            $allowed_times = ['10:00', '11:00', '12:00', '14:00', '15:00', '16:00', '17:00'];
            if (!in_array($input, $allowed_times)) {
                return false;
            }
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
    $content_type = $_SERVER['CONTENT_TYPE'] ?? '';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';

    // Check Content-Type for POST requests
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && strpos($content_type, 'application/x-www-form-urlencoded') === false) {
        return false;
    }

    // Check User-Agent (basic bot detection)
    if (empty($user_agent) || strlen($user_agent) < 10) {
        logSecurityEvent('Suspicious User Agent in Visit Scheduling API', [
            'user_agent' => $user_agent,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN'
        ]);
    }

    return true;
}

// Main API logic
try {
    // Get database connection
    $con = getDbConnection();
    if (!$con) {
        logSecurityEvent('Database Connection Failed in Visit Scheduling API', [
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN'
        ]);
        sendSecurityResponse(500, 'Database connection error.', null, 'DB_CONNECTION_ERROR');
    }

    // Initialize input validator
    $validator = new InputValidator($con);

    // Enhanced input validation and sanitization
    $property_id = validateInput($_POST['property_id'] ?? 0, 'integer');
    $name = validateInput($_POST['name'] ?? '', 'string', 100);
    $email = validateInput($_POST['email'] ?? '', 'email');
    $phone = validateInput($_POST['phone'] ?? '', 'phone');
    $preferred_date = validateInput($_POST['preferred_date'] ?? '', 'date');
    $preferred_time = validateInput($_POST['preferred_time'] ?? '', 'time');
    $notes = validateInput($_POST['notes'] ?? '', 'string', 500);

    // Validate all required fields
    if ($property_id === false) {
        logSecurityEvent('Invalid Property ID in Visit Scheduling API', [
            'property_id' => $_POST['property_id'] ?? 'NULL',
            'ip_address' => $ip_address
        ]);
        sendSecurityResponse(400, 'Invalid property ID. Must be a positive integer.');
    }

    if ($name === false) {
        logSecurityEvent('Invalid Name in Visit Scheduling API', [
            'name' => $_POST['name'] ?? 'NULL',
            'ip_address' => $ip_address
        ]);
        sendSecurityResponse(400, 'Invalid name. Must be 1-100 characters.');
    }

    if ($email === false) {
        logSecurityEvent('Invalid Email in Visit Scheduling API', [
            'email' => $_POST['email'] ?? 'NULL',
            'ip_address' => $ip_address
        ]);
        sendSecurityResponse(400, 'Invalid email address format.');
    }

    if ($phone === false) {
        logSecurityEvent('Invalid Phone in Visit Scheduling API', [
            'phone' => $_POST['phone'] ?? 'NULL',
            'ip_address' => $ip_address
        ]);
        sendSecurityResponse(400, 'Invalid phone number format. Must be 10-15 digits.');
    }

    if ($preferred_date === false) {
        logSecurityEvent('Invalid Date in Visit Scheduling API', [
            'date' => $_POST['preferred_date'] ?? 'NULL',
            'ip_address' => $ip_address
        ]);
        sendSecurityResponse(400, 'Invalid date format. Must be YYYY-MM-DD and not in the past.');
    }

    if ($preferred_time === false) {
        logSecurityEvent('Invalid Time in Visit Scheduling API', [
            'time' => $_POST['preferred_time'] ?? 'NULL',
            'ip_address' => $ip_address
        ]);
        sendSecurityResponse(400, 'Invalid time slot. Must be one of: 10:00, 11:00, 12:00, 14:00, 15:00, 16:00, 17:00.');
    }

    if ($notes === false) {
        logSecurityEvent('Invalid Notes in Visit Scheduling API', [
            'notes_length' => strlen($_POST['notes'] ?? ''),
            'ip_address' => $ip_address
        ]);
        sendSecurityResponse(400, 'Invalid notes. Must be 0-500 characters.');
    }

    // Check for suspicious patterns in input
    $all_input = json_encode($_POST);
    $suspicious_patterns = ['<script', 'javascript:', 'onload=', 'onerror=', 'eval(', 'alert(', 'document.cookie', 'http://', 'https://', 'union', 'select', 'drop', 'delete', 'update', 'insert'];
    foreach ($suspicious_patterns as $pattern) {
        if (stripos($all_input, $pattern) !== false) {
            logSecurityEvent('Suspicious Input Pattern Detected in Visit Scheduling API', [
                'pattern' => $pattern,
                'ip_address' => $ip_address,
                'input_data' => substr($all_input, 0, 200) . '...'
            ]);
            sendSecurityResponse(400, 'Suspicious content detected in request.');
        }
    }

    // Log visit scheduling attempt
    logSecurityEvent('Visit Scheduling Attempt', [
        'property_id' => $property_id,
        'name' => $name,
        'email' => $email,
        'phone' => $phone,
        'preferred_date' => $preferred_date,
        'preferred_time' => $preferred_time,
        'ip_address' => $ip_address
    ]);

    // Begin transaction
    $con->begin_transaction();

    try {
        // Get property details and agent availability with enhanced security
        $property_query = "SELECT p.id, p.title, p.location, p.owner_id,
                                 u.id as agent_id, u.email as agent_email,
                                 (SELECT COUNT(*) FROM bookings b
                                  WHERE b.property_id = p.id
                                    AND b.visit_date = ?
                                    AND b.visit_time = ?) as existing_bookings
                          FROM properties p
                          LEFT JOIN users u ON p.owner_id = u.id
                          WHERE p.id = ? AND p.status = 'available' AND p.is_active = 1";

        $stmt = $con->prepare($property_query);
        if (!$stmt) {
            throw new Exception('Failed to prepare property query');
        }

        $stmt->bind_param('ssi', $preferred_date, $preferred_time, $property_id);
        if (!$stmt->execute()) {
            throw new Exception('Failed to execute property query');
        }

        $property_result = $stmt->get_result();
        if ($property_result->num_rows === 0) {
            logSecurityEvent('Property Not Available for Visit Scheduling', [
                'property_id' => $property_id,
                'ip_address' => $ip_address
            ]);
            $stmt->close();
            throw new Exception('Property not found or not available');
        }

        $property = $property_result->fetch_assoc();
        $stmt->close();

        // Check if timeslot is available (max 2 bookings per slot)
        if ($property['existing_bookings'] >= 2) {
            // Find next available slot
            $next_slot_query = "SELECT visit_date, visit_time
                               FROM bookings
                               WHERE property_id = ?
                                 AND visit_date >= ?
                               GROUP BY visit_date, visit_time
                               HAVING COUNT(*) < 2
                               ORDER BY visit_date ASC, visit_time ASC
                               LIMIT 1";

            $stmt = $con->prepare($next_slot_query);
            if (!$stmt) {
                throw new Exception('Failed to prepare next slot query');
            }

            $stmt->bind_param('is', $property_id, $preferred_date);
            if (!$stmt->execute()) {
                throw new Exception('Failed to execute next slot query');
            }

            $next_slot_result = $stmt->get_result();
            $next_slot = $next_slot_result->fetch_assoc();
            $stmt->close();

            if ($next_slot) {
                $preferred_date = $next_slot['visit_date'];
                $preferred_time = $next_slot['visit_time'];
            } else {
                logSecurityEvent('No Available Slots for Property', [
                    'property_id' => $property_id,
                    'ip_address' => $ip_address
                ]);
                throw new Exception('No available slots');
            }
        }

        // Create or get customer with enhanced security
        $customer_query = "INSERT INTO customers (name, email, phone, created_at)
                          VALUES (?, ?, ?, NOW())
                          ON DUPLICATE KEY UPDATE
                          id=LAST_INSERT_ID(id),
                          updated_at=NOW()";

        $stmt = $con->prepare($customer_query);
        if (!$stmt) {
            throw new Exception('Failed to prepare customer query');
        }

        $stmt->bind_param('sss', $name, $email, $phone);
        if (!$stmt->execute()) {
            throw new Exception('Failed to execute customer query');
        }

        $customer_id = $con->insert_id;
        $stmt->close();

        // Create booking with enhanced security
        $booking_query = "INSERT INTO bookings (property_id, customer_id, visit_date, visit_time, notes, status, created_at)
                         VALUES (?, ?, ?, ?, ?, 'confirmed', NOW())";

        $stmt = $con->prepare($booking_query);
        if (!$stmt) {
            throw new Exception('Failed to prepare booking query');
        }

        $stmt->bind_param('iisss', $property_id, $customer_id, $preferred_date, $preferred_time, $notes);
        if (!$stmt->execute()) {
            throw new Exception('Failed to execute booking query');
        }

        $booking_id = $con->insert_id;
        $stmt->close();

        // Create lead with enhanced security
        $lead_query = "INSERT INTO leads (name, email, phone, source, status, notes, created_at)
                       VALUES (?, ?, ?, 'property_visit', 'scheduled', ?, NOW())";

        $stmt = $con->prepare($lead_query);
        if (!$stmt) {
            throw new Exception('Failed to prepare lead query');
        }

        $stmt->bind_param('ssss', $name, $email, $phone, $notes);
        if (!$stmt->execute()) {
            throw new Exception('Failed to execute lead query');
        }

        $lead_id = $con->insert_id;
        $stmt->close();

        // Link lead to customer journey with enhanced security
        $journey_query = "INSERT INTO customer_journeys (customer_id, lead_id, property_id, interaction_type, notes, created_at)
                         VALUES (?, ?, ?, 'visit_scheduled', ?, NOW())";

        $stmt = $con->prepare($journey_query);
        if (!$stmt) {
            throw new Exception('Failed to prepare journey query');
        }

        $stmt->bind_param('iiis', $customer_id, $lead_id, $property_id, $notes);
        if (!$stmt->execute()) {
            throw new Exception('Failed to execute journey query');
        }

        $stmt->close();

        // Notify agent with enhanced security
        if ($property['agent_id']) {
            $notification_query = "INSERT INTO notifications (user_id, type, message, link, created_at)
                                 VALUES (?, 'visit_scheduled', ?, ?, NOW())";

            $message = "New property visit scheduled for {$preferred_date} at {$preferred_time}";
            $link = "/admin/bookings.php?id={$booking_id}";

            $stmt = $con->prepare($notification_query);
            if (!$stmt) {
                throw new Exception('Failed to prepare notification query');
            }

            $stmt->bind_param('iss', $property['agent_id'], $message, $link);
            if (!$stmt->execute()) {
                throw new Exception('Failed to execute notification query');
            }

            $stmt->close();

            // Send secure email notification to agent
            $to = filter_var($property['agent_email'], FILTER_VALIDATE_EMAIL);
            if ($to) {
                $subject = "New Property Visit Scheduled - Security Protected";
                $message = "A new visit has been scheduled for your property.\n\n";
                $message .= "Details:\n";
                $message .= "Date: {$preferred_date}\n";
                $message .= "Time: {$preferred_time}\n";
                $message .= "Customer: " . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . "\n";
                $message .= "Phone: " . htmlspecialchars($phone, ENT_QUOTES, 'UTF-8') . "\n";
                $message .= "Email: " . htmlspecialchars($email, ENT_QUOTES, 'UTF-8') . "\n";
                $message .= "Notes: " . htmlspecialchars($notes, ENT_QUOTES, 'UTF-8') . "\n\n";
                $message .= "View booking: " . SITE_URL . "/admin/bookings.php?id={$booking_id}";

                // Use secure mail function instead of direct mail()
                $headers = [
                    'From: ' . (defined('SITE_EMAIL') ? SITE_EMAIL : 'noreply@apsdreamhome.com'),
                    'Reply-To: ' . $to,
                    'X-Mailer: APS-Dream-Home-Security/1.0',
                    'Content-Type: text/plain; charset=UTF-8'
                ];

                mail($to, $subject, $message, implode("\r\n", $headers));
            }
        }

        // Send confirmation to customer with enhanced security
        $to = filter_var($email, FILTER_VALIDATE_EMAIL);
        if ($to) {
            $subject = "Property Visit Confirmation - APS Dream Home";
            $message = "Your property visit has been scheduled successfully.\n\n";
            $message .= "Details:\n";
            $message .= "Property: " . htmlspecialchars($property['title'], ENT_QUOTES, 'UTF-8') . "\n";
            $message .= "Date: {$preferred_date}\n";
            $message .= "Time: {$preferred_time}\n\n";
            $message .= "Location: " . htmlspecialchars($property['location'], ENT_QUOTES, 'UTF-8') . "\n\n";
            $message .= "Notes: Please arrive 5 minutes before your scheduled time.\n";
            $message .= "If you need to reschedule, please contact us at " . (defined('SUPPORT_EMAIL') ? SUPPORT_EMAIL : 'support@apsdreamhome.com');

            $headers = [
                'From: ' . (defined('SITE_EMAIL') ? SITE_EMAIL : 'noreply@apsdreamhome.com'),
                'Reply-To: ' . (defined('SUPPORT_EMAIL') ? SUPPORT_EMAIL : 'support@apsdreamhome.com'),
                'X-Mailer: APS-Dream-Home-Security/1.0',
                'Content-Type: text/plain; charset=UTF-8'
            ];

            mail($to, $subject, $message, implode("\r\n", $headers));
        }

        // Commit transaction
        $con->commit();

        // Log successful visit scheduling
        logSecurityEvent('Visit Scheduled Successfully', [
            'booking_id' => $booking_id,
            'property_id' => $property_id,
            'customer_id' => $customer_id,
            'visit_date' => $preferred_date,
            'visit_time' => $preferred_time,
            'ip_address' => $ip_address
        ]);

        // Return success response
        $response_data = [
            'booking_id' => $booking_id,
            'visit_date' => $preferred_date,
            'visit_time' => $preferred_time,
            'property_title' => htmlspecialchars($property['title'], ENT_QUOTES, 'UTF-8'),
            'customer_name' => htmlspecialchars($name, ENT_QUOTES, 'UTF-8'),
            'message' => 'Visit scheduled successfully. You will receive a confirmation email shortly.'
        ];

        sendSecurityResponse(200, 'Visit scheduled successfully', $response_data);

    } catch (Exception $e) {
        // Rollback on error
        $con->rollback();
        throw $e;
    }

} catch (Exception $e) {
        // Enhanced error handling without information disclosure
    logSecurityEvent('Visit Scheduling API Exception', [
        'error_message' => $e->getMessage(),
        'error_file' => $e->getFile(),
        'error_line' => $e->getLine(),
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
        'trace' => $e->getTraceAsString()
    ]);

    // Send appropriate error response
    $error_code = 'INTERNAL_ERROR';
    $error_message = 'Failed to schedule visit. Please try again.';
    $status_code = 500;
    $error_data = null;

    // Handle specific error cases
    if ($e->getMessage() === 'No available slots') {
        $error_code = 'NO_AVAILABLE_SLOTS';
        $error_message = 'No available slots for this property. Please try a different date or time.';
        $status_code = 400;
        $error_data = ['suggestion' => 'Try a different time slot or date'];
    } elseif (strpos($e->getMessage(), 'Duplicate entry') !== false) {
        $error_code = 'DUPLICATE_BOOKING';
        $error_message = 'A booking already exists for this time slot.';
        $status_code = 409;
    }

    sendSecurityResponse($status_code, $error_message, $error_data, $error_code);
}
?>
