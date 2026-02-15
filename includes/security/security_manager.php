<?php
/**
 * Advanced Security Management System
 * Enhanced version with comprehensive security features
 */

class SecurityManager {
    // Security Levels
    const LEVEL_LOW = 'low';
    const LEVEL_MEDIUM = 'medium';
    const LEVEL_HIGH = 'high';
    const LEVEL_CRITICAL = 'critical';

    // Security Features
    private $security_config = [
        'xss_protection' => true,
        'csrf_protection' => true,
        'sql_injection_protection' => true,
        'rate_limiting' => true,
        'ip_reputation_check' => true,
        'security_headers' => true,
        'input_sanitization' => true,
        'file_upload_security' => true,
        'session_security' => true,
        'brute_force_protection' => true
    ];

    // Rate Limiting Configuration
    private $rate_limit_config = [
        'max_requests' => 100,
        'time_window' => 3600, // 1 hour
        'block_duration' => 86400 // 24 hours
    ];

    // Sensitive Data Encryption
    private $encryption_key;
    private $encryption_cipher = 'aes-256-gcm';

    // Dependencies
    private $logger;
    private $db;

    // Security tracking
    private $security_events = [];
    private $blocked_ips = [];

    public function __construct($logger = null, $db = null) {
        $this->logger = $logger;
        $this->db = $db;

        // Initialize encryption key
        $this->initializeEncryptionKey();

        // Load blocked IPs
        $this->loadBlockedIPs();

        // Initialize security monitoring
        $this->initializeSecurityMonitoring();
    }

    /**
     * Initialize secure encryption key
     */
    private function initializeEncryptionKey() {
        // Retrieve or generate encryption key
        $key = getenv('APP_ENCRYPTION_KEY');

        if (!$key) {
            // Generate a secure random key
            $key = bin2hex(random_bytes(32));
            // In production, store this securely in environment variables
        }

        $this->encryption_key = hash('sha256', $key, true);
    }

    /**
     * Initialize security monitoring
     */
    private function initializeSecurityMonitoring() {
        // Register shutdown function for security cleanup
        register_shutdown_function([$this, 'securityCleanup']);

        // Set up error handlers for security events
        set_error_handler([$this, 'handleSecurityError']);
        set_exception_handler([$this, 'handleSecurityException']);
    }

    /**
     * Load blocked IPs from storage
     */
    private function loadBlockedIPs() {
        $blocked_file = __DIR__ . '/../logs/blocked_ips.json';

        if (file_exists($blocked_file)) {
            $data = json_decode(file_get_contents($blocked_file), true);
            $this->blocked_ips = $data ?? [];
        }
    }

    /**
     * Save blocked IPs to storage
     */
    private function saveBlockedIPs() {
        $blocked_file = __DIR__ . '/../logs/blocked_ips.json';
        file_put_contents($blocked_file, json_encode($this->blocked_ips, JSON_PRETTY_PRINT));
    }

    /**
     * Check if IP is blocked
     */
    public function isIPBlocked($ip) {
        if (isset($this->blocked_ips[$ip])) {
            $blocked_until = $this->blocked_ips[$ip]['blocked_until'];

            if (time() > $blocked_until) {
                // Remove expired block
                unset($this->blocked_ips[$ip]);
                $this->saveBlockedIPs();
                return false;
            }

            return true;
        }

        return false;
    }

    /**
     * Block an IP address
     */
    public function blockIP($ip, $reason = 'Security violation', $duration = 86400) {
        $this->blocked_ips[$ip] = [
            'blocked_at' => time(),
            'blocked_until' => time() + $duration,
            'reason' => $reason
        ];

        $this->saveBlockedIPs();

        // Log the blocking
        $this->logSecurityEvent('IP_BLOCKED', [
            'ip' => $ip,
            'reason' => $reason,
            'duration' => $duration
        ]);

        return true;
    }

