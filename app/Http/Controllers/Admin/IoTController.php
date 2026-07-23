<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Services\IoTService;

class IoTController extends AdminController
{
    private $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new IoTService();
    }

    public function index()
    {
        $this->requireAdmin();
        $stats = $this->service->getStats();
        $devices = $this->service->getDevices([], 1, 8)['data'];
        $automations = $this->service->getAutomations([], 1, 5)['data'];

        $this->render('admin/iot/index', [
            'page_title' => 'IoT Smart Property',
            'stats' => $stats,
            'devices' => $devices,
            'automations' => $automations,
        ]);
    }

    // ==================== DEVICE CATALOG ====================

    public function catalog()
    {
        $this->requireAdmin();
        $category = $_GET['category'] ?? '';
        $items = $this->service->getCatalog($category ? ['category' => $category] : []);
        $this->render('admin/iot/catalog', [
            'page_title' => 'IoT Device Catalog',
            'items' => $items,
            'category' => $category,
        ]);
    }

    public function catalogForm($id = null)
    {
        $this->requireAdmin();
        $item = $id ? $this->service->getCatalogItem((int)$id) : null;
        $this->render('admin/iot/catalog_form', [
            'page_title' => $item ? 'Edit Catalog Item' : 'Add Catalog Item',
            'item' => $item,
        ]);
    }

    public function catalogSave()
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->redirect('/admin/iot/catalog');

        $this->service->saveCatalogItem([
            'id' => (int)($_POST['id'] ?? 0) ?: null,
            'name' => $_POST['name'] ?? '',
            'category' => $_POST['category'] ?? 'smart',
            'manufacturer' => $_POST['manufacturer'] ?? null,
            'model' => $_POST['model'] ?? null,
            'protocol' => $_POST['protocol'] ?? 'wifi',
            'description' => $_POST['description'] ?? null,
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
        ]);
        $_SESSION['success'] = 'Catalog item saved.';
        $this->redirect('/admin/iot/catalog');
    }

    public function catalogDelete($id)
    {
        $this->requireAdmin();
        $this->service->deleteCatalogItem((int)$id);
        $_SESSION['success'] = 'Catalog item deleted.';
        $this->redirect('/admin/iot/catalog');
    }

    // ==================== DEVICES ====================

    public function devices()
    {
        $this->requireAdmin();
        $page = max(1, (int)($_GET['page'] ?? 1));
        $filters = [];
        if (!empty($_GET['status'])) $filters['status'] = $_GET['status'];
        if (!empty($_GET['category'])) $filters['category'] = $_GET['category'];
        $result = $this->service->getDevices($filters, $page, 20);
        $this->render('admin/iot/devices', [
            'page_title' => 'IoT Devices',
            'devices' => $result['data'],
            'filters' => $filters,
            'pagination' => [
                'page' => $result['page'],
                'pages' => ceil($result['total'] / $result['limit']),
                'total' => $result['total'],
            ],
        ]);
    }

    public function deviceForm($id = null)
    {
        $this->requireAdmin();
        $device = $id ? $this->service->getDevice((int)$id) : null;
        $catalog = $this->service->getCatalog(['is_active' => 1]);
        $this->render('admin/iot/device_form', [
            'page_title' => $device ? 'Edit Device' : 'Add Device',
            'device' => $device,
            'catalog' => $catalog,
        ]);
    }

    public function deviceSave()
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->redirect('/admin/iot/devices');

        $catalogId = !empty($_POST['catalog_id']) ? (int)$_POST['catalog_id'] : null;
        $catalog = $catalogId ? $this->service->getCatalogItem($catalogId) : null;

        $this->service->saveDevice([
            'id' => (int)($_POST['id'] ?? 0) ?: null,
            'catalog_id' => $catalogId,
            'property_id' => !empty($_POST['property_id']) ? (int)$_POST['property_id'] : null,
            'colony_id' => !empty($_POST['colony_id']) ? (int)$_POST['colony_id'] : null,
            'name' => $_POST['name'] ?? '',
            'device_uid' => $_POST['device_uid'] ?? null,
            'category' => $catalog['category'] ?? ($_POST['category'] ?? 'smart'),
            'status' => $_POST['status'] ?? 'offline',
            'firmware_version' => $_POST['firmware_version'] ?? null,
            'location' => $_POST['location'] ?? null,
        ]);
        $_SESSION['success'] = 'Device saved.';
        $this->redirect('/admin/iot/devices');
    }

    public function deviceDelete($id)
    {
        $this->requireAdmin();
        $this->service->deleteDevice((int)$id);
        $_SESSION['success'] = 'Device deleted.';
        $this->redirect('/admin/iot/devices');
    }

    public function deviceDetail($id)
    {
        $this->requireAdmin();
        $device = $this->service->getDevice((int)$id);
        if (!$device) { $this->redirect('/admin/iot/devices'); return; }

        $readings = $this->service->getLatestReadings((int)$id);
        $history = $this->service->getReadingHistory((int)$id, $_GET['metric'] ?? ($readings[0]['metric'] ?? 'temperature'), 50);

        $this->render('admin/iot/device_detail', [
            'page_title' => 'Device — ' . $device['name'],
            'device' => $device,
            'readings' => $readings,
            'history' => $history,
        ]);
    }

    public function recordReading()
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->json(['success' => false], 405); return; }

        $id = (int)($_POST['device_id'] ?? 0);
        $metric = $_POST['metric'] ?? '';
        $value = !empty($_POST['value']) ? (float)$_POST['value'] : 0;
        $unit = $_POST['unit'] ?? null;

        if (!$id || !$metric) { $this->json(['success' => false, 'error' => 'Invalid input'], 400); return; }

        $this->service->recordReading($id, $metric, $value, $unit);
        $this->json(['success' => true]);
    }

    // ==================== AUTOMATIONS ====================

    public function automations()
    {
        $this->requireAdmin();
        $page = max(1, (int)($_GET['page'] ?? 1));
        $result = $this->service->getAutomations([], $page, 20);
        $this->render('admin/iot/automations', [
            'page_title' => 'IoT Automations',
            'automations' => $result['data'],
            'pagination' => [
                'page' => $result['page'],
                'pages' => ceil($result['total'] / $result['limit']),
                'total' => $result['total'],
            ],
        ]);
    }

    public function automationForm($id = null)
    {
        $this->requireAdmin();
        $automation = $id ? $this->service->getAutomation((int)$id) : null;
        $devices = $this->service->getDevices([], 1, 200)['data'];
        $this->render('admin/iot/automation_form', [
            'page_title' => $automation ? 'Edit Automation' : 'Add Automation',
            'automation' => $automation,
            'devices' => $devices,
        ]);
    }

    public function automationSave()
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->redirect('/admin/iot/automations');

        $triggerConfig = [
            'metric' => $_POST['trigger_metric'] ?? 'temperature',
            'op' => $_POST['trigger_op'] ?? '>',
            'value' => !empty($_POST['trigger_value']) ? (float)$_POST['trigger_value'] : 0,
        ];
        $actionConfig = [
            'target' => $_POST['action_target'] ?? 'security',
            'message' => $_POST['action_message'] ?? '',
        ];

        $this->service->saveAutomation([
            'id' => (int)($_POST['id'] ?? 0) ?: null,
            'name' => $_POST['name'] ?? '',
            'property_id' => !empty($_POST['property_id']) ? (int)$_POST['property_id'] : null,
            'device_id' => !empty($_POST['device_id']) ? (int)$_POST['device_id'] : null,
            'trigger_type' => $_POST['trigger_type'] ?? 'threshold',
            'trigger_config' => $triggerConfig,
            'action_type' => $_POST['action_type'] ?? 'notify',
            'action_config' => $actionConfig,
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
        ]);
        $_SESSION['success'] = 'Automation saved.';
        $this->redirect('/admin/iot/automations');
    }

    public function automationDelete($id)
    {
        $this->requireAdmin();
        $this->service->deleteAutomation((int)$id);
        $_SESSION['success'] = 'Automation deleted.';
        $this->redirect('/admin/iot/automations');
    }
}
