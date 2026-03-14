<?php

namespace App\Core;

/**
 * APS Dream Home - Security Helper Class
 * Centralized security functions for input sanitization and validation
 */

class Security
{
    private static $csrfToken;
    private static $blockedIPs = [];
    
    /**
     * Sanitize input data
     */
    public static function sanitize($input, $type = 'string')
    {
        if (is_array($input)) {
            return array_map([self::class, 'sanitize'], $input);
        }
        
        switch ($type) {
            case 'email':
                return filter_var($input, FILTER_SANITIZE_EMAIL);
            case 'url':
                return filter_var($input, FILTER_SANITIZE_URL);
            case 'int':
                return filter_var($input, FILTER_SANITIZE_NUMBER_INT);
            case 'float':
                return filter_var($input, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            case 'html':
                return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
            case 'sql':
                /**
                 * [STRICTLY DEPRECATED] Use PDO prepared statements.
                 * This method now logs a warning and returns the input unchanged to prevent false sense of security.
                 */
                error_log("CRITICAL SECURITY WARNING: Security::sanitize('sql') called. Replace with PDO prepared statements.");
                return $input;
            default:
                return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
        }
    }
    
    /**
     * [DEPRECATED] Escape SQL input
     * @deprecated Use PDO prepared statements instead. This method is kept only for legacy compatibility.
     */
    private static function escapeSql($value)
    {
        // Remove potential SQL injection patterns
        $patterns = [
            '/union\s+select/i',
            '/or\s+1\s*=\s*1/i',
            '/drop\s+table/i',
            '/insert\s+into/i',
            '/delete\s+from/i',
            '/update\s+.*\s+set/i',
            '/--/',
            '/\/\*/',
            '/\*\//',
            '/;/'
        ];
        
        foreach ($patterns as $pattern) {
            $value = preg_replace($pattern, '', $value);
        }
        
        return $value;
    }
    
    /**
     * Validate email
     */
    public static function validateEmail($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Validate phone number (Indian format)
     */
    public static function validatePhone($phone)
    {
        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Check if it's a valid Indian mobile number (10 digits)
        return preg_match('/^[6-9]\d{9}$/', $phone);
    }
    
    /**
     * Generate CSRF token
     */
    public static function generateCSRFToken()
    {
        if (empty(self::$csrfToken)) {
            self::$csrfToken = bin2hex(random_bytes(32));
            $_SESSION['csrf_token'] = self::$csrfToken;
        }
        
        return self::$csrfToken;
    }
    
    /**
     * Validate CSRF token
     */
    public static function validateCSRFToken($token)
    {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Hash password
     */
    public static function hashPassword($password)
    {
        return password_hash($password, PASSWORD_ARGON2ID);
    }
    
    /**
     * Verify password
     */
    public static function verifyPassword($password, $hash)
    {
        return password_verify($password, $hash);
    }
    
    /**
     * Generate secure random string
     */
    public static function generateRandomString($length = 32)
    {
        return bin2hex(random_bytes($length / 2));
    }
    
    /**
     * Check if IP is blocked
     */
    public static function isIPBlocked($ip)
    {
        return in_array($ip, self::$blockedIPs);
    }
    
    /**
     * Block IP address
     */
    public static function blockIP($ip, $reason = 'Manual block')
    {
        if (!self::isIPBlocked($ip)) {
            self::$blockedIPs[] = $ip;
            
            // Log the block
            error_log("IP Blocked: $ip - Reason: $reason");
            
            // Add to database if available
            try {
                $db = \App\Core\Database\Database::getInstance()->getConnection();
                
                $stmt = $db->prepare("INSERT INTO blocked_ips (ip_address, reason, blocked_at) VALUES (?, ?, NOW())");
                $stmt->execute([$ip, $reason]);
                
            } catch (\Exception $e) {
                // Database not available, just log it
            }
        }
    }
    
    /**
     * Validate file upload
     */
    public static function validateFile($file, $allowedTypes = [], $maxSize = 5242880) // 5MB default
    {
        $errors = [];
        
        // Check if file was uploaded
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            $errors[] = 'Invalid file upload';
        }
        
        // Check file size
        if ($file['size'] > $maxSize) {
            $errors[] = 'File size exceeds maximum allowed size';
        }
        
        // Check file type
        if (!empty($allowedTypes)) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            
            if (!in_array($mimeType, $allowedTypes)) {
                $errors[] = 'File type not allowed';
            }
        }
        
        return empty($errors) ? true : $errors;
    }
    
    /**
     * Sanitize filename
     */
    public static function sanitizeFilename($filename)
    {
        // Remove dangerous characters
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '', $filename);
        
        // Remove double dots
        $filename = str_replace('..', '', $filename);
        
        // Ensure filename is not empty
        if (empty($filename)) {
            $filename = 'file_' . time();
        }
        
        return $filename;
    }
    
