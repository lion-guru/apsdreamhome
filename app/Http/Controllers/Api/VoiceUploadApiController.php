<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Admin\AdminController;
use App\Core\Database\Database;
use App\Traits\TenantAwareTrait;
use Exception;

class VoiceUploadApiController extends AdminController
{
    use TenantAwareTrait;

    public function index()
    {
        $this->requireAuth();
        try {
            $tid = $this->tenantId();
            $page = max(1, (int)($_GET['page'] ?? 1));
            $perPage = 20;
            $offset = ($page - 1) * $perPage;
            $search = trim($_GET['search'] ?? '');
            $status = $_GET['status'] ?? '';

            $where = 'status != "deleted"';
            $params = [];

            if ($tid > 1) {
                $where .= ' AND tenant_id = ?';
                $params[] = $tid;
            }

            if ($search !== '') {
                $where .= ' AND (user_name LIKE ? OR user_phone LIKE ?)';
                $searchParam = "%{$search}%";
                $params[] = $searchParam;
                $params[] = $searchParam;
            }

            if ($status !== '' && in_array($status, ['pending', 'processed', 'failed'])) {
                $where .= ' AND status = ?';
                $params[] = $status;
            }

            // Total count
            $countSql = "SELECT COUNT(*) FROM customer_voice_upload WHERE $where";
            $stmt = Database::getInstance()->getConnection()->prepare($countSql);
            $stmt->execute($params);
            $total = (int)$stmt->fetchColumn();

            // Fetch page
            $sql = "SELECT * FROM customer_voice_upload WHERE $where ORDER BY created_at DESC LIMIT {$perPage} OFFSET {$offset}";
            $stmt = Database::getInstance()->getConnection()->prepare($sql);
            $stmt->execute($params);
            $uploads = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

            $this->json([
                'success' => true,
                'data' => $uploads,
                'pagination' => [
                    'total' => $total,
                    'page' => $page,
                    'per_page' => $perPage,
                    'total_pages' => max(1, (int)ceil($total / $perPage)),
                ],
            ]);
        } catch (Exception $e) {
            error_log("VoiceUploadApiController::index error: " . $e->getMessage());
            $this->json(['success' => false, 'error' => 'Failed to fetch voice uploads'], 500);
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
                SELECT v.*, u.name as processed_by_name
                FROM customer_voice_upload v
                LEFT JOIN users u ON v.processed_by = u.id
                WHERE $where
            ");
            $stmt->execute($params);
            $upload = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$upload) {
                $this->json(['success' => false, 'error' => 'Voice upload not found'], 404);
                return;
            }

            $this->json(['success' => true, 'data' => $upload]);
        } catch (Exception $e) {
            error_log("VoiceUploadApiController::show error: " . $e->getMessage());
            $this->json(['success' => false, 'error' => 'Failed to fetch voice upload'], 500);
        }
    }
}