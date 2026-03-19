<?php

namespace App\Services;

use App\Core\Database;
use App\Core\Config;
use Exception;

/**
 * Custom Core Functions Service
 * Pure PHP implementation for APS Dream Home Custom MVC
 */
class CoreFunctionsServiceCustom
{
    private static $db = null;
    
    /**
     * Initialize database connection
     */
    private static function getDb()
    {
        if (self::$db === null) {
            self::$db = Database::getInstance()->getConnection();
        }
        return self::$db;
    }
    
    /**
     * Log admin actions
     */
    public static function logAdminAction(array $data): bool
    {
        try {
            $db = self::getDb();
            
            $logEntry = [
                'user_id' => $_SESSION['user_id'] ?? 0,
                'action' => $data['action'] ?? 'unknown',
                'context' => json_encode($data),
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
                'created_at' => date('Y-m-d H:i:s')
            ];

            $sql = "INSERT INTO admin_activity_log (user_id, action, context, ip_address, user_agent, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            
            $stmt = $db->prepare($sql);
            return $stmt->execute([
                $logEntry['user_id'],
                $logEntry['action'],
                $logEntry['context'],
                $logEntry['ip_address'],
                $logEntry['user_agent'],
                $logEntry['created_at']
            ]);
            
        } catch (Exception $e) {
            error_log("Failed to log admin action: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Enhanced input validation and sanitization
     */
    public static function validateInput($input, string $type = 'string', ?int $maxLength = null, bool $required = true)
    {
        if ($required && empty($input)) {
            return false;
        }

        if (!$required && empty($input)) {
            return '';
        }

        switch ($type) {
            case 'email':
                return self::validateEmail($input);
            
            case 'password':
                return self::validatePassword($input, $maxLength);
            
            case 'phone':
                return self::validatePhone($input);
            
            case 'number':
                return self::validateNumber($input, $maxLength);
            
            case 'url':
                return self::validateUrl($input);
            
            case 'string':
            default:
                return self::validateString($input, $maxLength, $required);
        }
    }

    /**
     * Validate request headers for security
     */
    public static function validateRequestHeaders(): bool
    {
        // Check Content-Type for POST requests
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
            if (!$contentType || 
                (!str_contains($contentType, 'application/x-www-form-urlencoded') && 
                 !str_contains($contentType, 'multipart/form-data') &&
                 !str_contains($contentType, 'application/json'))) {
                return false;
            }
        }

        // Check User-Agent
        if (empty($_SERVER['HTTP_USER_AGENT'])) {
            return false;
        }

        return true;
    }

    /**
     * Send security response
     */
    public static function sendSecurityResponse(int $statusCode, string $message, $data = null): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        
        $response = [
            'success' => false,
            'status' => 'error',
            'message' => $message,
            'timestamp' => date('Y-m-d H:i:s')
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        echo json_encode($response);
        exit;
    }

    /**
     * Initialize admin session with proper security settings
     */
    public static function initAdminSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Set secure session parameters
        ini_set('session.cookie_httponly', 1);
        ini_set('session.cookie_secure', (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'));
        ini_set('session.use_strict_mode', 1);
        ini_set('session.gc_maxlifetime', 1800);

        // Generate CSRF token if not exists
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = self::generateRandomString(32);
        }

        // Regenerate session ID for security
        session_regenerate_id(true);
    }

    /**
     * Get current URL helper
     */
    public static function getCurrentUrl(): string
    {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        return $protocol . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    }

    /**
     * Check if file exists and is readable
     */
    public static function safeFileExists(string $filepath): bool
    {
        return file_exists($filepath) && is_readable($filepath);
    }

    /**
     * Safe redirect function
     */
    public static function safeRedirect(string $url, bool $permanent = false): void
    {
        // Validate URL for security
        if (!self::isValidUrl($url)) {
            throw new InvalidArgumentException('Invalid redirect URL');
        }

        $statusCode = $permanent ? 301 : 302;
        header("Location: $url", true, $statusCode);
        exit;
    }

    /**
     * Format phone number
     */
    public static function formatPhoneNumber(string $phone): string
    {
        // Remove all non-digit characters
        $phone = preg_replace('/\D/', '', $phone);

        // Add country code if not present (assuming India)
        if (strlen($phone) === 10) {
            $phone = '91' . $phone;
        }

        return $phone;
    }

    /**
     * Validate phone number
     */
    public static function isValidPhoneNumber(string $phone): bool
    {
        // Remove all non-digit characters first
        $phone = preg_replace('/\D/', '', $phone);
        
        // Basic validation - should be 10-15 digits
        return preg_match('/^\d{10,15}$/', $phone);
    }

    /**
     * Generate random string
     */
    public static function generateRandomString(int $length = 16): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $randomString;
    }

    /**
     * Check if user is authenticated
     */
    public static function isAuthenticated(): bool
    {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }

    /**
     * Get user role
     */
    public static function getUserRole(): ?string
    {
        return $_SESSION['user_role'] ?? null;
    }

    /**
     * Format currency
     */
    public static function formatCurrency(float $amount, string $currency = '₹'): string
    {
        return $currency . number_format($amount, 2);
    }

    /**
     * Format date
     */
    public static function formatDate($date, string $format = 'Y-m-d H:i:s'): string
    {
        if (is_string($date)) {
            $date = new DateTime($date);
        }
        return $date->format($format);
    }

    /**
     * Sanitize filename
     */
    public static function sanitizeFilename(string $filename): string
    {
        // Remove directory traversal attempts
        $filename = str_replace(['../', '..\\', '/', '\\'], '', $filename);
        
        // Remove special characters except dots, hyphens, and underscores
        $filename = preg_replace('/[^a-zA-Z0-9\-_.]/', '', $filename);
        
        // Limit length
        return substr($filename, 0, 255);
    }

    /**
     * Ensure directory exists
     */
    public static function ensureDirectoryExists(string $dir): bool
    {
        try {
            if (!is_dir($dir)) {
                return mkdir($dir, 0755, true);
            }
            return true;
        } catch (Exception $e) {
            error_log("Failed to create directory: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get file extension
     */
    public static function getFileExtension(string $filename): string
    {
        return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    }

    /**
     * Check if file is image
     */
    public static function isImageFile(string $filename): bool
    {
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        return in_array(self::getFileExtension($filename), $allowedExtensions);
    }

    /**
     * Generate slug from string
     */
    public static function generateSlug(string $string): string
    {
        $slug = strtolower($string);
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        $slug = trim($slug, '-');
        return substr($slug, 0, 100); // Limit length
    }

    /**
     * Truncate text
     */
    public static function truncateText(string $text, int $length = 100, string $suffix = '...'): string
    {
        if (strlen($text) <= $length) {
            return $text;
        }
        return substr($text, 0, $length) . $suffix;
    }

    /**
     * Get client IP address
     */
    public static function getClientIp(): string
    {
        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }

    /**
     * Rate limiting check
     */
    public static function checkRateLimit(string $key, int $maxAttempts = 5, int $timeWindow = 300): bool
    {
        $cacheKey = "rate_limit_" . md5($key);
        
        if (!isset($_SESSION[$cacheKey])) {
            $_SESSION[$cacheKey] = [];
        }
        
        $attempts = $_SESSION[$cacheKey];
        $now = time();
        
        // Filter out old attempts
        $recentAttempts = array_filter($attempts, fn($timestamp) => ($now - $timestamp) < $timeWindow);
        
        if (count($recentAttempts) >= $maxAttempts) {
            return false; // Rate limited
        }
        
        // Add current attempt
        $recentAttempts[] = $now;
        $_SESSION[$cacheKey] = $recentAttempts;
        
        return true; // Not rate limited
    }

    /**
     * Send JSON response
     */
    public static function sendJsonResponse($data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * Check if request is AJAX
     */
    public static function isAjaxRequest(): bool
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    /**
     * Hash password securely
     */
    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_ARGON2ID);
    }

    /**
     * Verify password hash
     */
    public static function verifyPasswordHash(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /**
     * Validate email
     */
    private static function validateEmail(string $email)
    {
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);
        return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : false;
    }

    /**
     * Validate password
     */
    private static function validatePassword(string $password, ?int $maxLength = null)
    {
        $maxLength = $maxLength ?? 255;
        
        if (strlen($password) > $maxLength) {
            return false;
        }
        
        // Don't sanitize passwords, return as-is
        return $password;
    }

    /**
     * Validate phone
     */
    private static function validatePhone(string $phone)
    {
        $phone = preg_replace('/\D/', '', $phone);
        return self::isValidPhoneNumber($phone) ? $phone : false;
    }

    /**
     * Validate number
     */
    private static function validateNumber($input, ?int $maxLength = null)
    {
        $number = filter_var($input, FILTER_SANITIZE_NUMBER_INT);
        if ($maxLength && strlen((string)$number) > $maxLength) {
            return false;
        }
        return is_numeric($number) ? $number : false;
    }

    /**
     * Validate URL
     */
    private static function validateUrl(string $url)
    {
        $url = filter_var($url, FILTER_SANITIZE_URL);
        return filter_var($url, FILTER_VALIDATE_URL) ? $url : false;
    }

    /**
     * Validate string
     */
    private static function validateString(string $input, ?int $maxLength = null, bool $required = true)
    {
        $input = trim($input);
        
        if ($required && empty($input)) {
            return false;
        }
        
        if (!$required && empty($input)) {
            return '';
        }
        
        if ($maxLength && strlen($input) > $maxLength) {
            return false;
        }
        
        return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Check if URL is valid and safe
     */
    private static function isValidUrl(string $url): bool
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }
        
        // Check for dangerous protocols
        $dangerousProtocols = ['javascript:', 'data:', 'vbscript:'];
        foreach ($dangerousProtocols as $protocol) {
            if (str_starts_with(strtolower($url), $protocol)) {
                return false;
            }
        }
        
        return true;
    }
}