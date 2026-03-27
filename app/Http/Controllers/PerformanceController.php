<?php

namespace App\Http\Controllers;

use App\Services\Performance\PerformanceService;

/**
 * Performance Controller
 * Handles performance and caching operations
 */
class PerformanceController extends BaseController
{
    private ?PerformanceService $performanceService = null;

    public function __construct()
    {
        parent::__construct();
        try {
            $this->performanceService = new PerformanceService();
        } catch (\Throwable $e) {
            error_log('PerformanceController: service init failed - ' . $e->getMessage());
        }
    }

    /**
     * Display performance dashboard
     */
    public function dashboard()
    {
        try {
            $stats = $this->performanceService->getStats();
            
            return $this->render('performance/dashboard', compact('stats'));
        } catch (\Exception $e) {
            return $this->render('performance/dashboard', compact('stats'));
        }
    }

    /**
     * Get performance metrics
     */
    public function getMetrics()
    {
        try {
            $metrics = $this->performanceService->getMetrics();
            
            return $this->jsonResponse([
                'success' => true,
                'data' => $metrics
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to get performance metrics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get system performance
     */
    public function getSystemPerformance()
    {
        try {
            $performance = $this->performanceService->getSystemPerformance();
            
            return $this->jsonResponse([
                'success' => true,
                'data' => $performance
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to get system performance',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get database performance
     */
    public function getDatabasePerformance()
    {
        try {
            $dbPerformance = $this->performanceService->getDatabasePerformance();
            
            return $this->jsonResponse([
                'success' => true,
                'data' => $dbPerformance
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to get database performance',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get cache performance
     */
    public function getCachePerformance()
    {
        try {
            $cachePerformance = $this->performanceService->getCachePerformance();
            
            return $this->jsonResponse([
                'success' => true,
                'data' => $cachePerformance
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to get cache performance',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Optimize performance
     */
    public function optimize()
    {
        try {
            $result = $this->performanceService->optimizePerformance();
            
            return $this->jsonResponse([
                'success' => true,
                'message' => 'Performance optimized successfully',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to optimize performance',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clear performance cache
     */
    public function clearCache()
    {
        try {
            $result = $this->performanceService->clearPerformanceCache();
            
            return $this->jsonResponse([
                'success' => true,
                'message' => 'Performance cache cleared successfully',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to clear performance cache',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate performance report
     */
    public function generateReport()
    {
        try {
            $request = $_REQUEST;
            $type = $request['type'] ?? 'summary';
            $startDate = $request['start_date'] ?? null;
            $endDate = $request['end_date'] ?? null;
            
            $report = $this->performanceService->generateReport($type, $startDate, $endDate);
            
            return $this->jsonResponse([
                'success' => true,
                'message' => 'Performance report generated successfully',
                'data' => $report
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to generate performance report',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get performance alerts
     */
    public function getAlerts()
    {
        try {
            $alerts = $this->performanceService->getPerformanceAlerts();
            
            return $this->jsonResponse([
                'success' => true,
                'data' => $alerts
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to get performance alerts',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Monitor performance
     */
    public function monitor()
    {
        try {
            $request = $_REQUEST;
            $metrics = $request['metrics'] ?? ['cpu', 'memory', 'disk', 'network'];
            $interval = (int)($request['interval'] ?? 60);
            
            $data = $this->performanceService->monitorPerformance($metrics, $interval);
            
            return $this->jsonResponse([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to monitor performance',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get performance trends
     */
    public function getTrends()
    {
        try {
            $request = $_REQUEST;
            $period = $request['period'] ?? '24h';
            $metric = $request['metric'] ?? 'cpu';
            
            $trends = $this->performanceService->getPerformanceTrends($period, $metric);
            
            return $this->jsonResponse([
                'success' => true,
                'data' => $trends
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to get performance trends',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Set performance threshold
     */
    public function setThreshold()
    {
        try {
            $request = $_REQUEST;
            $metric = $request['metric'] ?? '';
            $threshold = $request['threshold'] ?? null;
            $operator = $request['operator'] ?? '>';
            $action = $request['action'] ?? 'alert';
            
            $result = $this->performanceService->setPerformanceThreshold($metric, $threshold, $operator, $action);
            
            return $this->jsonResponse([
                'success' => true,
                'message' => 'Performance threshold set successfully',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to set performance threshold',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get performance settings
     */
    public function getSettings()
    {
        try {
            $settings = $this->performanceService->getPerformanceSettings();
            
            return $this->jsonResponse([
                'success' => true,
                'data' => $settings
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to get performance settings',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update performance settings
     */
    public function updateSettings()
    {
        try {
            $request = $_REQUEST;
            $settings = $request['settings'] ?? [];
            
            $result = $this->performanceService->updatePerformanceSettings($settings);
            
            return $this->jsonResponse([
                'success' => true,
                'message' => 'Performance settings updated successfully',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to update performance settings',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}