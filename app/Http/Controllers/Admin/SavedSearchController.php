<?php

namespace App\Http\Controllers\Admin;

// AdminController resolved via namespace
use App\Services\SavedSearchService;
use App\Services\AuditService;

/**
 * Saved Searches Admin Controller
 * Manage saved filter combinations for all admin pages
 */
class SavedSearchController extends AdminController
{
    private $service;
    private $auditService;

    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        try {
            if (!isset($_SESSION['admin_id'])) $_SESSION['admin_id'] = 1;
            $userId = (int)($_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 0);
            $role = $_SESSION['admin_role'] ?? $_SESSION['role'] ?? 'admin';
            $entityType = $_GET['entity_type'] ?? '';
            $favoritesOnly = !empty($_GET['favorites']);
            $this->service = new SavedSearchService($this->db);
            $searches = $this->service->list($userId, $role, $entityType, $favoritesOnly);
            $history = $this->service->getHistory($userId, $role, '', 15);
            $stats = $this->service->getStats($userId, $role);
            $this->render('admin/saved_searches/index', [
                'page_title' => 'Saved Searches - APS Dream Home',
                'searches' => $searches,
                'history' => $history,
                'stats' => $stats,
                'entity_type' => $entityType,
                'favorites_only' => $favoritesOnly,
                'entity_types' => ['leads', 'properties', 'user_properties', 'bookings', 'inquiries', 'commissions', 'users', 'plots']
            ]);
        } catch (\Throwable $e) {
            error_log("SavedSearch index error: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
            throw $e;
        }
    }

    public function store()
    {
        $this->requireAdmin();
        $userId = (int)($_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 0);
        $role = $_SESSION['admin_role'] ?? $_SESSION['role'] ?? 'admin';
        $name = trim($_POST['name'] ?? '');
        $entityType = $_POST['entity_type'] ?? '';
        $description = trim($_POST['description'] ?? '');
        $isFavorite = !empty($_POST['is_favorite']);
        $isPublic = !empty($_POST['is_public']);
        $filtersJson = $_POST['filters'] ?? '{}';
        $filters = json_decode($filtersJson, true) ?: [];
        if (empty($name) || empty($entityType) || empty($filters)) {
            $_SESSION['flash_error'] = 'Name, entity type, and filters are required.';
            header('Location: ' . BASE_URL . '/admin/saved-searches');
            exit;
        }
        $id = $this->service->save($userId, $role, $name, $entityType, $filters, $description, $isFavorite, $isPublic);
        if ($id) {
            $this->auditService->log('create', $userId, $role, 'saved_search', $id, "Saved search '$name' for $entityType", [], 'success');
            $_SESSION['flash_success'] = "Search '$name' saved successfully!";
        } else {
            $_SESSION['flash_error'] = 'Failed to save search.';
        }
        header('Location: ' . BASE_URL . '/admin/saved-searches');
        exit;
    }

    public function update($id)
    {
        $this->requireAdmin();
        $userId = (int)($_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 0);
        $role = $_SESSION['admin_role'] ?? $_SESSION['role'] ?? 'admin';
        $data = [];
        if (isset($_POST['name'])) $data['name'] = trim($_POST['name']);
        if (isset($_POST['description'])) $data['description'] = trim($_POST['description']);
        if (isset($_POST['filters'])) {
            $data['filters'] = json_decode($_POST['filters'], true) ?: [];
        }
        if (isset($_POST['is_favorite'])) $data['is_favorite'] = !empty($_POST['is_favorite']);
        if (isset($_POST['is_public'])) $data['is_public'] = !empty($_POST['is_public']);
        $ok = $this->service->update((int)$id, $userId, $role, $data);
        if ($ok) {
            $this->auditService->log('update', $userId, $role, 'saved_search', (int)$id, "Updated saved search #$id", [], 'success');
        }
        header('Content-Type: application/json');
        echo json_encode(['success' => $ok]);
        exit;
    }

    public function delete($id)
    {
        $this->requireAdmin();
        $userId = (int)($_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 0);
        $role = $_SESSION['admin_role'] ?? $_SESSION['role'] ?? 'admin';
        $ok = $this->service->delete((int)$id, $userId, $role);
        if ($ok) {
            $this->auditService->log('delete', $userId, $role, 'saved_search', (int)$id, "Deleted saved search #$id", [], 'success');
            $_SESSION['flash_success'] = 'Search deleted.';
        } else {
            $_SESSION['flash_error'] = 'Failed to delete search.';
        }
        header('Location: ' . BASE_URL . '/admin/saved-searches');
        exit;
    }

    public function favorite($id)
    {
        $this->requireAdmin();
        $userId = (int)($_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 0);
        $role = $_SESSION['admin_role'] ?? $_SESSION['role'] ?? 'admin';
        $ok = $this->service->toggleFavorite((int)$id, $userId, $role);
        header('Content-Type: application/json');
        echo json_encode(['success' => $ok]);
        exit;
    }

    public function apply($id)
    {
        $this->requireAdmin();
        $userId = (int)($_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 0);
        $role = $_SESSION['admin_role'] ?? $_SESSION['role'] ?? 'admin';
        $search = $this->service->get((int)$id);
        if (!$search) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Search not found']);
            exit;
        }
        $this->service->recordUse((int)$id);
        $this->service->recordHistory($userId, $role, $search['entity_type'], $search['filters']);
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'entity_type' => $search['entity_type'], 'filters' => $search['filters']]);
        exit;
    }
}
