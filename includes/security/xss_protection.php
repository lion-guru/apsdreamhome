<?php
// XSS Protection and Input Sanitization

/**
 * Sanitize input to prevent XSS attacks
 * @param mixed $input Input to be sanitized
 * @param string $type Type of input (string, int, float, email, url)
 * @return mixed Sanitized input
 */
function sanitize_input($input, $type = 'string') {
    if ($input === null) {
        return null;
    }

    switch ($type) {
        case 'string':
            return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
        
        case 'int':
            return filter_var($input, FILTER_VALIDATE_INT) !== false 
                ? intval($input) 
                : 0;
        
        case 'float':
            return filter_var($input, FILTER_VALIDATE_FLOAT) !== false 
                ? floatval($input) 
                : 0.0;
        
        case 'email':
            $email = filter_var($input, FILTER_SANITIZE_EMAIL);
            return filter_var($email, FILTER_VALIDATE_EMAIL) 
                ? $email 
                : '';
        
        case 'url':
            $url = filter_var($input, FILTER_SANITIZE_URL);
            return filter_var($url, FILTER_VALIDATE_URL) 
                ? $url 
                : '';
        
        default:
            return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
}

/**
 * Log security events
 * @param string $message Security event message
 * @param string $level Log level (info, warning, error)
 */
function log_security_event($message, $level = 'info') {
    $log_dir = __DIR__ . '/../../logs/security';
    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0755, true);
    }
    
    $log_file = $log_dir . '/security_' . date('Y-m-d') . '.log';
    $timestamp = date('Y-m-d H:i:s');
    $client_ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    
    $log_message = "[{$timestamp}] [{$level}] [{$client_ip}] {$message}\n";
    file_put_contents($log_file, $log_message, FILE_APPEND);
}

/**
 * Prevent potential CSRF attacks
 * @return bool
 */
function csrf_validate() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST['csrf_token'] ?? $_GET['csrf_token'] ?? null;
        
        if (!$token || $token !== $_SESSION['csrf_token']) {
            log_security_event('CSRF token validation failed', 'warning');
            die('Invalid request');
        }
    }
    
    return true;
}

/**
 * Generate CSRF token for forms
 * @return string
 */
function csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Additional security headers
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: geolocation=(self), camera=(), microphone=()');
header('Content-Security-Policy: default-src \'self\'; 
    script-src \'self\' https://cdn.jsdelivr.net https://unpkg.com; 
    style-src \'self\' https://cdn.jsdelivr.net; 
    img-src \'self\' data: https:; 
    connect-src \'self\'');
