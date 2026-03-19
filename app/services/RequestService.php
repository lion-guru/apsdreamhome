<?php

namespace App\Services;

use App\Core\Database;
use App\Core\Config;
use Exception;

/**
 * Custom Request Service
 * Pure PHP implementation for APS Dream Home Custom MVC
 */
class RequestService
{
    private $db;
    private $middlewares = [];
    
    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Add middleware
     */
    public function addMiddleware(string $name, callable $middleware): void
    {
        $this->middlewares[$name] = $middleware;
    }
    
    /**
     * Execute middleware
     */
    public function executeMiddleware(array $middlewareNames): bool
    {
        foreach ($middlewareNames as $name) {
            if (isset($this->middlewares[$name])) {
                $result = call_user_func($this->middlewares[$name]);
                if ($result === false) {
                    return false;
                }
            }
        }
        return true;
    }
    
    /**
     * Get request method
     */
    public function getMethod(): string
    {
        return $_SERVER['REQUEST_METHOD'] ?? 'GET';
    }
    
    /**
     * Get request URI
     */
    public function getUri(): string
    {
        return $_SERVER['REQUEST_URI'] ?? '/';
    }
    
    /**
     * Get input data
     */
    public function input(string $key = null, $default = null)
    {
        if ($key === null) {
            return array_merge($_GET, $_POST);
        }
        
        return $_POST[$key] ?? $_GET[$key] ?? $default;
    }
    
    /**
     * Get JSON input
     */
    public function getJson(): array
    {
        $input = file_get_contents('php://input');
        return json_decode($input, true) ?: [];
    }
    
    /**
     * Validate request
     */
    public function validate(array $rules): array
    {
        $errors = [];
        $data = $this->input();
        
        foreach ($rules as $field => $fieldRules) {
            $value = $data[$field] ?? null;
            
            foreach ($fieldRules as $rule => $ruleValue) {
                switch ($rule) {
                    case 'required':
                        if (empty($value)) {
                            $errors[$field][] = "$field is required";
                        }
                        break;
                        
                    case 'email':
                        if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                            $errors[$field][] = "$field must be a valid email";
                        }
                        break;
                        
                    case 'min':
                        if (!empty($value) && strlen($value) < $ruleValue) {
                            $errors[$field][] = "$field must be at least $ruleValue characters";
                        }
                        break;
                        
                    case 'max':
                        if (!empty($value) && strlen($value) > $ruleValue) {
                            $errors[$field][] = "$field must not exceed $ruleValue characters";
                        }
                        break;
                        
                    case 'numeric':
                        if (!empty($value) && !is_numeric($value)) {
                            $errors[$field][] = "$field must be numeric";
                        }
                        break;
                        
                    case 'regex':
                        if (!empty($value) && !preg_match($ruleValue, $value)) {
                            $errors[$field][] = "$field format is invalid";
                        }
                        break;
                }
            }
        }
        
