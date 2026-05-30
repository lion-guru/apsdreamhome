<?php

namespace App\Services;

/**
 * Modern Core Functions Service
 * Core utility functions without Laravel dependency
 */
class CoreFunctionsServiceNew
{
    private static function storagePath(string $path = ''): string
    {
        $base = defined('STORAGE_PATH') ? STORAGE_PATH : (defined('APP_ROOT') ? APP_ROOT . '/storage' : dirname(__DIR__, 2) . '/storage');
        return $base . ($path ? '/' . ltrim($path, '/') : '');
    }

    private static function getConfig(string $key, $default = null)
    {
        $parts = explode('.', $key);
        $constName = strtoupper(implode('_', $parts));
        return defined($constName) ? constant($constName) : $default;
    }

    /**
     * Log admin actions
     */
    public static function logAdminAction(array $data): bool
    {
        try {
            $logEntry = [
                'timestamp' => date('c'),
                'user_id' => $_SESSION['user_id'] ?? 'system',
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
                'data' => $data
            ];

            error_log('Admin action logged: ' . json_encode($logEntry));

            // Also store in database for audit trail
            if (self::getConfig('admin.log_to_database', true)) {
                $db = \App\Core\Database\Database::getInstance();
                $db->insert('admin_action_logs', [
                    'user_id' => $_SESSION['user_id'] ?? null,
                    'action' => $data['action'] ?? 'unknown',
                    'details' => json_encode($data),
                    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }

            return true;
        } catch (\Exception $e) {
            error_log('Failed to log admin action: ' . $e->getMessage());
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
            case 'username':
                return self::validateUsername($input, $maxLength);
            
            case 'email':
                return self::validateEmail($input);
            
            case 'password':
                return self::validatePassword($input, $maxLength);
            
            case 'phone':
                return self::validatePhone($input);
            
            case 'captcha':
                return self::validateCaptcha($input);
            
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
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
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

        // Check for suspicious headers
        $suspiciousHeaders = ['HTTP_X_FORWARDED_HOST', 'HTTP_X_REAL_IP'];
        foreach ($suspiciousHeaders as $header) {
            if (!empty($_SERVER[$header])) {
                error_log('Suspicious header detected: ' . $header . ' = ' . $_SERVER[$header]);
            }
        }

        return true;
    }

    /**
     * Send security response
     */
    public static function sendSecurityResponse(int $statusCode, string $message, $data = null): string
    {
        $response = [
            'success' => false,
            'status' => 'error',
            'message' => $message,
            'timestamp' => date('c')
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        http_response_code($statusCode);
        header('Content-Type: application/json');
        return json_encode($response);
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
        ini_set('session.cookie_secure', (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 1 : 0);
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
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
        return $scheme . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . ($_SERVER['REQUEST_URI'] ?? '/');
    }

    /**
     * Check if file exists and is readable
     */
    public static function safeFileExists(string $filepath): bool
    {
        $fullPath = self::storagePath('app/' . ltrim($filepath, '/'));
        return file_exists($fullPath) && is_readable($fullPath);
    }

    /**
     * Safe redirect function
     */
    public static function safeRedirect(string $url, bool $permanent = false): void
    {
        // Validate URL for security
        if (!self::isValidUrl($url)) {
            throw new \InvalidArgumentException('Invalid redirect URL');
        }

        http_response_code($permanent ? 301 : 302);
        header('Location: ' . $url);
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
        return (bool) preg_match('/^\d{10,15}$/', $phone);
    }

    /**
     * Generate random string
     */
    public static function generateRandomString(int $length = 16): string
    {
        return substr(bin2hex(random_bytes((int) ceil($length / 2))), 0, $length);
    }

    /**
     * Check if user is authenticated
     */
    public static function isAuthenticated(): bool
    {
        return !empty($_SESSION['user_id']);
    }

    /**
     * Get user role
     */
    public static function getUserRole(): ?string
    {
        return $_SESSION['role'] ?? null;
    }

    /**
     * Check if user has permission
     */
    public static function hasPermission(string $permission): bool
    {
        $permissions = $_SESSION['permissions'] ?? [];
        return in_array($permission, $permissions);
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
        if ($date instanceof \DateTime) {
            return $date->format($format);
        }
        return date($format, is_numeric($date) ? $date : strtotime((string) $date));
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
            $fullDir = self::storagePath('app/' . ltrim($dir, '/'));
            if (!is_dir($fullDir)) {
                mkdir($fullDir, 0755, true);
            }
            return true;
        } catch (\Exception $e) {
            error_log('Failed to create directory: ' . $dir . ' - ' . $e->getMessage());
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
     * Resize and compress image
     */
    public static function resizeImage(string $sourcePath, string $destinationPath, int $maxWidth = 800, int $maxHeight = 600, int $quality = 85): bool
    {
        try {
            if (!extension_loaded('gd')) {
                return false;
            }

            $fullSource = self::storagePath('app/' . ltrim($sourcePath, '/'));
            $fullDest = self::storagePath('app/' . ltrim($destinationPath, '/'));

            if (!file_exists($fullSource)) {
                return false;
            }

            $sourceContent = file_get_contents($fullSource);
            if ($sourceContent === false) {
                return false;
            }

            $imageInfo = getimagesizefromstring($sourceContent);
            
            if (!$imageInfo) {
                return false;
            }

            list($width, $height, $type) = $imageInfo;

            // Calculate new dimensions
            $ratio = min($maxWidth / $width, $maxHeight / $height);
            $newWidth = round($width * $ratio);
            $newHeight = round($height * $ratio);

            // Create image resource based on type
            $sourceImage = match ($type) {
                IMAGETYPE_JPEG => imagecreatefromjpeg($fullSource),
                IMAGETYPE_PNG => imagecreatefrompng($fullSource),
                IMAGETYPE_GIF => imagecreatefromgif($fullSource),
                default => false
            };

            if (!$sourceImage) {
                return false;
            }

            // Create new image
            $newImage = imagecreatetruecolor($newWidth, $newHeight);

            // Preserve transparency for PNG/GIF
            if ($type === IMAGETYPE_PNG || $type === IMAGETYPE_GIF) {
                imagecolortransparent($newImage, imagecolorallocatealpha($newImage, 0, 0, 0, 127));
                imagealphablending($newImage, false);
                imagesavealpha($newImage, true);
            }

            // Resize
            imagecopyresampled($newImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

            // Save to temporary file first
            $tempPath = tempnam(sys_get_temp_dir(), 'img_resize_');
            $result = match ($type) {
                IMAGETYPE_JPEG => imagejpeg($newImage, $tempPath, $quality),
                IMAGETYPE_PNG => imagepng($newImage, $tempPath, 9),
                IMAGETYPE_GIF => imagegif($newImage, $tempPath),
                default => false
            };

            imagedestroy($sourceImage);
            imagedestroy($newImage);

            if ($result) {
                file_put_contents($fullDest, file_get_contents($tempPath));
                unlink($tempPath);
            }

            return $result;
        } catch (\Exception $e) {
            error_log('Image resize failed: ' . $sourcePath . ' - ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate slug from string
     */
    public static function generateSlug(string $string): string
    {
        $slug = strtolower(trim(preg_replace('/[^a-z0-9-]+/', '-', strtolower(trim($string))), '-'));
        return substr($slug, 0, 100);
    }

    /**
     * Truncate text
     */
    public static function truncateText(string $text, int $length = 100, string $suffix = '...'): string
    {
        if (mb_strlen($text) <= $length) {
            return $text;
        }
        return mb_strimwidth($text, 0, $length, $suffix);
    }

    /**
     * Get client IP address
     */
    public static function getClientIp(): string
    {
        return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }

    /**
     * Rate limiting check
     */
    public static function checkRateLimit(string $key, int $maxAttempts = 5, int $timeWindow = 300): bool
    {
        $cacheKey = "rate_limit:" . md5($key);
        
        $attempts = $_SESSION['_cache'][$cacheKey] ?? [];
        $now = time();
        
        // Filter out old attempts
        $recentAttempts = array_filter($attempts, fn($timestamp) => ($now - $timestamp) < $timeWindow);
        
        if (count($recentAttempts) >= $maxAttempts) {
            return false; // Rate limited
        }
        
        // Add current attempt
        $recentAttempts[] = $now;
        $_SESSION['_cache'][$cacheKey] = $recentAttempts;
        
        return true; // Not rate limited
    }

    /**
     * Send JSON response - returns JSON string
     */
    public static function sendJsonResponse($data, int $statusCode = 200): string
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        return json_encode($data);
    }

    /**
     * Check if request is AJAX
     */
    public static function isAjaxRequest(): bool
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    /**
     * Get WhatsApp templates
     */
    public static function getWhatsAppTemplates(): array
    {
        try {
            $templatesFile = self::storagePath('app/whatsapp_templates.php');
            
            if (file_exists($templatesFile)) {
                return include $templatesFile;
            }
            
            return [];
        } catch (\Exception $e) {
            error_log('Failed to load WhatsApp templates: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Hash password securely
     */
    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT);
    }

    /**
     * Verify password hash
     */
    public static function verifyPasswordHash(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /**
     * Validate username
     */
    private static function validateUsername(string $username, ?int $maxLength = null)
    {
        $username = trim($username);
        $maxLength = $maxLength ?? 50;
        
        if (strlen($username) < 3 || strlen($username) > $maxLength) {
            return false;
        }
        
        if (!preg_match('/^[a-zA-Z0-9@._-]+$/', $username)) {
            return false;
        }
        
        return htmlspecialchars($username, ENT_QUOTES, 'UTF-8');
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
     * Validate captcha
     */
    private static function validateCaptcha(string $captcha)
    {
        $captcha = filter_var($captcha, FILTER_SANITIZE_NUMBER_INT);
        return is_numeric($captcha) ? (int)$captcha : false;
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
