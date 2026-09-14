<?php

namespace App\Http\Controllers\Admin;

use App\Core\Database\Database;
use \App\Traits\TenantAwareTrait;

class VoiceUploadController extends AdminController
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

    /**
     * List voice uploads with search, status filter, and pagination
     */
    public function index()
    {
        $this->requireAdmin();

        $search = trim($_GET['search'] ?? '');
        $status = $_GET['status'] ?? '';
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        $uploads = [];
        $totalRows = 0;
        $stats = ['total' => 0, 'pending' => 0, 'processed' => 0, 'failed' => 0];

        try {
            $pdo = $this->pdo();

            // Stats
            $stmt = $pdo->query("SELECT status, COUNT(*) as cnt FROM customer_voice_upload WHERE status != 'deleted' GROUP BY status");
            foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $row) {
                $stats[$row['status']] = (int)$row['cnt'];
                $stats['total'] += (int)$row['cnt'];
            }

            // Build query
            $sql = "SELECT v.* FROM customer_voice_upload v WHERE v.status != 'deleted'";
            $countSql = "SELECT COUNT(*) FROM customer_voice_upload v WHERE v.status != 'deleted'";
            $params = [];

            if ($search !== '') {
                $sql .= " AND (v.user_name LIKE ? OR v.user_phone LIKE ?)";
                $countSql .= " AND (v.user_name LIKE ? OR v.user_phone LIKE ?)";
                $searchParam = "%{$search}%";
                $params[] = $searchParam;
                $params[] = $searchParam;
            }

            if ($status !== '' && in_array($status, ['pending', 'processed', 'failed'])) {
                $sql .= " AND v.status = ?";
                $countSql .= " AND v.status = ?";
                $params[] = $status;
            }

            // Total count
            $stmt = $pdo->prepare($countSql);
            $stmt->execute($params);
            $totalRows = (int)$stmt->fetchColumn();

            // Fetch page
            $sql .= " ORDER BY v.created_at DESC LIMIT {$perPage} OFFSET {$offset}";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $uploads = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        } catch (\Exception $e) {
            error_log("VoiceUploadController::index error: " . $e->getMessage());
        }

        $totalPages = max(1, (int)ceil($totalRows / $perPage));

        return $this->render('admin/voice-uploads/index', [
            'page_title' => 'Voice Uploads',
            'uploads' => $uploads,
            'stats' => $stats,
            'search' => $search,
            'status' => $status,
            'page' => $page,
            'totalPages' => $totalPages,
            'totalRows' => $totalRows,
            'success' => $this->getFlash('success'),
            'error' => $this->getFlash('error'),
        ]);
    }

    /**
     * Show single voice upload detail
     */
    public function show($id)
    {
        $this->requireAdmin();
        $upload = null;

        try {
            $pdo = $this->pdo();
            $stmt = $pdo->prepare("SELECT v.*, u.name as processed_by_name FROM customer_voice_upload v LEFT JOIN users u ON v.processed_by = u.id WHERE v.id = ?");
            $stmt->execute([(int)$id]);
            $upload = $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("VoiceUploadController::show error: " . $e->getMessage());
        }

        if (!$upload) {
            $this->setFlash('error', 'Voice upload not found');
            $this->redirect('/admin/voice-uploads');
            return;
        }

        return $this->render('admin/voice-uploads/show', [
            'page_title' => 'Voice Upload #' . $id,
            'upload' => $upload,
            'success' => $this->getFlash('success'),
            'error' => $this->getFlash('error'),
        ]);
    }

    /**
     * Mark upload as processed
     */
    public function process($id)
    {
        $this->requireAdmin();

        try {
            $pdo = $this->pdo();
            $adminId = (int)($_SESSION['admin_id'] ?? 0);

            $stmt = $pdo->prepare(
                "UPDATE customer_voice_upload SET status = 'processed', processed_by = ?, processed_at = NOW() WHERE id = ? AND status != 'deleted'"
            );
            $stmt->execute([$adminId, (int)$id]);

            if ($stmt->rowCount() > 0) {
                $this->setFlash('success', 'Voice upload #' . $id . ' marked as processed');
            } else {
                $this->setFlash('error', 'Upload not found or already deleted');
            }
        } catch (\Exception $e) {
            error_log("VoiceUploadController::process error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to process voice upload');
        }

        $this->redirect('/admin/voice-uploads/show/' . (int)$id);
    }

    /**
     * Delete voice upload (soft-delete by default)
     */
    public function delete($id)
    {
        $this->requireAdmin();

        $hard = isset($_GET['hard']);

        try {
            $pdo = $this->pdo();

            if ($hard) {
                $stmt = $pdo->prepare("DELETE FROM customer_voice_upload WHERE id = ?");
                $stmt->execute([(int)$id]);
            } else {
                $stmt = $pdo->prepare("UPDATE customer_voice_upload SET status = 'deleted' WHERE id = ? AND status != 'deleted'");
                $stmt->execute([(int)$id]);
            }

            if ($stmt->rowCount() > 0) {
                $this->setFlash('success', 'Voice upload #' . $id . ' deleted');
            } else {
                $this->setFlash('error', 'Upload not found or already deleted');
            }
        } catch (\Exception $e) {
            error_log("VoiceUploadController::delete error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to delete voice upload');
        }

        $this->redirect('/admin/voice-uploads');
    }
}
