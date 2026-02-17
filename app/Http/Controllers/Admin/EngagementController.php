<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
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

        // Load legacy service
        require_once dirname(__DIR__, 3) . '/services/EngagementService.php';
        $this->engagementService = new \EngagementService();
    }

    public function index(): void
    {
        $this->render('admin/mlm_engagement');
    }

    public function metrics(): void
    {
        header('Content-Type: application/json');

        try {
            $limit = max(1, min(200, (int) ($_GET['limit'] ?? 50)));
            $offset = max(0, (int) ($_GET['offset'] ?? 0));
            $filters = [
                'user_id' => $_GET['user_id'] ?? null,
                'from' => $_GET['from'] ?? null,
                'to' => $_GET['to'] ?? null,
                'rank_label' => $_GET['rank_label'] ?? null,
            ];

            echo json_encode([
                'success' => true,
                'records' => $this->engagementService->getAssociateMetrics($filters, $limit, $offset),
            ]);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function leaderboard(): void
    {
        header('Content-Type: application/json');

        $metricType = $_GET['metric_type'] ?? '';
        $snapshotDate = $_GET['snapshot_date'] ?? null;
        $limit = max(1, min(200, (int) ($_GET['limit'] ?? 20)));

        try {
            $result = $this->engagementService->getLeaderboardSnapshot($metricType, $snapshotDate, $limit);
            echo json_encode(['success' => true, 'data' => $result]);
        } catch (InvalidArgumentException $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function goals(): void
    {
        header('Content-Type: application/json');

        try {
            $limit = max(1, min(200, (int) ($_GET['limit'] ?? 50)));
            $offset = max(0, (int) ($_GET['offset'] ?? 0));
            $filters = [
                'status' => $_GET['status'] ?? null,
                'scope' => $_GET['scope'] ?? null,
                'user_id' => $_GET['user_id'] ?? null,
                'goal_type' => $_GET['goal_type'] ?? null,
                'active_on' => $_GET['active_on'] ?? null,
            ];

            echo json_encode([
                'success' => true,
                'records' => $this->engagementService->getGoals($filters, $limit, $offset),
            ]);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function goalDetails(): void
    {
        header('Content-Type: application/json');

        $goalId = (int) ($_GET['goal_id'] ?? 0);
        if ($goalId <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'goal_id is required']);
            return;
        }

        try {
            $progress = $this->engagementService->getGoalProgress($goalId);
            $events = $this->engagementService->getGoalEvents($goalId);
            echo json_encode([
                'success' => true,
                'data' => [
                    'progress' => $progress,
                    'events' => $events,
                ],
            ]);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function notificationFeed(): void
    {
        header('Content-Type: application/json');

        try {
            $userId = (int) ($_GET['user_id'] ?? 0);
            $limit = max(1, min(100, (int) ($_GET['limit'] ?? 20)));
            $offset = max(0, (int) ($_GET['offset'] ?? 0));
            $unreadOnly = ($_GET['unread_only'] ?? '0') === '1';

            $notifications = $this->engagementService->getNotifications($userId, $unreadOnly, $limit, $offset);
            echo json_encode(['success' => true, 'records' => $notifications]);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function notificationPreferences(): void
    {
        header('Content-Type: application/json');

        $userId = (int) ($_GET['user_id'] ?? 0);
        if ($userId <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'user_id required']);
            return;
        }

        try {
            $prefs = $this->engagementService->getNotificationPreferences($userId);
            echo json_encode(['success' => true, 'data' => $prefs]);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function createGoal(): void
    {
        header('Content-Type: application/json');
        try {
            $result = $this->engagementService->createGoal($_POST);
            echo json_encode($result);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function updateGoal(): void
    {
        header('Content-Type: application/json');
        try {
            $result = $this->engagementService->updateGoal($_POST);
            echo json_encode($result);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function recordGoalProgress(): void
    {
        header('Content-Type: application/json');
        try {
            $goalId = (int) ($_POST['goal_id'] ?? 0);
            $value = (float) ($_POST['value'] ?? 0);
            $result = $this->engagementService->recordGoalProgress($goalId, $value);
            echo json_encode($result);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function updateGoalStatus(): void
    {
        header('Content-Type: application/json');
        try {
            $goalId = (int) ($_POST['goal_id'] ?? 0);
            $status = $_POST['status'] ?? '';
            $result = $this->engagementService->updateGoalStatus($goalId, $status);
            echo json_encode($result);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function markNotificationRead(): void
    {
        header('Content-Type: application/json');
        try {
            $id = (int) ($_POST['id'] ?? 0);
            $result = $this->engagementService->markNotificationRead($id);
            echo json_encode(['success' => $result]);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function markAllNotificationsRead(): void
    {
        header('Content-Type: application/json');
        try {
            $userId = (int) ($_POST['user_id'] ?? 0);
            $result = $this->engagementService->markAllNotificationsRead($userId);
            echo json_encode(['success' => $result]);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
