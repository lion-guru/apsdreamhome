<?php

namespace App\Http\Controllers\Admin;

use App\Services\Cache\CacheService;
use App\Services\Messenger\DeadLetterMonitorService;

class MessengerDashboardController extends AdminController
{
    private $cacheService;
    private $monitor;

    public function __construct()
    {
        parent::__construct();
        $this->cacheService = new CacheService();
        $this->monitor = new DeadLetterMonitorService();
    }

    public function index()
    {
        $this->requireAdmin();

        // Cache overview for 5 min
        $cacheKey = 'messenger_dashboard';
        $cached = $this->cacheService->get($cacheKey);
        if ($cached !== false && $cached !== null) {
            $this->render('admin/messenger/dashboard', $cached);
            return;
        }

        $stats = $this->monitor->getStats();
        $byTransport = $this->monitor->getFailedByTransport();
        $queueDepths = $this->queueDepths();

        $data = [
            'page_title' => 'Messenger Routing Dashboard',
            'stats' => $stats,
            'byTransport' => $byTransport,
            'queueDepths' => $queueDepths,
            'threshold' => $this->monitor->getThresholdCount(),
        ];

        $this->cacheService->set($cacheKey, $data, 300);
        $this->render('admin/messenger/dashboard', $data);
    }

    private function queueDepths(): array
    {
        try {
            $rows = $this->db->fetchAll("
                SELECT queue_name,
                       COUNT(*) as depth,
                       MIN(available_at) as oldest_available,
                       MAX(created_at) as newest,
                       MIN(created_at) as oldest
                FROM messenger_messages
                GROUP BY queue_name
                ORDER BY depth DESC
            ") ?: [];
            return $rows;
        } catch (\Throwable $e) {
            error_log('MessengerDashboard queueDepths: ' . $e->getMessage());
            return [];
        }
    }
}
