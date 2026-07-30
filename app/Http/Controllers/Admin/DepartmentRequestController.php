<?php
/**
 * Department Request Controller
 * Handles cross-department request submission, viewing, and management
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Services\DepartmentRequestService;
use App\Traits\TenantAwareTrait;

class DepartmentRequestController extends AdminController
{
    use TenantAwareTrait;

    private $requestService;

    public function __construct()
    {
        parent::__construct();
        $this->requireAdmin();
        $this->requestService = new DepartmentRequestService();
    }

    /**
     * Dashboard - show all pending requests across departments
     */
    public function dashboard()
    {
        $stats = $this->requestService->getAllDepartmentsWithCounts();
        $pending = $this->requestService->getAllPending(50);

        $this->render('admin/department_requests/dashboard', [
            'title' => 'Department Requests',
            'departments' => $stats,
            'pending_requests' => $pending
        ]);
    }

    /**
     * List requests for a specific department
     */
    public function index()
    {
        $departmentCode = $_GET['department'] ?? null;
        $statusFilter = $_GET['status'] ?? null;

        $userRole = $_SESSION['admin_role'] ?? $_SESSION['role'] ?? 'admin';
        $userDept = $this->requestService->getDepartmentForUser($userRole);

        if (!$departmentCode) {
            // Default to user's department
            $departmentCode = $userDept;
        }

        $requests = $this->requestService->getRequestsForDepartment($departmentCode, [
            'status' => $statusFilter
        ]);

        $stats = $this->requestService->getStats($departmentCode);

        $this->render('admin/department_requests/index', [
            'title' => 'Department Requests',
            'department_code' => $departmentCode,
            'statusFilter' => $statusFilter,
            'requests' => $requests,
            'stats' => $stats
        ]);
    }

    /**
     * Submit a new request
     */
    public function submit()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrfOrFail();

            $data = [
                'request_type' => $_POST['request_type'] ?? 'inquiry',
                'department_code' => $_POST['department_code'],
                'title' => $_POST['title'],
                'description' => $_POST['description'] ?? '',
                'priority' => $_POST['priority'] ?? 'medium',
                'requester_id' => $_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 0,
                'requester_role' => $_SESSION['admin_role'] ?? $_SESSION['role'] ?? 'admin',
                'requester_name' => $_SESSION['admin_name'] ?? $_SESSION['user_name'] ?? 'Unknown',
                'related_entity_type' => $_POST['related_entity_type'] ?? null,
                'related_entity_id' => $_POST['related_entity_id'] ?? null,
                'due_date' => $_POST['due_date'] ?? null
            ];

            $result = $this->requestService->submitRequest($data);

            if ($result['success']) {
                $this->flashMessage('Request submitted successfully', 'success');
                redirect('/admin/department-requests');
                exit;
            } else {
                $this->flashMessage('Failed to submit request: ' . $result['error'], 'error');
            }
        }

        // Show submission form
        $departments = $this->getDepartments();

        $this->render('admin/department_requests/submit', [
            'title' => 'Submit Department Request',
            'departments' => $departments
        ]);
    }

    /**
     * View a specific request
     */
    public function show(int $id)
    {
        $request = $this->requestService->getRequest($id);

        if (!$request) {
            $this->flashMessage('Request not found', 'error');
            redirect('/admin/department-requests');
            exit;
        }

        $comments = $this->requestService->getComments($id);
        $departments = $this->getDepartments();

        $this->render('admin/department_requests/show', [
            'title' => 'Request #' . $id,
            'request' => $request,
            'comments' => $comments,
            'departments' => $departments
        ]);
    }

    /**
     * Update request status
     */
    public function updateStatus(int $id)
    {
        $this->validateCsrfOrFail();

        $status = $_POST['status'] ?? 'submitted';
        $comment = $_POST['comment'] ?? '';

        $result = $this->requestService->updateStatus(
            $id,
            $status,
            $_SESSION['admin_id'] ?? 0,
            $comment
        );

        if ($result['success']) {
            $this->flashMessage('Status updated successfully', 'success');
        } else {
            $this->flashMessage('Failed to update status', 'error');
        }

        redirect('/admin/department-requests/' . $id);
        exit;
    }

    /**
     * Assign request to user
     */
    public function assign(int $id)
    {
        $this->validateCsrfOrFail();

        $userId = $_POST['user_id'] ?? null;
        $role = $_POST['role'] ?? null;

        $result = $this->requestService->assign($id, $userId ? (int)$userId : null, $role);

        if ($result['success']) {
            $this->flashMessage('Request assigned successfully', 'success');
        } else {
            $this->flashMessage('Failed to assign request', 'error');
        }

        redirect('/admin/department-requests/' . $id);
        exit;
    }

    /**
     * Add comment to request
     */
    public function addComment(int $id)
    {
        $this->validateCsrfOrFail();

        $comment = $_POST['comment'] ?? '';
        $isInternal = $_POST['is_internal'] ?? false;

        if ($comment) {
            $this->requestService->addComment(
                $id,
                $_SESSION['admin_id'] ?? 0,
                $comment,
                $isInternal
            );
        }

        redirect('/admin/department-requests/' . $id);
        exit;
    }

    /**
     * Get my requests (requests submitted by current user)
     */
    public function myRequests()
    {
        $userId = $_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 0;
        $requests = $this->requestService->getRequestsByUser($userId);

        $this->render('admin/department_requests/my_requests', [
            'title' => 'My Requests',
            'requests' => $requests
        ]);
    }

    /**
     * Get departments list
     */
    private function getDepartments(): array
    {
        $tid = $this->tenantId();
        $where = $tid > 1 ? " WHERE tenant_id = $tid" : "";

        $sql = "SELECT code, name FROM departments WHERE status = 'active'{$where} ORDER BY name";
        $stmt = $this->db->query($sql);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }
}