    /**
     * Check for XSS in content
     */
    public static function containsXSS($content)
    {
        $xssPatterns = [
            '/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/mi',
            '/<iframe\b[^<]*(?:(?!<\/iframe>)<[^<]*)*<\/iframe>/mi',
            '/<object\b[^<]*(?:(?!<\/object>)<[^<]*)*<\/object>/mi',
            '/<embed\b[^<]*(?:(?!<\/embed>)<[^<]*)*<\/embed>/mi',
            '/javascript:/i',
            '/on\w+\s*=/i'
        ];
        
        foreach ($xssPatterns as $pattern) {
            if (preg_match($pattern, $content)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Rate limiting check
     */
    public static function checkRateLimit($identifier, $limit = 10, $window = 60)
    {
        $cacheKey = "rate_limit_{$identifier}";
        
        // Get current attempts from session (simple implementation)
        $attempts = $_SESSION[$cacheKey] ?? ['count' => 0, 'start_time' => time()];
        
        // Reset if window expired
        if (time() - $attempts['start_time'] > $window) {
            $attempts = ['count' => 0, 'start_time' => time()];
        }
        
        // Check limit
        if ($attempts['count'] >= $limit) {
            return false;
        }
        
        // Increment counter
        $attempts['count']++;
        $_SESSION[$cacheKey] = $attempts;
        
        return true;
    }
    
    /**
     * Validate Indian PIN code
     */
    public static function validatePIN($pin)
    {
        return preg_match('/^[1-9]\d{5}$/', $pin);
    }
    
    /**
     * Validate Aadhaar number (basic format check)
     */
    public static function validateAadhaar($aadhaar)
    {
        // Remove spaces and hyphens
        $aadhaar = preg_replace('/[\s-]/', '', $aadhaar);
        
        // Check if it's 12 digits
        return preg_match('/^\d{12}$/', $aadhaar);
    }
    
    /**
     * Generate secure API key
     */
    public static function generateAPIKey()
    {
        return 'aps_' . bin2hex(random_bytes(24));
    }
    
    /**
     * Validate API key
     */
    public static function validateAPIKey($apiKey)
    {
        // Check format
        if (!preg_match('/^aps_[a-f0-9]{48}$/', $apiKey)) {
            return false;
        }
        
        // Check against database (implementation would go here)
        return true;
    }
    
    /**
     * Encrypt sensitive data
     */
    public static function encrypt($data, $key = null)
    {
        $key = $key ?? ($_ENV['ENCRYPTION_KEY'] ?? 'default_key_change_me');
        
        $iv = random_bytes(16);
        $encrypted = openssl_encrypt($data, 'AES-256-CBC', $key, 0, $iv);
        
        return base64_encode($iv . $encrypted);
    }
    
    /**
     * Decrypt sensitive data
     */
    public static function decrypt($encryptedData, $key = null)
    {
        $key = $key ?? ($_ENV['ENCRYPTION_KEY'] ?? 'default_key_change_me');
        
        $data = base64_decode($encryptedData);
        $iv = substr($data, 0, 16);
        $encrypted = substr($data, 16);
        
        return openssl_decrypt($encrypted, 'AES-256-CBC', $key, 0, $iv);
    }
    
    /**
     * Log security event
     */
    public static function logSecurityEvent($event, $details = [])
    {
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'event' => $event,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'details' => $details
        ];
        
        error_log("SECURITY: " . json_encode($logEntry));
        
        // Store in database if available
        try {
            $db = \App\Core\Database\Database::getInstance()->getConnection();
            
            $stmt = $db->prepare("INSERT INTO security_logs (event, ip_address, user_agent, details, created_at) VALUES (?, ?, ?, ?, NOW())");
            $stmt->execute([$event, $logEntry['ip'], $logEntry['user_agent'], json_encode($details)]);
            
        } catch (\Exception $e) {
            // Database not available, just log to error log
        }
    }
    
    /**
     * Check for suspicious patterns in request
     */
    public static function isSuspiciousRequest()
    {
        $suspiciousPatterns = [
            '/union\s+select/i',
            '/or\s+1\s*=\s*1/i',
            '/drop\s+table/i',
            '/<script/i',
            '/javascript:/i',
            '/on\w+\s*=/i'
        ];
        
        $requestUri = $_SERVER['REQUEST_URI'] ?? '';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        foreach ($suspiciousPatterns as $pattern) {
            if (preg_match($pattern, $requestUri) || preg_match($pattern, $userAgent)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Get client IP address
     */
    public static function getClientIP()
    {
        $ipKeys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'];
        
        foreach ($ipKeys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }
}
