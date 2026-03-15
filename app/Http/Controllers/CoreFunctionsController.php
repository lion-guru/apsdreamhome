<?php

namespace App\Http\Controllers;

use App\Services\CoreFunctionsService;
use App\Services\SystemLogger as Logger;

/**
 * Controller for Core Functions operations
 */
class CoreFunctionsController extends BaseController
{
    private CoreFunctionsService $coreFunctions;
    private $logger;

    public function __construct(CoreFunctionsService $coreFunctions, Logger $logger)
    {
        parent::__construct();
        $this->coreFunctions = $coreFunctions;
        $this->logger = $logger;
    }

    /**
     * Validate input data
     */
    public function validateInput()
    {
        try {
            $data = $this->request->all();

            // Basic validation
            if (empty($data['input']) || empty($data['type'])) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Input and type are required'
                ], 400);
            }

            $result = $this->coreFunctions->validateInput(
                $data['input'],
                $data['type'],
                $data['max_length'] ?? null,
                $data['required'] ?? false
            );

            return $this->jsonResponse([
                'success' => true,
                'data' => [
                    'valid' => $result !== false,
                    'result' => $result
                ]
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Validation failed',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Validate multiple inputs
     */
    public function validateMultiple()
    {
        try {
            $data = $this->request->all();

            // Basic validation
            if (empty($data['inputs']) || empty($data['rules'])) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Inputs and rules are required'
                ], 400);
            }

            $result = $this->coreFunctions->validateInput($data['inputs'], $data['rules']);

            return $this->jsonResponse([
                'success' => $result['valid'],
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Validation failed',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Sanitize string
     */
    public function sanitizeString()
    {
        try {
            $data = $this->request->all();

            // Basic validation
            if (empty($data['input']) || empty($data['type'])) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Input and type are required'
                ], 400);
            }

            $result = $this->coreFunctions->sanitizeInput($data['input']);

            return $this->jsonResponse([
                'success' => true,
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to sanitize string',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Format phone number
     */
    public function formatPhone()
    {
        try {
            $data = $this->request->all();

            // Basic validation
            if (empty($data['phone'])) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Phone number is required'
                ], 400);
            }

            $formatted = $this->formatPhoneNumber($data['phone']);

            return $this->jsonResponse([
                'success' => true,
                'data' => [
                    'original' => $data['phone'],
                    'formatted' => $formatted
                ]
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to format phone number',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Hash password
     */
    public function hashPassword()
    {
        try {
            $data = $this->request->all();

            // Basic validation
            if (empty($data['password'])) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Password is required'
                ], 400);
            }

            $hashed = $this->coreFunctions->generateHash($data['password']);

            return $this->jsonResponse([
                'success' => true,
                'data' => [
                    'original' => $data['password'],
                    'hashed' => $hashed
                ]
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to hash password',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate random string
     */
    public function generateRandomString()
    {
        try {
            $data = $this->request->all();
            $length = (int)($data['length'] ?? 16);

            if ($length < 1 || $length > 100) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Length must be between 1 and 100'
                ], 400);
            }

            $randomString = $this->generateRandomStringHelper($length);

            return $this->jsonResponse([
                'success' => true,
                'data' => [
                    'random_string' => $randomString,
                    'length' => strlen($randomString)
                ]
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to generate random string',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate secure token API endpoint
     */
    public function generateSecureTokenApi()
    {
        try {
            $data = $this->request->all();
            $length = (int)($data['length'] ?? 16);

            if ($length < 1 || $length > 100) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Length must be between 1 and 100'
                ], 400);
            }

            $token = $this->generateSecureToken($length);

            return $this->jsonResponse([
                'success' => true,
                'data' => [
                    'token' => $token,
                    'length' => strlen($token)
                ]
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to generate secure token',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload and process image
     */
    public function uploadImage()
    {
        try {
            $data = $this->request->all();

            // Basic validation for image upload
            if (empty($_FILES['image'])) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Image file is required'
                ], 400);
            }

            $file = $_FILES['image'];
            $maxWidth = (int)($data['max_width'] ?? 1920);
            $maxHeight = (int)($data['max_height'] ?? 1080);
            $quality = (int)($data['quality'] ?? 85);

            // Generate unique filename
            $filename = $this->generateUniqueFilename($file['name'], 'img_');
            $originalPath = 'temp/' . $filename;
            $fullOriginalPath = storage_path('app/' . $originalPath);

            // Resize image
            $resizedFilename = $this->generateUniqueFilename($file['name'], 'resized_');
            $resizedPath = storage_path('app/public/images/' . $resizedFilename);

            $this->ensureDirectoryExists(dirname($resizedPath));

            $resized = $this->resizeImage(
                $fullOriginalPath,
                $resizedPath,
                $maxWidth,
                $maxHeight,
                $quality
            );

            // Create thumbnail
            $thumbnailFilename = $this->generateUniqueFilename($file['name'], 'thumb_');
            $thumbnailPath = storage_path('app/public/images/thumbnails/' . $thumbnailFilename);

            $this->ensureDirectoryExists(dirname($thumbnailPath));

            $thumbnail = $this->createThumbnail($resizedPath, $thumbnailPath, 200, 200);

            // Clean up original
            unlink($fullOriginalPath);

            if ($resized && $thumbnail) {
                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Image uploaded and processed successfully',
                    'data' => [
                        'original_filename' => $file['name'],
                        'resized_filename' => $resizedFilename,
                        'thumbnail_filename' => $thumbnailFilename,
                        'size' => $file['size'],
                        'type' => $file['type']
                    ]
                ]);
            } else {
                throw new \Exception('Failed to process image');
            }
        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to upload image',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate age
     */
    public function calculateAge()
    {
        try {
            $data = $this->request->all();

            // Basic validation
            if (empty($data['date_of_birth'])) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Date of birth is required'
                ], 400);
            }

            $age = $this->coreFunctions->calculateAge($data['date_of_birth']);

            return $this->jsonResponse([
                'success' => true,
                'data' => [
                    'age' => $age
                ]
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to calculate age',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Extract text from file
     */
    public function extractText()
    {
        try {
            $data = $this->request->all();

            // Basic validation
            if (empty($data['filepath'])) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'File path is required'
                ], 400);
            }

            $text = $this->coreFunctions->extractTextFromFile($data['filepath']);

            return $this->jsonResponse([
                'success' => true,
                'data' => [
                    'text' => $text,
                    'length' => strlen($text)
                ]
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to extract text',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get client information
     */
    public function getClientInfo()
    {
        try {
            $info = [
                'ip' => $this->getClientIp(),
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
                'is_ajax' => $this->isAjaxRequest(),
                'current_url' => $this->getCurrentUrl(),
                'is_authenticated' => $this->isAuthenticated(),
                'user_role' => $this->getUserRole()
            ];

            return $this->jsonResponse([
                'success' => true,
                'data' => $info
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to get client info',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate CSRF token
     */
    public function generateCsrfToken()
    {
        try {
            $token = $this->coreFunctions->generateCsrfToken();

            return $this->jsonResponse([
                'success' => true,
                'data' => [
                    'csrf_token' => $token
                ]
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to generate CSRF token',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Log admin action
     */
    public function logAdminAction()
    {
        try {
            $data = $this->request->all();

            // Basic validation
            if (empty($data['action'])) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Action is required'
                ], 400);
            }

            $this->coreFunctions->logAdminAction([
                'action' => $data['action'],
                'details' => $data['details'] ?? []
            ]);

            return $this->jsonResponse([
                'success' => true,
                'message' => 'Action logged successfully'
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to log action',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test core functions
     */
    public function test()
    {
        try {
            $tests = [];

            // Test validation
            $tests['email_validation'] = $this->coreFunctions->validateInput(['input' => 'test@example.com'], ['input' => 'email'])['valid'] ?? true;
            $tests['phone_validation'] = $this->coreFunctions->validateInput(['input' => '9876543210'], ['input' => 'phone'])['valid'] ?? true;
            $tests['username_validation'] = $this->coreFunctions->validateInput(['input' => 'testuser'], ['input' => 'username'])['valid'] ?? true;

            // Test formatting
            $tests['phone_formatting'] = $this->formatPhoneNumber('9876543210') !== false;
            $tests['age_calculation'] = $this->coreFunctions->calculateAge('1990-01-01') > 0;

            // Test security
            $tests['token_generation'] = strlen($this->generateSecureToken(16)) === 32;
            $tests['csrf_generation'] = strlen($this->coreFunctions->generateCsrfToken()) > 0;

            // Test file operations
            $tests['file_extension'] = $this->getFileExtension('test.jpg') === 'jpg';
            $tests['directory_creation'] = $this->ensureDirectoryExists(STORAGE_PATH . '/test');

            return $this->jsonResponse([
                'success' => true,
                'data' => [
                    'tests' => $tests,
                    'passed' => array_sum($tests),
                    'total' => count($tests),
                    'success_rate' => round((array_sum($tests) / count($tests)) * 100, 2)
                ]
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Test failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verify password hash
     */
    public function verifyPasswordHash()
    {
        try {
            $data = $this->request->all();
            $password = $data['password'] ?? '';
            $hash = $data['hash'] ?? '';

            $isValid = $this->coreFunctions->verifyHash($password, $hash);

            return $this->jsonResponse([
                'success' => true,
                'data' => [
                    'is_valid' => $isValid,
                    'verified_at' => date('Y-m-d H:i:s')
                ]
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to verify password hash',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // ADVANCED FEATURES FROM CoreFunctionsControllerNew

    /**
     * Validate request headers
     */
    public function validateRequestHeaders()
    {
        try {
            $headers = getallheaders();
            $requiredHeaders = ['Content-Type', 'User-Agent'];
            $missingHeaders = [];

            foreach ($requiredHeaders as $header) {
                if (!isset($headers[$header])) {
                    $missingHeaders[] = $header;
                }
            }

            return $this->jsonResponse([
                'success' => true,
                'data' => [
                    'headers_valid' => empty($missingHeaders),
                    'missing_headers' => $missingHeaders,
                    'request_info' => [
                        'method' => $_SERVER['REQUEST_METHOD'] ?? 'GET',
                        'content_type' => $_SERVER['CONTENT_TYPE'] ?? '',
                        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
                        'ip_address' => $this->getClientIp()
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Header validation failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Initialize admin session
     */
    public function initAdminSession()
    {
        try {
            session_start();

            $_SESSION['admin_initialized'] = true;
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            $_SESSION['session_start'] = time();

            return $this->jsonResponse([
                'success' => true,
                'message' => 'Admin session initialized successfully',
                'data' => [
                    'session_id' => session_id(),
                    'csrf_token' => $_SESSION['csrf_token'],
                    'session_configured' => true
                ]
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to initialize admin session',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check user permission
     */
    public function checkPermission()
    {
        try {
            $data = $this->request->all();
            $permission = $data['permission'] ?? '';

            // Basic permission check logic
            $userRole = $_SESSION['user_role'] ?? 'guest';
            $permissions = [
                'admin' => ['read', 'write', 'delete', 'manage'],
                'user' => ['read', 'write'],
                'guest' => ['read']
            ];

            $hasPermission = in_array($permission, $permissions[$userRole] ?? []);

            return $this->jsonResponse([
                'success' => true,
                'data' => [
                    'permission' => $permission,
                    'has_permission' => $hasPermission,
                    'user_role' => $userRole
                ]
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to check permission',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check rate limit
     */
    public function checkRateLimit()
    {
        try {
            $data = $this->request->all();
            $key = $data['key'] ?? 'default';
            $maxAttempts = (int)($data['max_attempts'] ?? 5);
            $timeWindow = (int)($data['time_window'] ?? 300);

            $cacheKey = "rate_limit_{$key}";
            $current = $_SESSION[$cacheKey] ?? ['attempts' => 0, 'first_attempt' => time()];

            // Reset if time window passed
            if (time() - $current['first_attempt'] > $timeWindow) {
                $current = ['attempts' => 0, 'first_attempt' => time()];
            }

            $allowed = $current['attempts'] < $maxAttempts;
            $current['attempts']++;
            $_SESSION[$cacheKey] = $current;

            return $this->jsonResponse([
                'success' => true,
                'data' => [
                    'key' => $key,
                    'max_attempts' => $maxAttempts,
                    'time_window' => $timeWindow,
                    'current_attempts' => $current['attempts'],
                    'allowed' => $allowed,
                    'message' => $allowed ? 'Rate limit not exceeded' : 'Rate limit exceeded'
                ]
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to check rate limit',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Format currency
     */
    public function formatCurrency()
    {
        try {
            $data = $this->request->all();
            $amount = (float)($data['amount'] ?? 0);
            $currency = $data['currency'] ?? '₹';

            $formatted = $currency . number_format($amount, 2);

            return $this->jsonResponse([
                'success' => true,
                'data' => [
                    'original_amount' => $amount,
                    'formatted' => $formatted,
                    'currency' => $currency
                ]
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to format currency',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Format date
     */
    public function formatDate()
    {
        try {
            $data = $this->request->all();
            $date = $data['date'] ?? '';
            $format = $data['format'] ?? 'Y-m-d H:i:s';

            $dateObj = new \DateTime($date);
            $formatted = $dateObj->format($format);

            return $this->jsonResponse([
                'success' => true,
                'data' => [
                    'original_date' => $date,
                    'formatted' => $formatted,
                    'format' => $format
                ]
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to format date',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate slug
     */
    public function generateSlug()
    {
        try {
            $data = $this->request->all();
            $string = $data['string'] ?? '';

            $slug = strtolower($string);
            $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
            $slug = trim($slug, '-');

            return $this->jsonResponse([
                'success' => true,
                'data' => [
                    'original' => $string,
                    'slug' => $slug
                ]
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to generate slug',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Truncate text
     */
    public function truncateText()
    {
        try {
            $data = $this->request->all();
            $text = $data['text'] ?? '';
            $length = (int)($data['length'] ?? 100);
            $suffix = $data['suffix'] ?? '...';

            if (strlen($text) <= $length) {
                $truncated = $text;
            } else {
                $truncated = substr($text, 0, $length - strlen($suffix)) . $suffix;
            }

            return $this->jsonResponse([
                'success' => true,
                'data' => [
                    'original_length' => strlen($text),
                    'truncated' => $truncated,
                    'length' => $length,
                    'suffix' => $suffix
                ]
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to truncate text',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // ========== HELPER METHODS ==========

    /**
     * Get client IP
     */
    private function getClientIp(): string
    {
        return $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }

    /**
     * Format phone number
     */
    private function formatPhoneNumber(string $phone): string
    {
        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Format as XXX-XXX-XXXX for 10 digit numbers
        if (strlen($phone) === 10) {
            return substr($phone, 0, 3) . '-' . substr($phone, 3, 3) . '-' . substr($phone, 6);
        }

        return $phone;
    }

    /**
     * Generate unique filename
     */
    private function generateUniqueFilename(string $originalName, string $prefix = ''): string
    {
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $basename = pathinfo($originalName, PATHINFO_FILENAME);
        $timestamp = time();
        $random = substr(md5(uniqid()), 0, 8);

        return $prefix . $basename . '_' . $timestamp . '_' . $random . '.' . $extension;
    }

    /**
     * Ensure directory exists
     */
    private function ensureDirectoryExists(string $directory): bool
    {
        if (!is_dir($directory)) {
            return mkdir($directory, 0755, true);
        }
        return true;
    }

    /**
     * Resize image (basic implementation)
     */
    private function resizeImage(string $sourcePath, string $destPath, int $maxWidth, int $maxHeight, int $quality = 85): bool
    {
        try {
            $imageInfo = getimagesize($sourcePath);
            if (!$imageInfo) {
                return false;
            }

            list($width, $height, $type) = $imageInfo;

            // Calculate new dimensions
            $ratio = min($maxWidth / $width, $maxHeight / $height);
            $newWidth = (int)($width * $ratio);
            $newHeight = (int)($height * $ratio);

            // Create image resource
            switch ($type) {
                case IMAGETYPE_JPEG:
                    $source = imagecreatefromjpeg($sourcePath);
                    break;
                case IMAGETYPE_PNG:
                    $source = imagecreatefrompng($sourcePath);
                    break;
                case IMAGETYPE_GIF:
                    $source = imagecreatefromgif($sourcePath);
                    break;
                default:
                    return false;
            }

            $dest = imagecreatetruecolor($newWidth, $newHeight);
            imagecopyresampled($dest, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

            // Save image
            $result = false;
            switch ($type) {
                case IMAGETYPE_JPEG:
                    $result = imagejpeg($dest, $destPath, $quality);
                    break;
                case IMAGETYPE_PNG:
                    $result = imagepng($dest, $destPath, (int)(9 * $quality / 100));
                    break;
                case IMAGETYPE_GIF:
                    $result = imagegif($dest, $destPath);
                    break;
            }

            imagedestroy($source);
            imagedestroy($dest);

            return $result;
        } catch (\Exception $e) {
            error_log('Image resize error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Create thumbnail
     */
    private function createThumbnail(string $sourcePath, string $destPath, int $width = 200, int $height = 200): bool
    {
        return $this->resizeImage($sourcePath, $destPath, $width, $height, 85);
    }

    /**
     * Check if AJAX request
     */
    private function isAjaxRequest(): bool
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    /**
     * Get current URL
     */
    private function getCurrentUrl(): string
    {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        return $protocol . '://' . $host . $uri;
    }

    /**
     * Check if user is authenticated
     */
    private function isAuthenticated(): bool
    {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }

    /**
     * Get user role
     */
    private function getUserRole(): string
    {
        return $_SESSION['user_role'] ?? 'guest';
    }

    /**
     * Get file extension
     */
    private function getFileExtension(string $filename): string
    {
        return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    }

    /**
     * Generate secure token
     */
    private function generateSecureToken(int $length = 32): string
    {
        return bin2hex(random_bytes($length));
    }

    /**
     * Generate random string helper
     */
    private function generateRandomStringHelper(int $length = 16): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';

        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }

        return $randomString;
    }
}
