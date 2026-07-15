<?php
/**
 * DepartmentController
 * Admin CRUD for organizational departments.
 */
namespace App\Http\Controllers\Admin;

use App\Services\DepartmentService;

class DepartmentController extends AdminController
{
    private DepartmentService $deptService;

    public function __construct()
    {
        parent::__construct();
        $this->deptService = new DepartmentService();
    }

    /**
     * GET /admin/departments
     */
    public function index()
    {
        $this->requireAdmin();
        $departments = $this->deptService->getAll();
        $stats = $this->deptService->getStats();
        return $this->render('admin/departments/index', [
            'page_title' => 'Department Management',
            'departments' => $departments,
            'stats' => $stats,
        ]);
    }

    /**
     * GET /admin/departments/create
     */
    public function create()
    {
        $this->requireAdmin();
        $departments = $this->deptService->getActive();
        $users = $this->db->fetchAll("SELECT id, name FROM users WHERE role IN ('admin','manager','employee') ORDER BY name") ?? [];
        return $this->render('admin/departments/form', [
            'page_title' => 'Create Department',
            'department' => null,
            'departments' => $departments,
            'users' => $users,
        ]);
    }

    /**
     * POST /admin/departments/store
     */
    public function store()
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/admin/departments');
            exit;
        }
        try {
            $id = $this->deptService->create([
                'name'           => trim($_POST['name'] ?? ''),
                'code'           => trim($_POST['code'] ?? ''),
                'description'    => trim($_POST['description'] ?? ''),
                'head_user_id'   => !empty($_POST['head_user_id']) ? (int)$_POST['head_user_id'] : null,
                'parent_dept_id' => !empty($_POST['parent_dept_id']) ? (int)$_POST['parent_dept_id'] : null,
                'dept_budget'    => (float)($_POST['dept_budget'] ?? 0),
                'status'         => $_POST['status'] ?? 'active',
            ]);
            $_SESSION['success'] = "Department created successfully (ID: $id)";
        } catch (\Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }
        header('Location: ' . BASE_URL . '/admin/departments');
        exit;
    }

    /**
     * GET /admin/departments/{id}/edit
     */
    public function edit(int $id)
    {
        $this->requireAdmin();
        $department = $this->deptService->getById($id);
        if (!$department) {
            $_SESSION['error'] = 'Department not found';
            header('Location: ' . BASE_URL . '/admin/departments');
            exit;
        }
        $departments = $this->deptService->getActive();
        $users = $this->db->fetchAll("SELECT id, name FROM users WHERE role IN ('admin','manager','employee') ORDER BY name") ?? [];
        return $this->render('admin/departments/form', [
            'page_title' => 'Edit Department: ' . $department['name'],
            'department' => $department,
            'departments' => $departments,
            'users' => $users,
        ]);
    }

    /**
     * POST /admin/departments/{id}/update
     */
    public function update(int $id)
    {
        $this->requireAdmin();
        try {
            $this->deptService->update($id, [
                'name'           => trim($_POST['name'] ?? ''),
                'code'           => strtoupper(trim($_POST['code'] ?? '')),
                'description'    => trim($_POST['description'] ?? ''),
                'head_user_id'   => !empty($_POST['head_user_id']) ? (int)$_POST['head_user_id'] : null,
                'parent_dept_id' => !empty($_POST['parent_dept_id']) ? (int)$_POST['parent_dept_id'] : null,
                'dept_budget'    => (float)($_POST['dept_budget'] ?? 0),
                'status'         => $_POST['status'] ?? 'active',
            ]);
            $_SESSION['success'] = "Department updated successfully";
        } catch (\Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }
        header('Location: ' . BASE_URL . '/admin/departments');
        exit;
    }

    /**
     * POST /admin/departments/{id}/delete
     */
    public function delete(int $id)
    {
        $this->requireAdmin();
        try {
            $this->deptService->delete($id);
            $_SESSION['success'] = "Department deleted";
        } catch (\Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }
        header('Location: ' . BASE_URL . '/admin/departments');
        exit;
    }
}
