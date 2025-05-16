<?php
// Authentication and Authorization Middleware

class AuthMiddleware {
    private $auth_manager;
    private $logger;

    public function __construct($auth_manager, $logger) {
        $this->auth_manager = $auth_manager;
        $this->logger = $logger;
    }

    /**
     * Require authentication for a route
     * @param int $minimum_role Minimum role required
     * @return bool
     */
    public function requireAuth($minimum_role = AuthManager::ROLE_CUSTOMER) {
        // Check if user is logged in
        if (!$this->auth_manager->isLoggedIn()) {
            $this->handleUnauthorized('Not logged in');
            return false;
        }

        // Check user role/authorization
        if (!$this->auth_manager->checkAuthorization($minimum_role)) {
            $this->handleForbidden('Insufficient permissions');
            return false;
        }

        return true;
    }

    /**
     * Check if user has specific permission
     * @param string $permission Permission to check
     * @return bool
     */
    public function checkPermission($permission) {
        try {
            // Get current user role
            $role = $this->auth_manager->getCurrentUserRole();

            // Check permission in database
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as has_permission 
                FROM user_permissions 
                WHERE role = ? AND permission = ?
            ");
            $stmt->bind_param('ss', $role, $permission);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();

            return $row['has_permission'] > 0;
        } catch (Exception $e) {
            $this->logger->log(
                "Permission check error: {$permission}", 
                'error', 
                'security'
            );
            return false;
        }
    }

    /**
     * Handle unauthorized access
     * @param string $reason Reason for unauthorized access
     */
    private function handleUnauthorized($reason = 'Unauthorized') {
        // Log unauthorized access attempt
        $this->logger->log(
            "Unauthorized access attempt: {$reason}", 
            'warning', 
            'security'
        );

        // Redirect to login page or return JSON error
        if (php_sapi_name() !== 'cli') {
            header('HTTP/1.1 401 Unauthorized');
            header('Content-Type: application/json');
            echo json_encode([
                'error' => 'Unauthorized',
                'message' => 'Please log in to access this resource'
            ]);
            exit;
        }
    }

    /**
     * Handle forbidden access
     * @param string $reason Reason for forbidden access
     */
    private function handleForbidden($reason = 'Forbidden') {
        // Log forbidden access attempt
        $this->logger->log(
            "Forbidden access attempt: {$reason}", 
            'warning', 
            'security'
        );

        // Redirect to error page or return JSON error
        if (php_sapi_name() !== 'cli') {
            header('HTTP/1.1 403 Forbidden');
            header('Content-Type: application/json');
            echo json_encode([
                'error' => 'Forbidden',
                'message' => 'You do not have permission to access this resource'
            ]);
            exit;
        }
    }

    /**
     * CSRF Protection Middleware
     * @param string $token CSRF token to validate
     * @return bool
     */
    public function csrfProtect($token = null) {
        // Generate CSRF token if not provided
        if ($token === null) {
            return $this->generateCsrfToken();
        }

        // Validate CSRF token
        $session_token = $_SESSION['csrf_token'] ?? null;
        
        if (!hash_equals($session_token, $token)) {
            $this->logger->log(
                "CSRF token validation failed", 
                'warning', 
                'security'
            );
            
            header('HTTP/1.1 403 Forbidden');
            echo "CSRF token validation failed";
            exit;
        }

        return true;
    }

    /**
     * Generate CSRF token
     * @return string CSRF token
     */
    private function generateCsrfToken() {
        // Generate a secure random token
        $token = bin2hex(random_bytes(32));
        
        // Store in session
        $_SESSION['csrf_token'] = $token;
        
        return $token;
    }
}

// Helper function for dependency injection
function getAuthMiddleware() {
    $container = container(); // Assuming dependency container is loaded
    
    // Lazy load dependencies
    $auth_manager = $container->resolve('auth_manager');
    $logger = $container->resolve('logger');
    
    return new AuthMiddleware($auth_manager, $logger);
}

return getAuthMiddleware();
