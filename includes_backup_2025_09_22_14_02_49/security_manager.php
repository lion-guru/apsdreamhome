<?php
// Advanced Security Management System

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
        'input_sanitization' => true
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

    public function __construct($logger, $db) {
        $this->logger = $logger;
        $this->db = $db;

        // Initialize encryption key
        $this->initializeEncryptionKey();
    }

    /**
     * Initialize secure encryption key
     */
    private function initializeEncryptionKey() {
        // Retrieve or generate encryption key
        $key = getenv('APP_ENCRYPTION_KEY');
        
        if (!$key) {
            $key = bin2hex(random_bytes(32)); // Generate a secure random key
            // In a real-world scenario, store this securely and consistently
        }

        $this->encryption_key = hash('sha256', $key, true);
    }

    /**
     * Encrypt sensitive data
     * @param mixed $data Data to encrypt
     * @return string Encrypted data
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
     * @param string $encrypted_data Encrypted data
     * @return mixed Decrypted data
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
            $this->logger->log(
                "Decryption error: " . $e->getMessage(), 
                'error', 
                'security'
            );
            return null;
        }
    }

    /**
     * Sanitize input to prevent XSS
     * @param mixed $input Input to sanitize
     * @param string $type Input type (html, url, email, etc.)
     * @return mixed Sanitized input
     */
    public function sanitizeInput($input, $type = 'html') {
        if (!$this->security_config['input_sanitization']) return $input;

        if (is_array($input)) {
            return array_map(function($item) use ($type) {
                return $this->sanitizeInput($item, $type);
            }, $input);
        }

        if (!is_string($input)) return $input;

        switch ($type) {
            case 'html':
                return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
            case 'url':
                return filter_var($input, FILTER_SANITIZE_URL);
            case 'email':
                return filter_var($input, FILTER_SANITIZE_EMAIL);
            case 'int':
                return filter_var($input, FILTER_SANITIZE_NUMBER_INT);
            case 'float':
                return filter_var($input, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            default:
                return strip_tags($input);
        }
    }

    /**
     * Validate and sanitize SQL input
     * @param string $input SQL input
     * @return string Sanitized SQL input
     */
    public function sanitizeSqlInput($input) {
        if (!$this->security_config['sql_injection_protection']) return $input;

        // Remove potential SQL injection characters
        $input = preg_replace('/[\'";`()]/', '', $input);
        
        // Additional SQL injection prevention
        $input = str_replace(['--', '/*', '*/', 'xp_'], '', $input);

        return $input;
    }

    /**
     * Check IP reputation and block suspicious IPs
     * @param string $ip_address IP to check
     * @return bool True if IP is safe, false if suspicious
     */
    public function checkIPReputation($ip_address) {
        if (!$this->security_config['ip_reputation_check']) return true;

        try {
            // Check against local blacklist
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as blocked 
                FROM ip_blacklist 
                WHERE ip_address = ? AND blocked_until > NOW()
            ");
            $stmt->bind_param('s', $ip_address);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();

            if ($result['blocked'] > 0) {
                $this->logger->log(
                    "Blocked suspicious IP: {$ip_address}", 
                    'warning', 
                    'security'
                );
                return false;
            }

            // Additional reputation checks can be added here
            // e.g., external API calls to IP reputation services

            return true;
        } catch (Exception $e) {
            $this->logger->log(
                "IP reputation check error: " . $e->getMessage(), 
                'error', 
                'security'
            );
            return true; // Fail open
        }
    }

    /**
     * Implement rate limiting
     * @param string $identifier Unique identifier (IP or user ID)
     * @return bool True if request is allowed, false if rate limited
     */
    public function checkRateLimit($identifier) {
        if (!$this->security_config['rate_limiting']) return true;

        try {
            $current_time = time();
            $time_window_start = $current_time - $this->rate_limit_config['time_window'];

            // Check and clean up old rate limit entries
            $cleanup_stmt = $this->db->prepare("
                DELETE FROM rate_limits 
                WHERE timestamp < ? OR identifier = ?
            ");
            $cleanup_stmt->bind_param('is', $time_window_start, $identifier);
            $cleanup_stmt->execute();

            // Count requests in time window
            $count_stmt = $this->db->prepare("
                SELECT COUNT(*) as request_count 
                FROM rate_limits 
                WHERE identifier = ? AND timestamp > ?
            ");
            $count_stmt->bind_param('si', $identifier, $time_window_start);
            $count_stmt->execute();
            $result = $count_stmt->get_result()->fetch_assoc();

            // Check if request count exceeds limit
            if ($result['request_count'] >= $this->rate_limit_config['max_requests']) {
                // Block IP
                $block_stmt = $this->db->prepare("
                    INSERT INTO ip_blacklist 
                    (ip_address, blocked_until) 
                    VALUES (?, FROM_UNIXTIME(?))
                    ON DUPLICATE KEY UPDATE blocked_until = FROM_UNIXTIME(?)
                ");
                $block_time = $current_time + $this->rate_limit_config['block_duration'];
                $block_stmt->bind_param('sii', $identifier, $block_time, $block_time);
                $block_stmt->execute();

                $this->logger->log(
                    "Rate limit exceeded for {$identifier}", 
                    'warning', 
                    'security'
                );
                return false;
            }

            // Log request
            $log_stmt = $this->db->prepare("
                INSERT INTO rate_limits 
                (identifier, timestamp) 
                VALUES (?, ?)
            ");
            $log_stmt->bind_param('si', $identifier, $current_time);
            $log_stmt->execute();

            return true;
        } catch (Exception $e) {
            $this->logger->log(
                "Rate limit check error: " . $e->getMessage(), 
                'error', 
                'security'
            );
            return true; // Fail open
        }
    }

    /**
     * Set security headers
     */
    public function setSecurityHeaders() {
        if (!$this->security_config['security_headers']) return;

        // Prevent clickjacking
        header('X-Frame-Options: SAMEORIGIN');

        // Prevent MIME type sniffing
        header('X-Content-Type-Options: nosniff');

        // Enable XSS protection in browsers
        header('X-XSS-Protection: 1; mode=block');

        // Strict transport security (for HTTPS)
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');

        // Referrer policy
        header('Referrer-Policy: strict-origin-when-cross-origin');

        // Content Security Policy
        header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; img-src 'self' data: https:; connect-src 'self';");
    }

    /**
     * Generate CSRF Token
     * @return string CSRF Token
     */
    public function generateCsrfToken() {
        if (!$this->security_config['csrf_protection']) return null;

        $token = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $token;
        return $token;
    }

    /**
     * Validate CSRF Token
     * @param string $token Token to validate
     * @return bool
     */
    public function validateCsrfToken($token) {
        if (!$this->security_config['csrf_protection']) return true;

        $session_token = $_SESSION['csrf_token'] ?? null;
        return hash_equals($session_token, $token);
    }

    /**
     * Set Security Level
     * @param string $level Security level
     */
    public function setSecurityLevel($level) {
        switch ($level) {
            case self::LEVEL_LOW:
                $this->security_config = [
                    'xss_protection' => false,
                    'csrf_protection' => false,
                    'sql_injection_protection' => false,
                    'rate_limiting' => false,
                    'ip_reputation_check' => false,
                    'security_headers' => false,
                    'input_sanitization' => false
                ];
                break;
            case self::LEVEL_MEDIUM:
                $this->security_config = [
                    'xss_protection' => true,
                    'csrf_protection' => true,
                    'sql_injection_protection' => true,
                    'rate_limiting' => false,
                    'ip_reputation_check' => false,
                    'security_headers' => true,
                    'input_sanitization' => true
                ];
                break;
            case self::LEVEL_HIGH:
                $this->security_config = [
                    'xss_protection' => true,
                    'csrf_protection' => true,
                    'sql_injection_protection' => true,
                    'rate_limiting' => true,
                    'ip_reputation_check' => true,
                    'security_headers' => true,
                    'input_sanitization' => true
                ];
                break;
            case self::LEVEL_CRITICAL:
                $this->security_config = [
                    'xss_protection' => true,
                    'csrf_protection' => true,
                    'sql_injection_protection' => true,
                    'rate_limiting' => true,
                    'ip_reputation_check' => true,
                    'security_headers' => true,
                    'input_sanitization' => true
                ];
                // Additional strict measures can be added here
                break;
        }
    }
}

// Helper function for dependency injection
function getSecurityManager() {
    $container = container(); // Assuming dependency container is loaded
    
    // Lazy load dependencies
    $logger = $container->resolve('logger');
    $db = $container->resolve('db_connection');
    
    return new SecurityManager($logger, $db);
}

return getSecurityManager();
