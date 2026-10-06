<?php
namespace App\Http\Controllers\Admin;

use App\Services\FeatureFlagService;
use App\Traits\TenantAwareTrait;

/**
 * Feature Flag Controller
 * Admin UI for managing feature flags
 */
class FeatureFlagController extends AdminController
{
    use TenantAwareTrait;

    private FeatureFlagService $flagService;

    public function __construct()
    {
        parent::__construct();
        $this->flagService = new FeatureFlagService();
    }

    /**
     * Display feature flags dashboard
     */
    public function index()
    {
        $group = $_GET['group'] ?? null;
        $search = $_GET['search'] ?? '';
        $status = $_GET['status'] ?? ''; // all, enabled, disabled

        $flags = $this->flagService->getAllFlags();
        $groups = array_unique(array_map(fn($f) => $flag['group'] ?? 'general', $flags));

        // Filter by group
        if ($group) {
            $flags = array_filter($flags, fn($f) => ($f['group'] ?? 'general') === $group);
        }

        // Filter by status
        if ($status === 'enabled') {
            $flags = array_filter($flags, fn($f) => $f['enabled']);
        } elseif ($status === 'disabled') {
            $flags = array_filter($flags, fn($f) => !$f['enabled']);
        }

        // Filter by search
        if ($search) {
            $flags = array_filter($flags, function($f) use ($search) {
                return stripos($f['key'], $search) !== false || stripos($f['name'], $search) !== false;
            });
        }

        $stats = [
            'total' => count($flags),
            'enabled' => count(array_filter($flags, fn($f) => $f['enabled'])),
            'disabled' => count(array_filter($flags, fn($f) => !$f['enabled'])),
            'groups' => count(array_unique(array_map(fn($f) => $f['group'] ?? 'general', $flags))),
        ];

        $this->render('admin/feature-flags/index', [
            'page_title' => 'Feature Flags',
            'page_description' => 'Manage feature flags and gradual rollouts',
            'flags' => array_values($flags),
            'groups' => array_values($groups),
            'current_group' => $group,
            'current_status' => $status,
            'search' => $search,
            'stats' => $stats,
        ]);
    }

    /**
     * Show create form
     */
    public function create()
    {
        $groups = ['general', 'mlm', 'payment', 'wallet', 'referral', 'booking', 'ai', 'security', 'features'];
        
        $this->render('admin/feature-flags/create', [
            'page_title' => 'Create Feature Flag',
            'page_description' => 'Create a new feature flag',
            'groups' => $groups,
        ]);
    }

    /**
     * Store new feature flag
     */
    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/feature-flags/create');
            return;
        }

        $this->validateCsrfOrFail();

        $data = [
            'name' => $_POST['name'] ?? '',
            'description' => $_POST['description'] ?? '',
            'group' => $_POST['group'] ?? 'general',
            'enabled' => !empty($_POST['enabled']),
            'rollout_percentage' => (int)($_POST['rollout_percentage'] ?? 100),
            'targeting_rules' => $_POST['targeting_rules'] ?? null,
            'start_date' => $_POST['start_date'] ?? null,
            'end_date' => $_POST['end_date'] ?? null,
        ];

        // Validate key
        $key = trim($_POST['key'] ?? '');
        if (empty($key) || !preg_match('/^[a-z0-9_]+$/', $key)) {
            $this->setFlash('error', 'Key must contain only lowercase letters, numbers, and underscores');
            $this->redirect('/admin/feature-flags/create');
            return;
        }

        $result = $this->flagService->setFlag($key, $data);

        if ($result['success']) {
            $this->setFlash('success', $result['message']);
            $this->redirect('/admin/feature-flags');
        } else {
            $this->setFlash('error', $result['message']);
            $this->redirect('/admin/feature-flags/create');
        }
    }

    /**
     * Show edit form
     */
    public function edit($key)
    {
        $flag = $this->flagService->getFlag($key);
        
        if (!$flag) {
            $this->setFlash('error', 'Feature flag not found');
            $this->redirect('/admin/feature-flags');
            return;
        }

        $groups = ['general', 'mlm', 'payment', 'wallet', 'referral', 'booking', 'ai', 'security', 'features'];
        
        $this->render('admin/feature-flags/edit', [
            'page_title' => 'Edit Feature Flag: ' . $key,
            'page_description' => $flag['description'] ?? '',
            'flag' => $flag,
            'groups' => ['general', 'mlm', 'payment', 'wallet', 'referral', 'booking', 'ai', 'security', 'features'],
        ]);
    }

    /**
     * Update feature flag
     */
    public function update($key)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect("/admin/feature-flags/edit/$key");
            return;
        }

        $this->validateCsrfOrFail();

        $data = [
            'name' => $_POST['name'] ?? '',
            'description' => $_POST['description'] ?? '',
            'group' => $_POST['group'] ?? 'general',
            'enabled' => !empty($_POST['enabled']),
            'rollout_percentage' => (int)($_POST['rollout_percentage'] ?? 100),
            'targeting_rules' => $_POST['targeting_rules'] ?? null,
            'start_date' => $_POST['start_date'] ?? null,
            'end_date' => $_POST['end_date'] ?? null,
        ];

        $result = $this->flagService->setFlag($key, $data);

        if ($result['success']) {
            $this->setFlash('success', $result['message']);
        } else {
            $this->setFlash('error', $result['message']);
        }

        $this->redirect("/admin/feature-flags/edit/$key");
    }

    /**
     * Toggle flag status
     */
    public function toggle($key)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/feature-flags');
            return;
        }

        $this->validateCsrfOrFail();

        $result = $this->flagService->toggleFlag($key);

        if ($result['success']) {
            $this->setFlash('success', $result['message']);
        } else {
            $this->setFlash('error', $result['message']);
        }

        $this->redirect('/admin/feature-flags');
    }

    /**
     * Delete feature flag
     */
    public function delete($key)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/feature-flags');
            return;
        }

        $this->validateCsrfOrFail();

        $result = $this->flagService->deleteFlag($key);

        if ($result['success']) {
            $this->setFlash('success', $result['message']);
        } else {
            $this->setFlash('error', $result['message']);
        }

        $this->redirect('/admin/feature-flags');
    }

    /**
     * API endpoint to check flag status
     */
    public function check()
    {
        header('Content-Type: application/json');
        
        $key = $_GET['key'] ?? '';
        $context = $_GET['context'] ? json_decode($_GET['context'], true) : null;
        
        $enabled = $this->flagService->isEnabled($key, $context);
        
        echo json_encode([
            'success' => true,
            'key' => $key,
            'enabled' => $enabled,
        ]);
        exit;
    }

    /**
     * Bulk operations
     */
    public function bulkAction()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/feature-flags');
            return;
        }

        $this->validateCsrfOrFail();

        $action = $_POST['action'] ?? '';
        $keys = $_POST['keys'] ?? [];

        if (empty($keys)) {
            $this->setFlash('error', 'No flags selected');
            $this->redirect('/admin/feature-flags');
            return;
        }

        $success = 0;
        foreach ($keys as $key) {
            $result = match($action) {
                'enable' => $this->flagService->setFlag($key, ['enabled' => true]),
                'disable' => $this->flagService->setFlag($key, ['enabled' => false]),
                'delete' => $this->flagService->deleteFlag($key),
                default => ['success' => false]
            };
            if ($result['success']) $success++;
        }

        if ($success === count($keys)) {
            $this->setFlash('success', "All $success flags updated successfully");
        } else {
            $this->setFlash('warning', "$success of " . count($keys) . " flags updated");
        }

        $this->redirect('/admin/feature-flags');
    }
}