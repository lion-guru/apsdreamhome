<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;
use App\Services\WorkspaceHubService;
use App\Http\Middleware\RBACManager;

class WorkspaceHubController extends AdminController
{
    private $hubService;

    public function __construct()
    {
        parent::__construct();
        $this->hubService = new WorkspaceHubService();
    }

    /**
     * Main hub landing page - redirects to appropriate hub for current user
     */
    public function index()
    {
        $currentRole = RBACManager::getUserRole();
        if ($currentRole === RBACManager::ROLE_SUPER_ADMIN) {
            // Super admin can see all hubs - show hub selector
            $this->render('admin/workspace-hubs/index', [
                'page_title' => 'Workspace Hubs',
                'active_page' => 'workspace-hubs',
                'hubs' => $this->hubService->getAllHubs(),
                'userRole' => $currentRole,
            ]);
            return;
        }

        $hub = $this->hubService->getHubForRole($currentRole);
        if ($hub) {
            $this->render('admin/workspace-hubs/hub', [
                'page_title' => $hub['name'],
                'active_page' => 'workspace-hubs',
                'hub' => $hub,
                'userRole' => $currentRole,
            ]);
        } else {
            // Fallback to ERP dashboard
            header('Location: ' . BASE_URL . '/admin/erp');
            exit;
        }
    }

    /**
     * Show specific hub
     */
    public function show($hubKey)
    {
        $currentRole = RBACManager::getUserRole();
        
        // Super admin can access any hub
        if ($currentRole !== RBACManager::ROLE_SUPER_ADMIN) {
            $userHub = $this->hubService->getHubForRole($currentRole);
            if (!$userHub || $userHub['key'] !== $hubKey) {
                header('Location: ' . BASE_URL . '/admin/workspace-hubs');
                exit;
            }
        }

        $hubData = $this->hubService->getHubMenuItems($hubKey, $currentRole);
        if (!$hubData) {
            $this->setFlash('error', 'Hub not found');
            header('Location: ' . BASE_URL . '/admin/workspace-hubs');
            exit;
        }

        $this->render('admin/workspace-hubs/hub', [
            'page_title' => $hubData['name'],
            'active_page' => 'workspace-hubs',
            'hub' => $hubData,
            'userRole' => $currentRole,
        ]);
    }

    /**
     * Get hub data as JSON for AJAX
     */
    public function getHubData($hubKey)
    {
        $currentRole = RBACManager::getUserRole();
        $userId = $_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? null;
        
        $hubData = $this->hubService->getHubMenuItems($hubKey, $currentRole, $userId);
        
        echo json_encode(['success' => true, 'hub' => $hubData]);
        exit;
    }

    /**
     * Get current user's hub
     */
    public function getMyHub()
    {
        $currentRole = RBACManager::getUserRole();
        $userId = $_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? null;
        
        $hub = $this->hubService->getHubForRole($currentRole);
        
        if ($hub) {
            $hubData = $this->hubService->getHubMenuItems($hub['key'], $currentRole, $userId);
            echo json_encode(['success' => true, 'hub' => $hubData]);
        } else {
            echo json_encode(['success' => false, 'message' => 'No hub assigned for this role']);
        }
        exit;
    }
}