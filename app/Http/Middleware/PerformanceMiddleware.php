<?php
/**
 * APS Dream Home - Performance Middleware
 */

namespace App\Http\Middleware;

use App\Core\PerformanceMonitor;

class PerformanceMiddleware
{
    private $monitor;

    public function __construct()
    {
        $this->monitor = PerformanceMonitor::getInstance();
    }

    public function handle($request, $next)
    {
        // Start performance monitoring
        $this->monitor->startTimer('request');

        $response = $next($request);

        // End performance monitoring
        $this->monitor->endTimer('request');

        // Log performance metrics
        $this->monitor->logMetrics();

        // Add performance headers
        $metrics = $this->monitor->getMetrics();
        $response->header('X-Response-Time', $metrics['total_time'] . 'ms');
        $response->header('X-Memory-Usage', $metrics['current_memory']);

        return $response;
    }
}
