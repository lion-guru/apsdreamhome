<?php
/**
 * Admin Employee Grievance / Complaint Controller
 */

namespace App\Http\Controllers\Admin;

use App\Services\EmployeeGrievanceService;
use App\Http\Controllers\BaseController;

class EmployeeGrievanceController extends BaseController
{
    private $grievanceService;

    public function __construct()
    {
        parent::__construct();
        $this->requireAdmin();
        $this->grievanceService = new EmployeeGrievanceService();
    }

    /**
     * Employee grievances list
     */
    public function index()
    {
        $filters = [
            'status' => $_GET['status'] ?? 'all',
            'category' => $_GET['category'] ?? 'all',
            'priority' => $_GET['priority'] ?? 'all',
            'assignee' => $_GET['assignee'] ?? 'all',
            'search' => $_GET['search'] ?? '',
            'date_from' => $_GET['date_from'] ?? '',
            'date_to' => $_GET['date_to'] ?? '',
        ];

        $grievances = $this->grievanceService->getAllGrievances($filters);
        $stats = $this->grievanceService->getStatistics();

        return $this->render('admin/grievances/index', [
            'page_title' => 'Employee Grievances & Complaints',
            'grievances' => $grievances,
            'stats' => $stats,
            'filters' => $filters,
            'categories' => $this->grievanceService->getCategories(),
            'priorities' => $this->grievanceService->getPriorities(),
        ]);
    }

    /**
     * Show grievance detail
     */
    public function show($id)
    {
        $grievance = $this->grievanceService->getGrievance($id);
        if (!$grievance) {
            $_SESSION['error'] = 'Grievance not found';
            header('Location: ' . BASE_URL . '/admin/grievances');
            exit;
        }

        $comments = $this->grievanceService->getComments($id);
        $assignees = $this->grievanceService->getStaffList();

        return $this->render('admin/grievances/detail', [
            'page_title' => 'Grievance Details',
            'grievance' => $grievance,
            'comments' => $comments,
            'assignees' => $assignees,
            'categories' => $this->grievanceService->getCategories(),
            'priorities' => $this->grievanceService->getPriorities(),
        ]);
    }

    /**
     * Update grievance status
     */
    public function updateStatus($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/admin/grievances/' . $id);
            exit;
        }

        $status = $_POST['status'] ?? '';
        $result = $this->grievanceService->updateStatus(
            (int)$id,
            $status,
            $_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 0
        );

        if ($this->isAjax()) {
            return $this->jsonResponse($result, $result['success'] ? 200 : 400);
        }

        $_SESSION[$result['success'] ? 'success' : 'error'] = $result['message'];
        header('Location: ' . BASE_URL . '/admin/grievances/' . $id);
        exit;
    }

    /**
     * Assign grievance
     */
    public function assign($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/admin/grievances/' . $id);
            exit;
        }

        $assigneeId = (int)($_POST['assignee_id'] ?? 0);
        $result = $this->grievanceService->assignGrievance(
            (int)$id,
            $assigneeId,
            $_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 0
        );

        if ($this->isAjax()) {
            return $this->jsonResponse($result, $result['success'] ? 200 : 400);
        }

        $_SESSION[$result['success'] ? 'success' : 'error'] = $result['message'];
        header('Location: ' . BASE_URL . '/admin/grievances/' . $id);
        exit;
    }

    /**
     * Add comment (internal)
     */
    public function addComment($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/admin/grievances/' . $id);
            exit;
        }

        $comment = $_POST['comment'] ?? '';
        $isInternal = !empty($_POST['is_internal']);

        $result = $this->grievanceService->addComment(
            (int)$id,
            $_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 0,
            $comment,
            $isInternal
        );

        if ($this->isAjax()) {
            return $this->jsonResponse($result, $result['success'] ? 200 : 400);
        }

        $_SESSION[$result['success'] ? 'success' : 'error'] = $result['message'];
        header('Location: ' . BASE_URL . '/admin/grievances/' . $id);
        exit;
    }

    private function isAjax(): bool
    {
        return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
            || strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false
            || isset($_POST['ajax']);
    }
}