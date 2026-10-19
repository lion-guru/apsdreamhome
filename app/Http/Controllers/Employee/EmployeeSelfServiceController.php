<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\BaseController;
use App\Services\SalaryAdvanceService;
use App\Services\LeaveEncashmentService;
use App\Services\OvertimeService;
use App\Services\EmployeeAssetService;
use App\Services\AnnouncementService;
use App\Services\ShiftSwapService;
use App\Services\HelpdeskService;
use App\Services\EmployeeGrievanceService;
use App\Services\TrainingService;
use App\Services\OrgChartService;
use App\Services\EngagementSurveyService;
use App\Services\OnboardingService;
use App\Services\OffboardingService;
use App\Services\HolidayCalendarService;
use Exception;
use Throwable;

class EmployeeSelfServiceController extends BaseController
{
    use \App\Traits\TenantAwareTrait;

    private $advanceService;
    private $encashmentService;
    private $overtimeService;
    private $assetService;
    private $announcementService;
    private $shiftSwapService;
    private $helpdeskService;
    private $grievanceService;
    private $trainingService;
    private $orgChartService;
    private $surveyService;
    private $onboardingService;
    private $offboardingService;
    private $holidayCalendarService;

    public function __construct()
    {
        parent::__construct();
        $this->layout = 'layouts/employee';
        $this->advanceService = new SalaryAdvanceService();
        $this->encashmentService = new LeaveEncashmentService();
        $this->overtimeService = new OvertimeService();
        $this->assetService = new EmployeeAssetService();
        $this->announcementService = new AnnouncementService();
        $this->shiftSwapService = new ShiftSwapService();
        $this->helpdeskService = new HelpdeskService();
        $this->grievanceService = new EmployeeGrievanceService();
        $this->trainingService = new TrainingService();
        $this->orgChartService = new OrgChartService();
        $this->surveyService = new EngagementSurveyService();
        $this->onboardingService = new OnboardingService();
        $this->offboardingService = new OffboardingService();
        $this->holidayCalendarService = new HolidayCalendarService();
    }

    /**
     * Require employee to be logged in (has employee_id in session)
     */
    protected function requireEmployeeLogin(): void
    {
        $this->requireLogin();
        if (empty($_SESSION['employee_id'])) {
            // Try to find employee by user_id or admin_id
            $userId = $_SESSION['user_id'] ?? $_SESSION['admin_id'] ?? 0;
            if ($userId) {
                $emp = $this->db->fetchOne("SELECT id FROM employees WHERE user_id = ? LIMIT 1", [$userId]);
                if ($emp) {
                    $_SESSION['employee_id'] = (int)$emp['id'];
                    return;
                }
                // Allow admin/executive testing accounts to access self-service
                $role = $_SESSION['role'] ?? '';
                if (in_array($role, self::ADMIN_ROLES, true) || !empty($_SESSION['admin_id'])) {
                    $_SESSION['employee_id'] = (int)$userId;
                    return;
                }
            }
            $this->setFlash('error', 'Employee access required');
            $this->redirect(BASE_URL . '/employee/login');
            exit;
        }
    }

    // ... existing methods ...

    /**
     * Salary Advance/Loan Dashboard
     */
    public function advances()
    {
        $this->requireEmployeeLogin();
        $employeeId = $_SESSION['employee_id'];

        $advances = $this->advanceService->getEmployeeAdvances($employeeId);
        $pendingRepayments = $this->advanceService->getPendingRepayments($employeeId);

        // Calculate summary
        $totalAdvances = count($advances);
        $totalPending = 0;
        $totalRepaid = 0;
        foreach ($advances as $a) {
            if ($a['status'] === 'pending' || $a['status'] === 'approved') {
                $totalPending += $a['amount'];
            }
            $totalRepaid += ($a['amount'] - $a['remaining_balance']);
        }

        return $this->render('employee/self_service/advances', [
            'page_title' => 'Salary Advances & Loans',
            'advances' => $advances,
            'pending_repayments' => $pendingRepayments,
            'summary' => [
                'total_advances' => $totalAdvances,
                'total_pending' => $totalPending,
                'total_repaid' => $totalRepaid
            ]
        ]);
    }

    /**
     * Apply for salary advance/loan
     */
    public function advanceApply()
    {
        $this->requireEmployeeLogin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'type' => $_POST['type'] ?? 'advance',
                'amount' => (float)($_POST['amount'] ?? 0),
                'installments' => (int)($_POST['installments'] ?? 1),
                'interest_rate' => (float)($_POST['interest_rate'] ?? 0),
                'reason' => $_POST['reason'] ?? ''
            ];

