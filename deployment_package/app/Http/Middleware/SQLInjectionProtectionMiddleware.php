<?php
/**
 * APS Dream Home - SQL Injection Protection Middleware
 */

namespace App\Http\Middleware;

use App\Security\SQLInjectionDetector;

class SQLInjectionProtectionMiddleware
{
    private $detector;

    public function __construct()
    {
        $this->detector = SQLInjectionDetector::getInstance();
    }

    public function handle($request, $next)
    {
        // Get all input data
        $inputData = $request->getAllInput();

        // Check for SQL injection attempts
        if ($this->detector->detect($inputData)) {
            $threatLevel = $this->detector->getThreatLevel(json_encode($inputData));
            $this->detector->logAttempt(json_encode($inputData), $threatLevel);

            return response()->json([
                'success' => false,
                'message' => 'Security violation detected',
                'error' => 'SQL injection attempt blocked'
            ], 403);
        }

        return $next($request);
    }
}
