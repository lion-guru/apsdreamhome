<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Modern Core Functions Service
 * Provides utility functions for common operations
 */
class CoreFunctionsService
{
    private string $logPath;
    private array $allowedImageTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    public function __construct()
    {
        $this->logPath = storage_path('logs/admin_actions.log');
    }

    /**
     * Log admin actions with structured data
     */
    public function logAdminAction(array $data): void
    {
        $logEntry = [
            'timestamp' => now()->toISOString(),
            'user_id' => auth()->id() ?? null,
            'ip' => $this->getClientIp(),
            'user_agent' => request()->userAgent(),
            'data' => $data
        ];

        Log::channel('admin')->info('Admin action', $logEntry);
    }

    /**
     * Enhanced input validation and sanitization
     */
    public function validateInput($input, string $type = 'string', ?int $maxLength = null, bool $required = true)
    {
        if ($required && empty($input)) {
            return false;
        }

        if (!$required && empty($input)) {
            return '';
        }

        switch ($type) {
            case 'username':
                return $this->validateUsername($input);
            
            case 'email':
                return $this->validateEmail($input);
            
            case 'password':
                return $input; // Don't sanitize passwords
            
            case 'captcha':
                return $this->validateCaptcha($input);
            
            case 'phone':
                return $this->validatePhone($input);
            
            case 'url':
                return $this->validateUrl($input);
            
            case 'numeric':
                return $this->validateNumeric($input);
            
            case 'string':
            default:
                return $this->validateString($input, $maxLength);
        }
    }

