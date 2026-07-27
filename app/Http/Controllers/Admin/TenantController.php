<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Services\TenantService;

/**
 * TenantController — Super Admin panel for managing SaaS tenants.
 */
class TenantController extends AdminController
{
    private TenantService $tenantService;

    public function __construct()
    {
        parent::__construct();
        $this->tenantService = TenantService::getInstance();
    }

    public function dashboard()
    {
        $this->requireAdmin();
        $stats = $this->tenantService->getDashboardStats();
        $plans = $this->tenantService->getPlans();

        return $this->render('admin/tenants/dashboard', [
            'stats' => $stats,
            'plans' => $plans,
        ]);
    }

    public function index()
    {
        $this->requireAdmin();

        $filters = [
            'search'   => $_GET['search'] ?? '',
            'status'   => $_GET['status'] ?? '',
            'plan_id'  => $_GET['plan_id'] ?? '',
            'page'     => max(1, (int)($_GET['page'] ?? 1)),
            'per_page' => 20,
        ];

        $result = $this->tenantService->list($filters);
        $plans = $this->tenantService->getPlans();
        $stats = $this->tenantService->getDashboardStats();

        return $this->render('admin/tenants/index', [
            'tenants'  => $result['tenants'],
            'total'    => $result['total'],
            'page'     => $result['page'],
            'pages'    => $result['pages'],
            'per_page' => $result['per_page'],
            'filters'  => $filters,
            'plans'    => $plans,
            'stats'    => $stats,
        ]);
    }

    public function create()
    {
        $this->requireAdmin();
        $plans = $this->tenantService->getPlans();

        return $this->render('admin/tenants/create', [
            'plans' => $plans,
        ]);
    }

    public function store()
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/tenants');
        }

        $data = $this->getInputData();

        if (empty($data['name'])) {
            $this->setFlash('error', 'Tenant name is required');
            $this->redirect('/admin/tenants/create');
        }

        try {
            $tenantId = $this->tenantService->create($data);
            $this->setFlash('success', 'Tenant created successfully');
            $this->redirect('/admin/tenants/' . $tenantId);
        } catch (\Throwable $e) {
            error_log('Tenant create error: ' . $e->getMessage());
            $this->setFlash('error', 'Failed to create tenant: ' . $e->getMessage());
            $this->redirect('/admin/tenants/create');
        }
    }

    public function show(int $id)
    {
        $this->requireAdmin();

        $tenant = $this->tenantService->getById($id);
        if (!$tenant) {
            $this->setFlash('error', 'Tenant not found');
            $this->redirect('/admin/tenants');
        }

        $plans = $this->tenantService->getPlans();

        return $this->render('admin/tenants/show', [
            'tenant' => $tenant,
            'plans'  => $plans,
        ]);
    }

    public function edit(int $id)
    {
        $this->requireAdmin();

        $tenant = $this->tenantService->getById($id);
        if (!$tenant) {
            $this->setFlash('error', 'Tenant not found');
            $this->redirect('/admin/tenants');
        }

        $plans = $this->tenantService->getPlans();

        return $this->render('admin/tenants/edit', [
            'tenant' => $tenant,
            'plans'  => $plans,
        ]);
    }

    public function update(int $id)
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/tenants/' . $id);
        }

        $data = $this->getInputData();

        try {
            $this->tenantService->update($id, $data);
            $this->setFlash('success', 'Tenant updated successfully');
        } catch (\Throwable $e) {
            error_log('Tenant update error: ' . $e->getMessage());
            $this->setFlash('error', 'Failed to update tenant');
        }
        $this->redirect('/admin/tenants/' . $id);
    }

    public function delete(int $id)
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/tenants');
        }

        $tenant = $this->tenantService->getById($id);
        if ($tenant && $tenant['slug'] === 'apsdreamhome') {
            $this->setFlash('error', 'Cannot delete the primary tenant');
            $this->redirect('/admin/tenants/' . $id);
        }

        $this->tenantService->delete($id);
        $this->setFlash('success', 'Tenant deleted');
        $this->redirect('/admin/tenants');
    }

    public function suspend(int $id)
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/tenants/' . $id);
        }

        $reason = $_POST['reason'] ?? 'Suspended by admin';
        $this->tenantService->suspend($id, $reason);
        $this->setFlash('success', 'Tenant suspended');
        $this->redirect('/admin/tenants/' . $id);
    }

    public function restore(int $id)
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/tenants/' . $id);
        }

        $this->tenantService->restore($id);
        $this->setFlash('success', 'Tenant restored');
        $this->redirect('/admin/tenants/' . $id);
    }

    // ── Tenant Switching (SuperAdmin Impersonation) ────────

    public function switchTenant(int $id)
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/tenants/' . $id);
        }

        // Only super_admin can switch
        if (($_SESSION['admin_role'] ?? '') !== 'super_admin') {
            $this->setFlash('error', 'Only Super Admins can switch tenants');
            $this->redirect('/admin/tenants/' . $id);
        }

        $result = $this->tenantService->switchTenant($id);
        if ($result['success']) {
            $this->setFlash('success', 'Switched to tenant: ' . $result['tenant_name']);
            $this->redirect('/admin/dashboard');
        } else {
            $this->setFlash('error', $result['error']);
            $this->redirect('/admin/tenants/' . $id);
        }
    }

    public function stopSwitch()
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/dashboard');
        }

        $result = $this->tenantService->stopTenantSwitch();
        if ($result['success']) {
            $this->setFlash('success', 'Restored to original tenant');
        } else {
            $this->setFlash('error', $result['error']);
        }
        $this->redirect('/admin/tenants');
    }

    private function getInputData(): array
    {
        return [
            'name'             => trim($_POST['name'] ?? ''),
            'slug'             => trim($_POST['slug'] ?? ''),
            'domain'           => trim($_POST['domain'] ?? ''),
            'contact_name'     => trim($_POST['contact_name'] ?? ''),
            'contact_email'    => trim($_POST['contact_email'] ?? ''),
            'contact_phone'    => trim($_POST['contact_phone'] ?? ''),
            'address'          => trim($_POST['address'] ?? ''),
            'city'             => trim($_POST['city'] ?? ''),
            'state'            => trim($_POST['state'] ?? ''),
            'plan_id'          => (int)($_POST['plan_id'] ?? 1),
            'status'           => $_POST['status'] ?? 'trial',
            'max_users'        => (int)($_POST['max_users'] ?? 1),
            'max_leads'        => (int)($_POST['max_leads'] ?? 50),
            'max_properties'   => (int)($_POST['max_properties'] ?? 10),
            'storage_limit_mb' => (int)($_POST['storage_limit_mb'] ?? 100),
            'primary_color'    => $_POST['primary_color'] ?? '#667eea',
            'secondary_color'  => $_POST['secondary_color'] ?? '#764ba2',
        ];
    }
}
