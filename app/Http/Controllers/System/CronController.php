<?php
namespace App\Http\Controllers\System;

use App\Http\Controllers\BaseController;
use App\Services\AI\AIManager;
use App\Services\AgentOrchestrator;
use App\Services\PropertyMarketplaceService;
use App\Services\NotificationService;

class CronController extends BaseController
{
    public function __construct() { parent::__construct(); }

    private function ai(): AIManager { return new AIManager($this->db); }
    private function agent(): AgentOrchestrator { return new AgentOrchestrator($this->db); }
    private function mkt(): PropertyMarketplaceService { return new PropertyMarketplaceService($this->db); }
    private function notif(): NotificationService { return new NotificationService($this->db); }

    public function daily()
    {
        $key = $_GET['key'] ?? '';
        $secret = getenv('CRON_SECRET') ?: 'DreamHomeSecureCron!';
        if ($key !== $secret) {
            return $this->jsonError('Unauthorized access', 403);
        }

        $startTime = microtime(true);
        $results = [];

        // 1. Process pending agent tasks
        try {
            $taskResult = $this->agent()->processPendingTasks(100);
            $results['agent_tasks'] = ['processed' => $taskResult['processed'] ?? 0, 'ok' => true];
        } catch (\Throwable $e) {
            $results['agent_tasks'] = ['ok' => false, 'error' => $e->getMessage()];
        }

        // 2. Retrain AI models
        try {
            $aiResult = $this->ai()->retrain();
            $results['ai_retrain'] = ['ok' => true, 'result' => $aiResult];
        } catch (\Throwable $e) {
            $results['ai_retrain'] = ['ok' => false, 'error' => $e->getMessage()];
        }

        // 3. Clear expired agent state
        try {
            $cleared = $this->agent()->clearExpiredState();
            $results['agent_state_cleanup'] = ['cleared' => $cleared];
        } catch (\Throwable $e) {
            $results['agent_state_cleanup'] = ['ok' => false, 'error' => $e->getMessage()];
        }

        // 4. Send scheduled notifications
        try {
            $this->triggerScheduledNotifications();
            $results['notifications'] = ['ok' => true];
        } catch (\Throwable $e) {
            $results['notifications'] = ['ok' => false, 'error' => $e->getMessage()];
        }

        // 5. Process due maintenance
        try {
            $this->processDueMaintenance();
            $results['maintenance'] = ['ok' => true];
        } catch (\Throwable $e) {
            $results['maintenance'] = ['ok' => false, 'error' => $e->getMessage()];
        }

        $duration = round((microtime(true) - $startTime) * 1000);

        return $this->jsonResponse([
            'success' => true,
            'message' => 'Daily automation completed',
            'duration_ms' => $duration,
            'results' => $results,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }

    public function hourly()
    {
        $key = $_GET['key'] ?? '';
        $secret = getenv('CRON_SECRET') ?: 'DreamHomeSecureCron!';
        if ($key !== $secret) {
            return $this->jsonError('Unauthorized access', 403);
        }

        $startTime = microtime(true);
        $results = [];

        try {
            $taskResult = $this->agent()->processPendingTasks(20);
            $results['agent_tasks'] = $taskResult['processed'] ?? 0;
        } catch (\Throwable $e) { $results['agent_tasks_error'] = $e->getMessage(); }

        return $this->jsonResponse([
            'success' => true,
            'duration_ms' => round((microtime(true) - $startTime) * 1000),
            'results' => $results
        ]);
    }

    public function weekly()
    {
        $key = $_GET['key'] ?? '';
        $secret = getenv('CRON_SECRET') ?: 'DreamHomeSecureCron!';
        if ($key !== $secret) {
            return $this->jsonError('Unauthorized access', 403);
        }

        $startTime = microtime(true);
        $results = [];

        try {
            $this->ai()->retrain();
            $results['ai_retrain'] = 'complete';
        } catch (\Throwable $e) { $results['ai_retrain_error'] = $e->getMessage(); }

        try {
            $this->db->query("UPDATE agent_state SET state_value = '{}' WHERE updated_at < DATE_SUB(NOW(), INTERVAL 7 DAY)");
            $results['state_cleanup'] = 'done';
        } catch (\Throwable $e) { error_log("CronController::" . __FUNCTION__ . " query failed: " . $e->getMessage()); }

        return $this->jsonResponse([
            'success' => true,
            'duration_ms' => round((microtime(true) - $startTime) * 1000),
            'results' => $results
        ]);
    }

    private function triggerScheduledNotifications(): void
    {
        $this->agent()->triggerWorkflow('user.birthday', []);
        $this->agent()->triggerWorkflow('emi.due_soon', []);
    }

    private function processDueMaintenance(): void
    {
        $st = $this->db->query("SELECT id FROM property_maintenance WHERE status = 'open' AND created_at <= DATE_SUB(NOW(), INTERVAL 30 DAY)");
        $rows = $st->fetchAll(\PDO::FETCH_COLUMN);
        foreach ($rows as $id) {
            try { $this->mkt()->completeMaintenance((int)$id, 0, 'Auto-completed by cron'); } catch (\Throwable $e) { error_log("CronController::" . __FUNCTION__ . " query failed: " . $e->getMessage()); }
        }
    }
}