            $result = $this->advanceService->requestAdvance($_SESSION['employee_id'], $data);
            $_SESSION[$result['success'] ? 'success' : 'error'] = $result['message'];
            $this->redirect(BASE_URL . '/employee/self-service/advances');
            exit;
        }

        return $this->render('employee/self_service/advance_apply', [
            'page_title' => 'Request Salary Advance / Loan',
            'page_description' => 'Request a salary advance or loan with flexible repayment options'
        ]);
    }

    /**
     * View advance details
     */
    public function advanceDetail($id)
    {
        $this->requireEmployeeLogin();
        $employeeId = $_SESSION['employee_id'];

        $advance = $this->advanceService->getAdvance($id);
        if (!$advance || $advance['employee_id'] != $employeeId) {
            $_SESSION['error'] = 'Advance not found';
            $this->redirect(BASE_URL . '/employee/self-service/advances');
            exit;
        }

        $repayments = $this->advanceService->getRepaymentSchedule($id);

        return $this->render('employee/self_service/advance_detail', [
            'page_title' => 'Advance Details',
            'advance' => $advance,
            'repayments' => $repayments
        ]);
    }

    /**
     * Leave Encashment Dashboard
     */
    public function encashment()
    {
        $this->requireEmployeeLogin();
        $employeeId = $_SESSION['employee_id'];

        $policy = $this->encashmentService->getEncashmentPolicy($employeeId);
        $requests = $this->encashmentService->getEmployeeRequests($employeeId);

        return $this->render('employee/self_service/encashment', [
            'page_title' => 'Leave Encashment',
            'policy' => $policy,
            'requests' => $requests
        ]);
    }

    /**
     * Apply for leave encashment
     */
    public function encashmentApply()
    {
        $this->requireEmployeeLogin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'leave_type_id' => (int)($_POST['leave_type_id'] ?? 0),
                'days_to_encash' => (float)($_POST['days_to_encash'] ?? 0)
            ];

            $result = $this->encashmentService->requestEncashment($_SESSION['employee_id'], $data);
            $_SESSION[$result['success'] ? 'success' : 'error'] = $result['message'];
            $this->redirect(BASE_URL . '/employee/self-service/encashment');
            exit;
        }

        $employeeId = $_SESSION['employee_id'];
        $policy = $this->encashmentService->getEncashmentPolicy($employeeId);

        return $this->render('employee/self_service/encashment_apply', [
            'page_title' => 'Apply Leave Encashment',
            'policy' => $policy
        ]);
    }

    /**
     * Overtime Dashboard
     */
    public function overtime()
    {
        $this->requireEmployeeLogin();
        $employeeId = $_SESSION['employee_id'];

        $requests = $this->overtimeService->getEmployeeRequests($employeeId);
        $policy = $this->overtimeService->getEmployeePolicy($employeeId);

        // Calculate summary
        $totalRequests = count($requests);
        $totalHours = 0;
        $totalAmount = 0;
        $pendingCount = 0;

        foreach ($requests as $r) {
            $totalHours += $r['hours'];
            $totalAmount += $r['amount'];
            if ($r['status'] === 'pending') $pendingCount++;
        }

        return $this->render('employee/self_service/overtime', [
            'page_title' => 'Overtime Management',
            'requests' => $requests,
            'policy' => $policy,
            'summary' => [
                'total_requests' => $totalRequests,
                'total_hours' => $totalHours,
                'total_amount' => $totalAmount,
                'pending_count' => $pendingCount
            ]
        ]);
    }

    /**
     * Apply for overtime
     */
    public function overtimeApply()
    {
        $this->requireEmployeeLogin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'date' => $_POST['date'] ?? date('Y-m-d'),
                'start_time' => $_POST['start_time'] ?? '',
                'end_time' => $_POST['end_time'] ?? '',
                'reason' => $_POST['reason'] ?? ''
            ];

            $result = $this->overtimeService->requestOvertime($_SESSION['employee_id'], $data);
            $_SESSION[$result['success'] ? 'success' : 'error'] = $result['message'];
            $this->redirect(BASE_URL . '/employee/self-service/overtime');
            exit;
        }

        $employeeId = $_SESSION['employee_id'];
        $policy = $this->overtimeService->getEmployeePolicy($employeeId);

        return $this->render('employee/self_service/overtime_apply', [
            'page_title' => 'Apply Overtime',
            'policy' => $policy
        ]);
    }

    /**
     * Overtime request detail
     */
    public function overtimeDetail($id)
    {
        $this->requireEmployeeLogin();
        $employeeId = $_SESSION['employee_id'];

        $requests = $this->overtimeService->getEmployeeRequests($employeeId);
        $request = null;
        foreach ($requests as $r) {
            if ($r['id'] == $id) {
                $request = $r;
                break;
            }
        }

        if (!$request) {
            $_SESSION['error'] = 'Overtime request not found';
            $this->redirect(BASE_URL . '/employee/self-service/overtime');
            exit;
        }

        return $this->render('employee/self_service/overtime_detail', [
            'page_title' => 'Overtime Request Details',
            'request' => $request
        ]);
    }

    /**
     * Asset Assignments Dashboard
     */
    public function assets()
    {
        $this->requireEmployeeLogin();
        $employeeId = $_SESSION['employee_id'];

        $assets = $this->assetService->getEmployeeAssets($employeeId);
        $summary = $this->assetService->getAssetSummary($employeeId);

        return $this->render('employee/self_service/assets', [
            'page_title' => 'My Asset Assignments',
            'assets' => $assets,
            'summary' => $summary
        ]);
    }

    /**
     * Report asset issue (lost/damaged)
     */
    public function assetReportIssue()
    {
        $this->requireEmployeeLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(BASE_URL . '/employee/self-service/assets');
            exit;
        }

        $result = $this->assetService->reportIssue(
            (int)($_POST['assignment_id'] ?? 0),
            $_SESSION['employee_id'],
            $_POST['issue_type'] ?? '',
            $_POST['notes'] ?? ''
        );

        $_SESSION[$result['success'] ? 'success' : 'error'] = $result['message'];
        $this->redirect(BASE_URL . '/employee/self-service/assets');
        exit;
    }

    /**
     * Announcements Dashboard
     */
    public function announcements()
    {
        $this->requireEmployeeLogin();
        $employeeId = $_SESSION['employee_id'];

        // Get announcements for this employee
        $announcements = $this->announcementService->getEmployeeAnnouncements($employeeId);

        // Mark as read
        $unreadIds = array_filter($announcements, fn($a) => !$a['read']);
        foreach ($unreadIds as $a) {
            $this->announcementService->markAsRead($a['id'], $employeeId);
        }

        return $this->render('employee/self_service/announcements', [
            'page_title' => 'Company Announcements',
            'announcements' => $announcements
        ]);
    }

    /**
     * Mark announcement as read
     */
    public function announcementMarkRead($id)
    {
        $this->requireEmployeeLogin();
        $employeeId = $_SESSION['employee_id'];

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonError('Invalid request', 400);
        }

        $token = $_POST['csrf_token'] ?? '';
        if (!$this->validateCsrfToken($token)) {
            return $this->jsonError('Invalid CSRF token', 400);
        }

        try {
            $this->announcementService->markAsRead((int)$id, $_SESSION['employee_id']);
            return $this->jsonResponse(['success' => true, 'message' => 'Marked as read']);
        } catch (Exception $e) {
            return $this->jsonError('Failed: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Shift Swaps
     */
    public function shiftSwaps()
    {
        $this->requireEmployeeLogin();
        $employeeId = $_SESSION['employee_id'];

        $swaps = $this->shiftSwapService->getEmployeeSwaps($employeeId);
        $pendingSwaps = array_filter($swaps, fn($s) => $s['status'] === 'pending');
        $completedSwaps = array_filter($swaps, fn($s) => $s['status'] === 'completed');

        return $this->render('employee/self_service/shift_swaps', [
            'page_title' => 'Shift Swaps',
            'swaps' => $swaps,
            'pending_count' => count($pendingSwaps),
            'completed_count' => count($completedSwaps)
        ]);
    }

    /**
     * Request shift swap
     */
    public function shiftSwapRequest()
    {
        $this->requireEmployeeLogin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $result = $this->shiftSwapService->requestSwap($_SESSION['employee_id'], $_POST);
            $_SESSION[$result['success'] ? 'success' : 'error'] = $result['message'];
            $this->redirect(BASE_URL . '/employee/self-service/shift-swaps');
            exit;
        }

        // Get colleagues in same department
        $employeeId = $_SESSION['employee_id'];
        $colleagues = $this->db->fetchAll("
            SELECT e.id, e.name, e.designation
            FROM employees e
            JOIN users u ON e.user_id = u.id
            WHERE e.id != ? AND e.status = 'active' AND u.status = 'active'
            ORDER BY e.name
        ", [$employeeId]);

        return $this->render('employee/self_service/shift_swap_apply', [
            'page_title' => 'Request Shift Swap',
            'colleagues' => $colleagues
        ]);
    }

    /**
     * Respond to shift swap request
     */
    public function shiftSwapRespond($id)
    {
        $this->requireEmployeeLogin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $result = $this->shiftSwapService->respondToSwap(
                $id,
                $_SESSION['employee_id'],
                $_POST['action'] ?? '',
                $_POST['rejection_reason'] ?? ''
            );
            $_SESSION[$result['success'] ? 'success' : 'error'] = $result['message'];
            $this->redirect(BASE_URL . '/employee/self-service/shift-swaps');
            exit;
        }

        $swap = $this->shiftSwapService->getSwap($id);
        if (!$swap || ($swap['requester_id'] != $_SESSION['employee_id'] && $swap['target_id'] != $_SESSION['employee_id'])) {
            $_SESSION['error'] = 'Swap request not found';
            $this->redirect(BASE_URL . '/employee/self-service/shift-swaps');
            exit;
        }

        return $this->render('employee/self_service/shift_swap_respond', [
            'page_title' => 'Respond to Shift Swap',
            'swap' => $swap
        ]);
    }

    /**
     * Helpdesk/Tickets
     */
    public function helpdesk()
    {
        $this->requireEmployeeLogin();
        $employeeId = $_SESSION['employee_id'];

        $tickets = $this->helpdeskService->getEmployeeTickets($employeeId);

        return $this->render('employee/self_service/helpdesk', [
            'page_title' => 'Helpdesk & Support',
            'tickets' => $tickets
        ]);
    }

    /**
     * Create new ticket
     */
    public function ticketCreate()
    {
        $this->requireEmployeeLogin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $result = $this->helpdeskService->createTicket($_SESSION['employee_id'], $_POST);
            $_SESSION[$result['success'] ? 'success' : 'error'] = $result['message'];
            $this->redirect(BASE_URL . '/employee/self-service/helpdesk');
            exit;
        }

        return $this->render('employee/self_service/ticket_create', [
            'page_title' => 'Create Support Ticket',
            'categories' => ['it', 'hr', 'finance', 'facilities', 'security', 'other']
        ]);
    }

    /**
     * Ticket detail
     */
    public function ticketDetail($id)
    {
        $this->requireEmployeeLogin();
        $employeeId = $_SESSION['employee_id'];

        $ticket = $this->helpdeskService->getTicket($id);
        if (!$ticket || $ticket['employee_id'] != $employeeId) {
            $_SESSION['error'] = 'Ticket not found';
            $this->redirect(BASE_URL . '/employee/self-service/helpdesk');
            exit;
        }

        $comments = $this->helpdeskService->getTicketComments($id);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $result = $this->helpdeskService->addComment($id, $employeeId, $_POST);
            $_SESSION[$result['success'] ? 'success' : 'error'] = $result['message'];
            $this->redirect(BASE_URL . "/employee/self-service/helpdesk/$id");
            exit;
        }

        return $this->render('employee/self_service/ticket_detail', [
            'page_title' => 'Ticket #' . $ticket['ticket_number'],
            'ticket' => $ticket,
            'comments' => $comments
        ]);
    }

    /**
     * Employee Grievances Dashboard
     */
    public function grievances()
    {
        $this->requireEmployeeLogin();
        $employeeId = $_SESSION['employee_id'];

        $grievances = $this->grievanceService->getEmployeeGrievances($employeeId);

        return $this->render('employee/self_service/grievances', [
            'page_title' => 'My Grievances',
            'grievances' => $grievances
        ]);
    }

    /**
     * Create new grievance
     */
    public function grievanceCreate()
    {
        $this->requireEmployeeLogin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $result = $this->grievanceService->submitGrievance($_SESSION['employee_id'], $_POST);
            $_SESSION[$result['success'] ? 'success' : 'error'] = $result['message'];
            if ($result['success']) {
                $this->redirect(BASE_URL . '/employee/self-service/grievances');
            } else {
                $this->redirect(BASE_URL . '/employee/self-service/grievances/create');
            }
            exit;
        }

        return $this->render('employee/self_service/grievance_create', [
            'page_title' => 'Submit Grievance',
            'categories' => $this->grievanceService->getCategories(),
            'priorities' => $this->grievanceService->getPriorities(),
        ]);
    }

    /**
     * Grievance detail
     */
    public function grievanceDetail($id)
    {
        $this->requireEmployeeLogin();
        $employeeId = $_SESSION['employee_id'];

        $grievance = $this->grievanceService->getGrievance($id);
        if (!$grievance || $grievance['employee_id'] != $employeeId) {
            $_SESSION['error'] = 'Grievance not found';
            $this->redirect(BASE_URL . '/employee/self-service/grievances');
            exit;
        }

        $comments = $this->grievanceService->getComments($id);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $result = $this->grievanceService->addComment($id, $employeeId, $_POST['comment'] ?? '', false);
            $_SESSION[$result['success'] ? 'success' : 'error'] = $result['message'];
            $this->redirect(BASE_URL . "/employee/self-service/grievances/$id");
            exit;
        }

        return $this->render('employee/self_service/grievance_detail', [
            'page_title' => 'Grievance #' . $grievance['grievance_number'],
            'grievance' => $grievance,
            'comments' => $comments
        ]);
    }

    /**
     * Add comment to grievance
     */
    public function grievanceComment($id)
    {
        $this->requireEmployeeLogin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $result = $this->grievanceService->addComment(
                $id,
                $_SESSION['employee_id'],
                $_POST['comment'] ?? '',
                false
            );
            $_SESSION[$result['success'] ? 'success' : 'error'] = $result['message'];
            $this->redirect(BASE_URL . "/employee/self-service/grievances/$id");
            exit;
        }

        $this->redirect(BASE_URL . "/employee/self-service/grievances/$id");
    }

    /**
     * Close grievance with rating
     */
    public function grievanceClose($id)
    {
        $this->requireEmployeeLogin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $result = $this->grievanceService->closeWithRating(
                $id,
                $_SESSION['employee_id'],
                (int)($_POST['rating'] ?? 0),
                $_POST['feedback'] ?? ''
            );
            $_SESSION[$result['success'] ? 'success' : 'error'] = $result['message'];
            $this->redirect(BASE_URL . '/employee/self-service/grievances');
            exit;
        }

        $grievance = $this->grievanceService->getGrievance($id);
        if (!$grievance || $grievance['employee_id'] != $_SESSION['employee_id']) {
            $_SESSION['error'] = 'Grievance not found';
            $this->redirect(BASE_URL . '/employee/self-service/grievances');
            return;
        }

        return $this->render('employee/self_service/grievance_close', [
            'page_title' => 'Close Grievance',
            'grievance' => $grievance
        ]);
    }

    // ──────────────────────────────────────────────
    // TRAINING / L&D
    // ──────────────────────────────────────────────

    public function training()
    {
        $this->requireEmployeeLogin();
        $employeeId = $_SESSION['employee_id'];

        $assignments = $this->trainingService->getEmployeeAssignments($employeeId);

        // Separate by status
        $inProgress = array_filter($assignments, fn($a) => $a['status'] === 'in_progress');
        $completed = array_filter($assignments, fn($a) => $a['status'] === 'completed');
        $assigned = array_filter($assignments, fn($a) => $a['status'] === 'assigned' || $a['status'] === 'overdue');

        return $this->render('employee/self_service/training', [
            'page_title' => 'My Training',
            'in_progress' => array_values($inProgress),
            'completed' => array_values($completed),
            'assigned' => array_values($assigned),
            'summary' => [
                'total' => count($assignments),
                'in_progress' => count($inProgress),
                'completed' => count($completed),
                'overdue' => count(array_filter($assigned, fn($a) => $a['status'] === 'overdue'))
            ]
        ]);
    }

    public function trainingCourseDetail($id)
    {
        $this->requireEmployeeLogin();
        $employeeId = $_SESSION['employee_id'];

        $assignments = $this->trainingService->getEmployeeAssignments($employeeId);
        $assignment = null;
        foreach ($assignments as $a) {
            if ($a['id'] == $id) {
                $assignment = $a;
                break;
            }
        }

        if (!$assignment) {
            $_SESSION['error'] = 'Course assignment not found';
            $this->redirect(BASE_URL . '/employee/self-service/training');
            exit;
        }

        return $this->render('employee/self_service/training_detail', [
            'page_title' => $assignment['course_title'],
            'assignment' => $assignment
        ]);
    }

    public function trainingUpdateProgress()
    {
        $this->requireEmployeeLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonError('Invalid request', 400);
        }

        $token = $_POST['csrf_token'] ?? '';
        if (!$this->validateCsrfToken($token)) {
            return $this->jsonError('Invalid CSRF token', 400);
        }

        $assignmentId = (int)($_POST['assignment_id'] ?? 0);
        $progress = (float)($_POST['progress'] ?? 0);

        $assignments = $this->trainingService->getEmployeeAssignments($_SESSION['employee_id']);
        $found = false;
        foreach ($assignments as $a) {
            if ($a['id'] == $assignmentId) {
                $found = true;
                break;
            }
        }

        if (!$found) {
            return $this->jsonError('Assignment not found', 404);
        }

        $result = $this->trainingService->updateAssignmentProgress($assignmentId, ['progress' => $progress]);
        return $this->jsonResponse($result);
    }

    public function certifications()
    {
        $this->requireEmployeeLogin();
        $employeeId = $_SESSION['employee_id'];

        $certifications = $this->trainingService->getEmployeeCertifications($employeeId);

        return $this->render('employee/self_service/certifications', [
            'page_title' => 'My Certifications',
            'certifications' => $certifications
        ]);
    }

    // ──────────────────────────────────────────────
    // ORG CHART (VIEW ONLY)
    // ──────────────────────────────────────────────

    public function orgChart()
    {
        $this->requireEmployeeLogin();
        $employeeId = $_SESSION['employee_id'];

        // Find employee's position in org chart
        $myNode = $this->db->fetch("SELECT * FROM org_chart_nodes WHERE employee_id = ? AND tenant_id = ? AND is_active = 1", [$employeeId, $this->tenantId()]);

        $tree = $this->orgChartService->getTree();
        $departments = $this->orgChartService->getDepartments();

        return $this->render('employee/self_service/org_chart', [
            'page_title' => 'Organizational Chart',
            'tree' => $tree,
            'my_node' => $myNode,
            'departments' => $departments
        ]);
    }

    public function orgChartDepartment($dept)
    {
        $this->requireEmployeeLogin();

        $tree = $this->orgChartService->getDepartmentChart($dept);

        return $this->render('employee/self_service/org_chart_department', [
            'page_title' => 'Department: ' . $dept,
            'tree' => $tree,
            'department' => $dept
        ]);
    }

    // ──────────────────────────────────────────────
    // ENGAGEMENT SURVEYS
    // ──────────────────────────────────────────────

    public function surveys()
    {
        $this->requireEmployeeLogin();
        $employeeId = $_SESSION['employee_id'];

        $availableSurveys = $this->surveyService->getEmployeeAvailableSurveys($employeeId);

        return $this->render('employee/self_service/surveys', [
            'page_title' => 'Engagement Surveys',
            'surveys' => $availableSurveys
        ]);
    }

    public function surveyTake($id)
    {
        $this->requireEmployeeLogin();
        $employeeId = $_SESSION['employee_id'];

        $survey = $this->surveyService->getSurvey($id);
        if (!$survey) {
            $_SESSION['error'] = 'Survey not found';
            $this->redirect(BASE_URL . '/employee/self-service/surveys');
            exit;
        }

        // Check if survey is active and within date range
        if ($survey['status'] !== 'active') {
            $_SESSION['error'] = 'Survey is not active';
            $this->redirect(BASE_URL . '/employee/self-service/surveys');
            exit;
        }

        if ($survey['start_date'] && $survey['start_date'] > date('Y-m-d')) {
            $_SESSION['error'] = 'Survey has not started yet';
            $this->redirect(BASE_URL . '/employee/self-service/surveys');
            exit;
        }

        if ($survey['end_date'] && $survey['end_date'] < date('Y-m-d')) {
            $_SESSION['error'] = 'Survey has ended';
            $this->redirect(BASE_URL . '/employee/self-service/surveys');
            exit;
        }

        // Check if already responded (unless allow_multiple)
        $existingResponse = $this->surveyService->getEmployeeResponse($id, $employeeId);
        if ($existingResponse && !$survey['allow_multiple']) {
            $_SESSION['info'] = 'You have already completed this survey';
            $this->redirect(BASE_URL . '/employee/self-service/surveys');
            exit;
        }

        $questions = $survey['questions'] ?? [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $answers = [];
            foreach ($questions as $index => $question) {
                $key = 'q_' . $index;
                if (isset($_POST[$key])) {
                    $answers[$index] = is_array($_POST[$key]) ? $_POST[$key] : $_POST[$key];
                }
            }

            $completionTime = (int)($_POST['completion_time_seconds'] ?? 0);

            $result = $this->surveyService->submitResponse($id, $employeeId, $answers);
            $_SESSION[$result['success'] ? 'success' : 'error'] = $result['message'];
            if ($result['success']) {
                $this->redirect(BASE_URL . '/employee/self-service/surveys');
            } else {
                $this->redirect(BASE_URL . '/employee/self-service/surveys/take/' . $id);
            }
            exit;
        }

        return $this->render('employee/self_service/survey_take', [
            'page_title' => $survey['title'],
            'survey' => $survey,
            'questions' => $questions
        ]);
    }

    public function surveyResults($id)
    {
        return $this->render('employee/self_service/survey_results', [
            'page_title' => 'Survey Results: ' . $survey['title'],
            'survey' => $survey,
            'response' => $response
        ]);
    }

    // ──────────────────────────────────────────────
    // ONBOARDING
    // ──────────────────────────────────────────────

    public function onboarding()
    {
        $this->requireEmployeeLogin();
        $employeeId = $_SESSION['employee_id'];

        $progress = $this->onboardingService->getOnboardingProgress($employeeId);
        $templates = $this->onboardingService->getTemplates();

        return $this->render('employee/self_service/onboarding', [
            'page_title' => 'My Onboarding',
            'progress' => $progress,
            'templates' => $templates
        ]);
    }

    public function onboardingUpdate()
    {
        $this->requireEmployeeLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonError('Invalid request', 400);
        }

        $token = $_POST['csrf_token'] ?? '';
        if (!$this->validateCsrfToken($token)) {
            return $this->jsonError('Invalid CSRF token', 400);
        }

        $result = $this->onboardingService->updateItemStatus(
            (int)($_POST['item_id'] ?? 0),
            (int)($_POST['employee_id'] ?? 0),
            $_POST['status'] ?? '',
            ['notes' => $_POST['notes'] ?? '']
        );
        return $this->jsonResponse($result);
    }

    // ──────────────────────────────────────────────
    // OFFBOARDING
    // ──────────────────────────────────────────────

    public function offboarding()
    {
        $this->requireEmployeeLogin();
        $employeeId = $_SESSION['employee_id'];

        $progress = $this->offboardingService->getOffboardingProgress($employeeId);

        return $this->render('employee/self_service/offboarding', [
            'page_title' => 'My Offboarding',
            'progress' => $progress
        ]);
    }

    public function offboardingUpdate()
    {
        $this->requireEmployeeLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonError('Invalid request', 400);
        }

        $token = $_POST['csrf_token'] ?? '';
        if (!$this->validateCsrfToken($token)) {
            return $this->jsonError('Invalid CSRF token', 400);
        }

        $result = $this->offboardingService->updateItemStatus(
            (int)($_POST['item_id'] ?? 0),
            (int)($_POST['employee_id'] ?? 0),
            $_POST['status'] ?? '',
            ['notes' => $_POST['notes'] ?? '']
        );
        return $this->jsonResponse($result);
    }

    // ──────────────────────────────────────────────
    // HOLIDAY CALENDAR
    // ──────────────────────────────────────────────

    public function holidayCalendar()
    {
        $this->requireEmployeeLogin();
        $employeeId = $_SESSION['employee_id'];

        $filters = [
            'type' => $_GET['type'] ?? null,
            'region' => $_GET['region'] ?? null,
            'year' => $_GET['year'] ?? null,
            'month' => $_GET['month'] ?? null,
            'is_active' => $_GET['is_active'] ?? null
        ];

        $holidays = $this->holidayCalendarService->getHolidays($filters);
        $stats = $this->holidayCalendarService->getStats();
        $holidayTypes = $this->holidayCalendarService->getHolidayTypes();

        return $this->render('employee/self_service/holiday_calendar', [
            'page_title' => 'Holiday Calendar',
            'holidays' => $holidays,
            'stats' => $stats,
            'holiday_types' => $holidayTypes,
            'filters' => $filters
        ]);
    }
}