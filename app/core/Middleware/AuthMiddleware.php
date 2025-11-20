<?php

namespace App\Core\Middleware;

use App\Core\Middleware;

/**
 * Authentication Middleware
 * Ensures user is authenticated before accessing route
 */
class AuthMiddleware extends Middleware
{
    private array $options;
    
    /**
     * AuthMiddleware constructor
     * 
     * @param array $options Configuration options
     */
    public function __construct(array $options = [])
    {
        parent::__construct();
        $this->options = array_merge([
            'redirect' => '/login',
            'except' => [],
            'only' => [],
            'ajax_only' => false,
            'remember_me' => true,
        ], $options);
    }
    
    /**
     * Handle the request
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
        
        // Check if AJAX only
        if ($this->options['ajax_only'] && !$this->isAjaxRequest()) {
            return $this->errorResponse('This endpoint requires AJAX requests', 400);
        }
        
        // Update session activity
        $this->updateSessionActivity();
        
        // Continue to next middleware
        return $next($request);
    }
    
    /**
     * Check if request should bypass authentication
     */
    private function shouldBypass(array $request): bool
    {
        $path = $request['path'] ?? '';
        $method = $request['method'] ?? 'GET';
        
        // Check except routes
        foreach ($this->options['except'] as $pattern) {
            if ($this->matchesPattern($path, $pattern)) {
                return true;
            }
        }
        
        // Check if only specific routes require auth
        if (!empty($this->options['only'])) {
            foreach ($this->options['only'] as $pattern) {
                if ($this->matchesPattern($path, $pattern)) {
                    return false; // Don't bypass, require auth
                }
            }
            return true; // Bypass if not in 'only' list
        }
        
        return false;
    }
    
    /**
     * Handle unauthenticated request
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
        
        // Store intended URL for redirect after login
        if ($this->options['remember_me']) {
            $_SESSION['intended_url'] = $request['path'] ?? $_SERVER['REQUEST_URI'] ?? '/';
        }
        
        // Redirect to login
        $this->redirect($this->options['redirect']);
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
     * Update session activity
     */
    private function updateSessionActivity(): void
    {
        $_SESSION['last_activity'] = time();
        
        // Regenerate session ID periodically for security
        if (!isset($_SESSION['last_regeneration']) || 
            $_SESSION['last_regeneration'] < time() - 300) { // 5 minutes
            session_regenerate_id(true);
            $_SESSION['last_regeneration'] = time();
        }
    }
    
    /**
     * Check if path matches pattern
     */
    private function matchesPattern(string $path, string $pattern): bool
    {
        // Convert pattern to regex
        $pattern = str_replace('*', '.*', $pattern);
        $pattern = '#^' . $pattern . '$#';
        
        return (bool) preg_match($pattern, $path);
    }
}