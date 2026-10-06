<?php
/**
 * ShiftRosterController
 * Admin controller for Shift Roster & Overtime management
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Traits\TenantAwareTrait;

class ShiftRosterController extends AdminController
{
    use TenantAwareTrait;

    public function __construct()
    {
        parent::__construct();
        $this->layout = 'layouts/admin';
    }

    /* ═════════════════════════════════════════════════════════════
       SHIFT TYPES
       ═════════════════════════════════════════════════════════════ */

    public function shiftTypes()
    {
        $this->requireAdmin();
        
        try {
            $service = new \App\Services\ShiftRosterService();
            $shiftTypes = $service->getShiftTypes();
        } catch (\Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            $shiftTypes = [];
        }

        return $this->render('admin/shift_roster/shift_types', [
            'page_title' => 'Shift Types',
            'shift_types' => $shiftTypes,
        ]);
    }

    public function createShiftType()
    {
        $this->requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $service = new \App\Services\ShiftRosterService();
                $result = $service->createShiftType($_POST);
                $_SESSION[$result['success'] ? 'success' : 'error'] = $result['message'];
            } catch (\Exception $e) {
                $_SESSION['error'] = $e->getMessage();
            }
            $this->redirect(BASE_URL . '/admin/shift-roster/shift-types');
            exit;
        }

        return $this->render('admin/shift_roster/create_shift_type', [
            'page_title' => 'Create Shift Type',
        ]);
    }

    /* ═════════════════════════════════════════════════════════════
       ROSTER SCHEDULING
       ═════════════════════════════════════════════════════════════ */

    public function roster()
    {
        $this->requireAdmin();
        
        $startDate = $_GET['start_date'] ?? date('Y-m-d');
        $endDate = $_GET['end_date'] ?? date('Y-m-d', strtotime('+7 days'));
        $employeeId = (int)($_GET['employee_id'] ?? 0);

        try {
            $service = new \App\Services\ShiftRosterService();
            $roster = $service->getRoster($startDate, $endDate, $employeeId);
            $employees = $this->getActiveEmployees();
            $shiftTypes = $service->getShiftTypes();
        } catch (\Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            $roster = [];
            $employees = [];
            $shiftTypes = [];
        }

        return $this->render('admin/shift_roster/roster', [
            'page_title' => 'Shift Roster',
            'roster' => $roster,
            'employees' => $employees,
            'shift_types' => $shiftTypes,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'employee_id' => $employeeId,
        ]);
    }

    public function assignShift()
    {
        $this->requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $service = new \App\Services\ShiftRosterService();
                $result = $service->assignShift($_POST);
                $_SESSION[$result['success'] ? 'success' : 'error'] = $result['message'];
            } catch (\Exception $e) {
                $_SESSION['error'] = $e->getMessage();
            }
            $this->redirect(BASE_URL . '/admin/shift-roster/roster');
            exit;
        }

        $employees = $this->getActiveEmployees();
        $shiftTypes = (new \App\Services\ShiftRosterService())->getShiftTypes();
        
        return $this->render('admin/shift_roster/assign_shift', [
            'page_title' => 'Assign Shift',
            'employees' => $employees,
            'shift_types' => $shiftTypes,
            'today' => date('Y-m-d'),
        ]);
    }

    public function weeklyRoster()
    {
        $this->requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $service = new \App\Services\ShiftRosterService();
                $result = $service->assignWeeklyRoster(
                    (int)($_POST['employee_id'] ?? 0),
                    (int)($_POST['shift_type_id'] ?? 0),
                    $_POST['start_date'] ?? date('Y-m-d'),
                    (int)($_POST['weeks'] ?? 4)
                );
                $_SESSION[$result['success'] ? 'success' : 'error'] = $result['message'];
            } catch (\Exception $e) {
                $_SESSION['error'] = $e->getMessage();
            }
            $this->redirect(BASE_URL . '/admin/shift-roster/roster');
            exit;
        }

        $employees = $this->getActiveEmployees();
        $shiftTypes = (new \App\Services\ShiftRosterService())->getShiftTypes();
        
        return $this->render('admin/shift_roster/weekly_roster', [
            'page_title' => 'Weekly Roster Assignment',
            'employees' => $employees,
            'shift_types' => $shiftTypes,
            'today' => date('Y-m-d'),
        ]);
    }

    /* ═════════════════════════════════════════════════════════════
       OVERTIME MANAGEMENT
       ═════════════════════════════════════════════════════════════ */

    public function overtimeRequests()
    {
        $this->requireAdmin();
        
        $status = $_GET['status'] ?? 'pending';
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 25;
        $offset = ($page - 1) * $perPage;

        try {
            $service = new \App\Services\ShiftRosterService();
            
            if ($status === 'pending') {
                $requests = $service->getPendingOvertime();
            } else {
                // Get all with status filter
                $where = "WHERE or.tenant_id = ?";
                $params = [$this->tenantId()];
                if ($status) {
                    $where .= " AND or.status = ?";
                    $params[] = $status;
                }
                $requests = $this->db->fetchAll("
                    SELECT or.*, u.name as employee_name, e.employee_code, e.designation, e.department
                    FROM overtime_requests or
                    LEFT JOIN employees e ON or.employee_id = e.id
                    LEFT JOIN users u ON e.user_id = u.id
                    $where
                    ORDER BY or.created_at DESC
                    LIMIT $perPage OFFSET $offset
                ", $params);
            }

            $total = count($requests); // Simplified
            $totalPages = max(1, ceil($total / $perPage));
        } catch (\Exception $e) {
            $requests = [];
            $totalPages = 1;
        }

        return $this->render('admin/shift_roster/overtime_requests', [
            'page_title' => 'Overtime Requests',
            'requests' => $requests,
            'status' => $status,
            'page' => $page,
            'total_pages' => $totalPages,
        ]);
    }

    public function processOvertime($id)
    {
        $this->requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(BASE_URL . '/admin/shift-roster/overtime-requests');
            exit;
        }

        try {
            $service = new \App\Services\ShiftRosterService();
            $action = $_POST['action'] ?? '';
            $remarks = $_POST['remarks'] ?? '';
            $result = $service->processOvertime($id, $action, $_SESSION['admin_id'] ?? 0, $remarks);
            $_SESSION[$result['success'] ? 'success' : 'error'] = $result['message'];
        } catch (\Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }
        $this->redirect(BASE_URL . '/admin/shift-roster/overtime-requests');
        exit;
    }

    public function overtimeReports()
    {
        $this->requireAdmin();
        
        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate = $_GET['end_date'] ?? date('Y-m-t');

        try {
            $service = new \App\Services\ShiftRosterService();
            $summary = $service->getOvertimeSummaryReport($startDate, $endDate);
        } catch (\Exception $e) {
            $summary = ['total_requests' => 0, 'total_hours' => 0, 'by_status' => [], 'by_department' => [], 'details' => []];
        }

        return $this->render('admin/shift_roster/overtime_reports', [
            'page_title' => 'Overtime Reports',
            'summary' => $summary,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);
    }

    public function shiftCoverage()
    {
        $this->requireAdmin();
        
        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate = $_GET['end_date'] ?? date('Y-m-t');

        try {
            $service = new \App\Services\ShiftRosterService();
            $coverage = $service->getShiftCoverageReport($startDate, $endDate);
        } catch (\Exception $e) {
            $coverage = [];
        }

        return $this->render('admin/shift_roster/shift_coverage', [
            'page_title' => 'Shift Coverage Report',
            'coverage' => $coverage,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);
    }

    private function getActiveEmployees(): array
    {
        return $this->db->fetchAll("
            SELECT e.id, e.employee_code, u.name, e.designation, e.department
            FROM employees e
            JOIN users u ON e.user_id = u.id
            WHERE e.status = 'active' AND e.tenant_id = ?
            ORDER BY u.name
        ", [$this->tenantId()]);
    }
}