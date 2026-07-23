<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Services\AuditLogService;

class AuditLogController extends AdminController
{
    private AuditLogService $auditLogService;

    public function __construct()
    {
        parent::__construct();
        $this->auditLogService = new AuditLogService();
    }

    public function index()
    {
        $this->requireAdmin();

        $filters = [
            'user_id' => $_GET['user_id'] ?? null,
            'user_role' => $_GET['user_role'] ?? null,
            'action' => $_GET['action'] ?? null,
            'entity_type' => $_GET['entity_type'] ?? null,
            'entity_id' => $_GET['entity_id'] ?? null,
            'date_from' => $_GET['date_from'] ?? null,
            'date_to' => $_GET['date_to'] ?? null,
            'status' => $_GET['status'] ?? null,
        ];

        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = 50;
        $offset = ($page - 1) * $limit;

        $result = $this->auditLogService->getLogs($filters, $limit, $offset);
        $stats = $this->auditLogService->getStats(30);

        // Get unique values for filter dropdowns
        $roles = $this->db->fetchAll("SELECT DISTINCT user_role FROM audit_logs ORDER BY user_role");
        $actions = $this->db->fetchAll("SELECT DISTINCT action FROM audit_logs ORDER BY action");
        $entityTypes = $this->db->fetchAll("SELECT DISTINCT entity_type FROM audit_logs WHERE entity_type IS NOT NULL ORDER BY entity_type");

        $totalPages = ceil($result['total'] / $limit);

        $this->render('admin/audit-log/index', [
            'page_title' => 'Audit Logs - Admin',
            'logs' => $result['logs'],
            'total' => $result['total'],
            'page' => $page,
            'total_pages' => $totalPages,
            'limit' => $limit,
            'filters' => $filters,
            'stats' => $stats,
            'roles' => array_column($roles, 'user_role'),
            'actions' => array_column($actions, 'action'),
            'entity_types' => array_column($entityTypes, 'entity_type'),
        ]);
    }

    public function detail(int $id)
    {
        $this->requireAdmin();

        $log = $this->db->fetchOne("SELECT * FROM audit_logs WHERE id = ?", [$id]);

        if (!$log) {
            $this->setFlash('error', 'Audit log not found');
            $this->redirect('/admin/audit-log');
            return;
        }

        // Get related logs for same entity
        $related = [];
        if ($log['entity_type'] && $log['entity_id']) {
            $related = $this->auditLogService->getEntityTimeline($log['entity_type'], $log['entity_id'], 20);
        }

        $this->render('admin/audit-log/detail', [
            'page_title' => 'Audit Log Detail - Admin',
            'log' => $log,
            'related' => $related,
        ]);
    }

    public function userTimeline(int $userId)
    {
        $this->requireAdmin();

        $user = $this->db->fetchOne("SELECT id, name, email, role FROM users WHERE id = ?", [$userId]);

        if (!$user) {
            $this->setFlash('error', 'User not found');
            $this->redirect('/admin/audit-log');
            return;
        }

        $timeline = $this->auditLogService->getUserTimeline($userId, 100);

        $this->render('admin/audit-log/user_timeline', [
            'page_title' => 'User Activity Timeline - Admin',
            'user' => $user,
            'timeline' => $timeline,
        ]);
    }

    public function entityTimeline()
    {
        $this->requireAdmin();

        $entityType = $_GET['entity_type'] ?? '';
        $entityId = (int)($_GET['entity_id'] ?? 0);

        if (!$entityType || !$entityId) {
            $this->setFlash('error', 'Invalid entity');
            $this->redirect('/admin/audit-log');
            return;
        }

        $timeline = $this->auditLogService->getEntityTimeline($entityType, $entityId, 100);

        $this->render('admin/audit-log/entity_timeline', [
            'page_title' => 'Entity Timeline - Admin',
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'timeline' => $timeline,
        ]);
    }

    public function stats()
    {
        $this->requireAdmin();

        $days = (int)($_GET['days'] ?? 30);
        $stats = $this->auditLogService->getStats($days);

         $this->render('admin/audit-log/stats', [
            'page_title' => 'Audit Log Statistics - Admin',
            'stats' => $stats,
            'days' => $days,
        ]);
    }

    public function api()
    {
        $this->requireAdmin();

        $days = (int)($_GET['days'] ?? 7);
        $stats = $this->auditLogService->getStats($days);

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'data' => $stats,
        ]);
        exit;
    }
}
