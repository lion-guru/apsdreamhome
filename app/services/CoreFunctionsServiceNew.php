<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

/**
 * Modern Core Functions Service
 * Enhanced implementation of legacy core functions with Laravel integration
 */
class CoreFunctionsServiceNew
{
    /**
     * Log admin actions
     */
    public static function logAdminAction(array $data): bool
    {
        try {
            $logEntry = [
                'timestamp' => now()->toISOString(),
                'user_id' => auth()->id() ?? 'system',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'data' => $data
            ];

            Log::channel('admin_actions')->info('Admin action logged', $logEntry);
            
            // Also store in database for audit trail
            if (config('admin.log_to_database', true)) {
                \DB::table('admin_action_logs')->insert([
                    'user_id' => auth()->id(),
                    'action' => $data['action'] ?? 'unknown',
                    'details' => json_encode($data),
                    'ip_address' => request()->ip(),
                    'created_at' => now()
                ]);
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to log admin action', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);
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
        $request = request();
        
        // Check Content-Type for POST requests
        if ($request->isMethod('POST')) {
            $contentType = $request->header('Content-Type');
            if (!$contentType || 
                (!str_contains($contentType, 'application/x-www-form-urlencoded') && 
                 !str_contains($contentType, 'multipart/form-data') &&
                 !str_contains($contentType, 'application/json'))) {
                return false;
            }
        }

        // Check User-Agent
        if (!$request->header('User-Agent')) {
            return false;
        }

        // Check for suspicious headers
        $suspiciousHeaders = ['X-Forwarded-Host', 'X-Real-IP'];
        foreach ($suspiciousHeaders as $header) {
            if ($request->header($header)) {
                Log::warning('Suspicious header detected', [
                    'header' => $header,
                    'value' => $request->header($header),
                    'ip' => $request->ip()
                ]);
            }
        }

        return true;
    }

    /**
     * Send security response
     */
    public static function sendSecurityResponse(int $statusCode, string $message, $data = null): \Illuminate\Http\JsonResponse
    {
        $response = [
            'success' => false,
            'status' => 'error',
            'message' => $message,
            'timestamp' => now()->toISOString()
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        return response()->json($response, $statusCode);
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
        ini_set('session.cookie_secure', request()->secure());
        ini_set('session.use_strict_mode', 1);
        ini_set('session.gc_maxlifetime', config('session.lifetime', 1800));

        // Generate CSRF token if not exists
        if (!session()->has('csrf_token')) {
            session()->put('csrf_token', self::generateRandomString(32));
        }

        // Regenerate session ID for security
        session()->regenerate();
    }

    /**
     * Get current URL helper
     */
    public static function getCurrentUrl(): string
    {
        return request()->fullUrl();
    }

    /**
     * Check if file exists and is readable
     */
    public static function safeFileExists(string $filepath): bool
    {
        return Storage::exists($filepath) && Storage::get($filepath) !== false;
    }

    /**
     * Safe redirect function
     */
    public static function safeRedirect(string $url, bool $permanent = false): \Illuminate\Http\RedirectResponse
    {
        // Validate URL for security
        if (!self::isValidUrl($url)) {
            throw new \InvalidArgumentException('Invalid redirect URL');
        }

        return redirect($url, $permanent ? 301 : 302);
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
        return \Illuminate\Support\Str::random($length);
    }

    /**
     * Check if user is authenticated
     */
    public static function isAuthenticated(): bool
    {
        return auth()->check();
    }

    /**
     * Get user role
     */
    public static function getUserRole(): ?string
    {
        return auth()->user()?->role ?? null;
    }

    /**
     * Check if user has permission
     */
    public static function hasPermission(string $permission): bool
    {
        return auth()->user()?->hasPermission($permission) ?? false;
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
        return Carbon::parse($date)->format($format);
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
            if (!Storage::exists($dir)) {
                Storage::makeDirectory($dir, 0755, true);
            }
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to create directory', [
                'directory' => $dir,
                'error' => $e->getMessage()
            ]);
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

            if (!Storage::exists($sourcePath)) {
                return false;
            }

            $sourceContent = Storage::get($sourcePath);
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
                IMAGETYPE_JPEG => imagecreatefromjpeg($sourceContent),
                IMAGETYPE_PNG => imagecreatefrompng($sourceContent),
                IMAGETYPE_GIF => imagecreatefromgif($sourceContent),
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
                Storage::put($destinationPath, file_get_contents($tempPath));
                unlink($tempPath);
            }

            return $result;
        } catch (\Exception $e) {
            Log::error('Image resize failed', [
                'source' => $sourcePath,
                'destination' => $destinationPath,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Generate slug from string
     */
    public static function generateSlug(string $string): string
    {
        $slug = \Illuminate\Support\Str::slug($string, '-');
        return substr($slug, 0, 100); // Limit length
    }

    /**
     * Truncate text
     */
    public static function truncateText(string $text, int $length = 100, string $suffix = '...'): string
    {
        return \Illuminate\Support\Str::limit($text, $length, $suffix);
    }

    /**
     * Get client IP address
     */
    public static function getClientIp(): string
    {
        return request()->ip();
    }

    /**
     * Rate limiting check
     */
    public static function checkRateLimit(string $key, int $maxAttempts = 5, int $timeWindow = 300): bool
    {
        $cacheKey = "rate_limit:" . md5($key);
        
        $attempts = Cache::get($cacheKey, []);
        $now = now()->timestamp;
        
        // Filter out old attempts
        $recentAttempts = array_filter($attempts, fn($timestamp) => ($now - $timestamp) < $timeWindow);
        
        if (count($recentAttempts) >= $maxAttempts) {
            return false; // Rate limited
        }
        
        // Add current attempt
        $recentAttempts[] = $now;
        Cache::put($cacheKey, $recentAttempts, $timeWindow);
        
        return true; // Not rate limited
    }

    /**
     * Send JSON response
     */
    public static function sendJsonResponse($data, int $statusCode = 200): \Illuminate\Http\JsonResponse
    {
        return response()->json($data, $statusCode);
    }

    /**
     * Check if request is AJAX
     */
    public static function isAjaxRequest(): bool
    {
        return request()->ajax();
    }

    /**
     * Get WhatsApp templates
     */
    public static function getWhatsAppTemplates(): array
    {
        try {
            $templatesFile = 'whatsapp_templates.php';
            
            if (Storage::exists($templatesFile)) {
                return include Storage::path($templatesFile);
            }
            
            return config('whatsapp.templates', []);
        } catch (\Exception $e) {
            Log::error('Failed to load WhatsApp templates', [
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Hash password securely
     */
    public static function hashPassword(string $password): string
    {
        return \Illuminate\Support\Facades\Hash::make($password);
    }

    /**
     * Verify password hash
     */
    public static function verifyPasswordHash(string $password, string $hash): bool
    {
        return \Illuminate\Support\Facades\Hash::check($password, $hash);
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
