<?php
// Enhanced database connection function with logging and security
function db_connect($retry_attempts = 3) {
    $attempt = 0;
    while ($attempt < $retry_attempts) {
        try {
            // Use persistent connection for better performance
            $conn = mysqli_connect('p:' . DB_HOST, DB_USER, DB_PASS, DB_NAME);
            
            if (!$conn) {
                throw new Exception("Connection failed: " . mysqli_connect_error());
            }
            
            // Set connection parameters for security and performance
            mysqli_set_charset($conn, 'utf8mb4');
            
            // Enable strict mode to catch more potential errors
            mysqli_query($conn, "SET sql_mode = 'STRICT_ALL_TABLES'");
            
            return $conn;
        } catch (Exception $e) {
            error_log('Database connection attempt ' . ($attempt + 1) . ' failed: ' . $e->getMessage());
            $attempt++;
            
            // Exponential backoff
            if ($attempt < $retry_attempts) {
                usleep(pow(2, $attempt) * 100000); // Increases wait time between attempts
            }
        }
    }
    
    // Log critical error and terminate
    error_log('CRITICAL: Unable to establish database connection after ' . $retry_attempts . ' attempts');
    die('System is temporarily unavailable. Please try again later.');
}

// Advanced input sanitization and validation
function clean_input($data, $type = 'string', $options = []) {
    // Remove whitespace
    $data = trim($data);
    
    // Decode HTML entities to prevent double-encoding
    $data = htmlspecialchars_decode($data, ENT_QUOTES);
    
    // Sanitize based on type
    switch ($type) {
        case 'email':
            $data = filter_var($data, FILTER_SANITIZE_EMAIL);
            if (!filter_var($data, FILTER_VALIDATE_EMAIL)) {
                return false;
            }
            break;
        
        case 'int':
            $data = filter_var($data, FILTER_SANITIZE_NUMBER_INT);
            $min = $options['min'] ?? PHP_INT_MIN;
            $max = $options['max'] ?? PHP_INT_MAX;
            if (!filter_var($data, FILTER_VALIDATE_INT, [
                'options' => ['min_range' => $min, 'max_range' => $max]
            ])) {
                return false;
            }
            break;
        
        case 'float':
            $data = filter_var($data, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            break;
        
        case 'url':
            $data = filter_var($data, FILTER_SANITIZE_URL);
            if (!filter_var($data, FILTER_VALIDATE_URL)) {
                return false;
            }
            break;
        
        default: // string
            // Remove potentially dangerous characters
            $data = strip_tags($data);
            $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    }
    
    return $data;
}

// Enhanced user authentication check with role verification
function is_logged_in($required_role = null) {
    if (!isset($_SESSION['user_id'])) {
        return false;
    }
    
    // Optional role-based check
    if ($required_role !== null && $_SESSION['user_role'] !== $required_role) {
        return false;
    }
    
    // Additional security checks
    $inactive_timeout = 1800; // 30 minutes
    if (isset($_SESSION['last_activity']) && 
        (time() - $_SESSION['last_activity']) > $inactive_timeout) {
        // Destroy session on timeout
        session_unset();
        session_destroy();
        return false;
    }
    
    // Update last activity timestamp
    $_SESSION['last_activity'] = time();
    
    return true;
}

// Advanced redirect with security and logging
function redirect($url, $permanent = false, $log_redirect = true) {
    // Prevent open redirect vulnerabilities
    $allowed_hosts = ['apsdreamhomes.com', 'localhost'];
    $parsed_url = parse_url($url);
    
    if (!in_array($parsed_url['host'] ?? 'localhost', $allowed_hosts)) {
        error_log('Potential open redirect attempt: ' . $url);
        $url = '/'; // Default to homepage
    }
    
    // Log redirect for audit purposes
    if ($log_redirect) {
        error_log('Redirect: ' . $url . ' by user ' . ($_SESSION['user_id'] ?? 'guest'));
    }
    
    // HTTP status code for redirect
    $status_code = $permanent ? 301 : 302;
    
    header('Location: ' . $url, true, $status_code);
    exit();
}

// Generate a cryptographically secure random token
function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Validate CSRF token
function validate_csrf_token($token) {
    return hash_equals($_SESSION['csrf_token'], $token);
}
