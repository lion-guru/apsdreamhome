<?php

namespace App\Services;

use App\Core\Database;
use App\Core\Config;
use Exception;

/**
 * Authentication Middleware Service - APS Dream Home
 * Handles authentication and authorization for the application
 */
class AuthMiddleware
{
    private $options = [];
    
    public function __construct(array $options = [])
    {
        $this->options = array_merge([
            'redirect' => '/login',
            'except' => [],
            'only' => [],
            'ajax_only' => false,
            'remember_me' => true,
        ], $options);
    }
    
    /**
     * Handle authentication middleware
     */
    public function handle(array $request, callable $next)
    {
        // Check if this request should bypass authentication
        if ($this->shouldBypass($request)) {
            return $next($request);
        }
        
        // Check if user is authenticated
        if (!$this->isAuthenticated()) {
            return $this->handleUnauthenticated($request);
        }
        
        return $next($request);
    }
    
    /**
     * Check if request should bypass authentication
     */
    private function shouldBypass(array $request): bool
    {
        $path = $request['path'] ?? '';
        
        // Check except paths
        foreach ($this->options['except'] as $except) {
            if (str_starts_with($path, $except)) {
                return true;
            }
        }
        
        // Check only paths
        if (!empty($this->options['only'])) {
            foreach ($this->options['only'] as $only) {
                if (str_starts_with($path, $only)) {
                    return false;
                }
            }
            return true;
        }
        
        return false;
    }
    
    /**
     * Check if user is authenticated
     */
    private function isAuthenticated(): bool
    {
        return isset($_SESSION['user_id']) || 
               isset($_SESSION['admin_logged_in']) || 
               isset($_SESSION['employee_id']) || 
               isset($_SESSION['customer_id']) || 
               isset($_SESSION['associate_id']);
    }
    
    /**
     * Handle unauthenticated requests
     */
    private function handleUnauthenticated(array $request)
    {
        $this->logSecurityEvent('unauthenticated_access_attempt', [
            'path' => $request['path'] ?? '',
            'method' => $request['method'] ?? 'GET',
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ]);
        
        // Check if AJAX request
        if ($this->isAjaxRequest()) {
            return $this->errorResponse('Authentication required', 401);
        }
        
        // Redirect to login
        header('Location: ' . $this->options['redirect']);
        exit;
    }
    
    /**
     * Check if request is AJAX
     */
    private function isAjaxRequest(): bool
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
    
    /**
     * Send error response
     */
    private function errorResponse(string $message, int $code): array
    {
        http_response_code($code);
        return ['error' => $message];
    }
    
    /**
     * Log security event
     */
    private function logSecurityEvent(string $event, array $data): void
    {
        // Log security events for monitoring
        error_log("Security Event: $event - " . json_encode($data));
    }
    
    /**
     * Require admin authentication
     */
    public static function requireAdminAuth(): void
    {
        if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                http_response_code(401);
                echo json_encode(['error' => 'Admin authentication required']);
                exit;
            }
            header('Location: /admin/login.php');
            exit();
        }
    }
    
    /**
     * Require employee authentication
     */
    public static function requireEmployeeAuth(): void
    {
        if (!isset($_SESSION['employee_id'])) {
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                http_response_code(401);
                echo json_encode(['error' => 'Employee authentication required']);
                exit;
            }
            header('Location: /login');
            exit;
        }
    }
    
    /**
     * Require customer authentication
     */
    public static function requireCustomerAuth(): void
    {
        if (!isset($_SESSION['customer_id'])) {
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                http_response_code(401);
                echo json_encode(['error' => 'Customer authentication required']);
                exit;
            }
            header('Location: /login');
            exit;
        }
    }
    
    /**
     * Require associate authentication
     */
    public static function requireAssociateAuth(): void
    {
        if (!isset($_SESSION['associate_id'])) {
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                http_response_code(401);
                echo json_encode(['error' => 'Associate authentication required']);
                exit;
            }
            header('Location: /login');
            exit;
        }
    }
    
    /**
     * General authentication check
     */
    public static function requireAuth(): void
    {
        if (!isset($_SESSION['user_id']) && !isset($_SESSION['admin_logged_in']) &&
            !isset($_SESSION['employee_id']) && !isset($_SESSION['customer_id']) &&
            !isset($_SESSION['associate_id'])) {
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                http_response_code(401);
                echo json_encode(['error' => 'Authentication required']);
                exit;
            }
            header('Location: /login');
            exit;
        }
    }
}

// Helper functions for backward compatibility
function admin_auth_required() {
    AuthMiddleware::requireAdminAuth();
}

function employee_auth_required() {
    AuthMiddleware::requireEmployeeAuth();
}

function customer_auth_required() {
    AuthMiddleware::requireCustomerAuth();
}

function associate_auth_required() {
    AuthMiddleware::requireAssociateAuth();
}

function auth_required() {
    AuthMiddleware::requireAuth();
}
