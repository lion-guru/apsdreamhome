<?php
/**
 * Enhanced Security Settings Handler
 * Provides secure system settings management with comprehensive security measures
 * Security Enhanced Version
 */

// Disable error display in production
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/save_settings_security.log');
error_reporting(E_ALL);

// Set comprehensive security headers for admin panel
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Content-Security-Policy: default-src \'self\'; script-src \'self\' \'unsafe-inline\'; style-src \'self\' \'unsafe-inline\';');
header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
header('Permissions-Policy: geolocation=(), microphone=(), camera=(), payment=()');
header('X-Permitted-Cross-Domain-Policies: none');
header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Secure CORS configuration - Only allow specific origins
$allowed_origins = [
    'https://localhost',
    'http://localhost',
    'https://127.0.0.1',
    'http://127.0.0.1',
    'https://localhost:3000',
    'http://localhost:3000'
];

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, $allowed_origins)) {
    header('Access-Control-Allow-Origin: ' . $origin);
    header('Access-Control-Allow-Methods: POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-API-Key, X-CSRF-Token');
    header('Access-Control-Allow-Credentials: false');
    header('Access-Control-Max-Age: 3600');
}

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Rate limiting for settings changes
$max_settings_changes = 5; // changes per hour
$ip_address = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
$current_time = time();

// Start secure session for admin
$session_name = 'secure_admin_session';
$secure = true; // Only send cookies over HTTPS
$httponly = true; // Prevent JavaScript access to session cookie
$samesite = 'Strict';

