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
                $emp = $this->db->fetchOne("SELECT id FROM employee_profiles WHERE user_id = ? LIMIT 1", [$userId]);
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
        } catch (\Throwable $e) {
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
            SELECT e.id, u.name, e.designation
            FROM employee_profiles e
            JOIN users u ON e.user_id = u.id
            WHERE e.id != ? AND u.status = 'active'
            ORDER BY u.name
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

    // ──────────────────────────────────────────────
    // EXPENSE CLAIMS
    // ──────────────────────────────────────────────

    public function expenses()
    {
        $this->requireEmployeeLogin();
        $employeeId = $_SESSION['employee_id'];

        $claims = $this->service = new \App\Services\ExpenseReimbursementService();
        $claims = $this->service->getEmployeeClaims($employeeId);
        $categories = $this->service->getCategories(['is_active' => 1]);

        return $this->render('employee/self_service/expenses', [
            'page_title' => 'My Expense Claims',
            'claims' => $claims,
            'categories' => $categories,
        ]);
    }

    public function expenseStore()
    {
        $this->requireEmployeeLogin();
        $this->validateCsrfOrFail();

        $data = [
            'category_id' => (int)($_POST['category_id'] ?? 0),
            'claim_date' => $_POST['claim_date'],
            'description' => $_POST['description'] ?? '',
            'items' => $_POST['items'] ?? [],
        ];

        $result = $this->service->submitClaim($_SESSION['employee_id'], $data);
        $_SESSION[$result['success'] ? 'success' : 'error'] = $result['message'];
        $this->redirect(BASE_URL . '/employee/self-service/expenses');
    }

    public function expenseDetail($id)
    {
        $this->requireEmployeeLogin();
        $claim = $this->service->getClaim($id);
        if (!$claim || $claim['employee_id'] != $_SESSION['employee_id']) {
            $_SESSION['error'] = 'Claim not found';
            $this->redirect(BASE_URL . '/employee/self-service/expenses');
        }
        return $this->render('employee/self_service/expense_detail', [
            'page_title' => 'Claim: ' . $claim['claim_number'],
            'claim' => $claim,
        ]);
    }

    // ──────────────────────────────────────────────
    // PERFORMANCE & GOALS
    // ──────────────────────────────────────────────

    public function performance()
    {
        $this->requireEmployeeLogin();
        $employeeId = $_SESSION['employee_id'];

        $performanceService = new \App\Services\PerformanceService();
        $goals = $performanceService->getEmployeeGoals($employeeId);

        return $this->render('employee/self_service/performance', [
            'page_title' => 'My Performance & Goals',
            'goals' => $goals,
        ]);
    }

    public function performanceProgress()
    {
        $this->requireEmployeeLogin();
        $this->validateCsrfOrFail();

        $performanceService = new \App\Services\PerformanceService();
        $result = $performanceService->recordProgress((int)($_POST['goal_id'] ?? 0), [
            'checkpoint_date' => $_POST['checkpoint_date'],
            'actual_value' => (float)($_POST['actual_value'] ?? 0),
            'notes' => $_POST['notes'] ?? '',
            'reviewer_id' => $_SESSION['employee_id'],
        ]);
        return $this->jsonResponse($result);
    }

    // ──────────────────────────────────────────────
    // PAYSLIPS
    // ──────────────────────────────────────────────

    public function payslips()
    {
        $this->requireEmployeeLogin();
        $employeeId = $_SESSION['employee_id'];

        $payrollService = new \App\Services\PayrollService();
        // Get payslips for this employee
        $payslips = $this->db->fetchAll("
            SELECT pi.*, pr.run_name AS run_number, pr.pay_period_start AS period_start, pr.pay_period_end AS period_end, pr.pay_date
            FROM payroll_items pi
            JOIN payroll_runs pr ON pi.run_id = pr.id
            WHERE pi.employee_id = ? AND pi.tenant_id = ? AND pi.status = 'paid'
            ORDER BY pr.pay_period_end DESC
        ", [$employeeId, $this->tenantId()]);

        return $this->render('employee/self_service/payslips', [
            'page_title' => 'My Payslips',
            'payslips' => $payslips,
        ]);
    }

    public function downloadPayslip($id)
    {
        $this->requireEmployeeLogin();
        $employeeId = $_SESSION['employee_id'];

        $payslip = $this->db->fetch("
            SELECT pi.*, pr.run_name AS run_number, pr.pay_period_start AS period_start, pr.pay_period_end AS period_end, pr.pay_date,
                   u.name as employee_name, u.email as employee_email,
                   e.employee_code, e.department, e.designation,
                   s.basic_salary, s.hra, s.da, s.conveyance, s.medical_allowance, s.special_allowance,
                   s.pf_employee, s.tds, s.professional_tax
            FROM payroll_items pi
            JOIN payroll_runs pr ON pi.run_id = pr.id
            JOIN employees e ON pi.employee_id = e.id
            JOIN users u ON e.user_id = u.id
            LEFT JOIN salary_structures s ON pi.salary_structure_id = s.id
            WHERE pi.id = ? AND pi.employee_id = ? AND pi.tenant_id = ? AND pi.status = 'paid'
        ", [$id, $employeeId, $this->tenantId()]);

        if (!$payslip) {
            $_SESSION['error'] = 'Payslip not found';
            $this->redirect(BASE_URL . '/employee/self-service/payslips');
        }

        // Generate PDF using TCPDF
        require_once __DIR__ . '/../../../vendor/tecnickcom/tcpdf/tcpdf.php';

        $pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetCreator('APS Dream Home');
        $pdf->SetAuthor('APS Dream Home HR System');
        $pdf->SetTitle('Payslip - ' . $payslip['run_number']);
        $pdf->SetSubject('Salary Payslip');
        $pdf->SetKeywords('payslip, salary, HR, APS Dream Home');

        $pdf->SetHeaderData('assets/images/logo.png', 30, 'APS Dream Home', 'Salary Payslip - ' . $payslip['run_number']);
        $pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        $pdf->AddPage();

        // Company header
        $html = '<table style="width:100%; border:0;">';
        $html .= '<tr><td style="width:60%;"><strong style="font-size:18px;color:#1e3a8a;">APS Dream Home</strong><br/>';
        $html .= '<span style="font-size:10px;color:#64748b;">HR Management System</span></td>';
        $html .= '<td style="width:40%;text-align:right;"><strong style="font-size:16px;color:#1e3a8a;">SALARY PAYSLIP</strong><br/>';
        $html .= '<span style="font-size:10px;color:#64748b;">' . $payslip['run_number'] . '</span></td></tr>';
        $html .= '</table><hr style="border:1px solid #e2e8f0;"/>';

        // Employee info
        $html .= '<table style="width:100%; border:0; margin-top:10px;">';
        $html .= '<tr><td style="width:25%;"><strong>Employee Name:</strong></td><td style="width:25%;">' . htmlspecialchars($payslip['employee_name']) . '</td>';
        $html .= '<td style="width:25%;"><strong>Employee Code:</strong></td><td style="width:25%;">' . htmlspecialchars($payslip['employee_code'] ?? '—') . '</td></tr>';
        $html .= '<tr><td><strong>Email:</strong></td><td>' . htmlspecialchars($payslip['employee_email'] ?? '—') . '</td>';
        $html .= '<td><strong>Department:</strong></td><td>' . htmlspecialchars($payslip['department'] ?? '—') . '</td></tr>';
        $html .= '<tr><td><strong>Designation:</strong></td><td>' . htmlspecialchars($payslip['designation'] ?? '—') . '</td>';
        $html .= '<td><strong>Employee Code:</strong></td><td>' . htmlspecialchars($payslip['employee_code'] ?? '—') . '</td></tr>';
        $html .= '</table><hr style="border:1px solid #e2e8f0; margin:10px 0;"/>';

        // Pay period
        $html .= '<table style="width:100%; border:0; margin-top:10px;">';
        $html .= '<tr><td style="width:25%;"><strong>Pay Period:</strong></td><td style="width:25%;">' . date('d M Y', strtotime($payslip['period_start'])) . ' to ' . date('d M Y', strtotime($payslip['period_end'])) . '</td>';
        $html .= '<td style="width:25%;"><strong>Pay Date:</strong></td><td style="width:25%;">' . date('d M Y', strtotime($payslip['pay_date'])) . '</td></tr>';
        $html .= '<tr><td><strong>Run Number:</strong></td><td>' . htmlspecialchars($payslip['run_number']) . '</td>';
        $html .= '<td><strong>Pay Date:</strong></td><td>' . date('d M Y', strtotime($payslip['pay_date'])) . '</td></tr>';
        $html .= '</table><hr style="border:1px solid #e2e8f0; margin:10px 0;"/>';

        // Earnings
        $html .= '<table style="width:100%; border-collapse:collapse; margin-top:10px;">';
        $html .= '<tr style="background:#1e3a8a;color:white;"><th style="padding:8px;border:1px solid #e2e8f0;">Earnings</th><th style="padding:8px;border:1px solid #e2e8f0;">Amount (₹)</th></tr>';
        
        $basic = $payslip['basic_salary'] ?? 0;
        $hra = $payslip['hra'] ?? 0;
        $da = $payslip['da'] ?? 0;
        $conveyance = $payslip['conveyance'] ?? 0;
        $medical = $payslip['medical_allowance'] ?? 0;
        $special = $payslip['special_allowance'] ?? 0;
        $gross = $basic + $hra + $da + $conveyance + $medical + $special;

        $html .= '<tr><td style="padding:8px;border:1px solid #e2e8f0;">Basic Salary</td><td style="padding:8px;border:1px solid #e2e8f0;text-align:right;">' . number_format($basic, 2) . '</td></tr>';
        $html .= '<tr style="background:#f8fafc;"><td style="padding:8px;border:1px solid #e2e8f0;">HRA</td><td style="padding:8px;border:1px solid #e2e8f0;text-align:right;">' . number_format($hra, 2) . '</td></tr>';
        $html .= '<tr><td style="padding:8px;border:1px solid #e2e8f0;">DA</td><td style="padding:8px;border:1px solid #e2e8f0;text-align:right;">' . number_format($da, 2) . '</td></tr>';
        $html .= '<tr style="background:#f8fafc;"><td style="padding:8px;border:1px solid #e2e8f0;">Conveyance</td><td style="padding:8px;border:1px solid #e2e8f0;text-align:right;">' . number_format($conveyance, 2) . '</td></tr>';
        $html .= '<tr><td style="padding:8px;border:1px solid #e2e8f0;">Medical Allowance</td><td style="padding:8px;border:1px solid #e2e8f0;text-align:right;">' . number_format($medical, 2) . '</td></tr>';
        $html .= '<tr style="background:#f8fafc;"><td style="padding:8px;border:1px solid #e2e8f0;">Special Allowance</td><td style="padding:8px;border:1px solid #e2e8f0;text-align:right;">' . number_format($special, 2) . '</td></tr>';
        $html .= '<tr style="background:#1e3a8a;color:white;font-weight:bold;"><td style="padding:8px;border:1px solid #e2e8f0;">Gross Earnings</td><td style="padding:8px;border:1px solid #e2e8f0;text-align:right;">' . number_format($gross, 2) . '</td></tr>';
        $html .= '</table><br/>';

        // Deductions
        $pf = $payslip['pf_employee'] ?? 0;
        $tds = $payslip['tds'] ?? 0;
        $profTax = $payslip['professional_tax'] ?? 0;
        $totalDeductions = $pf + $tds + $profTax;
        $netPay = $gross - $totalDeductions;

        $html .= '<table style="width:100%; border-collapse:collapse; margin-top:10px;">';
        $html .= '<tr style="background:#dc2626;color:white;"><th style="padding:8px;border:1px solid #e2e8f0;">Deductions</th><th style="padding:8px;border:1px solid #e2e8f0;">Amount (₹)</th></tr>';
        $html .= '<tr><td style="padding:8px;border:1px solid #e2e8f0;">PF (Employee)</td><td style="padding:8px;border:1px solid #e2e8f0;text-align:right;">' . number_format($pf, 2) . '</td></tr>';
        $html .= '<tr style="background:#f8fafc;"><td style="padding:8px;border:1px solid #e2e8f0;">TDS</td><td style="padding:8px;border:1px solid #e2e8f0;text-align:right;">' . number_format($tds, 2) . '</td></tr>';
        $html .= '<tr><td style="padding:8px;border:1px solid #e2e8f0;">Professional Tax</td><td style="padding:8px;border:1px solid #e2e8f0;text-align:right;">' . number_format($profTax, 2) . '</td></tr>';
        $html .= '<tr style="background:#dc2626;color:white;font-weight:bold;"><td style="padding:8px;border:1px solid #e2e8f0;">Total Deductions</td><td style="padding:8px;border:1px solid #e2e8f0;text-align:right;">' . number_format($totalDeductions, 2) . '</td></tr>';
        $html .= '</table><br/>';

        // Net Pay
        $html .= '<table style="width:100%; border-collapse:collapse; margin-top:10px;">';
        $html .= '<tr style="background:#16a34a;color:white;font-weight:bold;font-size:14px;"><td style="padding:12px;border:1px solid #e2e8f0;">Net Pay</td><td style="padding:12px;border:1px solid #e2e8f0;text-align:right;">₹' . number_format($netPay, 2) . '</td></tr>';
        $html .= '</table><br/><br/>';

        // Footer
        $html .= '<hr style="border:1px solid #e2e8f0; margin:20px 0;"/>';
        $html .= '<table style="width:100%; border:0;">';
        $html .= '<tr><td style="width:50%;"><strong>Prepared by:</strong> HR Department, APS Dream Home</td>';
        $html .= '<td style="width:50%;text-align:right;"><strong>Authorized Signatory</strong></td></tr>';
        $html .= '</table>';

        $pdf->writeHTML($html, true, false, true, false, '');

        $pdf->Output('payslip_' . $payslip['run_number'] . '.pdf', 'D');
        exit;
    }

    // ──────────────────────────────────────────────
    // DOCUMENTS
    // ──────────────────────────────────────────────

    public function documents()
    {
        $this->requireEmployeeLogin();
        $employeeId = $_SESSION['employee_id'];

        $docVaultService = new \App\Services\DocumentVaultService();
        $documents = $docVaultService->getEmployeeDocuments($employeeId);
        $categories = $docVaultService->getCategories(['is_active' => 1]);

        return $this->render('employee/self_service/documents', [
            'page_title' => 'My Documents',
            'documents' => $documents,
            'categories' => $categories,
        ]);
    }

    public function documentUpload()
    {
        $this->requireEmployeeLogin();
        $this->validateCsrfOrFail();

        if (!isset($_FILES['document_file']) || $_FILES['document_file']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['error'] = 'No file uploaded';
            $this->redirect(BASE_URL . '/employee/self-service/documents');
        }

        $docVaultService = new \App\Services\DocumentVaultService();
        $data = [
            'category_id' => (int)($_POST['category_id'] ?? 0),
            'document_name' => $_POST['document_name'] ?? '',
            'issue_date' => $_POST['issue_date'] ?? null,
            'expiry_date' => $_POST['expiry_date'] ?? null,
            'issuing_authority' => $_POST['issuing_authority'] ?? null,
            'document_number_ref' => $_POST['document_number_ref'] ?? null,
            'is_confidential' => !empty($_POST['is_confidential']),
            'access_level' => $_POST['access_level'] ?? 'self',
        ];

        try {
            $result = $docVaultService->uploadDocument($_SESSION['employee_id'], $data, $_FILES['document_file']);
            $_SESSION[$result['success'] ? 'success' : 'error'] = $result['message'];
        } catch (\Throwable $e) {
            $_SESSION['error'] = 'Upload failed: ' . $e->getMessage();
        }
        $this->redirect(BASE_URL . '/employee/self-service/documents');
    }

    public function documentVerifyRequest($id)
    {
        $this->requireEmployeeLogin();
        $this->validateCsrfOrFail();

        $docVaultService = new \App\Services\DocumentVaultService();
        $doc = $docVaultService->getDocument($id);
        if (!$doc || $doc['employee_id'] != $_SESSION['employee_id']) {
            $_SESSION['error'] = 'Document not found';
            $this->redirect(BASE_URL . '/employee/self-service/documents');
        }

        $result = $docVaultService->verifyDocument($id, $_SESSION['employee_id'], 'pending', 'Employee requested verification');
        $_SESSION[$result['success'] ? 'success' : 'error'] = $result['message'];
        $this->redirect(BASE_URL . '/employee/self-service/documents');
    }

    // ──────────────────────────────────────────────
    // LEAVE CALENDAR
    // ──────────────────────────────────────────────

    public function leaveCalendar()
    {
        $this->requireEmployeeLogin();
        $employeeId = $_SESSION['employee_id'];

        $holidayService = new \App\Services\HolidayCalendarService();
        $filters = [
            'type' => $_GET['type'] ?? null,
            'region' => $_GET['region'] ?? null,
            'year' => $_GET['year'] ?? null,
            'month' => $_GET['month'] ?? null,
            'is_active' => $_GET['is_active'] ?? null
        ];

        $holidays = $holidayService->getHolidays($filters);
        $stats = $holidayService->getStats();
        $holidayTypes = $holidayService->getHolidayTypes();

        // Get employee leaves
        $leaves = $this->db->fetchAll("
            SELECT el.*, lt.name as leave_type_name
            FROM employee_leaves el
            LEFT JOIN leave_types lt ON el.leave_type_id = lt.id
            WHERE el.employee_id = ? AND el.status IN ('approved', 'pending')
            ORDER BY el.start_date
        ", [$employeeId]);

        return $this->render('employee/self_service/leave_calendar', [
            'page_title' => 'Leave & Holiday Calendar',
            'holidays' => $holidays,
            'leaves' => $leaves,
            'stats' => $stats,
            'holiday_types' => $holidayTypes,
            'filters' => $_GET
        ]);
    }

    // ──────────────────────────────────────────────
    // DOCUMENT DETAIL & DOWNLOAD
    // ──────────────────────────────────────────────

    public function documentDetail($id)
    {
        $this->requireEmployeeLogin();
        $employeeId = $_SESSION["employee_id"];

        $docVaultService = new \App\Services\DocumentVaultService();
        $document = $docVaultService->getDocument($id);

        if (!$document || $document["employee_id"] != $employeeId) {
            $_SESSION["error"] = "Document not found";
            $this->redirect(BASE_URL . "/employee/self-service/documents");
        }

        $verificationHistory = $this->db->fetchAll("
            SELECT dv.*, u.name as verifier_name 
            FROM document_verification_log dv
            LEFT JOIN users u ON dv.verified_by = u.id
            WHERE dv.document_id = ? AND dv.tenant_id = ?
            ORDER BY dv.verified_at DESC
        ", [$id, $this->tenantId()]);

        return $this->render("employee/self_service/document_detail", [
            "page_title" => "Document: " . $document["document_name"],
            "document" => $document,
            "verificationHistory" => $verificationHistory,
        ]);
    }

    public function documentDownload($id)
    {
        $this->requireEmployeeLogin();
        $employeeId = $_SESSION["employee_id"];

        $docVaultService = new \App\Services\DocumentVaultService();
        $document = $docVaultService->getDocument($id);

        if (!$document || $document["employee_id"] != $employeeId) {
            $_SESSION["error"] = "Document not found";
            $this->redirect(BASE_URL . "/employee/self-service/documents");
        }

        if (!empty($document["file_path"]) && file_exists(PUBLIC_PATH . "/" . $document["file_path"])) {
            header("Content-Type: " . $document["mime_type"]);
            header("Content-Disposition: attachment; filename=\"" . basename($document["file_path"]) . "\"");
            header("Content-Length: " . $document["file_size"]);
            readfile(PUBLIC_PATH . "/" . $document["file_path"]);
            exit;
        } else {
            $_SESSION["error"] = "File not found";
            $this->redirect(BASE_URL . "/employee/self-service/documents");
        }
    }

    // ──────────────────────────────────────────────
    // HUB + DASHBOARD
    // ──────────────────────────────────────────────

    /**
     * Self-service hub landing page.
     * GET /employee/self-service
     */
    public function index()
    {
        $this->requireEmployeeLogin();
        $employeeId = (int)$_SESSION['employee_id'];

        // The hub and the dashboard render the same view — reuse dashboard() so
        // every variable the view expects is always present.
        return $this->dashboard();
    }

    /**
     * Self-service summary dashboard.
     * GET /employee/self-service/dashboard
     */
    public function dashboard()
    {
        $this->requireEmployeeLogin();
        $employeeId = (int)$_SESSION['employee_id'];
        $tid = $this->tenantId();
        $fy = $this->currentFinancialYear();

        // leave balances
        $leaveBalances = $this->db->fetchAll(
            "SELECT lb.*, lt.name AS leave_type_name, lt.code AS leave_type_code
             FROM employee_leave_balances lb
             LEFT JOIN leave_types lt ON lt.id = lb.leave_type_id
             WHERE lb.employee_id = ? AND lb.tenant_id = ? AND lb.year = ?
             ORDER BY lt.name",
            [$employeeId, $tid, (int)date('Y')]
        );
        $totalLeave = 0.0;
        foreach ($leaveBalances as $lb) {
            $totalLeave += (float)($lb['remaining_days'] ?? 0);
        }

        // attendance stats for the current month
        $month = date('Y-m');
        $attendanceStats = $this->db->fetchOne(
            "SELECT COUNT(*) AS total_days,
                    SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) AS present_days,
                    SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) AS absent_days,
                    SUM(CASE WHEN status = 'leave' THEN 1 ELSE 0 END) AS leave_days,
                    SUM(CASE WHEN status = 'half_day' THEN 1 ELSE 0 END) AS half_days,
                    COALESCE(SUM(hours_worked), 0) AS total_hours,
                    COALESCE(AVG(hours_worked), 0) AS avg_hours,
                    COALESCE(SUM(late_minutes), 0) AS late_minutes
             FROM employee_attendance
             WHERE employee_id = ? AND tenant_id = ? AND DATE_FORMAT(attendance_date, '%Y-%m') = ?",
            [$employeeId, $tid, $month]
        ) ?: [];

        // pending salary advance amount
        $advances = $this->advanceService->getEmployeeAdvances($employeeId);
        $pendingAmt = 0.0;
        foreach ($advances as $a) {
            if (in_array($a['status'] ?? '', ['pending', 'approved'], true)) {
                $pendingAmt += (float)($a['amount'] ?? 0);
            }
        }

        // payslips
        $payslips = $this->db->fetchAll(
            "SELECT * FROM salary_payments
             WHERE employee_id = ? AND tenant_id = ?
             ORDER BY payment_date DESC, id DESC LIMIT 6",
            [$employeeId, $tid]
        );

        // reimbursements
        $reimbursements = $this->db->fetchAll(
            "SELECT * FROM employee_reimbursements
             WHERE employee_id = ? AND tenant_id = ?
             ORDER BY expense_date DESC, id DESC LIMIT 6",
            [$employeeId, $tid]
        );

        // declared tax regime
        $regimeRow = $this->db->fetchOne(
            "SELECT * FROM employee_tax_regime WHERE employee_id = ? AND tenant_id = ? AND financial_year = ? ORDER BY id DESC LIMIT 1",
            [$employeeId, $tid, $fy]
        );
        // The dashboard view expects an array (row) with a 'regime' key.
        $taxRegime = $regimeRow ?: null;

        return $this->render('employee/self_service/dashboard', [
            'page_title' => 'Self Service Dashboard',
            'employeeId' => $employeeId,
            'current_fy' => $fy,
            'leave_balances' => $leaveBalances,
            'totalLeave' => $totalLeave,
            'attendance_stats' => $attendanceStats,
            'pendingAmt' => $pendingAmt,
            'payslips' => $payslips,
            'reimbursements' => $reimbursements,
            'tax_regime' => $taxRegime,
        ]);
    }

    // ──────────────────────────────────────────────
    // STORE / ACTION ENDPOINTS (routes exist, actions were missing)
    // ──────────────────────────────────────────────

    /** POST /employee/self-service/surveys/{id}/submit */
    public function surveySubmit($id)
    {
        $this->requireEmployeeLogin();
        $employeeId = (int)$_SESSION['employee_id'];

        $answers = $_POST['answers'] ?? [];
        if (is_string($answers)) {
            $decoded = json_decode($answers, true);
            $answers = is_array($decoded) ? $decoded : [];
        }
        if (!is_array($answers) || empty($answers)) {
            $_SESSION['error'] = 'Please answer at least one question';
            $this->redirect(BASE_URL . '/employee/self-service/surveys/' . (int)$id);
            exit;
        }

        $result = $this->surveyService->submitResponse((int)$id, $employeeId, $answers);
        $_SESSION[$result['success'] ? 'success' : 'error'] = $result['message'] ?? 'Survey submitted';
        $this->redirect(BASE_URL . '/employee/self-service/surveys/' . (int)$id);
        exit;
    }

    /** POST /employee/self-service/advances/apply */
    public function advanceStore()
    {
        $this->requireEmployeeLogin();
        $employeeId = (int)$_SESSION['employee_id'];

        $data = [
            'type'            => $_POST['type'] ?? 'advance',
            'amount'          => (float)($_POST['amount'] ?? 0),
            'installments'    => (int)($_POST['installments'] ?? 0),
            'interest_rate'   => (float)($_POST['interest_rate'] ?? 0),
            'reason'          => $_POST['reason'] ?? '',
            'deduction_type'  => $_POST['deduction_type'] ?? 'salary',
        ];

        $result = $this->advanceService->requestAdvance($employeeId, $data);
        $_SESSION[$result['success'] ? 'success' : 'error'] = $result['message'] ?? 'Request submitted';
        $this->redirect(BASE_URL . '/employee/self-service/advances');
        exit;
    }

    /** POST /employee/self-service/encashment/apply */
    public function encashmentStore()
    {
        $this->requireEmployeeLogin();
        $employeeId = (int)$_SESSION['employee_id'];

        $data = [
            'leave_type_id' => (int)($_POST['leave_type_id'] ?? 0),
            'days_to_encash' => (float)($_POST['days_to_encash'] ?? 0),
        ];

        $result = $this->encashmentService->requestEncashment($employeeId, $data);
        $_SESSION[$result['success'] ? 'success' : 'error'] = $result['message'] ?? 'Request submitted';
        $this->redirect(BASE_URL . '/employee/self-service/encashment');
        exit;
    }

    /** POST /employee/self-service/overtime/apply */
    public function overtimeStore()
    {
        $this->requireEmployeeLogin();
        $employeeId = (int)$_SESSION['employee_id'];

        $data = [
            'date'       => $_POST['date'] ?? date('Y-m-d'),
            'start_time' => $_POST['start_time'] ?? '',
            'end_time'   => $_POST['end_time'] ?? '',
            'reason'     => $_POST['reason'] ?? '',
        ];

        $result = $this->overtimeService->requestOvertime($employeeId, $data);
        $_SESSION[$result['success'] ? 'success' : 'error'] = $result['message'] ?? 'Request submitted';
        $this->redirect(BASE_URL . '/employee/self-service/overtime');
        exit;
    }

    /** POST /employee/self-service/assets/report-issue */
    public function reportAssetIssue()
    {
        $this->requireEmployeeLogin();
        $employeeId = (int)$_SESSION['employee_id'];

        $assignmentId = (int)($_POST['asset_assignment_id'] ?? $_POST['assignment_id'] ?? 0);
        $issueType    = $_POST['issue_type'] ?? 'damage';
        $notes        = $_POST['notes'] ?? '';

        $result = $this->assetService->reportIssue($assignmentId, $employeeId, $issueType, $notes);
        $_SESSION[$result['success'] ? 'success' : 'error'] = $result['message'] ?? 'Issue reported';
        $this->redirect(BASE_URL . '/employee/self-service/assets');
        exit;
    }

    /** GET /employee/self-service/announcements/{id}/read */
    public function markAnnouncementRead($id)
    {
        $this->requireEmployeeLogin();
        $employeeId = (int)$_SESSION['employee_id'];

        $this->announcementService->markAsRead((int)$id, $employeeId);
        $this->redirect(BASE_URL . '/employee/self-service/announcements');
        exit;
    }

    /** POST /employee/self-service/shift-swaps/request */
    public function shiftSwapStore()
    {
        $this->requireEmployeeLogin();
        $employeeId = (int)$_SESSION['employee_id'];

        $data = [
            'swap_date'        => $_POST['swap_date'] ?? date('Y-m-d'),
            'from_shift'       => $_POST['from_shift'] ?? '',
            'to_shift'         => $_POST['to_shift'] ?? '',
            'colleague_id'     => (int)($_POST['colleague_id'] ?? 0),
            'reason'           => $_POST['reason'] ?? '',
        ];

        $result = $this->shiftSwapService->requestSwap($employeeId, $data);
        $_SESSION[$result['success'] ? 'success' : 'error'] = $result['message'] ?? 'Swap requested';
        $this->redirect(BASE_URL . '/employee/self-service/shift-swaps');
        exit;
    }

    /** POST /employee/self-service/shift-swaps/{id}/respond */
    public function shiftSwapRespondStore($id)
    {
        $this->requireEmployeeLogin();
        $employeeId = (int)$_SESSION['employee_id'];

        $action = $_POST['action'] ?? 'reject';
        $reason = $_POST['reason'] ?? '';

        $result = $this->shiftSwapService->respondToSwap((int)$id, $employeeId, $action, $reason);
        $_SESSION[$result['success'] ? 'success' : 'error'] = $result['message'] ?? 'Response recorded';
        $this->redirect(BASE_URL . '/employee/self-service/shift-swaps');
        exit;
    }

    /** GET /employee/self-service/helpdesk/create */
    public function helpdeskCreate()
    {
        $this->requireEmployeeLogin();

        return $this->render('employee/self_service/ticket_create', [
            'page_title' => 'Raise a Ticket',
        ]);
    }

    /** POST /employee/self-service/helpdesk/create */
    public function helpdeskStore()
    {
        $this->requireEmployeeLogin();
        $employeeId = (int)$_SESSION['employee_id'];

        $data = [
            'category'    => $_POST['category'] ?? 'general',
            'priority'    => $_POST['priority'] ?? 'medium',
            'subject'     => $_POST['subject'] ?? '',
            'description' => $_POST['description'] ?? '',
        ];

        $result = $this->helpdeskService->createTicket($employeeId, $data);
        $_SESSION[$result['success'] ? 'success' : 'error'] = $result['message'] ?? 'Ticket created';
        $this->redirect(BASE_URL . '/employee/self-service/helpdesk');
        exit;
    }

    /** GET /employee/self-service/helpdesk/{id} */
    public function helpdeskDetail($id)
    {
        $this->requireEmployeeLogin();
        $employeeId = (int)$_SESSION['employee_id'];

        $ticket = $this->helpdeskService->getTicket((int)$id);
        if (!$ticket || (int)($ticket['employee_id'] ?? 0) !== $employeeId) {
            $_SESSION['error'] = 'Ticket not found';
            $this->redirect(BASE_URL . '/employee/self-service/helpdesk');
            exit;
        }

        $comments = $this->helpdeskService->getTicketComments((int)$id);

        return $this->render('employee/self_service/ticket_detail', [
            'page_title' => 'Ticket: ' . ($ticket['ticket_number'] ?? ''),
            'ticket' => $ticket,
            'comments' => $comments,
        ]);
    }

    /** POST /employee/self-service/grievances/create */
    public function grievanceStore()
    {
        $this->requireEmployeeLogin();
        $employeeId = (int)$_SESSION['employee_id'];

        $data = [
            'category'    => $_POST['category'] ?? 'general',
            'sub_category' => $_POST['sub_category'] ?? '',
            'priority'    => $_POST['priority'] ?? 'medium',
            'subject'     => $_POST['subject'] ?? '',
            'description' => $_POST['description'] ?? '',
            'is_anonymous' => !empty($_POST['is_anonymous']) ? 1 : 0,
        ];

        $result = $this->grievanceService->submitGrievance($employeeId, $data);
        $_SESSION[$result['success'] ? 'success' : 'error'] = $result['message'] ?? 'Grievance submitted';
        $this->redirect(BASE_URL . '/employee/self-service/grievances');
        exit;
    }

    // ──────────────────────────────────────────────
    // TAX / DECLARATIONS / FORM 16
    // ──────────────────────────────────────────────

    /** GET|POST /employee/self-service/tax-regime, GET /employee/self-service/tax-calculator */
    public function taxRegime()
    {
        $this->requireEmployeeLogin();
        $employeeId = (int)$_SESSION['employee_id'];
        $tid = $this->tenantId();
        $fy = $this->currentFinancialYear();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $regime = ($_POST['regime'] ?? 'new') === 'old' ? 'old' : 'new';
            $exists = $this->db->fetchOne(
                "SELECT id FROM employee_tax_regime WHERE employee_id = ? AND tenant_id = ? AND financial_year = ? LIMIT 1",
                [$employeeId, $tid, $fy]
            );
            if ($exists) {
                $this->db->update('employee_tax_regime', ['regime' => $regime], ['id' => (int)$exists['id']]);
            } else {
                $this->db->insert('employee_tax_regime', [
                    'employee_id'    => $employeeId,
                    'tenant_id'      => $tid,
                    'financial_year' => $fy,
                    'regime'         => $regime,
                ]);
            }
            $_SESSION['success'] = 'Tax regime saved';
            $this->redirect(BASE_URL . '/employee/self-service/tax-regime');
            exit;
        }

        $row = $this->db->fetchOne(
            "SELECT * FROM employee_tax_regime WHERE employee_id = ? AND tenant_id = ? AND financial_year = ? ORDER BY id DESC LIMIT 1",
            [$employeeId, $tid, $fy]
        );

        return $this->render('employee/self_service/tax_regime', [
            'page_title'      => 'Tax Regime',
            'financial_year'  => $fy,
            'fy_label'        => $this->fyLabel($fy),
            // The view expects an array (row) with a 'regime' key, or null.
            'current_regime'  => $row ?: null,
        ]);
    }

    /** POST /employee/self-service/tax-regime (alias store endpoint) */
    public function taxRegimeStore()
    {
        $this->taxRegime();
    }

    /**
     * Income tax calculator.
     * GET /employee/self-service/tax-calculator, GET /employee/tax
     */
    public function taxCalculator()
    {
        $this->requireEmployeeLogin();
        $employeeId = (int)$_SESSION['employee_id'];
        $tid = $this->tenantId();

        $fy = isset($_GET['financial_year']) ? (int)$_GET['financial_year'] : $this->currentFinancialYear();

        $salaryRow = $this->db->fetchOne(
            "SELECT COALESCE(SUM(net_amount), 0) AS gross
             FROM salary_payments
             WHERE employee_id = ? AND tenant_id = ?
               AND payment_year = ?",
            [$employeeId, $tid, (int)substr($fy, 0, 4)]
        );
        $currentSalary = (float)($salaryRow['gross'] ?? 0);

        // slabs from tax_slabs (old regime) if present
        $slabs = $this->db->fetchAll(
            "SELECT * FROM tax_slabs WHERE tax_type = 'income_tax' AND is_active = 1 ORDER BY min_amount ASC"
        );

        return $this->render('employee/self_service/tax_calculator', [
            'page_title'     => 'Income Tax Calculator',
            'employeeId'     => $employeeId,
            'financial_year' => $fy,
            'fy_label'       => $this->fyLabel($fy),
            'currentSalary'  => $currentSalary,
            'salary'         => $currentSalary,
            'slabs'          => $slabs,
        ]);
    }

    /** GET|POST /employee/self-service/investment-declaration */
    public function investmentDeclaration()
    {
        $this->requireEmployeeLogin();
        $employeeId = (int)$_SESSION['employee_id'];
        $tid = $this->tenantId();
        $fy = $this->currentFinancialYear();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $section = $_POST['section'] ?? '80C';
            $amount  = (float)($_POST['amount'] ?? 0);
            $desc    = $_POST['description'] ?? '';

            $table = $this->declarationTable();
            if ($table) {
                $exists = $this->db->fetchOne(
                    "SELECT id FROM {$table} WHERE employee_id = ? AND tenant_id = ? AND financial_year = ? AND section = ? LIMIT 1",
                    [$employeeId, $tid, $fy, $section]
                );
                $payload = [
                    'employee_id'    => $employeeId,
                    'tenant_id'      => $tid,
                    'financial_year' => $fy,
                    'section'        => $section,
                    'amount'         => $amount,
                    'description'    => $desc,
                ];
                if ($exists) {
                    $this->db->update($table, $payload, ['id' => (int)$exists['id']]);
                } else {
                    $this->db->insert($table, $payload);
                }
            }
            $_SESSION['success'] = 'Investment declaration saved';
            $this->redirect(BASE_URL . '/employee/self-service/investment-declaration');
            exit;
        }

        $items = [];
        $table = $this->declarationTable();
        if ($table) {
            $items = $this->db->fetchAll(
                "SELECT * FROM {$table} WHERE employee_id = ? AND tenant_id = ? AND financial_year = ? ORDER BY id DESC",
                [$employeeId, $tid, $fy]
            );
        }

        $sections = ['80C' => 'Section 80C', '80D' => 'Section 80D', '80G' => 'Section 80G', '80TTA' => 'Section 80TTA'];
        $section_limits = ['80C' => 150000, '80D' => 50000, '80G' => 50000, '80TTA' => 10000];

        return $this->render('employee/self_service/investment_declaration', [
            'page_title'      => 'Investment Declaration',
            'financial_year'  => $fy,
            'fy_label'        => $this->fyLabel($fy),
            'items'           => $items,
            'declarations'    => $items,
            'sections'        => $sections,
            'section_limits'  => $section_limits,
        ]);
    }

    /** POST /employee/self-service/investment-declaration/upload-proof */
    public function uploadInvestmentProof()
    {
        $this->requireEmployeeLogin();
        $employeeId = (int)$_SESSION['employee_id'];

        if (!isset($_FILES['proof']) || $_FILES['proof']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['error'] = 'Please choose a file to upload';
            $this->redirect(BASE_URL . '/employee/self-service/investment-declaration');
            exit;
        }

        $dir = PUBLIC_PATH . '/uploads/employee-investments';
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        $safe = preg_replace('/[^A-Za-z0-9._-]/', '_', (string)$_FILES['proof']['name']);
        $path = $dir . '/emp' . $employeeId . '_' . time() . '_' . $safe;

        if (!@move_uploaded_file($_FILES['proof']['tmp_name'], $path)) {
            $_SESSION['error'] = 'Upload failed';
            $this->redirect(BASE_URL . '/employee/self-service/investment-declaration');
            exit;
        }

        $table = $this->declarationTable();
        if ($table) {
            $fy = (int)date('Y');
            $section = $_POST['section'] ?? '80C';
            $exists = $this->db->fetchOne(
                "SELECT id FROM {$table} WHERE employee_id = ? AND tenant_id = ? AND financial_year = ? AND section = ? LIMIT 1",
                [$employeeId, $this->tenantId(), $fy, $section]
            );
            $payload = [
                'employee_id'    => $employeeId,
                'tenant_id'      => $this->tenantId(),
                'financial_year' => $fy,
                'section'        => $section,
                'amount'         => (float)($_POST['amount'] ?? 0),
                'description'    => $_POST['description'] ?? '',
                'status'         => 'declared',
            ];
            if ($exists) {
                $this->db->update($table, $payload, ['id' => (int)$exists['id']]);
            } else {
                $this->db->insert($table, $payload);
            }

            // proof file -> employee_investment_proofs
            $proofRows = $this->db->fetchAll("SHOW TABLES LIKE 'employee_investment_proofs'");
            if ($proofRows) {
                $this->db->insert('employee_investment_proofs', [
                    'tenant_id'      => $this->tenantId(),
                    'employee_id'    => $employeeId,
                    'financial_year' => $fy,
                    'section'        => $section,
                    'file_path'      => str_replace(PUBLIC_PATH . '/', '', $path),
                    'original_name'  => $safe,
                    'file_size'      => (int)($_FILES['proof']['size'] ?? 0),
                    'mime_type'      => (string)($_FILES['proof']['type'] ?? 'application/octet-stream'),
                ]);
            }
        }

        $_SESSION['success'] = 'Proof uploaded';
        $this->redirect(BASE_URL . '/employee/self-service/investment-declaration');
        exit;
    }

    /** GET|POST /employee/self-service/form16 */
    public function form16()
    {
        $this->requireEmployeeLogin();
        $employeeId = (int)$_SESSION['employee_id'];
        $tid = $this->tenantId();
        $fy = $this->currentFinancialYear();

        $form16List = $this->db->fetchAll(
            "SELECT * FROM employee_form16 WHERE employee_id = ? AND tenant_id = ? ORDER BY financial_year DESC, id DESC",
            [$employeeId, $tid]
        );

        $f16 = null;
        foreach ($form16List as $row) {
            if ((int)($row['financial_year'] ?? 0) === (int)$fy) {
                $f16 = $row;
                break;
            }
        }

        return $this->render('employee/self_service/form16', [
            'page_title' => 'Form 16',
            'form16_list' => $form16List,
            'f16' => $f16,
        ]);
    }

    /** GET /employee/self-service/form16/download/{financial_year} */
    public function downloadForm16($financialYear = '')
    {
        $this->requireEmployeeLogin();
        $employeeId = (int)$_SESSION['employee_id'];
        $tid = $this->tenantId();
        $fy = $financialYear !== '' ? (int)$financialYear : $this->currentFinancialYear();

        $row = $this->db->fetchOne(
            "SELECT * FROM employee_form16 WHERE employee_id = ? AND tenant_id = ? AND financial_year = ? ORDER BY id DESC LIMIT 1",
            [$employeeId, $tid, $fy]
        );

        if (!$row || empty($row['file_path'])) {
            $_SESSION['error'] = 'Form 16 is not available for ' . $this->fyLabel($fy);
            $this->redirect(BASE_URL . '/employee/self-service/form16');
            exit;
        }

        $file = PUBLIC_PATH . '/' . ltrim((string)$row['file_path'], '/');
        if (is_file($file)) {
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . basename($file) . '"');
            header('Content-Length: ' . filesize($file));
            readfile($file);
            exit;
        }

        $_SESSION['error'] = 'Form 16 file is missing';
        $this->redirect(BASE_URL . '/employee/self-service/form16');
        exit;
    }

    // ──────────────────────────────────────────────
    // LEAVE / REIMBURSEMENT / PROFILE / ATTENDANCE
    // ──────────────────────────────────────────────

    /** GET|POST /employee/self-service/leave */
    public function leave()
    {
        $this->requireEmployeeLogin();
        $employeeId = (int)$_SESSION['employee_id'];
        $tid = $this->tenantId();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $leaveTypeId = (int)($_POST['leave_type_id'] ?? 0);
            $fromDate     = $_POST['from_date'] ?? date('Y-m-d');
            $toDate       = $_POST['to_date'] ?? $fromDate;
            $days         = (float)($_POST['days'] ?? 0);
            $reason       = $_POST['reason'] ?? '';

            if ($days <= 0 && $fromDate && $toDate) {
                $ts1 = strtotime($fromDate);
                $ts2 = strtotime($toDate);
                if ($ts1 && $ts2 && $ts2 >= $ts1) {
                    $days = (int)(($ts2 - $ts1) / 86400) + 1;
                }
            }

            $this->db->insert('leave_applications', [
                'tenant_id'   => $tid,
                'user_id'     => (int)($_SESSION['user_id'] ?? $employeeId),
                'leave_type'  => $leaveTypeId,
                'from_date'   => $fromDate,
                'to_date'     => $toDate,
                'total_days'  => $days,
                'reason'      => $reason,
                'status'      => 'pending',
            ]);
            $_SESSION['success'] = 'Leave application submitted';
            $this->redirect(BASE_URL . '/employee/self-service/leave');
            exit;
        }

        $leaveTypes = $this->db->fetchAll(
            "SELECT * FROM leave_types WHERE tenant_id = ? AND status = 'active' ORDER BY name",
            [$tid]
        );

        $leaveBalances = $this->db->fetchAll(
            "SELECT lb.*, lt.name AS leave_type_name, lt.code AS leave_type_code
             FROM employee_leave_balances lb
             LEFT JOIN leave_types lt ON lt.id = lb.leave_type_id
             WHERE lb.employee_id = ? AND lb.tenant_id = ? AND lb.year = ?",
            [$employeeId, $tid, (int)date('Y')]
        );

        $leaveHistory = $this->db->fetchAll(
            "SELECT la.*, lt.name AS leave_type_name
             FROM leave_applications la
             LEFT JOIN leave_types lt ON lt.id = la.leave_type
             WHERE la.user_id = ? AND la.tenant_id = ?
             ORDER BY la.id DESC LIMIT 50",
            [(int)($_SESSION['user_id'] ?? $employeeId), $tid]
        );

        return $this->render('employee/self_service/leave', [
            'page_title'     => 'Leave',
            'leave_types'    => $leaveTypes,
            'leave_balances' => $leaveBalances,
            'leave_history'  => $leaveHistory,
        ]);
    }

    /** GET|POST /employee/self-service/reimbursement */
    public function reimbursement()
    {
        $this->requireEmployeeLogin();
        $employeeId = (int)$_SESSION['employee_id'];
        $tid = $this->tenantId();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->db->insert('employee_reimbursements', [
                'tenant_id'    => $tid,
                'employee_id'  => $employeeId,
                'claim_type'   => $_POST['claim_type'] ?? 'travel',
                'amount'       => (float)($_POST['amount'] ?? 0),
                'description'  => $_POST['description'] ?? '',
                'expense_date' => $_POST['expense_date'] ?? date('Y-m-d'),
                'status'       => 'pending',
            ]);
            $_SESSION['success'] = 'Reimbursement claim submitted';
            $this->redirect(BASE_URL . '/employee/self-service/reimbursement');
            exit;
        }

        $claimTypes = [
            'travel'   => 'Travel',
            'medical'  => 'Medical',
            'food'     => 'Food / Meals',
            'telephone' => 'Telephone',
            'other'    => 'Other',
        ];

        $reimbursements = $this->db->fetchAll(
            "SELECT * FROM employee_reimbursements WHERE employee_id = ? AND tenant_id = ? ORDER BY id DESC LIMIT 50",
            [$employeeId, $tid]
        );

        return $this->render('employee/self_service/reimbursement', [
            'page_title'      => 'Reimbursements',
            'claim_types'     => $claimTypes,
            'reimbursements'  => $reimbursements,
        ]);
    }

    /** GET|POST /employee/self-service/profile */
    public function profile()
    {
        $this->requireEmployeeLogin();
        $employeeId = (int)$_SESSION['employee_id'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $emp = new \App\Services\EmployeeService();
            $data = [
                'name'      => $_POST['name'] ?? '',
                'email'     => $_POST['email'] ?? '',
                'phone'     => $_POST['phone'] ?? '',
                'designation' => $_POST['designation'] ?? '',
                'department'  => $_POST['department'] ?? '',
                'address'     => $_POST['address'] ?? '',
            ];
            $result = $emp->updateEmployee($employeeId, $data);
            $_SESSION[$result['success'] ? 'success' : 'error'] = $result['message'] ?? 'Profile updated';
            $this->redirect(BASE_URL . '/employee/self-service/profile');
            exit;
        }

        $emp = new \App\Services\EmployeeService();
        $user = $emp->getEmployeeWithUser($employeeId) ?: [];

        return $this->render('employee/self_service/profile', [
            'page_title' => 'My Profile',
            'user' => $user,
        ]);
    }

    /** GET|POST /employee/self-service/change-password */
    public function changePassword()
    {
        $this->requireEmployeeLogin();
        $employeeId = (int)$_SESSION['employee_id'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $current = $_POST['current_password'] ?? '';
            $new     = $_POST['new_password'] ?? '';
            $confirm = $_POST['confirm_password'] ?? '';

            if ($new === '' || $new !== $confirm) {
                $_SESSION['error'] = 'New passwords do not match';
            } elseif (strlen($new) < 8) {
                $_SESSION['error'] = 'Password must be at least 8 characters';
            } else {
                $emp = new \App\Services\EmployeeService();
                $row = $emp->getEmployeeWithUser($employeeId);
                $userId = (int)($row['user_id'] ?? 0);
                if ($userId > 0) {
                    $db = \App\Core\Database\Database::getInstance();
                    $hashRow = $db->fetchOne("SELECT password FROM users WHERE id = ? LIMIT 1", [$userId]);
                    $hash = $hashRow['password'] ?? '';
                    if ($hash && !password_verify($current, $hash)) {
                        $_SESSION['error'] = 'Current password is incorrect';
                    } else {
                        $db->update('users', ['password' => password_hash($new, PASSWORD_DEFAULT)], ['id' => $userId]);
                        $_SESSION['success'] = 'Password changed successfully';
                    }
                } else {
                    $_SESSION['error'] = 'No linked user account to update';
                }
            }
            $this->redirect(BASE_URL . '/employee/self-service/change-password');
            exit;
        }

        return $this->render('employee/self_service/change_password', [
            'page_title' => 'Change Password',
        ]);
    }

    /** GET /employee/self-service/attendance */
    public function attendance()
    {
        $this->requireEmployeeLogin();
        $employeeId = (int)$_SESSION['employee_id'];
        $tid = $this->tenantId();

        $month = isset($_GET['month']) ? (string)$_GET['month'] : date('Y-m');
        if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
            $month = date('Y-m');
        }

        $records = $this->db->fetchAll(
            "SELECT * FROM employee_attendance
             WHERE employee_id = ? AND tenant_id = ? AND DATE_FORMAT(attendance_date, '%Y-%m') = ?
             ORDER BY attendance_date DESC",
            [$employeeId, $tid, $month]
        );

        $stats = [
            'total_days'   => count($records),
            'present_days' => 0,
            'absent_days'  => 0,
            'leave_days'   => 0,
            'half_days'    => 0,
            'total_hours'  => 0,
            'late_minutes' => 0,
        ];
        foreach ($records as $r) {
            $st = (string)($r['status'] ?? '');
            if ($st === 'present') {
                $stats['present_days']++;
            } elseif ($st === 'absent') {
                $stats['absent_days']++;
            } elseif ($st === 'leave') {
                $stats['leave_days']++;
            } elseif ($st === 'half_day') {
                $stats['half_days']++;
            }
            $stats['total_hours'] += (float)($r['hours_worked'] ?? 0);
            $stats['late_minutes'] += (int)($r['late_minutes'] ?? 0);
        }
        $stats['avg_hours'] = $stats['total_days'] > 0 ? round($stats['total_hours'] / $stats['total_days'], 2) : 0.0;

        $prev = date('Y-m', strtotime($month . ' -1 month'));
        $next = date('Y-m', strtotime($month . ' +1 month'));

        return $this->render('employee/self_service/attendance', [
            'page_title'   => 'Attendance',
            'attendance'   => $records,
            'stats'        => $stats,
            'month'        => $month,
            'prev_month'   => $prev,
            'next_month'   => $next,
            'workingDays'  => (int)date('t', strtotime($month . '-01')),
        ]);
    }

    // ──────────────────────────────────────────────
    // HELPERS
    // ──────────────────────────────────────────────

    /**
     * Financial year key stored in employee_tax_regime / employee_form16 /
     * employee_investment_declarations — those tables store a plain year
     * (verified via DESCRIBE: financial_year = '2026').
     */
    private function currentFinancialYear(): string
    {
        $y = (int)date('Y');
        $m = (int)date('n');
        $start = $m >= 4 ? $y : $y - 1;          // Indian FY starts in April
        return (string)$start;
    }

    private function fyLabel(string $fy): string
    {
        $y = (int)$fy;
        return 'FY ' . $y . '-' . substr((string)($y + 1), -2);
    }

    /**
     * Investment declaration table name (may not exist on older DBs).
     */
    private function declarationTable(): ?string
    {
        try {
            $rows = $this->db->fetchAll("SHOW TABLES LIKE 'employee_investment_declarations'");
            if ($rows) {
                return 'employee_investment_declarations';
            }
            $rows = $this->db->fetchAll("SHOW TABLES LIKE 'employee_tax_declarations'");
            if ($rows) {
                return 'employee_tax_declarations';
            }
            $rows = $this->db->fetchAll("SHOW TABLES LIKE 'investment_declarations'");
            if ($rows) {
                return 'investment_declarations';
            }
        } catch (\Throwable $e) {
            error_log('[EmployeeSelfServiceController::declarationTable] ' . $e->getMessage());
        }
        return null;
    }
}