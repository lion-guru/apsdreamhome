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
            $this->setFlash('error', 'Failed to create tenant. Please check the application logs.');
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

    // ── Tenant Onboarding Wizard (Multi-Step) ──────────────

    public function onboard()
    {
        $this->requireAdmin();
        $plans = $this->tenantService->getPlans();
        $step  = max(1, min(5, (int)($_GET['step'] ?? 1)));

        // Persist wizard state across steps
        if (empty($_SESSION['onboard_data'])) {
            $_SESSION['onboard_data'] = [];
        }

        return $this->render('admin/tenants/onboard', [
            'plans'      => $plans,
            'step'       => $step,
            'wizardData' => $_SESSION['onboard_data'],
        ]);
    }

    public function onboardSave()
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/tenants/onboard');
        }

        $step = (int)($_POST['step'] ?? 1);
        $data = &$_SESSION['onboard_data'];

        switch ($step) {
            case 1: // Company Info
                $data['name']          = trim($_POST['name'] ?? '');
                $data['slug']          = trim($_POST['slug'] ?? '');
                $data['domain']        = trim($_POST['domain'] ?? '');
                $data['contact_name']  = trim($_POST['contact_name'] ?? '');
                $data['contact_email'] = trim($_POST['contact_email'] ?? '');
                $data['contact_phone'] = trim($_POST['contact_phone'] ?? '');
                $data['address']       = trim($_POST['address'] ?? '');
                $data['city']          = trim($_POST['city'] ?? '');
                $data['state']         = trim($_POST['state'] ?? '');
                if (empty($data['name'])) {
                    $this->setFlash('error', 'Company name is required');
                    $this->redirect('/admin/tenants/onboard?step=1');
                }
                break;
            case 2: // Branding
                $data['primary_color']   = $_POST['primary_color'] ?? '#667eea';
                $data['secondary_color'] = $_POST['secondary_color'] ?? '#764ba2';
                $data['logo_url']        = trim($_POST['logo_url'] ?? '');
                break;
            case 3: // Plan
                $data['plan_id']          = (int)($_POST['plan_id'] ?? 1);
                $data['max_users']        = (int)($_POST['max_users'] ?? 1);
                $data['max_leads']        = (int)($_POST['max_leads'] ?? 50);
                $data['max_properties']   = (int)($_POST['max_properties'] ?? 10);
                $data['storage_limit_mb'] = (int)($_POST['storage_limit_mb'] ?? 100);
                break;
            case 4: // Invites
                $data['invite_emails'] = array_filter(array_map('trim', explode("\n", $_POST['invite_emails'] ?? '')));
                break;
        }

        if ($step < 5) {
            $this->redirect('/admin/tenants/onboard?step=' . ($step + 1));
        }
        // Step 5 = review, no save needed
    }

    public function onboardLaunch()
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/tenants/onboard');
        }

        $data = $_SESSION['onboard_data'] ?? [];
        if (empty($data['name'])) {
            $this->setFlash('error', 'Please complete the wizard first');
            $this->redirect('/admin/tenants/onboard?step=1');
        }

        $data['status'] = 'active';
        $data['plan_id'] = $data['plan_id'] ?? 1;
        $data['max_users'] = $data['max_users'] ?? 1;
        $data['max_leads'] = $data['max_leads'] ?? 50;
        $data['max_properties'] = $data['max_properties'] ?? 10;
        $data['storage_limit_mb'] = $data['storage_limit_mb'] ?? 100;
        $data['primary_color'] = $data['primary_color'] ?? '#667eea';
        $data['secondary_color'] = $data['secondary_color'] ?? '#764ba2';

        try {
            $tenantId = $this->tenantService->create($data);
            unset($_SESSION['onboard_data']);

            // Send invite emails if any
            $invites = $data['invite_emails'] ?? [];
            foreach ($invites as $email) {
                if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    // Log invite (actual email sending requires SMTP config)
                    error_log("Tenant invite: {$email} for tenant #{$tenantId}");
                }
            }

            $this->setFlash('success', 'Tenant "' . htmlspecialchars($data['name']) . '" created successfully!');
            $this->redirect('/admin/tenants/' . $tenantId);
        } catch (\Throwable $e) {
            error_log('Tenant onboard launch error: ' . $e->getMessage());
            $this->setFlash('error', 'Failed to create tenant. Please check the application logs.');
            $this->redirect('/admin/tenants/onboard?step=5');
        }
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