if (PHP_VERSION_ID < 70300) {
    session_set_cookie_params([
        'lifetime' => 1800, // 30 minutes
        'path' => '/admin',
        'domain' => $_SERVER['HTTP_HOST'],
        'secure' => $secure,
        'httponly' => $httponly,
        'samesite' => $samesite
    ]);
} else {
    session_set_cookie_params([
        'lifetime' => 1800, // 30 minutes
        'path' => '/admin',
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
} elseif (time() - $_SESSION['last_regeneration'] > 1800) { // 30 minutes
    session_regenerate_id(true);
    $_SESSION['last_regeneration'] = time();
}

// Session timeout check
if (isset($_SESSION['last_activity']) &&
    (time() - $_SESSION['last_activity']) > 1800) { // 30 minutes timeout
    session_unset();
    session_destroy();
    logSecurityEvent('Save Settings Session Timeout', [
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN'
    ]);
    sendSecurityResponse(401, 'Session expired. Please login again.');
}

// Update last activity timestamp
$_SESSION['last_activity'] = time();

// Generate CSRF token if not exists
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Rate limiting check
$rate_limit_key = 'settings_changes_' . md5($ip_address);
if (!isset($_SESSION[$rate_limit_key])) {
    $_SESSION[$rate_limit_key] = [
        'changes' => 0,
        'first_change' => $current_time,
        'last_change' => $current_time
    ];
}

$rate_limit_data = &$_SESSION[$rate_limit_key];

// Check if rate limit exceeded
if ($current_time - $rate_limit_data['first_change'] < 3600) {
    $rate_limit_data['changes']++;
    if ($rate_limit_data['changes'] > $max_settings_changes) {
        logSecurityEvent('Settings Changes Rate Limit Exceeded', [
            'ip_address' => $ip_address,
            'changes' => $rate_limit_data['changes'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN'
        ]);
        sendSecurityResponse(429, 'Too many settings changes. Please slow down.');
    }
} else {
    $rate_limit_data['changes'] = 1;
    $rate_limit_data['first_change'] = $current_time;
}

$rate_limit_data['last_change'] = $current_time;

// Security event logging function
function logSecurityEvent($event, $context = []) {
    static $logFile = null;

    if ($logFile === null) {
        $logDir = __DIR__ . '/logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        $logFile = $logDir . '/save_settings_security.log';
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

// Secure response function
function sendSecurityResponse($status_code, $message, $data = null) {
    http_response_code($status_code);

    $response = [
        'success' => $status_code >= 200 && $status_code < 300,
        'message' => $message,
        'timestamp' => date('Y-m-d H:i:s'),
        'request_id' => uniqid('settings_change_')
    ];

    if ($data !== null) {
        $response['data'] = $data;
    }

    // Add security headers to response
    header('X-Response-Time: ' . (microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']));
    header('X-Security-Status: protected');
    header('X-Rate-Limit-Remaining: ' . ($GLOBALS['max_settings_changes'] - $GLOBALS['rate_limit_data']['changes']));
    header('X-Rate-Limit-Reset: ' . ($GLOBALS['rate_limit_data']['first_change'] + 3600));

    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
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
        case 'email':
            $input = filter_var($input, FILTER_SANITIZE_EMAIL);
            if (!filter_var($input, FILTER_VALIDATE_EMAIL)) {
                return false;
            }
            break;
        case 'url':
            $input = filter_var($input, FILTER_SANITIZE_URL);
            if (!filter_var($input, FILTER_VALIDATE_URL)) {
                return false;
            }
            break;
        case 'boolean':
            if (!is_bool($input) && !in_array($input, [0, 1, '0', '1', true, false], true)) {
                return false;
            }
            return (bool)$input;
        case 'integer':
            $input = filter_var($input, FILTER_VALIDATE_INT);
            if ($input === false) {
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

// Validate settings structure
function validateSettingsStructure($settings) {
    if (!is_array($settings)) {
        return false;
    }

    $allowed_settings = [
        'site_title', 'site_description', 'contact_email', 'contact_phone',
        'address', 'notification_email', 'booking_notification_email',
        'booking_notification_whatsapp', 'default_user_role', 'maintenance_mode',
        'max_file_size', 'allowed_file_types', 'session_timeout', 'login_attempts'
    ];

    foreach ($settings as $key => $value) {
        if (!in_array($key, $allowed_settings)) {
            return false;
        }

        // Validate specific setting types
        switch ($key) {
            case 'site_title':
            case 'site_description':
                if (!is_string($value) || strlen($value) > 255) {
                    return false;
                }
                break;
            case 'contact_email':
            case 'notification_email':
                if (!is_string($value) || !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    return false;
                }
                break;
            case 'contact_phone':
                if (!is_string($value) || strlen($value) < 10 || strlen($value) > 15) {
                    return false;
                }
                break;
            case 'booking_notification_email':
            case 'booking_notification_whatsapp':
            case 'maintenance_mode':
                if (!is_bool($value) && !in_array($value, [0, 1], true)) {
                    return false;
                }
                break;
            case 'default_user_role':
                $allowed_roles = ['user', 'customer', 'associate', 'builder', 'agent', 'employee'];
                if (!in_array($value, $allowed_roles)) {
                    return false;
                }
                break;
            case 'max_file_size':
                if (!is_int($value) || $value < 1 || $value > 100) {
                    return false;
                }
                break;
            case 'session_timeout':
                if (!is_int($value) || $value < 300 || $value > 86400) {
                    return false;
                }
                break;
            case 'login_attempts':
                if (!is_int($value) || $value < 3 || $value > 10) {
                    return false;
                }
                break;
        }
    }

    return true;
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
        logSecurityEvent('Suspicious User Agent in Save Settings', [
            'user_agent' => $user_agent,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN'
        ]);
    }

    return true;
}

// Load required files with validation
$required_files = [
    __DIR__ . '/../includes/functions/role_helper.php'
];

foreach ($required_files as $file) {
    if (!file_exists($file) || !is_readable($file)) {
        logSecurityEvent('Required File Missing in Save Settings', [
            'file_path' => $file,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN'
        ]);
        sendSecurityResponse(500, 'System configuration error.');
    }
}

require_once $required_files[0];

// Check if user has required role (superadmin only)
enforceRole(['superadmin']);

// Validate request headers
if (!validateRequestHeaders()) {
    logSecurityEvent('Invalid Request Headers in Save Settings', [
        'content_type' => $_SERVER['CONTENT_TYPE'] ?? 'MISSING',
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN'
    ]);
    sendSecurityResponse(400, 'Invalid request headers.');
}

// CSRF token validation
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    logSecurityEvent('CSRF Token Mismatch in Save Settings', [
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
        'provided_token' => substr($_POST['csrf_token'] ?? '', 0, 8) . '...',
        'expected_token' => substr($_SESSION['csrf_token'], 0, 8) . '...'
    ]);
    sendSecurityResponse(403, 'Security validation failed.');
}

// Check if settings data is provided
if (!isset($_POST['settings'])) {
    logSecurityEvent('Missing Settings Data', [
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN'
    ]);
    sendSecurityResponse(400, 'Settings data is required.');
}

$settings = $_POST['settings'];

// Validate settings structure
if (!validateSettingsStructure($settings)) {
    logSecurityEvent('Invalid Settings Structure', [
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
        'settings' => json_encode($settings, JSON_PARTIAL_OUTPUT_ON_ERROR)
    ]);
    sendSecurityResponse(400, 'Invalid settings structure.');
}

// Sanitize settings data
$sanitized_settings = [];
foreach ($settings as $key => $value) {
    switch ($key) {
        case 'site_title':
        case 'site_description':
            $sanitized_value = validateInput($value, 'string', 255);
            break;
        case 'contact_email':
        case 'notification_email':
            $sanitized_value = validateInput($value, 'email');
            break;
        case 'contact_phone':
            $sanitized_value = validateInput($value, 'string', 15);
            break;
        case 'booking_notification_email':
        case 'booking_notification_whatsapp':
        case 'maintenance_mode':
            $sanitized_value = validateInput($value, 'boolean');
            break;
        case 'default_user_role':
            $sanitized_value = validateInput($value, 'string', 50);
            break;
        case 'max_file_size':
            $sanitized_value = validateInput($value, 'integer');
            break;
        case 'session_timeout':
            $sanitized_value = validateInput($value, 'integer');
            break;
        case 'login_attempts':
            $sanitized_value = validateInput($value, 'integer');
            break;
        default:
            $sanitized_value = validateInput($value, 'string', 255);
    }

    if ($sanitized_value === false) {
        logSecurityEvent('Invalid Setting Value', [
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
            'setting' => $key,
            'value' => $value
        ]);
        sendSecurityResponse(400, "Invalid value for setting: {$key}");
    }

    $sanitized_settings[$key] = $sanitized_value;
}

// Define the settings file path
$settings_file = __DIR__ . '/../includes/config/site_settings.php';
$backup_file = __DIR__ . '/../includes/config/site_settings.php.backup';

// Create backup of current settings file
if (file_exists($settings_file)) {
    if (!copy($settings_file, $backup_file)) {
        logSecurityEvent('Failed to Create Settings Backup', [
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
            'original_file' => $settings_file,
            'backup_file' => $backup_file
        ]);
        sendSecurityResponse(500, 'Failed to create backup of current settings.');
    }
}

// Create the new settings file content
try {
    $content = "<?php\n/**\n * Site Settings Configuration\n * Auto-generated by save_settings.php\n * Last updated: " . date('Y-m-d H:i:s') . "\n */\n\nreturn " . var_export($sanitized_settings, true) . ";\n";

    // Write the new settings file
    if (file_put_contents($settings_file, $content, LOCK_EX) === false) {
        logSecurityEvent('Failed to Write Settings File', [
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
            'file' => $settings_file
        ]);
        sendSecurityResponse(500, 'Failed to write settings file.');
    }

    // Verify the file was written correctly
    $written_content = file_get_contents($settings_file);
    if ($written_content !== $content) {
        logSecurityEvent('Settings File Verification Failed', [
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
            'file' => $settings_file
        ]);
        sendSecurityResponse(500, 'Settings file verification failed.');
    }

    // Log successful settings update
    logSecurityEvent('Settings Updated Successfully', [
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN',
        'settings_updated' => array_keys($sanitized_settings)
    ]);

    sendSecurityResponse(200, 'Settings updated successfully', [
        'settings_updated' => array_keys($sanitized_settings),
        'backup_created' => $backup_file
    ]);

} catch (Exception $e) {
    logSecurityEvent('Exception in Save Settings', [
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
        'error_message' => $e->getMessage(),
        'error_file' => $e->getFile(),
        'error_line' => $e->getLine()
    ]);
    sendSecurityResponse(500, 'An error occurred while saving settings.');
}
?>
