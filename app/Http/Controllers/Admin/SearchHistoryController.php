<?php

namespace App\Http\Controllers\Admin;

use App\Core\Database;
use App\Traits\TenantAwareTrait;

class SearchHistoryController extends AdminController
{
    use TenantAwareTrait;

    public function __construct()
    {
        parent::__construct();
        $this->layout = 'layouts/admin';
    }

    protected function pdo(): \PDO
    {
        return Database::getInstance()->getConnection();
    }

    public function index()
    {
        $this->requireAdmin();

        $search = trim($_GET['search'] ?? '');
        $entityType = $_GET['entity_type'] ?? '';
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 20;

        try {
            $pdo = $this->pdo();
            $tid = (int)$this->tenantId();

            $where = [];
            $params = [];

            if ($tid > 1) {
                $where[] = 'sh.tenant_id = ?';
                $params[] = $tid;
            }

            if ($search !== '') {
                $where[] = '(u.name LIKE ? OR sh.search_term LIKE ?)';
                $params[] = '%' . $search . '%';
                $params[] = '%' . $search . '%';
            }

            if ($entityType !== '' && $entityType !== 'all') {
                $where[] = 'sh.entity_type = ?';
                $params[] = $entityType;
            }

            $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

            $countSql = "SELECT COUNT(*) FROM search_history sh LEFT JOIN users u ON sh.user_id = u.id {$whereSql}";
            $stmt = $pdo->prepare($countSql);
            $stmt->execute($params);
            $total = (int)$stmt->fetchColumn();
            $totalPages = max(1, (int)ceil($total / $perPage));
            $offset = ($page - 1) * $perPage;

            $dataSql = "SELECT sh.*, u.name AS user_name, u.role AS user_role
                FROM search_history sh
                LEFT JOIN users u ON sh.user_id = u.id
                {$whereSql}
                ORDER BY sh.created_at DESC
                LIMIT {$perPage} OFFSET {$offset}";
            $stmt = $pdo->prepare($dataSql);
            $stmt->execute($params);
            $history = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

            $stats = [
                'total_searches' => $total,
                'unique_users' => 0,
                'top_entity' => '-',
            ];

            try {
                $statsSql = "SELECT COUNT(DISTINCT user_id) FROM search_history sh {$whereSql}";
                $stmt = $pdo->prepare($statsSql);
                $stmt->execute($params);
                $stats['unique_users'] = (int)$stmt->fetchColumn();
            } catch (\Exception $e) {
                error_log("[SearchHistoryController] unique_users stats error: " . $e->getMessage());
            }

            try {
                $topSql = "SELECT sh.entity_type, COUNT(*) AS cnt FROM search_history sh {$whereSql} GROUP BY sh.entity_type ORDER BY cnt DESC LIMIT 1";
                $stmt = $pdo->prepare($topSql);
                $stmt->execute($params);
                $topRow = $stmt->fetch(\PDO::FETCH_ASSOC);
                if ($topRow) {
                    $stats['top_entity'] = $topRow['entity_type'];
                }
            } catch (\Exception $e) {
                error_log("[SearchHistoryController] top_entity stats error: " . $e->getMessage());
            }

            $entityTypes = ['properties', 'plots', 'colonies', 'leads', 'all'];

        } catch (\Exception $e) {
            error_log("[SearchHistoryController] index error: " . $e->getMessage());
            $history = [];
            $stats = ['total_searches' => 0, 'unique_users' => 0, 'top_entity' => '-'];
            $total = 0;
            $totalPages = 1;
            $page = 1;
            $entityTypes = ['properties', 'plots', 'colonies', 'leads', 'all'];
        }

        return $this->render('admin/search-history/index', [
            'page_title' => 'Search History',
            'history' => $history,
            'stats' => $stats,
            'total' => $total,
            'page' => $page,
            'total_pages' => $totalPages,
            'search' => $search,
            'entity_type' => $entityType,
            'entity_types' => $entityTypes,
        ]);
    }
}
