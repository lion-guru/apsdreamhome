<?php

namespace App\Core\Middleware;

use App\Core\Middleware;

/**
 * Role-based Authorization Middleware
 * Ensures user has required role(s) before accessing route
 */
class RoleMiddleware extends Middleware
{
    private array $requiredRoles;
    private array $options;
    
    /**
     * RoleMiddleware constructor
     * 
     * @param array $requiredRoles Required roles for access
     * @param array $options Configuration options
     */
    public function __construct(array $requiredRoles, array $options = [])
    {
        parent::__construct();
        $this->requiredRoles = $requiredRoles;
        $this->options = array_merge([
            'redirect' => '/unauthorized',
            'ajax_only' => false,
            'require_all' => false, // true = require all roles, false = require any role
            'super_admin_bypass' => true, // super admin can access everything
        ], $options);
    }
    
    /**
     * Handle the request
     */
    public function handle(array $request, callable $next)
    {
        // Check if user is authenticated
        if (!$this->isAuthenticated()) {
            return $this->handleUnauthenticated($request);
        }
        
        // Check if user has required roles
        if (!$this->hasRequiredRoles()) {
            return $this->handleUnauthorized($request);
        }
        
        // Check if AJAX only
        if ($this->options['ajax_only'] && !$this->isAjaxRequest()) {
            return $this->errorResponse('This endpoint requires AJAX requests', 400);
        }
        
        // Continue to next middleware
        return $next($request);
    }
    
    /**
     * Check if user has required roles
     */
    private function hasRequiredRoles(): bool
    {
        $userRole = $this->getUserRole();
        
        // Super admin bypass
        if ($this->options['super_admin_bypass'] && $userRole === 'super_admin') {
            return true;
        }
        
        // Check if user has required roles
        if ($this->options['require_all']) {
            // User must have ALL required roles
            return $this->hasAllRoles($this->requiredRoles);
        } else {
            // User must have ANY of the required roles
            return $this->hasAnyRole($this->requiredRoles);
        }
    }
    
    /**
     * Check if user has all required roles
     */
    private function hasAllRoles(array $roles): bool
    {
        $userRole = $this->getUserRole();
        
        if ($userRole === null) {
            return false;
        }
        
        foreach ($roles as $role) {
            if (!$this->hasRole($role)) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Handle unauthenticated request
     */
    private function handleUnauthenticated(array $request)
    {
        $this->logSecurityEvent('unauthenticated_access_attempt', [
            'path' => $request['path'] ?? '',
            'method' => $request['method'] ?? 'GET',
            'required_roles' => $this->requiredRoles,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ]);
        
        // Check if AJAX request
        if ($this->isAjaxRequest()) {
            return $this->errorResponse('Authentication required', 401);
        }
        
        // Store intended URL for redirect after login
        $_SESSION['intended_url'] = $request['path'] ?? $_SERVER['REQUEST_URI'] ?? '/';
        
        // Redirect to login
        $this->redirect('/login');
    }
    
    /**
     * Handle unauthorized request (user authenticated but lacks required roles)
     */
    private function handleUnauthorized(array $request)
    {
        $userRole = $this->getUserRole();
        
        $this->logSecurityEvent('unauthorized_access_attempt', [
            'path' => $request['path'] ?? '',
            'method' => $request['method'] ?? 'GET',
            'user_role' => $userRole,
            'required_roles' => $this->requiredRoles,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ]);
        
        // Check if AJAX request
        if ($this->isAjaxRequest()) {
            return $this->errorResponse('Insufficient permissions', 403);
        }
        
        // Redirect to unauthorized page
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
}