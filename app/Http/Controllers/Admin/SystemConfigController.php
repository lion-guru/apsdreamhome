<?php
namespace App\Http\Controllers\Admin;

use App\Services\SystemConfigService;
use App\Traits\TenantAwareTrait;

/**
 * System Configuration Controller
 * Admin UI for managing all system configurations
 */
class SystemConfigController extends AdminController
{
    use TenantAwareTrait;

    private SystemConfigService $configService;

    public function __construct()
    {
        parent::__construct();
        $this->configService = new SystemConfigService();
    }

    /**
     * Display configuration dashboard
     */
    public function index()
    {
        $group = $_GET['group'] ?? null;
        $search = $_GET['search'] ?? '';
        
        $configs = $this->configService->getGrouped();
        $groups = $this->configService->getGroups();
        
        // Filter by group if specified
        if ($group && isset($configs[$group])) {
            $configs = [$group => $configs[$group]];
        }
        
        // Filter by search
        if ($search) {
            foreach ($configs as $g => $groupConfigs) {
                $configs[$g] = array_filter($groupConfigs, function($key) use ($search) {
                    return stripos($key, $search) !== false;
                }, ARRAY_FILTER_USE_KEY);
            }
            $configs = array_filter($configs);
        }

        $stats = [
            'total' => array_sum(array_map('count', $configs)),
            'groups' => count($configs),
            'editable' => 0,
            'public' => 0,
        ];
        
        foreach ($configs as $groupConfigs) {
            foreach ($groupConfigs as $config) {
                if ($config['is_editable']) $stats['editable']++;
                if ($config['is_public']) $stats['public']++;
            }
        }

        $this->render('admin/system-config/index', [
            'page_title' => 'System Configuration',
            'page_description' => 'Manage all system settings via UI',
            'configs' => $configs,
            'groups' => $groups,
            'current_group' => $group,
            'search' => $search,
            'stats' => $stats,
        ]);
    }

    /**
     * Show configuration form
     */
    public function edit($key)
    {
        $config = $this->configService->getMetadata($key);
        
        if (!$config) {
            $this->setFlash('error', 'Configuration not found');
            $this->redirect('/admin/system-config');
            return;
        }

        $currentValue = $this->configService->get($key);
        $config['current_value'] = $currentValue;

        $this->render('admin/system-config/edit', [
            'page_title' => 'Edit Configuration: ' . $key,
            'page_description' => $config['description'] ?? '',
            'config' => $config,
        ]);
    }

    /**
     * Update configuration
     */
    public function update($key)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect("/admin/system-config/edit/$key");
            return;
        }

        $this->validateCsrfOrFail();

        $value = $_POST['value'] ?? '';
        $type = $_POST['type'] ?? 'string';
        
        // Handle different input types
        switch ($type) {
            case 'boolean':
                $value = !empty($_POST['value']) && $_POST['value'] !== '0';
                break;
            case 'integer':
                $value = (int)($_POST['value'] ?? 0);
                break;
            case 'float':
                $value = (float)($_POST['value'] ?? 0);
                break;
            case 'json':
            case 'array':
                $value = $_POST['value'] ?? '';
                break;
            default:
                $value = (string)$_POST['value'];
        }

        $result = $this->configService->set($key, $value, $type);

        if ($result['success']) {
            $this->setFlash('success', $result['message']);
        } else {
            $this->setFlash('error', $result['message']);
        }

        $this->redirect("/admin/system-config/edit/$key");
    }

    /**
     * Bulk update configurations
     */
    public function bulkUpdate()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/system-config');
            return;
        }

        $this->validateCsrfOrFail();

        $configs = [];
        foreach ($_POST['configs'] ?? [] as $key => $data) {
            $type = $data['type'] ?? 'string';
            $value = $data['value'] ?? '';
            
            switch ($type) {
                case 'boolean':
                    $value = !empty($data['value']) && $data['value'] !== '0';
                    break;
                case 'integer':
                    $value = (int)($data['value'] ?? 0);
                    break;
                case 'float':
                    $value = (float)($data['value'] ?? 0);
                    break;
                default:
                    $value = (string)$data['value'];
            }
            
            $configs[$key] = [
                'value' => $value,
                'type' => $type,
                'group' => $data['group'] ?? null,
                'description' => $data['description'] ?? null,
            ];
        }

        $results = $this->configService->setMultiple($configs);
        
        $successCount = array_filter($results, fn($r) => $r['success']);
        $errorCount = count($results) - count($successCount);
        
        if ($errorCount === 0) {
            $this->setFlash('success', "All $successCount configurations updated successfully");
        } else {
            $this->setFlash('warning', "$successCount updated, $errorCount failed");
        }

        $this->redirect('/admin/system-config');
    }

    /**
     * Export configurations
     */
    public function export()
    {
        $configs = $this->configService->export();
        
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="system-configs-' . date('Y-m-d') . '.json"');
        echo json_encode($configs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Import configurations
     */
    public function import()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->render('admin/system-config/import', [
                'page_title' => 'Import Configuration',
                'page_description' => 'Import system configurations from JSON file',
            ]);
            return;
        }

        $this->validateCsrfOrFail();

        if (!isset($_FILES['config_file']) || $_FILES['config_file']['error'] !== UPLOAD_ERR_OK) {
            $this->setFlash('error', 'Please upload a valid JSON file');
            $this->redirect('/admin/system-config/import');
            return;
        }

        $content = file_get_contents($_FILES['config_file']['tmp_name']);
        $configs = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->setFlash('error', 'Invalid JSON file');
            $this->redirect('/admin/system-config/import');
            return;
        }

        $overwrite = !empty($_POST['overwrite']);
        $results = $this->configService->import($configs, $overwrite);
        
        $successCount = array_filter($results, fn($r) => $r['success']);
        $errorCount = count($results) - count($successCount);
        
        if ($errorCount === 0) {
            $this->setFlash('success', "All $successCount configurations imported successfully");
        } else {
            $this->setFlash('warning', "$successCount imported, $errorCount failed");
        }

        $this->redirect('/admin/system-config');
    }

    /**
     * Reset configuration to default
     */
    public function reset($key)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/system-config');
            return;
        }

        $this->validateCsrfOrFail();

        $result = $this->configService->delete($key);

        if ($result['success']) {
            $this->setFlash('success', $result['message']);
        } else {
            $this->setFlash('error', $result['message']);
        }

        $this->redirect('/admin/system-config');
    }

    /**
     * View audit history
     */
    public function audit()
    {
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = 50;
        $offset = ($page - 1) * $limit;

        $tid = $this->tenantId();
        $tenantSql = $tid > 1 ? " AND tenant_id = ?" : "";
        $params = [];
        if ($tid > 1) $params[] = $tid;

        $total = $this->db->fetchOne(
            "SELECT COUNT(*) as cnt FROM system_config_audit" . $tenantSql,
            $params
        )['cnt'] ?? 0;

        $audits = $this->db->fetchAll(
            "SELECT a.*, u.name as changed_by_name FROM system_config_audit a
             LEFT JOIN users u ON a.changed_by = u.id
             WHERE 1=1" . $tenantSql . "
             ORDER BY a.changed_at DESC
             LIMIT {$limit} OFFSET {$offset}",
            $params
        );

        $this->render('admin/system-config/audit', [
            'page_title' => 'Configuration Audit Log',
            'page_description' => 'View all configuration changes',
            'audits' => $audits,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'pages' => ceil($total / $limit),
            ],
        ]);
    }
}