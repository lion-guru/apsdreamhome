<?php

namespace App\Http\Controllers;

use App\Services\Performance\PerformanceService;
use App\Http\Controllers\Admin\AdminController;

/**
 * Performance Controller
 * Handles performance and caching operations
 */
class PerformanceController extends AdminController
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
            $stats = $this->performanceService ? $this->performanceService->getStats() : [];
        } catch (\Throwable $e) {
            $stats = [];
        }
        return $this->render('performance/dashboard', ['stats' => $stats]);
    }

    public function getMetrics()
    {
        try {
            $metrics = $this->performanceService ? $this->performanceService->getMetrics() : [];
            return $this->jsonResponse(['success' => true, 'data' => $metrics]);
        } catch (\Throwable $e) {
            return $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getSystemPerformance()
    {
        try {
            $performance = $this->performanceService ? $this->performanceService->getSystemPerformance() : [];
            return $this->jsonResponse(['success' => true, 'data' => $performance]);
        } catch (\Throwable $e) {
            return $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getDatabasePerformance()
    {
        try {
            $dbPerformance = $this->performanceService ? $this->performanceService->getDatabasePerformance() : [];
            return $this->jsonResponse(['success' => true, 'data' => $dbPerformance]);
        } catch (\Throwable $e) {
            return $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getCachePerformance()
    {
        try {
            $cachePerformance = $this->performanceService ? $this->performanceService->getCachePerformance() : [];
            return $this->jsonResponse(['success' => true, 'data' => $cachePerformance]);
        } catch (\Throwable $e) {
            return $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function optimize()
    {
        try {
            $result = $this->performanceService ? $this->performanceService->optimizePerformance() : null;
            return $this->jsonResponse(['success' => true, 'message' => 'Performance optimized', 'data' => $result]);
        } catch (\Throwable $e) {
            return $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function clearCache()
    {
        try {
            $result = $this->performanceService ? $this->performanceService->clearPerformanceCache() : null;
            return $this->jsonResponse(['success' => true, 'message' => 'Cache cleared', 'data' => $result]);
        } catch (\Throwable $e) {
            return $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function generateReport()
    {
        try {
            $request = $_REQUEST;
            $type = $request['type'] ?? 'summary';
            $startDate = $request['start_date'] ?? null;
            $endDate = $request['end_date'] ?? null;
            $report = $this->performanceService ? $this->performanceService->generateReport($type, $startDate, $endDate) : [];
            return $this->jsonResponse(['success' => true, 'data' => $report]);
        } catch (\Throwable $e) {
            return $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getAlerts()
    {
        try {
            $alerts = $this->performanceService ? $this->performanceService->getPerformanceAlerts() : [];
            return $this->jsonResponse(['success' => true, 'data' => $alerts]);
        } catch (\Throwable $e) {
            return $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function monitor()
    {
        try {
            $request = $_REQUEST;
            $metrics = $request['metrics'] ?? ['cpu', 'memory', 'disk', 'network'];
            $interval = (int)($request['interval'] ?? 60);
            $data = $this->performanceService ? $this->performanceService->monitorPerformance($metrics, $interval) : [];
            return $this->jsonResponse(['success' => true, 'data' => $data]);
        } catch (\Throwable $e) {
            return $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getTrends()
    {
        try {
            $request = $_REQUEST;
            $period = $request['period'] ?? '24h';
            $metric = $request['metric'] ?? 'cpu';
            $trends = $this->performanceService ? $this->performanceService->getPerformanceTrends($period, $metric) : [];
            return $this->jsonResponse(['success' => true, 'data' => $trends]);
        } catch (\Throwable $e) {
            return $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function setThreshold()
    {
        try {
            $request = $_REQUEST;
            $result = $this->performanceService ? $this->performanceService->setPerformanceThreshold(
                $request['metric'] ?? '', $request['threshold'] ?? null, $request['operator'] ?? '>', $request['action'] ?? 'alert'
            ) : null;
            return $this->jsonResponse(['success' => true, 'data' => $result]);
        } catch (\Throwable $e) {
            return $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getSettings()
    {
        try {
            $settings = $this->performanceService ? $this->performanceService->getPerformanceSettings() : [];
            return $this->jsonResponse(['success' => true, 'data' => $settings]);
        } catch (\Throwable $e) {
            return $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function updateSettings()
    {
        try {
            $request = $_REQUEST;
            $settings = $request['settings'] ?? [];
            $result = $this->performanceService ? $this->performanceService->updatePerformanceSettings($settings) : null;
            return $this->jsonResponse(['success' => true, 'data' => $result]);
        } catch (\Throwable $e) {
            return $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}