        return $errors;
    }
    
    /**
     * Get headers
     */
    public function getHeaders(): array
    {
        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $header = str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))));
                $headers[$header] = $value;
            }
        }
        return $headers;
    }
    
    /**
     * Get header
     */
    public function getHeader(string $key, $default = null)
    {
        $headers = $this->getHeaders();
        return $headers[$key] ?? $default;
    }
    
    /**
     * Check if AJAX request
     */
    public function isAjax(): bool
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
    
    /**
     * Check if JSON request
     */
    public function isJson(): bool
    {
        $contentType = $this->getHeader('Content-Type', '');
        return str_contains($contentType, 'application/json');
    }
    
    /**
     * Get client IP
     */
    public function getClientIp(): string
    {
        $ipKeys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'];
        
        foreach ($ipKeys as $key) {
            if (!empty($_SERVER[$key])) {
                $ips = explode(',', $_SERVER[$key]);
                $ip = trim($ips[0]);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }
    
    /**
     * Get user agent
     */
    public function getUserAgent(): string
    {
        return $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    }
    
    /**
     * Check if HTTPS
     */
    public function isSecure(): bool
    {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || 
               $_SERVER['SERVER_PORT'] == 443;
    }
    
    /**
     * Get full URL
     */
    public function getFullUrl(): string
    {
        $protocol = $this->isSecure() ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        return $protocol . '://' . $host . $uri;
    }
    
    /**
     * Get base URL
     */
    public function getBaseUrl(): string
    {
        $protocol = $this->isSecure() ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $path = dirname($_SERVER['SCRIPT_NAME']);
        return $protocol . '://' . $host . $path;
    }
    
    /**
     * CSRF protection
     */
    public function validateCsrfToken(): bool
    {
        $token = $this->input('_token') ?? $this->getHeader('X-CSRF-TOKEN');
        return $token === ($_SESSION['csrf_token'] ?? '');
    }
    
    /**
     * Rate limiting
     */
    public function checkRateLimit(string $key, int $maxAttempts = 60, int $timeWindow = 60): bool
    {
        $cacheKey = "rate_limit_" . md5($key);
        
        if (!isset($_SESSION[$cacheKey])) {
            $_SESSION[$cacheKey] = ['attempts' => 0, 'reset_time' => time() + $timeWindow];
        }
        
        $rateData = $_SESSION[$cacheKey];
        
        // Reset if time window passed
        if (time() > $rateData['reset_time']) {
            $rateData['attempts'] = 0;
            $rateData['reset_time'] = time() + $timeWindow;
        }
        
        if ($rateData['attempts'] >= $maxAttempts) {
            return false;
        }
        
        $rateData['attempts']++;
        $_SESSION[$cacheKey] = $rateData;
        
        return true;
    }
    
    /**
     * File upload handling
     */
    public function handleFileUpload(string $fieldName, array $options = []): array
    {
        if (!isset($_FILES[$fieldName])) {
            return [
                'success' => false,
                'message' => 'No file uploaded',
                'error_code' => 'NO_FILE'
            ];
        }
        
        $file = $_FILES[$fieldName];
        
        // Check upload error
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errorMessages = [
                UPLOAD_ERR_INI_SIZE => 'File is too large',
                UPLOAD_ERR_FORM_SIZE => 'File is too large',
                UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
                UPLOAD_ERR_NO_FILE => 'No file was uploaded',
                UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
                UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
                UPLOAD_ERR_EXTENSION => 'File upload stopped by extension'
            ];
            
            return [
                'success' => false,
                'message' => $errorMessages[$file['error']] ?? 'Upload failed',
                'error_code' => 'UPLOAD_ERROR'
            ];
        }
        
        // Validate file
        $maxSize = $options['max_size'] ?? 5 * 1024 * 1024; // 5MB
        $allowedTypes = $options['allowed_types'] ?? ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx'];
        
        if ($file['size'] > $maxSize) {
            return [
                'success' => false,
                'message' => 'File is too large',
                'error_code' => 'FILE_TOO_LARGE'
            ];
        }
        
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $allowedTypes)) {
            return [
                'success' => false,
                'message' => 'File type not allowed',
                'error_code' => 'INVALID_FILE_TYPE'
            ];
        }
        
        // Generate unique filename
        $filename = date('Y-m-d_H-i-s') . '_' . uniqid() . '.' . $extension;
        $uploadDir = $options['upload_dir'] ?? 'uploads/';
        
        // Ensure upload directory exists
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // Move file
        $destination = $uploadDir . $filename;
        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            return [
                'success' => false,
                'message' => 'Failed to save file',
                'error_code' => 'SAVE_FAILED'
            ];
        }
        
        return [
            'success' => true,
            'message' => 'File uploaded successfully',
            'filename' => $filename,
            'filepath' => $destination,
            'size' => $file['size'],
            'type' => $extension
        ];
    }
    
    /**
     * Send JSON response
     */
    public function jsonResponse($data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    
    /**
     * Send error response
     */
    public function errorResponse(string $message, int $statusCode = 400, $data = null): void
    {
        $response = [
            'success' => false,
            'message' => $message,
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        if ($data !== null) {
            $response['data'] = $data;
        }
        
        $this->jsonResponse($response, $statusCode);
    }
    
    /**
     * Send success response
     */
    public function successResponse(string $message, $data = null): void
    {
        $response = [
            'success' => true,
            'message' => $message,
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        if ($data !== null) {
            $response['data'] = $data;
        }
        
        $this->jsonResponse($response, 200);
    }
    
    /**
     * Redirect
     */
    public function redirect(string $url, int $statusCode = 302): void
    {
        header("Location: $url", true, $statusCode);
        exit;
    }
    
    /**
     * Set flash message
     */
    public function setFlash(string $type, string $message): void
    {
        if (!isset($_SESSION['flash'])) {
            $_SESSION['flash'] = [];
        }
        
        $_SESSION['flash'][$type] = $message;
    }
    
    /**
     * Get flash message
     */
    public function getFlash(string $type): ?string
    {
        $message = $_SESSION['flash'][$type] ?? null;
        if ($message !== null) {
            unset($_SESSION['flash'][$type]);
        }
        return $message;
    }
    
    /**
     * Get all flash messages
     */
    public function getAllFlash(): array
    {
        $messages = $_SESSION['flash'] ?? [];
        $_SESSION['flash'] = [];
        return $messages;
    }
}