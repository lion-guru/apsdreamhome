<?php
/**
 * Authentication Middleware
 */

namespace App\Http\Middleware;

class Auth
{
    /**
     * Handle an incoming request
     */
    public function handle($request, $next)
    {
        // Check if user is authenticated
        if (!$this->isAuthenticated($request)) {
            return $this->unauthorizedResponse();
        }

        return $next($request);
    }

    /**
     * Check if user is authenticated
     */
    protected function isAuthenticated($request)
    {
        // Check session or token
        if (isset($_SESSION['user_id']) || $this->validateToken($request)) {
            return true;
        }

        return false;
    }

    /**
     * Validate JWT token
     */
    protected function validateToken($request)
    {
        $headers = getallheaders();
        $token = $headers['Authorization'] ?? '';

        if (empty($token)) {
            return false;
        }

        // Remove "Bearer " prefix
        $token = str_replace('Bearer ', '', $token);

        try {
            // Validate JWT token
            $payload = $this->decodeJWT($token);
            return $payload !== false;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Decode JWT token
     */
    protected function decodeJWT($token)
    {
        // JWT decoding logic
        // This is a simplified version - use a proper JWT library in production
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return false;
        }

        $payload = json_decode(base64_decode($parts[1]), true);
        return $payload;
    }

    /**
     * Unauthorized response
     */
    protected function unauthorizedResponse()
    {
        header('HTTP/1.1 401 Unauthorized');
        header('Content-Type: application/json');
        echo json_encode([
            'error' => 'Unauthorized',
            'message' => 'Authentication required'
        ]);
        exit;
    }
}