    /**
     * Comprehensive input sanitization
     */
    public function sanitizeInput($input, $type = 'string') {
        if (is_array($input)) {
            return array_map([$this, 'sanitizeInput'], $input);
        }

        switch ($type) {
            case 'email':
                return filter_var(trim($input), FILTER_SANITIZE_EMAIL);
            case 'url':
                return filter_var(trim($input), FILTER_SANITIZE_URL);
            case 'int':
                return filter_var($input, FILTER_SANITIZE_NUMBER_INT);
            case 'float':
                return filter_var($input, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            case 'html':
                return $this->sanitizeHTML($input);
            default:
                return $this->sanitizeString($input);
        }
    }

    /**
     * Sanitize string input
     */
    private function sanitizeString($input) {
        $input = trim($input);

        // Remove null bytes
        $input = str_replace("\0", '', $input);

        // Remove control characters except newlines and tabs
        $input = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $input);

        // Escape special characters
        return htmlspecialchars($input, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /**
     * Sanitize HTML input
     */
    private function sanitizeHTML($input) {
        // Allow only specific HTML tags
        $allowed_tags = '<p><br><strong><em><u><h1><h2><h3><h4><h5><h6><ul><ol><li><blockquote><a><img><div><span>';

        return strip_tags($input, $allowed_tags);
    }

    /**
     * Validate CSRF token
     */
    public function validateCSRFToken($token) {
        if (!isset($_SESSION['csrf_token']) || !isset($_SESSION['csrf_token_time'])) {
            return false;
        }

        // Check if token has expired (4 hours)
        if (time() - $_SESSION['csrf_token_time'] > 14400) {
            return false;
        }

        return hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Generate new CSRF token
     */
    public function generateCSRFToken() {
        $token = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $token;
        $_SESSION['csrf_token_time'] = time();

        return $token;
    }

    /**
     * Check rate limiting
     */
    public function checkRateLimit($ip, $action = 'general') {
        $rate_file = __DIR__ . "/../logs/rate_limit_{$action}.json";

        // Create directory if it doesn't exist
        $dir = dirname($rate_file);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $rate_data = [];
        if (file_exists($rate_file)) {
            $rate_data = json_decode(file_get_contents($rate_file), true) ?? [];
        }

        $current_time = time();
        $window_start = $current_time - $this->rate_limit_config['time_window'];

        // Clean old entries
        $rate_data = array_filter($rate_data, function($entry) use ($window_start) {
            return $entry['timestamp'] > $window_start;
        });

        // Check current rate
        $recent_requests = array_filter($rate_data, function($entry) use ($ip) {
            return $entry['ip'] === $ip;
        });

        if (count($recent_requests) >= $this->rate_limit_config['max_requests']) {
            // Rate limit exceeded
            return [
                'allowed' => false,
                'remaining' => 0,
                'reset_time' => $window_start + $this->rate_limit_config['time_window']
            ];
        }

        // Add current request
        $rate_data[] = [
            'ip' => $ip,
            'timestamp' => $current_time
        ];

        file_put_contents($rate_file, json_encode($rate_data));

        return [
            'allowed' => true,
            'remaining' => $this->rate_limit_config['max_requests'] - count($recent_requests) - 1,
            'reset_time' => $window_start + $this->rate_limit_config['time_window']
        ];
    }

    /**
     * Encrypt sensitive data
     */
    public function encrypt($data) {
        $data = serialize($data);
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($this->encryption_cipher));
        $encrypted = openssl_encrypt(
            $data,
            $this->encryption_cipher,
            $this->encryption_key,
            0,
            $iv,
            $tag
        );

        return base64_encode(json_encode([
            'data' => $encrypted,
            'iv' => base64_encode($iv),
            'tag' => base64_encode($tag)
        ]));
    }

    /**
     * Decrypt sensitive data
     */
    public function decrypt($encrypted_data) {
        try {
            $decoded = json_decode(base64_decode($encrypted_data), true);
            $iv = base64_decode($decoded['iv']);
            $tag = base64_decode($decoded['tag']);

            $decrypted = openssl_decrypt(
                $decoded['data'],
                $this->encryption_cipher,
                $this->encryption_key,
                0,
                $iv,
                $tag
            );

            return unserialize($decrypted);
        } catch (Exception $e) {
            $this->logSecurityEvent('DECRYPTION_FAILED', [
                'error' => $e->getMessage(),
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            return null;
        }
    }

    /**
     * Log security events
     */
    public function logSecurityEvent($event, $data = []) {
        $event_data = [
            'event' => $event,
            'timestamp' => date('Y-m-d H:i:s'),
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'session_id' => session_id() ?? 'no_session',
            'data' => $data
        ];

        // Add to security events array
        $this->security_events[] = $event_data;

        // Log to file
        $log_file = __DIR__ . '/../logs/security_events.log';
        $log_entry = json_encode($event_data) . PHP_EOL;

        file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);

        // Database logging if available
        if ($this->db) {
            try {
                $stmt = $this->db->prepare("INSERT INTO security_logs (event, data, ip, user_agent, created_at) VALUES (:event, :data, :ip, :user_agent, NOW())");
                $stmt->execute([
                    'event' => $event, 
                    'data' => json_encode($data), 
                    'ip' => $event_data['ip'], 
                    'user_agent' => $event_data['user_agent']
                ]);
            } catch (Exception $e) {
                // Silent fail for database logging
            }
        }

        // Trigger event listeners
        $this->triggerSecurityEventListeners($event, $event_data);

        return $event_data;
    }

    /**
     * Trigger security event listeners
     */
    private function triggerSecurityEventListeners($event, $data) {
        // This could trigger email alerts, SMS notifications, etc.
        switch ($event) {
            case 'BRUTE_FORCE_ATTEMPT':
                // Send alert email
                $this->sendSecurityAlert('Brute Force Attempt Detected', $data);
                break;
            case 'SUSPICIOUS_ACTIVITY':
                // Log and monitor
                break;
            case 'IP_BLOCKED':
                // Log blocking action
                break;
        }
    }

    /**
     * Send security alert
     */
    private function sendSecurityAlert($subject, $data) {
        // Implementation for sending security alerts
        // Could use email, SMS, Slack, etc.
    }

    /**
     * Handle security errors
     */
    public function handleSecurityError($errno, $errstr, $errfile, $errline) {
        $this->logSecurityEvent('PHP_ERROR', [
            'error_number' => $errno,
            'error_string' => $errstr,
            'error_file' => $errfile,
            'error_line' => $errline
        ]);

        // Don't suppress the error, let PHP handle it normally
        return false;
    }

    /**
     * Handle security exceptions
     */
    public function handleSecurityException($exception) {
        $this->logSecurityEvent('EXCEPTION', [
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString()
        ]);

        // Show generic error page
        http_response_code(500);
        echo "An unexpected error occurred. Please try again later.";
        exit();
    }

    /**
     * Security cleanup on shutdown
     */
    public function securityCleanup() {
        // Clean up sensitive data
        if (isset($_SESSION)) {
            // Remove sensitive session data
            unset($_SESSION['csrf_token']);
            unset($_SESSION['csrf_token_time']);
        }

        // Clear any temporary security data
        $this->security_events = [];
    }

    /**
     * Get security status
     */
    public function getSecurityStatus() {
        return [
            'ip_blocked' => $this->isIPBlocked($_SERVER['REMOTE_ADDR'] ?? 'unknown'),
            'rate_limit_status' => $this->checkRateLimit($_SERVER['REMOTE_ADDR'] ?? 'unknown'),
            'security_features' => $this->security_config,
            'recent_events' => array_slice($this->security_events, -10)
        ];
    }

    /**
     * Validate file upload
     */
    public function validateFileUpload($file, $allowed_types = [], $max_size = 5242880) {
        $errors = [];

        // Check if file was uploaded
        if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
            $errors[] = 'No file uploaded';
            return $errors;
        }

        // Check file size
        if ($file['size'] > $max_size) {
            $errors[] = 'File size exceeds maximum allowed size';
        }

        // Check file type
        if (!empty($allowed_types)) {
            $file_type = mime_content_type($file['tmp_name']);
            if (!in_array($file_type, $allowed_types)) {
                $errors[] = 'File type not allowed';
            }
        }

        // Check for malicious content
        if ($this->isMaliciousFile($file['tmp_name'])) {
            $errors[] = 'File contains malicious content';
        }

        return $errors;
    }

    /**
     * Check if file contains malicious content
     */
    private function isMaliciousFile($file_path) {
        // Check file extension
        $extension = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
        $dangerous_extensions = ['php', 'php3', 'php4', 'php5', 'phtml', 'exe', 'bat', 'cmd', 'com', 'scr', 'vbs', 'js'];

        if (in_array($extension, $dangerous_extensions)) {
            return true;
        }

        // Check file content for PHP code
        $content = file_get_contents($file_path);
        if (preg_match('/<\?php|<\?=|<\?|\b(eval|exec|system|shell_exec|passthru|proc_open|popen)\s*\(/i', $content)) {
            return true;
        }

        return false;
    }

    /**
     * Get client IP address
     */
    public function getClientIP() {
        $ip_headers = [
            'HTTP_CF_CONNECTING_IP', // Cloudflare
            'HTTP_X_FORWARDED_FOR',  // Standard proxy header
            'HTTP_X_REAL_IP',        // Nginx proxy header
            'HTTP_CLIENT_IP',        // Common header
            'REMOTE_ADDR'            // Direct connection
        ];

        foreach ($ip_headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ip = $_SERVER[$header];

                // Handle multiple IPs (take the first one)
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }

                // Validate IP address
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }

        return 'UNKNOWN';
    }
}
?>
