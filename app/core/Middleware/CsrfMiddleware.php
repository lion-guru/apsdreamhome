<?php

namespace App\Core\Middleware;

use App\Core\Middleware;

/**
 * CSRF Protection Middleware
 * Validates CSRF tokens for state-changing requests
 */
class CsrfMiddleware extends Middleware
{
    private array $options;
    
    /**
     * CsrfMiddleware constructor
     * 
     * @param array $options Configuration options
     */
    public function __construct(array $options = [])
    {
        parent::__construct();
        $this->options = array_merge([
            'token_name' => 'csrf_token',
            'header_name' => 'X-CSRF-Token',
            'methods' => ['POST', 'PUT', 'DELETE', 'PATCH'],
            'skip_validation' => false,
            'error_message' => 'CSRF token validation failed',
        ], $options);
    }
    
    /**
     * Handle the request
     */
    public function handle(array $request, callable $next)
    {
        // Skip if validation is disabled
        if ($this->options['skip_validation']) {
            return $next($request);
        }
        
        $method = $request['method'] ?? 'GET';
        
        // Only validate for specified methods
        if (!in_array(strtoupper($method), $this->options['methods'])) {
            return $next($request);
        }
        
        // Generate CSRF token if not exists
        $this->generateToken();
        
        // Validate CSRF token
        if (!$this->validateToken($request)) {
            return $this->handleInvalidToken($request);
        }
        
        // Continue to next middleware
        return $next($request);
    }
    
    /**
     * Generate CSRF token
     */
    private function generateToken(): void
    {
        if (!isset($_SESSION['csrf_tokens'])) {
            $_SESSION['csrf_tokens'] = [];
        }
        
        // Generate new token if needed
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = $this->createToken();
        }
    }
    
    /**
     * Create a new CSRF token
     */
    private function createToken(): string
    {
        return bin2hex(random_bytes(32));
    }
    
    /**
     * Validate CSRF token
     */
    private function validateToken(array $request): bool
    {
        $submittedToken = $this->getSubmittedToken($request);
        $sessionToken = $_SESSION['csrf_token'] ?? null;
        
        if (empty($submittedToken) || empty($sessionToken)) {
            return false;
        }
        
        // Use timing-attack safe comparison
        return hash_equals($sessionToken, $submittedToken);
    }
    
    /**
     * Get submitted CSRF token
     */
    private function getSubmittedToken(array $request): ?string
    {
        // Check form data
        $token = $request['data'][$this->options['token_name']] ?? null;
        
        // Check header
        if (empty($token)) {
            $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        }
        
        // Check custom header name
        if (empty($token) && isset($_SERVER['HTTP_' . strtoupper(str_replace('-', '_', $this->options['header_name']))])) {
            $token = $_SERVER['HTTP_' . strtoupper(str_replace('-', '_', $this->options['header_name']))];
        }
        
        return $token;
    }
    
    /**
     * Handle invalid CSRF token
     */
    private function handleInvalidToken(array $request)
    {
        $this->logSecurityEvent('csrf_validation_failed', [
            'path' => $request['path'] ?? '',
            'method' => $request['method'] ?? 'GET',
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ]);
        
        // Regenerate token for next attempt
        $_SESSION['csrf_token'] = $this->createToken();
        
        // Check if AJAX request
        if ($this->isAjaxRequest()) {
            return $this->errorResponse($this->options['error_message'], 403);
        }
        
        // Store error message for next page load
        $_SESSION['csrf_error'] = $this->options['error_message'];
        
        // Redirect back
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        $this->redirect($referer);
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
     * Get current CSRF token
     */
    public function getToken(): string
    {
        $this->generateToken();
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Generate CSRF token HTML input field
     */
    public function getTokenField(): string
    {
        $token = $this->getToken();
        return sprintf(
            '<input type="hidden" name="%s" value="%s">',
            htmlspecialchars($this->options['token_name']),
            htmlspecialchars($token)
        );
    }
    
    /**
     * Generate CSRF token meta tag
     */
    public function getTokenMeta(): string
    {
        $token = $this->getToken();
        return sprintf(
            '<meta name="csrf-token" content="%s">',
            htmlspecialchars($token)
        );
    }
}