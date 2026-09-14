<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Core\Database\Database;
use App\Traits\TenantAwareTrait;
use Exception;

class AppFeedbackApiController extends BaseController
{
    use TenantAwareTrait;

    public function __construct()
    {
        parent::__construct();
        $this->skipCsrfProtection();
    }

    private function requireAuth(): int
    {
        $userId = (int)($GLOBALS['api_user_id'] ?? 0);
        if (!$userId) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Authentication required']);
            exit;
        }
        return $userId;
    }

    private function requireAdmin(): int
    {
        $userId = $this->requireAuth();
        $role = $GLOBALS['api_user_role'] ?? '';
        if (!in_array($role, ['admin', 'employee', 'superadmin'])) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Admin access required']);
            exit;
        }
        return $userId;
    }

    public function index()
    {
        $this->requireAdmin();
        try {
            $tid = $this->tenantId();
            $page = max(1, (int)($_GET['page'] ?? 1));
            $perPage = 20;
            $offset = ($page - 1) * $perPage;
            $search = trim($_GET['search'] ?? '');
            $type = $_GET['type'] ?? '';
            $status = $_GET['status'] ?? '';
            $platform = $_GET['platform'] ?? '';

            $where = '1=1';
            $params = [];

            if ($tid > 1) {
                $where .= ' AND tenant_id = ?';
                $params[] = $tid;
            }

            if ($search !== '') {
                $where .= ' AND (user_name LIKE ? OR user_email LIKE ?)';
                $searchParam = "%{$search}%";
                $params[] = $searchParam;
                $params[] = $searchParam;
            }

            if ($type !== '' && in_array($type, ['bug', 'feature', 'improvement', 'complaint', 'other'])) {
                $where .= ' AND feedback_type = ?';
                $params[] = $type;
            }

            if ($status !== '' && in_array($status, ['new', 'acknowledged', 'in_progress', 'resolved', 'closed'])) {
                $where .= ' AND status = ?';
                $params[] = $status;
            }

            if ($platform !== '' && in_array($platform, ['android', 'ios', 'web'])) {
                $where .= ' AND platform = ?';
                $params[] = $platform;
            }

            // Total count
            $countSql = "SELECT COUNT(*) FROM app_feedback WHERE $where";
            $stmt = Database::getInstance()->getConnection()->prepare($countSql);
            $stmt->execute($params);
            $total = (int)$stmt->fetchColumn();

            // Fetch page
            $sql = "SELECT * FROM app_feedback WHERE $where ORDER BY created_at DESC LIMIT {$perPage} OFFSET {$offset}";
            $stmt = Database::getInstance()->getConnection()->prepare($sql);
            $stmt->execute($params);
            $feedback = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

            $this->json([
                'success' => true,
                'data' => $feedback,
                'pagination' => [
                    'total' => $total,
                    'page' => $page,
                    'per_page' => $perPage,
                    'total_pages' => max(1, (int)ceil($total / $perPage)),
                ],
            ]);
        } catch (Exception $e) {
            error_log("AppFeedbackApiController::index error: " . $e->getMessage());
            $this->json(['success' => false, 'error' => 'Failed to fetch feedback'], 500);
        }
    }

    public function store()
    {
        $this->requireAuth();
        try {
            $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;

            $required = ['feedback_type', 'description'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    $this->json(['success' => false, 'error' => "Missing required field: {$field}"], 400);
                    return;
                }
            }

            $allowedTypes = ['bug', 'feature', 'improvement', 'complaint', 'other'];
            if (!in_array($data['feedback_type'], $allowedTypes)) {
                $this->json(['success' => false, 'error' => 'Invalid feedback type'], 400);
                return;
            }

            $tid = $this->tenantId();
            $userId = $this->requireAuth();

            $stmt = Database::getInstance()->getConnection()->prepare("
                INSERT INTO app_feedback (tenant_id, user_id, user_name, user_email, feedback_type, platform, app_version, rating, title, description, screenshot_path, device_info, os_version, status, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'new', NOW())
            ");
            $stmt->execute([
                $tid,
                $userId,
                $data['user_name'] ?? '',
                $data['user_email'] ?? '',
                $data['feedback_type'],
                $data['platform'] ?? 'android',
                $data['app_version'] ?? '',
                $data['rating'] ?? null,
                $data['title'] ?? '',
                $data['description'],
                $data['screenshot_path'] ?? '',
                $data['device_info'] ?? '',
                $data['os_version'] ?? '',
            ]);

            $id = Database::getInstance()->getConnection()->lastInsertId();

            $this->json(['success' => true, 'message' => 'Feedback submitted', 'id' => $id], 201);
        } catch (Exception $e) {
            error_log("AppFeedbackApiController::store error: " . $e->getMessage());
            $this->json(['success' => false, 'error' => 'Failed to submit feedback'], 500);
        }
    }

    public function show($id)
    {
        $this->requireAuth();
        try {
            $tid = $this->tenantId();
            $where = 'id = ?';
            $params = [$id];
            if ($tid > 1) {
                $where .= ' AND tenant_id = ?';
                $params[] = $tid;
            }

            $stmt = Database::getInstance()->getConnection()->prepare("
                SELECT * FROM app_feedback WHERE $where
            ");
            $stmt->execute($params);
            $feedback = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$feedback) {
                $this->json(['success' => false, 'error' => 'Feedback not found'], 404);
                return;
            }

            $this->json(['success' => true, 'data' => $feedback]);
        } catch (Exception $e) {
            error_log("AppFeedbackApiController::show error: " . $e->getMessage());
            $this->json(['success' => false, 'error' => 'Failed to fetch feedback'], 500);
        }
    }
}