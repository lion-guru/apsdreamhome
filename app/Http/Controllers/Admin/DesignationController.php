<?php
/**
 * DesignationController
 * Admin CRUD for designation definitions (role templates).
 */
namespace App\Http\Controllers\Admin;

use App\Services\DesignationService;
use App\Services\DepartmentService;

class DesignationController extends AdminController
{
    private DesignationService $desigService;
    private DepartmentService $deptService;

    public function __construct()
    {
        parent::__construct();
        $this->desigService = new DesignationService();
        $this->deptService = new DepartmentService();
    }

    /**
     * GET /admin/designations
     */
    public function index()
    {
        $this->requireAdmin();
        $departmentId = !empty($_GET['department_id']) ? (int)$_GET['department_id'] : null;
        $designations = $this->desigService->getAll($departmentId);
        $stats = $this->desigService->getStats();
        $departments = $this->deptService->getActive();
        return $this->render('admin/designations/index', [
            'page_title' => 'Designation Management',
            'designations' => $designations,
            'stats' => $stats,
            'departments' => $departments,
            'filter_dept' => $departmentId,
        ]);
    }

    /**
     * GET /admin/designations/create
     */
    public function create()
    {
        $this->requireAdmin();
        $departments = $this->deptService->getActive();
        return $this->render('admin/designations/form', [
            'page_title' => 'Create Designation',
            'designation' => null,
            'departments' => $departments,
        ]);
    }

    /**
     * POST /admin/designations/store
     */
    public function store()
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/admin/designations');
            exit;
        }
        try {
            $id = $this->desigService->create([
                'name'            => trim($_POST['name'] ?? ''),
                'department_id'   => (int)($_POST['department_id'] ?? 0),
                'level'           => (int)($_POST['level'] ?? 1),
                'min_salary'      => (float)($_POST['min_salary'] ?? 0),
                'max_salary'      => (float)($_POST['max_salary'] ?? 0),
                'sub_role'        => trim($_POST['sub_role'] ?? ''),
                'dashboard_view'  => trim($_POST['dashboard_view'] ?? '') ?: null,
                'status'          => $_POST['status'] ?? 'active',
            ]);
            $_SESSION['success'] = "Designation created successfully (ID: $id)";
        } catch (\Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }
        header('Location: ' . BASE_URL . '/admin/designations');
        exit;
    }

    /**
     * GET /admin/designations/{id}/edit
     */
    public function edit(int $id)
    {
        $this->requireAdmin();
        $designation = $this->desigService->getById($id);
        if (!$designation) {
            $_SESSION['error'] = 'Designation not found';
            header('Location: ' . BASE_URL . '/admin/designations');
            exit;
        }
        $departments = $this->deptService->getActive();
        return $this->render('admin/designations/form', [
            'page_title' => 'Edit Designation: ' . $designation['name'],
            'designation' => $designation,
            'departments' => $departments,
        ]);
    }

    /**
     * POST /admin/designations/{id}/update
     */
    public function update(int $id)
    {
        $this->requireAdmin();
        try {
            $this->desigService->update($id, [
                'name'            => trim($_POST['name'] ?? ''),
                'department_id'   => (int)($_POST['department_id'] ?? 0),
                'level'           => (int)($_POST['level'] ?? 1),
                'min_salary'      => (float)($_POST['min_salary'] ?? 0),
                'max_salary'      => (float)($_POST['max_salary'] ?? 0),
                'sub_role'        => trim($_POST['sub_role'] ?? ''),
                'dashboard_view'  => trim($_POST['dashboard_view'] ?? '') ?: null,
                'status'          => $_POST['status'] ?? 'active',
            ]);
            $_SESSION['success'] = "Designation updated successfully";
        } catch (\Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }
        header('Location: ' . BASE_URL . '/admin/designations');
        exit;
    }

    /**
     * POST /admin/designations/{id}/delete
     */
    public function delete(int $id)
    {
        $this->requireAdmin();
        try {
            $this->desigService->delete($id);
            $_SESSION['success'] = "Designation deleted";
        } catch (\Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }
        header('Location: ' . BASE_URL . '/admin/designations');
        exit;
    }
}
