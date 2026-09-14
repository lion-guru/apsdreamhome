<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use \App\Traits\TenantAwareTrait;
use Exception;

class AppFeedbackController extends AdminController
{
    use TenantAwareTrait;

    public function __construct()
    {
        parent::__construct();
    }

protected function pdo(): \PDO
{
    return $this->db->getConnection();
}

    public function index()
    {
        $this->requireAdmin();

        $search = trim($_GET['search'] ?? '');
        $typeFilter = $_GET['type'] ?? '';
        $statusFilter = $_GET['status'] ?? '';
        $platformFilter = $_GET['platform'] ?? '';
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        $where = ['1=1'];
        $params = [];

        $tid = (int)$this->tenantId();
        if ($tid > 1) {
            $where[] = 'af.tenant_id = ?';
            $params[] = $tid;
        }

        if ($search !== '') {
            $where[] = '(af.user_name LIKE ? OR af.user_email LIKE ?)';
            $params[] = '%' . $search . '%';
            $params[] = '%' . $search . '%';
        }

        $allowedTypes = ['bug', 'feature', 'improvement', 'complaint', 'other'];
        if ($typeFilter && in_array($typeFilter, $allowedTypes, true)) {
            $where[] = 'af.type = ?';
            $params[] = $typeFilter;
        }

        $allowedStatuses = ['new', 'acknowledged', 'in_progress', 'resolved', 'closed'];
        if ($statusFilter && in_array($statusFilter, $allowedStatuses, true)) {
            $where[] = 'af.status = ?';
            $params[] = $statusFilter;
        }

        $allowedPlatforms = ['android', 'ios', 'web'];
        if ($platformFilter && in_array($platformFilter, $allowedPlatforms, true)) {
            $where[] = 'af.platform = ?';
            $params[] = $platformFilter;
        }

        $whereSql = implode(' AND ', $where);

        $feedback = [];
        $total = 0;

        try {
            $countSql = "SELECT COUNT(*) as cnt FROM app_feedback af WHERE {$whereSql}";
            $countStmt = $this->pdo()->prepare($countSql);
            $countStmt->execute($params);
            $total = (int)$countStmt->fetch(\PDO::FETCH_ASSOC)['cnt'];
        } catch (Exception $e) {
            error_log('AppFeedbackController::index count error: ' . $e->getMessage());
        }

        try {
            $sql = "SELECT af.* FROM app_feedback af WHERE {$whereSql} ORDER BY af.created_at DESC LIMIT ? OFFSET ?";
            $fetchParams = array_merge($params, [$perPage, $offset]);
            $stmt = $this->pdo()->prepare($sql);
            $stmt->execute($fetchParams);
            $feedback = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log('AppFeedbackController::index fetch error: ' . $e->getMessage());
        }

        $stats = ['total' => 0, 'new' => 0, 'acknowledged' => 0, 'in_progress' => 0, 'resolved' => 0, 'closed' => 0, 'avg_rating' => 0];
        try {
            $statsWhere = $tid > 1 ? 'WHERE tenant_id = ?' : 'WHERE 1=1';
            $statsParams = $tid > 1 ? [$tid] : [];
            $statsSql = "SELECT status, COUNT(*) as cnt, AVG(rating) as avg_r FROM app_feedback {$statsWhere} GROUP BY status";
            $statsStmt = $this->pdo()->prepare($statsSql);
            $statsStmt->execute($statsParams);
            $totalRating = 0;
            $totalRated = 0;
            foreach ($statsStmt->fetchAll(\PDO::FETCH_ASSOC) as $row) {
                $stats['total'] += (int)$row['cnt'];
                $s = $row['status'];
                if (isset($stats[$s])) {
                    $stats[$s] = (int)$row['cnt'];
                }
                if ($row['avg_r'] !== null) {
                    $totalRating += (float)$row['avg_r'] * (int)$row['cnt'];
                    $totalRated += (int)$row['cnt'];
                }
            }
            $stats['avg_rating'] = $totalRated > 0 ? round($totalRating / $totalRated, 1) : 0;
        } catch (Exception $e) {
            error_log('AppFeedbackController::index stats error: ' . $e->getMessage());
        }

        $totalPages = max(1, (int)ceil($total / $perPage));

        return $this->render('admin/app-feedback/index', [
            'page_title' => 'App Feedback',
            'feedback' => $feedback,
            'stats' => $stats,
            'total' => $total,
            'page' => $page,
            'total_pages' => $totalPages,
            'filters' => [
                'search' => $search,
                'type' => $typeFilter,
                'status' => $statusFilter,
                'platform' => $platformFilter,
            ],
        ]);
    }

