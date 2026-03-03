<?php
namespace App\Services\Security;

class SecurityHardeningService
{
    /**
     * Enhanced input sanitization
     */
    public function sanitizeInput($input, $type = 'string')
    {
        if (is_array($input)) {
            return array_map([$this, 'sanitizeInput'], $input, array_fill(0, count($input), $type));
        }
        
        switch ($type) {
            case 'email':
                return filter_var($input, FILTER_SANITIZE_EMAIL);
            case 'url':
                return filter_var($input, FILTER_SANITIZE_URL);
            case 'int':
                return filter_var($input, FILTER_SANITIZE_NUMBER_INT);
            case 'float':
                return filter_var($input, FILTER_SANITIZE_NUMBER_FLOAT);
            case 'string':
            default:
                return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
        }
    }
    
    /**
     * Enhanced SQL injection prevention
     */
    public function preventSQLInjection($query, $params = [])
    {
        // Basic SQL pattern detection
        $dangerousPatterns = [
            '/\b(DROP|DELETE|INSERT|UPDATE|CREATE|ALTER|TRUNCATE)\b/i',
            '/\b(UNION|SELECT|FROM|WHERE|JOIN|GROUP BY|ORDER BY)\b/i',
            '/\b(OR|AND|NOT|IN|EXISTS|BETWEEN|LIKE)\b/i',
            '/\b(SCRIPT|JAVASCRIPT|VBSCRIPT|ONLOAD|ONERROR)\b/i'
        ];
        
        foreach ($dangerousPatterns as $pattern) {
            if (preg_match($pattern, $query)) {
                error_log("Potential SQL injection detected: " . $query);
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Enhanced XSS prevention
     */
    public function preventXSS($input)
    {
        if (is_array($input)) {
            return array_map([$this, 'preventXSS'], $input);
        }
        
        // Remove dangerous HTML tags and attributes
        $dangerousTags = [
            '<script', '</script>', '<iframe', '</iframe>',
            '<object>', '</object>', '<embed>', '</embed>',
            '<form>', '</form>', '<input>', '<textarea>',
            '<link>', '<meta>', '<style>', '</style>'
        ];
        
        $dangerousAttributes = [
            'onload', 'onerror', 'onclick', 'onmouseover', 'onmouseout',
            'onchange', 'onsubmit', 'onfocus', 'onblur', 'onkeydown',
            'onkeyup', 'onkeypress', 'onmousedown', 'onmouseup', 'onmousemove',
            'javascript:', 'vbscript:', 'data:', 'src', 'href'
        ];
        
        $input = str_ireplace($dangerousTags, '', $input);
        
        foreach ($dangerousAttributes as $attr) {
            $input = preg_replace('/\b' . preg_quote($attr, '/') . '\b/i', '', $input);
        }
        
        return $input;
    }
    
    /**
     * Enhanced CSRF protection
     */
    public function generateCSRFToken($userId)
    {
        $token = bin2hex(random_bytes(32));
        $key = "csrf_token:{$userId}";
        
        // Store token in session/cache
        $_SESSION[$key] = $token;
        
        return $token;
    }
    
    /**
     * Validate CSRF token
     */
    public function validateCSRFToken($token, $userId)
    {
        $key = "csrf_token:{$userId}";
        
        return isset($_SESSION[$key]) && hash_equals($_SESSION[$key], $token);
    }
    
    /**
     * Enhanced rate limiting
     */
    public function checkRateLimit($identifier, $limit = 100, $window = 3600)
    {
        $key = "rate_limit:{$identifier}";
        $current = time();
        
        // Get current attempts
        $attempts = $this->getCacheValue($key, []);
        
        // Remove old attempts outside window
        $attempts = array_filter($attempts, function($timestamp) use ($current, $window) {
            return ($current - $timestamp) < $window;
        });
        
        // Check if limit exceeded
        if (count($attempts) >= $limit) {
            return false;
        }
        
        // Add current attempt
        $attempts[] = $current;
        
        // Store updated attempts
        $this->setCacheValue($key, $attempts, $window);
        
        return true;
    }
    
    /**
     * Enhanced password validation
     */
    public function validatePassword($password)
    {
        $errors = [];
        
        // Minimum length
        if (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters long';
        }
        
        // Check for common patterns
        if (preg_match('/^(.)\1+$', $password)) {
            $errors[] = 'Password cannot contain repeated characters';
        }
        
        // Check for common passwords
        $commonPasswords = [
            'password', '123456', 'qwerty', 'abc123', 'password123',
            'admin', 'root', 'user', 'test', 'guest'
        ];
        
        if (in_array(strtolower($password), $commonPasswords)) {
            $errors[] = 'Password is too common';
        }
        
        // Check for complexity
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Password must contain at least one uppercase letter';
        }
        
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'Password must contain at least one lowercase letter';
        }
        
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'Password must contain at least one number';
        }
        
        if (!preg_match('/[^A-Za-z0-9]/', $password)) {
            $errors[] = 'Password must contain at least one special character';
        }
        
        return $errors;
    }
    
    /**
     * Enhanced file upload security
     */
    public function validateFileUpload($file, $allowedTypes = [], $maxSize = 5242880)
    {
        $errors = [];
        
        // Check file size
        if ($file['size'] > $maxSize) {
            $errors[] = 'File size exceeds maximum allowed size';
        }
        
        // Check file type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!empty($allowedTypes) && !in_array($mimeType, $allowedTypes)) {
            $errors[] = 'File type not allowed';
        }
        
        // Check for malicious content
        $content = file_get_contents($file['tmp_name']);
        $maliciousPatterns = [
            '/\<\?php/i',
            '/\<\%=/i',
            '/\<script/i',
            '/\<iframe/i',
            '/\<object/i',
            '/\<embed/i'
        ];
        
        foreach ($maliciousPatterns as $pattern) {
            if (preg_match($pattern, $content)) {
                $errors[] = 'File contains potentially malicious content';
                break;
            }
        }
        
        return $errors;
    }
    
    /**
     * Enhanced session security
     */
    public function secureSession()
    {
        // Regenerate session ID
        session_regenerate_id(true);
        
        // Set secure session parameters
        ini_set('session.cookie_httponly', 1);
        ini_set('session.cookie_secure', 1);
        ini_set('session.cookie_samesite', 'Strict');
        ini_set('session.use_strict_mode', 1);
        ini_set('session.gc_maxlifetime', 7200);
        ini_set('session.cookie_lifetime', 7200);
    }
    
    /**
     * Helper method to get cache value
     */
    private function getCacheValue($key, $default = null)
    {
        // This would use your cache service
        return $_SESSION[$key] ?? $default;
    }
    
    /**
     * Helper method to set cache value
     */
    private function setCacheValue($key, $value, $ttl = 3600)
    {
        // This would use your cache service
        $_SESSION[$key] = $value;
    }
}