    /**
     * Validate username
     */
    private function validateUsername(string $input): string|false
    {
        $input = trim($input);
        
        if (strlen($input) < 3 || strlen($input) > 50) {
            return false;
        }
        
        if (!preg_match('/^[a-zA-Z0-9@._-]+$/', $input)) {
            return false;
        }
        
        return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Validate email
     */
    private function validateEmail(string $input): string|false
    {
        $input = filter_var($input, FILTER_SANITIZE_EMAIL);
        return filter_var($input, FILTER_VALIDATE_EMAIL) ? $input : false;
    }

    /**
     * Validate captcha
     */
    private function validateCaptcha($input): int|false
    {
        $input = filter_var($input, FILTER_SANITIZE_NUMBER_INT);
        return is_numeric($input) ? (int)$input : false;
    }

    /**
     * Validate phone number
     */
    private function validatePhone($input): string|false
    {
        $phone = preg_replace('/\D/', '', $input);
        
        if (strlen($phone) === 10) {
            $phone = '91' . $phone; // Add India country code
        }
        
        return preg_match('/^\d{10,15}$/', $phone) ? $phone : false;
    }

    /**
     * Validate URL
     */
    private function validateUrl(string $input): string|false
    {
        $input = filter_var($input, FILTER_SANITIZE_URL);
        return filter_var($input, FILTER_VALIDATE_URL) ? $input : false;
    }

    /**
     * Validate numeric input
     */
    private function validateNumeric($input): float|int|false
    {
        if (is_numeric($input)) {
            return strpos($input, '.') !== false ? (float)$input : (int)$input;
        }
        return false;
    }

    /**
     * Validate string input
     */
    private function validateString(string $input, ?int $maxLength): string|false
    {
        $input = trim($input);
        
        if ($maxLength && strlen($input) > $maxLength) {
            return false;
        }
        
        return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Validate request headers for security
     */
    public function validateRequestHeaders(Request $request): bool
    {
        // Check Content-Type for POST requests
        if ($request->isMethod('POST')) {
            $contentType = $request->header('Content-Type');
            if (!$contentType || 
                (strpos($contentType, 'application/x-www-form-urlencoded') === false &&
                 strpos($contentType, 'multipart/form-data') === false &&
                 strpos($contentType, 'application/json') === false)) {
                return false;
            }
        }

        // Check User-Agent
        if (empty($request->userAgent())) {
            return false;
        }

        return true;
    }

    /**
     * Get current URL
     */
    public function getCurrentUrl(): string
    {
        return request()->fullUrl();
    }

    /**
     * Check if file exists and is readable
     */
    public function safeFileExists(string $filepath): bool
    {
        return file_exists($filepath) && is_readable($filepath);
    }

    /**
     * Safe redirect
     */
    public function safeRedirect(string $url, bool $permanent = false): \Illuminate\Http\RedirectResponse
    {
        return redirect()->away($url, $permanent ? 301 : 302);
    }

    /**
     * Format phone number for display
     */
    public function formatPhoneNumber(string $phone): string
    {
        $phone = preg_replace('/\D/', '', $phone);
        
        if (strlen($phone) === 10) {
            return '+91 ' . substr($phone, 0, 5) . ' ' . substr($phone, 5);
        }
        
        if (strlen($phone) === 12 && substr($phone, 0, 2) === '91') {
            return '+91 ' . substr($phone, 2, 5) . ' ' . substr($phone, 7);
        }
        
        return $phone;
    }

    /**
     * Generate random string
     */
    public function generateRandomString(int $length = 16): string
    {
        return Str::random($length);
    }

    /**
     * Check if user is authenticated
     */
    public function isAuthenticated(): bool
    {
        return auth()->check();
    }

    /**
     * Get current user role
     */
    public function getUserRole(): ?string
    {
        return auth()->user()?->role;
    }

    /**
     * Check if user has permission
     */
    public function hasPermission(string $permission): bool
    {
        return auth()->user()?->hasPermission($permission) ?? false;
    }

    /**
     * Format currency
     */
    public function formatCurrency(float $amount, string $currency = '₹'): string
    {
        return $currency . number_format($amount, 2);
    }

    /**
     * Format date
     */
    public function formatDate($date, string $format = 'Y-m-d H:i:s'): string
    {
        return date($format, strtotime($date));
    }

    /**
     * Sanitize filename
     */
    public function sanitizeFilename(string $filename): string
    {
        return preg_replace('/[^a-zA-Z0-9\-_.]/', '', $filename);
    }

    /**
     * Ensure directory exists
     */
    public function ensureDirectoryExists(string $dir): void
    {
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }

    /**
     * Get file extension
     */
    public function getFileExtension(string $filename): string
    {
        return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    }

    /**
     * Check if file is image
     */
    public function isImageFile(string $filename): bool
    {
        return in_array($this->getFileExtension($filename), $this->allowedImageTypes);
    }

    /**
     * Resize and compress image
     */
    public function resizeImage(string $sourcePath, string $destinationPath, int $maxWidth = 800, int $maxHeight = 600, int $quality = 85): bool
    {
        if (!extension_loaded('gd') || !file_exists($sourcePath)) {
            return false;
        }

        $imageInfo = @getimagesize($sourcePath);
        if (!$imageInfo) {
            return false;
        }

        [$width, $height, $type] = $imageInfo;

        // Calculate new dimensions
        $ratio = min($maxWidth / $width, $maxHeight / $height);
        $newWidth = round($width * $ratio);
        $newHeight = round($height * $ratio);

        try {
            $sourceImage = $this->createImageResource($sourcePath, $type);
            if (!$sourceImage) {
                return false;
            }

            $newImage = imagecreatetruecolor($newWidth, $newHeight);

            // Preserve transparency for PNG/GIF
            if ($type === IMAGETYPE_PNG || $type === IMAGETYPE_GIF) {
                imagecolortransparent($newImage, imagecolorallocatealpha($newImage, 0, 0, 0, 127));
                imagealphablending($newImage, false);
                imagesavealpha($newImage, true);
            }

            imagecopyresampled($newImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

            $result = $this->saveImage($newImage, $destinationPath, $type, $quality);

            imagedestroy($sourceImage);
            imagedestroy($newImage);

            return $result;
        } catch (\Exception $e) {
            Log::error('Image resize error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Create image resource from file
     */
    private function createImageResource(string $path, int $type)
    {
        return match ($type) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($path),
            IMAGETYPE_PNG => @imagecreatefrompng($path),
            IMAGETYPE_GIF => @imagecreatefromgif($path),
            default => false
        };
    }

    /**
     * Save image resource to file
     */
    private function saveImage($image, string $path, int $type, int $quality): bool
    {
        return match ($type) {
            IMAGETYPE_JPEG => imagejpeg($image, $path, $quality),
            IMAGETYPE_PNG => imagepng($image, $path, 9),
            IMAGETYPE_GIF => imagegif($image, $path),
            default => false
        };
    }

    /**
     * Generate slug from string
     */
    public function generateSlug(string $string): string
    {
        return Str::slug($string);
    }

    /**
     * Truncate text
     */
    public function truncateText(string $text, int $length = 100, string $suffix = '...'): string
    {
        return Str::limit($text, $length, $suffix);
    }

    /**
     * Get client IP address
     */
    public function getClientIp(): string
    {
        return request()->ip();
    }

    /**
     * Rate limiting check
     */
    public function checkRateLimit(string $key, int $maxAttempts = 5, int $timeWindow = 300): bool
    {
        // Use Laravel's built-in rate limiting
        return !RateLimiter::tooManyAttempts($key, $maxAttempts);
    }

    /**
     * Check if request is AJAX
     */
    public function isAjaxRequest(): bool
    {
        return request()->ajax();
    }

    /**
     * Hash password securely
     */
    public function hashPassword(string $password): string
    {
        return bcrypt($password);
    }

    /**
     * Verify password hash
     */
    public function verifyPasswordHash(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /**
     * Generate CSRF token
     */
    public function generateCsrfToken(): string
    {
        return csrf_token();
    }

    /**
     * Validate CSRF token
     */
    public function validateCsrfToken(?string $token): bool
    {
        return $token === csrf_token();
    }

    /**
     * Send JSON response
     */
    public function sendJsonResponse(array $data, int $statusCode = 200): \Illuminate\Http\JsonResponse
    {
        return response()->json($data, $statusCode);
    }

    /**
     * Get WhatsApp templates
     */
    public function getWhatsAppTemplates(): array
    {
        return config('whatsapp.templates', []);
    }

    /**
     * Validate array of inputs
     */
    public function validateInputs(array $inputs, array $rules): array
    {
        $validator = Validator::make($inputs, $rules);
        
        if ($validator->fails()) {
            return [
                'valid' => false,
                'errors' => $validator->errors()->toArray()
            ];
        }
        
        return [
            'valid' => true,
            'data' => $validator->validated()
        ];
    }

    /**
     * Generate unique filename
     */
    public function generateUniqueFilename(string $originalName, string $prefix = ''): string
    {
        $extension = $this->getFileExtension($originalName);
        $filename = $prefix . Str::random(40);
        
        return $extension ? $filename . '.' . $extension : $filename;
    }

    /**
     * Get file size in human readable format
     */
    public function formatFileSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Create thumbnail from image
     */
    public function createThumbnail(string $sourcePath, string $thumbnailPath, int $width = 200, int $height = 200): bool
    {
        return $this->resizeImage($sourcePath, $thumbnailPath, $width, $height, 90);
    }

    /**
     * Extract text from file (basic implementation)
     */
    public function extractTextFromFile(string $filePath): string
    {
        if (!file_exists($filePath)) {
            return '';
        }

        $extension = $this->getFileExtension($filePath);
        
        return match ($extension) {
            'txt' => file_get_contents($filePath),
            'csv' => $this->parseCsv(file_get_contents($filePath)),
            'json' => json_encode(json_decode(file_get_contents($filePath), true), JSON_PRETTY_PRINT),
            default => ''
        };
    }

    /**
     * Parse CSV content
     */
    private function parseCsv(string $content): string
    {
        $lines = explode("\n", $content);
        $result = [];
        
        foreach ($lines as $line) {
            if (!empty(trim($line))) {
                $result[] = str_getcsv($line);
            }
        }
        
        return json_encode($result, JSON_PRETTY_PRINT);
    }
}
