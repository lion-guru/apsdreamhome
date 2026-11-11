<?php

namespace App\Http\Controllers\Admin;

use App\Services\AIEmployeeService;

class AIEmployeeController extends AdminController
{
    private $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new AIEmployeeService();
    }

    public function index()
    {
        $this->requireAdmin();
        $db = \App\Core\Database\Database::getInstance();
        $employees = $db->fetchAll("SELECT * FROM ai_employees ORDER BY id") ?: [];
        $stats = $this->service->getStats();
        $queue = $this->service->getQueue(0, 50);

        return $this->render('admin/ai_employee/index', [
            'page_title' => 'AI Employee Desk',
            'employees' => $employees,
            'stats' => $stats,
            'queue' => $queue,
        ]);
    }

    public function setStatus($id)
    {
        $this->requireAdmin();
        $this->service->setStatus((int) $id, $_POST['status'] ?? 'paused');
        if (method_exists($this, 'setFlash')) {
            $this->setFlash('success', 'Employee status updated');
        }
        header('Location: ' . (defined('BASE_URL') ? rtrim(BASE_URL, '/') : '') . '/admin/ai-employee');
        exit;
    }

    public function enqueueLead()
    {
        $this->requireAdmin();
        $employeeId = (int) ($_POST['employee_id'] ?? 0);
        $type = $_POST['type'] ?? 'qualify_lead';
        $leadId = (int) ($_POST['lead_id'] ?? 0);
        if (!in_array($type, ['qualify_lead', 'followup_draft', 'notify_stale'], true)) {
            $type = 'qualify_lead';
        }
        if ($employeeId > 0 && $leadId > 0) {
            $this->service->enqueue($employeeId, $type, $leadId, []);
            if (method_exists($this, 'setFlash')) {
                $this->setFlash('success', "Task queued for lead #$leadId");
            }
        }
        header('Location: ' . (defined('BASE_URL') ? rtrim(BASE_URL, '/') : '') . '/admin/ai-employee');
        exit;
    }

    public function runNow($id)
    {
        $this->requireAdmin();
        $r = $this->service->runNext((int) $id);
        if (method_exists($this, 'setFlash')) {
            $this->setFlash($r['success'] ? 'success' : 'error', $r['success'] ? 'Task completed' : ($r['error'] ?? 'Failed'));
        }
        header('Location: ' . (defined('BASE_URL') ? rtrim(BASE_URL, '/') : '') . '/admin/ai-employee');
        exit;
    }
}
