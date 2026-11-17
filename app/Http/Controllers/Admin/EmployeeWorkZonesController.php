<?php

namespace App\Http\Controllers\Admin;

use App\Services\HR\GeoCheckinService;

class EmployeeWorkZonesController extends AdminController
{
    private GeoCheckinService $service;

    public function __construct()
    {
        parent::__construct();
        $this->layout = 'layouts/admin';
        $this->service = new GeoCheckinService();
    }

    public function index()
    {
        $this->requireAdmin();

        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);

        $this->render('admin/hrm/work_zones', [
            'page_title' => 'GPS Work Zones — Location Manager',
            'zones' => $this->service->listAllZones(),
            'flash_success' => is_array($flash) && !empty($flash['success']) ? $flash['success'] : (is_string($flash) ? $flash : null),
            'flash_error' => is_array($flash) && !empty($flash['error']) ? $flash['error'] : null,
        ]);
    }

    public function store()
    {
        $this->requireAdmin();

        $res = $this->service->createZone($_POST);
        if (!empty($res['success'])) {
            $_SESSION['flash'] = ['success' => 'Zone added. Employees on their next app open will punch against the new coordinates.'];
        } else {
            $_SESSION['flash'] = ['error' => $res['error'] ?? 'Failed to add zone'];
        }

        header('Location: ' . BASE_URL . '/admin/hrm/work-zones');
        exit;
    }

    public function update(int $id = 0)
    {
        $this->requireAdmin();
        $id = (int) ($_POST['id'] ?? $id);

        $res = $this->service->updateZone($id, $_POST);
        $_SESSION['flash'] = !empty($res['success'])
            ? ['success' => 'Zone updated.']
            : ['error' => $res['error'] ?? 'Failed to update zone'];

        header('Location: ' . BASE_URL . '/admin/hrm/work-zones');
        exit;
    }

    public function toggle(int $id = 0)
    {
        $this->requireAdmin();
        $id = (int) ($_POST['id'] ?? $id);
        $active = !empty($_POST['active']);

        $res = $this->service->setZoneActive($id, $active);
        $_SESSION['flash'] = !empty($res['success'])
            ? ['success' => $active ? 'Zone enabled.' : 'Zone disabled.']
            : ['error' => $res['error'] ?? 'Failed to change zone state'];

        header('Location: ' . BASE_URL . '/admin/hrm/work-zones');
        exit;
    }
}
