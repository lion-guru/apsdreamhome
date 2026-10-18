<?php
/**
 * Admin Employee Grievance / Complaint Controller
 */

namespace App\Http\Controllers\Admin;

use App\Services\EmployeeGrievanceService;
use App\Core\Controller\BaseController;

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
            $this->redirect(BASE_URL . '/admin/grievances');
            return;
        }

        $comments = $this->grievanceService->getComments($id);

        return $this->render('admin/grievances/detail', [
            'page_title' => 'Grievance Details',
            'grievance' => $grievance,
            'comments' => $comments,
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
            $this->redirect(BASE_URL . '/admin/grievances/' . $id);
            return;
        }

        $result = $this->grievanceService->updateStatus(
            $id,
            $_POST['status'] ?? '',
            $_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 0
        );

        $_SESSION[$result['success'] ? 'success' : 'error'] = $result['message'];
        $this->redirect(BASE_URL . '/admin/grievances/' . $id);
    }

    /**
     * Assign grievance
     */
    public function assign($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(BASE_URL . '/admin/grievances/' . $id);
            return;
        }

        $result = $this->grievanceService->assignGrievance(
            $id,
            (int)($_POST['assignee_id'] ?? 0),
            $_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 0
        );

        $_SESSION[$result['success'] ? 'success' : 'error'] = $result['message'];
        $this->redirect(BASE_URL . '/admin/grievances/' . $id);
    }

    /**
     * Add comment (internal)
     */
    public function addComment($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(BASE_URL . '/admin/grievances/' . $id);
            return;
        }

        $result = $this->grievanceService->addComment(
            $id,
            $_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 0,
            $_POST['comment'] ?? '',
            true // isInternal = true for admin comments
        );

        $_SESSION[$result['success'] ? 'success' : 'error'] = $result['message'];
        $this->redirect(BASE_URL . '/admin/grievances/' . $id);
    }
}