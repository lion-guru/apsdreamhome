<?php
/**
 * APS Dream Home - APM Middleware
 */

namespace App\Http\Middleware;

use App\Monitoring\APM;

class APMMiddleware
{
    private $apm;

    public function __construct()
    {
        $this->apm = APM::getInstance();
    }

    public function handle($request, $next)
    {
        // Start monitoring
        $requestId = $this->apm->startRequest();

        try {
            // Process request
            $response = $next($request);
        } catch (Exception $e) {
            // Record error
            $this->apm->recordError($e->getMessage(), 'exception');
            throw $e;
        }

        // End monitoring
        $this->apm->endRequest($requestId);

        // Add performance headers
        $metrics = $this->apm->getMetrics();
        $response->header('X-Request-ID', $requestId);
        $response->header('X-Response-Time', round($metrics['request']['avg_time'], 2) . 'ms');
        $response->header('X-Memory-Usage', $this->formatBytes($metrics['memory']['current_usage']));

        return $response;
    }

    private function formatBytes($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
