<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Services\EngagementService;
use Exception;
use Throwable;
use InvalidArgumentException;

/**
 * EngagementController
 * Admin UI endpoints for MLM engagement metrics and goals.
 */
class EngagementController extends AdminController
{
    private $engagementService;

    public function __construct()
    {
        parent::__construct();

        $this->engagementService = new EngagementService();
    }

    public function index(): void
    {
        $this->render('admin/mlm_engagement');
    }

    public function metrics(): void
    {
        try {
            $limit = max(1, min(200, (int) ($this->request->get('limit', 50))));
            $offset = max(0, (int) ($this->request->get('offset', 0)));
            $filters = [
                'user_id' => $this->request->get('user_id'),
                'from' => $this->request->get('from'),
                'to' => $this->request->get('to'),
                'rank_label' => $this->request->get('rank_label'),
            ];

            $this->json([
                'success' => true,
                'records' => $this->engagementService->getAssociateMetrics($filters, $limit, $offset),
            ])->send();
        } catch (Throwable $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 500)->send();
        }
    }

    public function leaderboard(): void
    {
        $metricType = $this->request->get('metric_type', '');
        $snapshotDate = $this->request->get('snapshot_date');
        $limit = max(1, min(200, (int) ($this->request->get('limit', 20))));

        try {
            $result = $this->engagementService->getLeaderboardSnapshot($metricType, $snapshotDate, $limit);
            $this->json(['success' => true, 'data' => $result])->send();
        } catch (InvalidArgumentException $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 400)->send();
        } catch (Throwable $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 500)->send();
        }
    }

    public function goals(): void
    {
        try {
            $limit = max(1, min(200, (int) ($this->request->get('limit', 50))));
            $offset = max(0, (int) ($this->request->get('offset', 0)));
            $filters = [
                'status' => $this->request->get('status'),
                'scope' => $this->request->get('scope'),
                'user_id' => $this->request->get('user_id'),
                'goal_type' => $this->request->get('goal_type'),
                'active_on' => $this->request->get('active_on'),
            ];

            $this->json([
                'success' => true,
                'records' => $this->engagementService->getGoals($filters, $limit, $offset),
            ])->send();
        } catch (Throwable $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 500)->send();
        }
    }

    public function goalDetails(): void
    {
        $goalId = (int) ($this->request->get('goal_id', 0));
        if ($goalId <= 0) {
            $this->json(['success' => false, 'message' => 'goal_id is required'], 400)->send();
            return;
        }

        try {
            $progress = $this->engagementService->getGoalProgress($goalId);
            $events = $this->engagementService->getGoalEvents($goalId);
            $this->json([
                'success' => true,
                'data' => [
                    'progress' => $progress,
                    'events' => $events,
                ],
            ])->send();
        } catch (Throwable $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 500)->send();
        }
    }

    public function notificationFeed(): void
    {
        try {
            $userId = (int) ($this->request->get('user_id', 0));
            $limit = max(1, min(100, (int) ($this->request->get('limit', 20))));
            $offset = max(0, (int) ($this->request->get('offset', 0)));
            $unreadOnly = ($this->request->get('unread_only', '0')) === '1';

            $notifications = $this->engagementService->getNotificationFeed($userId, $limit, $offset, null, $unreadOnly);
            $this->json(['success' => true, 'records' => $notifications])->send();
        } catch (Throwable $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 500)->send();
        }
    }

    public function notificationPreferences(): void
    {
        $userId = (int) ($this->request->get('user_id', 0));
        if ($userId <= 0) {
            $this->json(['success' => false, 'message' => 'user_id required'], 400)->send();
            return;
        }

        try {
            $prefs = $this->engagementService->getNotificationPreferences($userId);
            $this->json(['success' => true, 'data' => $prefs])->send();
        } catch (Throwable $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 500)->send();
        }
    }

    public function createGoal(): void
    {
        if ($this->request->method() !== 'POST') {
            $this->json(['success' => false, 'message' => 'Invalid request method'], 405)->send();
            return;
        }

        if (!$this->validateCsrfToken()) {
            $this->json(['success' => false, 'message' => 'Security validation failed'], 403)->send();
            return;
        }

        try {
            $result = $this->engagementService->createGoal($this->request->post());
            $this->json($result)->send();
        } catch (Throwable $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 500)->send();
        }
    }

    public function updateGoal(): void
    {
        if ($this->request->method() !== 'POST') {
            $this->json(['success' => false, 'message' => 'Invalid request method'], 405)->send();
            return;
        }

        if (!$this->validateCsrfToken()) {
            $this->json(['success' => false, 'message' => 'Security validation failed'], 403)->send();
            return;
        }

        try {
            $data = $this->request->post();
            $goalId = (int) ($data['goal_id'] ?? 0);
            $result = $this->engagementService->updateGoal($goalId, $data);
            $this->json($result)->send();
        } catch (Throwable $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 500)->send();
        }
    }

    public function recordGoalProgress(): void
    {
        if ($this->request->method() !== 'POST') {
            $this->json(['success' => false, 'message' => 'Invalid request method'], 405)->send();
            return;
        }

        if (!$this->validateCsrfToken()) {
            $this->json(['success' => false, 'message' => 'Security validation failed'], 403)->send();
            return;
        }

        try {
            $goalId = (int) ($this->request->post('goal_id', 0));
            $value = (float) ($this->request->post('value', 0));
            $checkpointDate = $this->request->post('checkpoint_date') ?: date('Y-m-d');
            $result = $this->engagementService->recordGoalProgress($goalId, $checkpointDate, $value);
            $this->json($result)->send();
        } catch (Throwable $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 500)->send();
        }
    }

    public function updateGoalStatus(): void
    {
        if ($this->request->method() !== 'POST') {
            $this->json(['success' => false, 'message' => 'Invalid request method'], 405)->send();
            return;
        }

        if (!$this->validateCsrfToken()) {
            $this->json(['success' => false, 'message' => 'Security validation failed'], 403)->send();
            return;
        }

        try {
            $goalId = (int) ($this->request->post('goal_id', 0));
            $status = $this->request->post('status', '');
            $result = $this->engagementService->updateGoalStatus($goalId, $status);
            $this->json($result)->send();
        } catch (Throwable $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 500)->send();
        }
    }

    public function markNotificationRead(): void
    {
        if ($this->request->method() !== 'POST') {
            $this->json(['success' => false, 'message' => 'Invalid request method'], 405)->send();
            return;
        }

        if (!$this->validateCsrfToken()) {
            $this->json(['success' => false, 'message' => 'Security validation failed'], 403)->send();
            return;
        }

        try {
            $id = (int) ($this->request->post('id', 0));
            $userId = (int)($this->session->get('admin_id') ?? $this->session->get('user_id') ?? 0);
            $result = $this->engagementService->markNotificationRead($id, $userId);
            $this->json(['success' => $result])->send();
        } catch (Throwable $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 500)->send();
        }
    }

    public function markAllNotificationsRead(): void
    {
        if ($this->request->method() !== 'POST') {
            $this->json(['success' => false, 'message' => 'Invalid request method'], 405)->send();
            return;
        }

        if (!$this->validateCsrfToken()) {
            $this->json(['success' => false, 'message' => 'Security validation failed'], 403)->send();
            return;
        }

        try {
            $userId = (int) ($this->request->post('user_id', 0));
            $result = $this->engagementService->markAllNotificationsRead($userId);
            $this->json(['success' => $result])->send();
        } catch (Throwable $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 500)->send();
        }
    }
}
