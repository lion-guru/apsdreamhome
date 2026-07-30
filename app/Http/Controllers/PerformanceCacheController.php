<?php

namespace App\Http\Controllers;

use App\Services\PerformanceCacheService;
use Exception;

class PerformanceCacheController extends BaseController
{
    private PerformanceCacheService $cacheService;

    public function __construct(PerformanceCacheService $cacheService = null)
    {
        parent::__construct();
        $this->cacheService = $cacheService ?? new PerformanceCacheService();
    }

    public function cacheSet()
    {
        try {
            $key = $_POST['key'] ?? ($_GET['key'] ?? '');
            $value = $_POST['value'] ?? ($_GET['value'] ?? null);
            $ttl = (int)($_POST['ttl'] ?? ($_GET['ttl'] ?? 3600));
            $result = $this->cacheService->set($key, json_decode($value, true) ?? $value, $ttl);
            return $this->jsonResponse(['success' => $result, 'message' => $result ? 'Cache set' : 'Failed']);
        } catch (Exception $e) {
            return $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function cacheGet()
    {
        try {
            $key = $_GET['key'] ?? '';
            $value = $this->cacheService->get($key);
            return $this->jsonResponse(['success' => true, 'data' => ['key' => $key, 'value' => $value, 'found' => $value !== null]]);
        } catch (Exception $e) {
            return $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function cacheRemember()
    {
        try {
            $key = $_POST['key'] ?? '';
            $ttl = (int)($_POST['ttl'] ?? 3600);
            $callback = function () use ($key) {
                return $_POST['default'] ?? 'remembered_value';
            };
            $value = $this->cacheService->remember($key, $callback, $ttl);
            return $this->jsonResponse(['success' => true, 'data' => ['key' => $key, 'value' => $value, 'ttl' => $ttl]]);
        } catch (Exception $e) {
            return $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function cacheForget()
    {
        try {
            $key = $_REQUEST['key'] ?? '';
            $result = $this->cacheService->forget($key);
            return $this->jsonResponse(['success' => $result, 'message' => $result ? 'Deleted' : 'Not found']);
        } catch (Exception $e) {
            return $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function cacheFlush()
    {
        try {
            $result = $this->cacheService->flush();
            return $this->jsonResponse(['success' => $result, 'message' => $result ? 'Cache cleared' : 'Failed']);
        } catch (Exception $e) {
            return $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function cacheStats()
    {
        try {
            return $this->jsonResponse(['success' => true, 'data' => $this->cacheService->getStats()]);
        } catch (Exception $e) {
            return $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function dashboardStats()
    {
        try {
            return $this->jsonResponse(['success' => true, 'data' => $this->cacheService->cacheDashboardStats(function() { return ['test' => 'data']; })]);
        } catch (Exception $e) {
            return $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function invalidateDashboard()
    {
        try {
            $this->cacheService->invalidateDashboard();
            return $this->jsonResponse(['success' => true, 'message' => 'Dashboard cache invalidated']);
        } catch (Exception $e) {
            return $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}