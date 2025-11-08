<?php
/**
 * API Authentication Middleware
 * Handles API key validation and rate limiting for API requests
 */

require_once __DIR__ . '/../// SECURITY: Sensitive information removed_manager.php';
require_once __DIR__ . '/../security_logger.php';
require_once __DIR__ . '/rate_limit_middleware.php';

class ApiAuthMiddleware {
    private $apiKeyManager;
    private $securityLogger;
    private $rateLimitMiddleware;
    private $requiredPermissions = [];
    private $bypassAuth = false;

    public function __construct(
        $apiKeyManager = null,
        $securityLogger = null,
        $rateLimitMiddleware = null
    ) {
        $this->apiKeyManager = $apiKeyManager ?? new ApiKeyManager();
        $this->securityLogger = $securityLogger ?? new SecurityLogger();
        $this->rateLimitMiddleware = $rateLimitMiddleware ?? new RateLimitMiddleware();
    }

    /**
     * Set required permissions for the endpoint
     */
    public function requirePermissions($permissions) {
        $this->requiredPermissions = is_array($permissions) ? $permissions : [$permissions];
        return $this;
    }

    /**
     * Allow endpoint to be accessed without API key
     */
    public function allowPublicAccess() {
        $this->bypassAuth = true;
        return $this;
    }

    /**
     * Handle API authentication
     */
    public function handle() {
        // Skip authentication for public endpoints
        if ($this->bypassAuth) {
            return true;
        }

        // Get API key from various possible sources
        $apiKey = $this->getApiKey();
        
        if (!$apiKey) {
            $this->sendError('API key is required', 401);
            return false;
        }

        // Validate API key
        $keyData = $this->apiKeyManager->validateKey($apiKey, $this->requiredPermissions);
        
        if (!$keyData) {
            $this->sendError('Invalid or expired API key', 401);
            return false;
        }

        // Apply rate limiting
        $rateLimitResult = $this->rateLimitMiddleware->handle('api', [
            'rate_limit' => $keyData['rate_limit'],
            'key_id' => $keyData['id']
        ]);

        if ($rateLimitResult !== true) {
            $this->sendError('Rate limit exceeded', 429);
            return false;
        }

        // Store API key data in request
        $_REQUEST['// SECURITY: Sensitive information removed_data'] = $keyData;
        
        return true;
    }

    /**
     * Get API key from request
     */
    private function getApiKey() {
        // Check Authorization header
        $headers = getallheaders();
        if (isset($headers['Authorization'])) {
            if (preg_match('/Bearer\s+(.*)$/i', $headers['Authorization'], $matches)) {
                return $matches[1];
            }
        }

        // Check X-API-Key header
        if (isset($headers['X-API-Key'])) {
            return $headers['X-API-Key'];
        }

        // Check query string
        if (isset($_GET['// SECURITY: Sensitive information removed'])) {
            return $_GET['// SECURITY: Sensitive information removed'];
        }

        return null;
    }

    /**
     * Send error response
     */
    private function sendError($message, $code) {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode([
            'error' => true,
            'message' => $message,
            'code' => $code
        ]);
        exit;
    }
}

// Create global API auth middleware instance
$apiAuthMiddleware = new ApiAuthMiddleware(
    $apiKeyManager ?? null,
    $securityLogger ?? null,
    $rateLimitMiddleware ?? null
);

