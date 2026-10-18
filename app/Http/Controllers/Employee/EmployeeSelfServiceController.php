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
}