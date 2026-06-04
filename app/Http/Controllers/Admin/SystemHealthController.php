<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Services\SystemHealthService;

class SystemHealthController extends AdminController
{
    public function __construct() { parent::__construct(); }

    public function index()
    {
        $this->requireAdmin();
        $svc = new SystemHealthService($this->db);
        $report = $svc->getFullReport();
        $this->data = array_merge($this->data, [
            'page_title' => 'System Health',
            'report' => $report,
        ]);
        return $this->render('admin/features/system_health', $this->data);
    }

    public function api()
    {
        $this->requireAdmin();
        $svc = new SystemHealthService($this->db);
        return $this->jsonResponse($svc->getFullReport());
    }
}
