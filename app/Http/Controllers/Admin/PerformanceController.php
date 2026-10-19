<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;
use App\Services\PerformanceService;

class PerformanceController extends AdminController
{
    private $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new PerformanceService();
    }

    public function index()
    {
        $this->requireAdmin();
        $filters = [
            'employee_id' => $_GET['employee_id'] ?? null,
            'status' => $_GET['status'] ?? null,
            'goal_type' => $_GET['goal_type'] ?? null,
            'assigned_by' => $_GET['assigned_by'] ?? null,
        ];
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = 25;
        $offset = ($page - 1) * $limit;

        $goals = $this->service->getGoals($filters, $limit, $offset);
        $employees = $this->db->fetchAll("SELECT e.id, u.name FROM employees e JOIN users u ON e.user_id = u.id WHERE e.status = 'active' ORDER BY u.name");
        $stats = $this->service->getStats();

        return $this->render('admin/performance/index', [
            'page_title' => 'Performance & Goals',
            'goals' => $goals,
            'employees' => $employees,
            'filters' => $_GET,
            'stats' => $stats,
        ]);
    }

    public function create()
    {
        $this->requireAdmin();
        $employees = $this->db->fetchAll("SELECT e.id, u.name FROM employees e JOIN users u ON e.user_id = u.id WHERE e.status = 'active' ORDER BY u.name");
        $managers = $this->db->fetchAll("SELECT id, name FROM users WHERE status = 'active' AND role IN ('admin','super_admin','manager','director','hr_manager') ORDER BY name");
        return $this->render('admin/performance/create', [
            'page_title' => 'Create Goal',
            'employees' => $employees,
            'managers' => $managers,
        ]);
    }

    public function store()
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        $data = $_POST;
        $data['assigned_by'] = $data['assigned_by'] ?? ($_SESSION['admin_id'] ?? 1);
        $result = $this->service->createGoal($data);
        $this->setFlash($result['success'] ? 'success' : 'error', $result['message']);
        $this->redirect(BASE_URL . '/admin/performance');
    }

    public function show($id)
    {
        $this->requireAdmin();
        $goal = $this->service->getGoal($id);
        if (!$goal) {
            $this->setFlash('error', 'Goal not found');
            $this->redirect(BASE_URL . '/admin/performance');
        }
        return $this->render('admin/performance/show', [
            'page_title' => 'Goal: ' . $goal['title'],
            'goal' => $goal,
        ]);
    }

    public function edit($id)
    {
        $this->requireAdmin();
        $goal = $this->service->getGoal($id);
        if (!$goal) {
            $this->setFlash('error', 'Goal not found');
            $this->redirect(BASE_URL . '/admin/performance');
        }
        $employees = $this->db->fetchAll("SELECT e.id, u.name FROM employees e JOIN users u ON e.user_id = u.id WHERE e.status = 'active' ORDER BY u.name");
        $managers = $this->db->fetchAll("SELECT id, name FROM users WHERE status = 'active' AND role IN ('admin','super_admin','manager','director','hr_manager') ORDER BY name");
        return $this->render('admin/performance/create', [
            'page_title' => 'Edit Goal',
            'goal' => $goal,
            'employees' => $employees,
            'managers' => $managers,
            'mode' => 'edit',
        ]);
    }

    public function update($id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        $result = $this->service->updateGoal($id, $_POST);
        $this->setFlash($result['success'] ? 'success' : 'error', $result['message']);
        $this->redirect(BASE_URL . '/admin/performance/' . $id);
    }

    public function recordProgress($id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        $data = ['goal_id' => $id, 'checkpoint_date' => $_POST['checkpoint_date'], 'actual_value' => (float)($_POST['actual_value'] ?? 0), 'notes' => $_POST['notes'] ?? '', 'reviewer_id' => $_SESSION['admin_id'] ?? 1];
        $result = $this->service->recordProgress($id, $data);
        $this->setFlash($result['success'] ? 'success' : 'error', $result['message']);
        $this->redirect(BASE_URL . '/admin/performance/' . $id);
    }

    public function stats()
    {
        $this->requireAdmin();
        return $this->jsonResponse($this->service->getStats());
    }
}