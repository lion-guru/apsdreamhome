<?php
/**
 * CORS Middleware
 */

namespace App\Http\Middleware;

class Cors
{
    /**
     * Handle an incoming request
     */
    public function handle($request, $next)
    {
        // Handle preflight requests
        if ($request->method === 'OPTIONS') {
            return $this->preflightResponse();
        }

        // Add CORS headers
        $this->addCorsHeaders();

        return $next($request);
    }

    /**
     * Add CORS headers
     */
    protected function addCorsHeaders()
    {
        $config = require APP_ROOT . '/app/config/security.php';
        $cors = $config['cors'];

        header('Access-Control-Allow-Origin: ' . implode(', ', $cors['allowed_origins']));
        header('Access-Control-Allow-Methods: ' . implode(', ', $cors['allowed_methods']));
        header('Access-Control-Allow-Headers: ' . implode(', ', $cors['allowed_headers']));
        header('Access-Control-Max-Age: ' . $cors['max_age']);
        header('Access-Control-Allow-Credentials: true');
    }

    /**
     * Handle preflight requests
     */
    protected function preflightResponse()
    {
        $this->addCorsHeaders();
        
        header('HTTP/1.1 200 OK');
        header('Content-Type: application/json');
        echo json_encode(['message' => 'CORS preflight successful']);
        exit;
    }
}
