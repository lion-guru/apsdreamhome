<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Core\Database\Database;
use App\Traits\TenantAwareTrait;
use Exception;

class SearchHistoryApiController extends BaseController
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

    public function index()
    {
        $this->requireAuth();
        try {
            $tid = $this->tenantId();
            $page = max(1, (int)($_GET['page'] ?? 1));
            $perPage = 20;
            $offset = ($page - 1) * $perPage;
            $search = trim($_GET['search'] ?? '');
            $entityType = $_GET['entity_type'] ?? '';

            $where = '1=1';
            $params = [];

            if ($tid > 1) {
                $where .= ' AND sh.tenant_id = ?';
                $params[] = $tid;
            }

            if ($search !== '') {
                $where .= ' AND (u.name LIKE ? OR sh.filters LIKE ?)';
                $searchParam = "%{$search}%";
                $params[] = $searchParam;
                $params[] = $searchParam;
            }

            if ($entityType !== '' && in_array($entityType, ['properties', 'plots', 'colonies', 'leads'])) {
                $where .= ' AND sh.entity_type = ?';
                $params[] = $entityType;
            }

            // Total count
            $countSql = "SELECT COUNT(*) FROM search_history sh LEFT JOIN users u ON sh.user_id = u.id WHERE $where";
            $stmt = Database::getInstance()->getConnection()->prepare($countSql);
            $stmt->execute($params);
            $total = (int)$stmt->fetchColumn();

            // Fetch page
            $sql = "SELECT sh.*, u.name as user_name, u.role as user_role 
                    FROM search_history sh 
                    LEFT JOIN users u ON sh.user_id = u.id 
                    WHERE $where 
                    ORDER BY sh.created_at DESC 
                    LIMIT {$perPage} OFFSET {$offset}";
            $stmt = Database::getInstance()->getConnection()->prepare($sql);
            $stmt->execute($params);
            $history = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

            $this->json([
                'success' => true,
                'data' => $history,
                'pagination' => [
                    'total' => $total,
                    'page' => $page,
                    'per_page' => $perPage,
                    'total_pages' => max(1, (int)ceil($total / $perPage)),
                ],
            ]);
        } catch (Exception $e) {
            error_log("SearchHistoryApiController::index error: " . $e->getMessage());
            $this->json(['success' => false, 'error' => 'Failed to fetch search history'], 500);
        }
    }
}