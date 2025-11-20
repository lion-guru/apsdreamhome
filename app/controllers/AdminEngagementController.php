<?php

require_once __DIR__ . '/../services/EngagementService.php';

class AdminEngagementController
{
    private EngagementService $engagementService;

    public function __construct()
    {
        $this->engagementService = new EngagementService();
    }

    public function metrics(): void
    {
        $this->ensureAdmin();
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
        $this->ensureAdmin();
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
        $this->ensureAdmin();
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
        $this->ensureAdmin();
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
                'goal_id' => $goalId,
                'progress' => $progress,
                'events' => $events,
            ]);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function createGoal(): void
    {
        $this->ensureAdmin();
        header('Content-Type: application/json');

        try {
            $payload = $_POST;
            $payload['created_by'] = $_SESSION['admin_id'] ?? null;
            $result = $this->engagementService->createGoal($payload);
            echo json_encode(['success' => true, 'goal_id' => $result['id']]);
        } catch (InvalidArgumentException $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function updateGoal(): void
    {
        $this->ensureAdmin();
        header('Content-Type: application/json');

        $goalId = (int) ($_POST['goal_id'] ?? 0);
        if ($goalId <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'goal_id is required']);
            return;
        }

        try {
            $payload = $_POST;
            $payload['updated_by'] = $_SESSION['admin_id'] ?? null;

            $updated = $this->engagementService->updateGoal($goalId, $payload);
            echo json_encode(['success' => $updated]);
        } catch (InvalidArgumentException $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function recordGoalProgress(): void
    {
        $this->ensureAdmin();
        header('Content-Type: application/json');

        $goalId = (int) ($_POST['goal_id'] ?? 0);
        $checkpointDate = $_POST['checkpoint_date'] ?? '';
        $actualValue = isset($_POST['actual_value']) ? (float) $_POST['actual_value'] : 0.0;
        $percentage = isset($_POST['percentage_complete']) && $_POST['percentage_complete'] !== ''
            ? (float) $_POST['percentage_complete']
            : null;

        if ($goalId <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'goal_id is required']);
            return;
        }

        try {
            $payload = [
                'notes' => $_POST['notes'] ?? null,
                'recorded_by' => $_SESSION['admin_id'] ?? null,
            ];

            $this->engagementService->recordGoalProgress($goalId, $checkpointDate, $actualValue, $percentage, $payload);
            echo json_encode(['success' => true]);
        } catch (InvalidArgumentException $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function updateGoalStatus(): void
    {
        $this->ensureAdmin();
        header('Content-Type: application/json');

        $goalId = (int) ($_POST['goal_id'] ?? 0);
        $status = $_POST['status'] ?? '';

        if ($goalId <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'goal_id is required']);
            return;
        }

        try {
            $updated = $this->engagementService->updateGoalStatus($goalId, $status);
            echo json_encode(['success' => $updated]);
        } catch (InvalidArgumentException $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function notificationFeed(): void
    {
        $this->ensureAdmin();
        header('Content-Type: application/json');

        $userId = (int) ($_GET['user_id'] ?? 0);
        if ($userId <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'user_id is required']);
            return;
        }

        $limit = max(1, min(200, (int) ($_GET['limit'] ?? 20)));
        $offset = max(0, (int) ($_GET['offset'] ?? 0));
        $category = $_GET['category'] ?? null;

        try {
            $records = $this->engagementService->getNotificationFeed($userId, $limit, $offset, $category);
            echo json_encode(['success' => true, 'records' => $records]);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function notificationPreferences(): void
    {
        $this->ensureAdmin();
        header('Content-Type: application/json');

        $userId = (int) ($_GET['user_id'] ?? 0);
        if ($userId <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'user_id is required']);
            return;
        }

        try {
            $records = $this->engagementService->getNotificationPreferences($userId);
            echo json_encode(['success' => true, 'records' => $records]);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function markNotificationRead(): void
    {
        $this->ensureAdmin();
        header('Content-Type: application/json');

        $notificationId = (int) ($_POST['notification_id'] ?? 0);
        $userId = (int) ($_POST['user_id'] ?? 0);

        if ($notificationId <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'notification_id is required']);
            return;
        }

        if ($userId <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'user_id is required']);
            return;
        }

        try {
            $marked = $this->engagementService->markNotificationRead($notificationId, $userId);
            echo json_encode(['success' => true, 'updated' => $marked]);
        } catch (InvalidArgumentException $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function markAllNotificationsRead(): void
    {
        $this->ensureAdmin();
        header('Content-Type: application/json');

        $userId = (int) ($_POST['user_id'] ?? 0);
        if ($userId <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'user_id is required']);
            return;
        }

        try {
            $count = $this->engagementService->markAllNotificationsRead($userId);
            echo json_encode(['success' => true, 'updated' => $count]);
        } catch (InvalidArgumentException $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    private function ensureAdmin(): void
    {
        if (empty($_SESSION['admin_logged_in'])) {
            header('Location: ' . BASE_URL . 'admin/');
            exit();
        }
    }
}
