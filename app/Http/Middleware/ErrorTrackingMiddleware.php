<?php
/**
 * APS Dream Home - Error Tracking Middleware
 */

namespace App\Http\Middleware;

use App\Monitoring\ErrorTracker;

class ErrorTrackingMiddleware
{
    private $errorTracker;

    public function __construct()
    {
        $this->errorTracker = ErrorTracker::getInstance();
    }

    public function handle($request, $next)
    {
        try {
            // Process request
            $response = $next($request);
        } catch (Exception $e) {
            // Track error
            $this->errorTracker->trackError(
                $e->getMessage(),
                'exception',
                $this->determineSeverity($e),
                [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString()
                ]
            );
            
            // Re-throw exception
            throw $e;
        }

        return $response;
    }

    private function determineSeverity($exception)
    {
        $message = $exception->getMessage();
        
        // Critical errors
        if (strpos($message, 'database') !== false || 
            strpos($message, 'connection') !== false ||
            strpos($message, 'fatal') !== false) {
            return 'critical';
        }
        
        // High severity errors
        if (strpos($message, 'permission') !== false || 
            strpos($message, 'access denied') !== false ||
            strpos($message, 'security') !== false) {
            return 'high';
        }
        
        // Default to medium
        return 'medium';
    }
}