    public function show($id)
    {
        $this->requireAdmin();

        $id = (int)$id;
        $item = null;

        try {
            $sql = "SELECT af.* FROM app_feedback af WHERE af.id = ?";
            $params = [$id];

            $tid = (int)$this->tenantId();
            if ($tid > 1) {
                $sql .= " AND af.tenant_id = ?";
                $params[] = $tid;
            }

            $stmt = $this->pdo()->prepare($sql);
            $stmt->execute($params);
            $item = $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log('AppFeedbackController::show error: ' . $e->getMessage());
        }

        if (!$item) {
            $this->setFlash('error', 'Feedback not found');
            $this->redirect('/admin/app-feedback');
            return;
        }

        return $this->render('admin/app-feedback/show', [
            'page_title' => 'Feedback #' . $id,
            'item' => $item,
        ]);
    }

    public function updateStatus($id)
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/app-feedback/' . (int)$id);
            return;
        }

        $id = (int)$id;
        $status = $_POST['status'] ?? '';
        $allowedStatuses = ['new', 'acknowledged', 'in_progress', 'resolved', 'closed'];

        if (!in_array($status, $allowedStatuses, true)) {
            $this->setFlash('error', 'Invalid status');
            $this->redirect('/admin/app-feedback/' . $id);
            return;
        }

        try {
            $adminId = (int)($_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 0);
            $sql = "UPDATE app_feedback SET status = ?";
            $params = [$status];

            if (in_array($status, ['resolved', 'closed'], true)) {
                $sql .= ", responded_by = ?, responded_at = NOW()";
                $params[] = $adminId;
            }

            $sql .= " WHERE id = ?";
            $params[] = $id;

            $tid = (int)$this->tenantId();
            if ($tid > 1) {
                $sql .= " AND tenant_id = ?";
                $params[] = $tid;
            }

            $stmt = $this->pdo()->prepare($sql);
            $stmt->execute($params);

            if ($stmt->rowCount() > 0) {
                $this->setFlash('success', 'Status updated to ' . ucfirst(str_replace('_', ' ', $status)));
            } else {
                $this->setFlash('warning', 'Status was already set to ' . ucfirst(str_replace('_', ' ', $status)));
            }
        } catch (Exception $e) {
            error_log('AppFeedbackController::updateStatus error: ' . $e->getMessage());
            $this->setFlash('error', 'Failed to update status');
        }

        $this->redirect('/admin/app-feedback/' . $id);
    }

    public function respond($id)
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/app-feedback/' . (int)$id);
            return;
        }

        $id = (int)$id;
        $response = trim($_POST['admin_response'] ?? '');

        if ($response === '') {
            $this->setFlash('error', 'Response cannot be empty');
            $this->redirect('/admin/app-feedback/' . $id);
            return;
        }

        try {
            $adminId = (int)($_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 0);
            $sql = "UPDATE app_feedback SET admin_response = ?, responded_by = ?, responded_at = NOW(), status = 'acknowledged' WHERE id = ?";
            $params = [$response, $adminId, $id];

            $tid = (int)$this->tenantId();
            if ($tid > 1) {
                $sql .= " AND tenant_id = ?";
                $params[] = $tid;
            }

            $stmt = $this->pdo()->prepare($sql);
            $stmt->execute($params);

            if ($stmt->rowCount() > 0) {
                $this->setFlash('success', 'Response saved and status set to Acknowledged');
            } else {
                $this->setFlash('warning', 'Feedback not found or unchanged');
            }
        } catch (Exception $e) {
            error_log('AppFeedbackController::respond error: ' . $e->getMessage());
            $this->setFlash('error', 'Failed to save response');
        }

        $this->redirect('/admin/app-feedback/' . $id);
    }

    public function delete($id)
    {
        $this->requireAdmin();

        $id = (int)$id;

        try {
            $sql = "DELETE FROM app_feedback WHERE id = ?";
            $params = [$id];

            $tid = (int)$this->tenantId();
            if ($tid > 1) {
                $sql .= " AND tenant_id = ?";
                $params[] = $tid;
            }

            $stmt = $this->pdo()->prepare($sql);
            $stmt->execute($params);

            if ($stmt->rowCount() > 0) {
                $this->setFlash('success', 'Feedback deleted');
            } else {
                $this->setFlash('error', 'Feedback not found');
            }
        } catch (Exception $e) {
            error_log('AppFeedbackController::delete error: ' . $e->getMessage());
            $this->setFlash('error', 'Failed to delete feedback');
        }

        $this->redirect('/admin/app-feedback');
    }
}
