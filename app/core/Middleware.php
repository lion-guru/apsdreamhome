<?php

namespace App\Core;

use App\Core\DatabaseManager;

/**
 * Middleware Base Class
 * Provides foundation for all middleware components
 */
abstract class Middleware
{
    protected ?DatabaseManager $db = null;
    
    /**
     * Middleware constructor
     */
    public function __construct()
    {
        $this->db = DatabaseManager::getInstance();
    }
    
    /**
     * Process the request
     * 
     * @param array $request Request data
     * @param callable $next Next middleware in chain
     * @return mixed Result of processing
     */
    abstract public function handle(array $request, callable $next);
    
    /**
     * Get current user session data
     */
    protected function getUserSession(): ?array
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        return $_SESSION['user'] ?? null;
    }
    
    /**
     * Check if user is authenticated
     */
    protected function isAuthenticated(): bool
    {
        return $this->getUserSession() !== null;
    }
    
    /**
     * Get current user ID
     */
    protected function getUserId(): ?int
    {
        $user = $this->getUserSession();
        if ($user && isset($user['id'])) {
            return $user['id'];
        }
        return $_SESSION['user_id'] ?? null;
    }
    
    /**
     * Get current user role
     */
    protected function getUserRole(): ?string
    {
        $user = $this->getUserSession();
        if ($user && isset($user['role'])) {
            return $user['role'];
        }
        return $_SESSION['user_role'] ?? null;
    }
    
    /**
     * Check if user has specific role
     */
    protected function hasRole(string $role): bool
    {
        return $this->getUserRole() === $role;
    }
    
    /**
     * Check if user has any of the specified roles
     */
    protected function hasAnyRole(array $roles): bool
    {
        $userRole = $this->getUserRole();
        return $userRole !== null && in_array($userRole, $roles);
    }
    
    /**
     * Redirect to specific URL
     */
    protected function redirect(string $url): void
    {
        header("Location: {$url}");
        exit;
    }
    
    /**
     * Return JSON response
     */
    protected function jsonResponse(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    
    /**
     * Return error response
     */
    protected function errorResponse(string $message, int $statusCode = 400): void
    {
        $this->jsonResponse(['error' => $message], $statusCode);
    }
    
    /**
     * Log security event
     */
    protected function logSecurityEvent(string $event, array $data = []): void
    {
        $logData = [
            'event' => $event,
            'timestamp' => date('Y-m-d H:i:s'),
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'user_id' => $this->getUserId(),
            'data' => $data
        ];
        
        // Log to file
        $logFile = __DIR__ . '/../../logs/security.log';
        if (!is_dir(dirname($logFile))) {
            mkdir(dirname($logFile), 0755, true);
        }
        
        error_log(json_encode($logData) . "\n", 3, $logFile);
    }
}