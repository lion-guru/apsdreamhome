<?php

namespace App\Http\Controllers\Admin;

use App\Traits\TenantAwareTrait;

class HRController extends AdminController
{
    use TenantAwareTrait;
    public function __construct()
    {
        parent::__construct();
        $this->layout = 'layouts/admin';
    }

    // ──────────────────────────────────────────────
    // DASHBOARD
    // ──────────────────────────────────────────────

    public function index()
    {
        $this->requireAdmin();
        try {
            $totalEmployees = $this->db->fetch("SELECT COUNT(*) as c FROM employees WHERE status='active'")['c'] ?? 0;
            $totalUsers = $this->db->fetch("SELECT COUNT(*) as c FROM users WHERE role='employee' AND status='active'")['c'] ?? 0;
            $presentToday = $this->db->fetch("SELECT COUNT(*) as c FROM employee_attendance WHERE attendance_date=CURDATE() AND status='present'")['c'] ?? 0;
            $onLeave = $this->db->fetch("SELECT COUNT(*) as c FROM employee_leaves WHERE CURDATE() BETWEEN start_date AND end_date AND status='approved'")['c'] ?? 0;
            $pendingLeaves = $this->db->fetch("SELECT COUNT(*) as c FROM employee_leaves WHERE status='pending'")['c'] ?? 0;
            $attendanceRate = $totalEmployees > 0 ? round(($presentToday / $totalEmployees) * 100, 1) : 0;
            $activeEmployees = $this->db->fetchAll("SELECT e.id, e.name, e.department, e.designation, e.status, u.email, u.phone FROM employees e JOIN users u ON e.user_id = u.id WHERE e.status='active' ORDER BY e.name LIMIT 5");
        } catch (\Exception $e) {
            error_log("[HRController] " . __METHOD__ . "() exception: " . $e->getMessage());

            $totalEmployees = 0; $totalUsers = 0; $presentToday = 0; $onLeave = 0; $pendingLeaves = 0; $attendanceRate = 0; $activeEmployees = [];
        }
        return $this->render('admin/hr/index', [
            'page_title' => 'HR Dashboard',
            'total_employees' => $totalEmployees,
            'total_users' => $totalUsers,
            'present_today' => $presentToday,
            'on_leave' => $onLeave,
            'pending_leaves' => $pendingLeaves,
            'attendance_rate' => $attendanceRate,
            'active_employees' => $activeEmployees,
        ]);
    }

    // ──────────────────────────────────────────────
    // users
    // ──────────────────────────────────────────────

    public function users()
    {
        $this->requireAdmin();
        $search = $_GET['search'] ?? '';
        $department = $_GET['department'] ?? '';
        $status = $_GET['status'] ?? '';
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 20;
        $offset = ($page - 1) * $perPage;
        $where = "WHERE 1=1";
        $params = [];
        if ($search) { $where .= " AND (e.name LIKE ? OR u.email LIKE ? OR u.phone LIKE ?)"; $s = "%$search%"; $params[] = $s; $params[] = $s; $params[] = $s; }
        if ($department) { $where .= " AND e.department=?"; $params[] = $department; }
        if ($status) { $where .= " AND e.status=?"; $params[] = $status; }
        try {
            $total = $this->db->fetch("SELECT COUNT(*) as c FROM employees e JOIN users u ON e.user_id=u.id $where", $params)['c'] ?? 0;
            $users = $this->db->fetchAll("SELECT e.*, u.email, u.phone FROM employees e JOIN users u ON e.user_id=u.id $where ORDER BY e.id DESC LIMIT $perPage OFFSET $offset", $params);
            $departments = $this->db->fetchAll("SELECT DISTINCT department FROM employees WHERE department IS NOT NULL AND department!='' ORDER BY department");
        } catch (\Exception $e) {
            error_log("[HRController] " . __METHOD__ . "() exception: " . $e->getMessage());

            $total = 0; $users = []; $departments = [];
        }
        $totalPages = $perPage > 0 ? max(1, ceil($total / $perPage)) : 1;
        return $this->render('admin/hr/users', [
            'page_title' => 'users',
            'users' => $users,
            'departments' => $departments,
            'search' => $search,
            'department' => $department,
            'status' => $status,
            'page' => $page,
            'total_pages' => $totalPages,
            'total' => $total,
        ]);
    }

    public function createEmployee()
    {
        $this->requireAdmin();
        return $this->render('admin/hr/employee_create', ['page_title' => 'Add Employee']);
    }

    public function storeEmployee()
    {
        $this->requireAdmin();
        $data = [
            'name' => $_POST['name'] ?? '',
            'email' => $_POST['email'] ?? '',
            'phone' => $_POST['phone'] ?? '',
            'department' => $_POST['department'] ?? 'General',
            'designation' => $_POST['designation'] ?? '',
            'salary' => ($_POST['salary'] ?? '') === '' ? null : ($_POST['salary'] ?? 0),
            'incentive_model' => $_POST['incentive_model'] ?? 'salary_only',
            'commission_rate' => ($_POST['commission_rate'] ?? '') === '' ? null : ($_POST['commission_rate'] ?? 0.00),
            'commission_type' => $_POST['commission_type'] ?? 'percentage',
            'joining_date' => $_POST['join_date'] ?? date('Y-m-d'),
            'password' => $_POST['password'] ?? 'employee@123',
            'address' => $_POST['address'] ?? null,
            'emergency_contact' => $_POST['emergency_contact'] ?? null,
            'date_of_birth' => $_POST['date_of_birth'] ?? null,
            'pan_number' => $_POST['pan_number'] ?? null,
            'aadhaar_number' => $_POST['aadhaar_number'] ?? null,
            'bank_account' => $_POST['bank_account'] ?? null,
            'bank_ifsc' => $_POST['bank_ifsc'] ?? null,
        ];
        
        if (!$data['name'] || !$data['email']) { 
            $this->setFlash('error', 'Name and Email are required'); 
            header('Location: ' . BASE_URL . '/admin/hr/users/create'); 
            exit; 
        }

        try {
            $empService = new \App\Services\EmployeeService();
            $result = $empService->createEmployee($data);
            
            if (!$result['success']) {
                $this->setFlash('error', 'Error: ' . $result['message']);
            } else {
                $this->setFlash('success', 'Employee created successfully. ID: ' . $result['user_id'] . ', Code: ' . $result['employee_code']);
            }
        } catch (\Exception $e) {
            error_log("[HRController] " . __METHOD__ . "() exception: " . $e->getMessage());
            $this->setFlash('error', 'Error: ' . $e->getMessage());
        }
        header('Location: ' . BASE_URL . '/admin/hr/users');
        exit;
    }

    public function editEmployee($id)
    {
        $this->requireAdmin();
        try {
            $employee = $this->db->fetch("SELECT e.*, u.email, u.phone FROM employees e JOIN users u ON e.user_id = u.id WHERE e.id = ?", [$id]);
            if (!$employee) { $this->setFlash('error', 'Employee not found'); header('Location: ' . BASE_URL . '/admin/hr/users'); exit; }
        } catch (\Exception $e) {
            error_log("[HRController] " . __METHOD__ . "() exception: " . $e->getMessage());
 $employee = null; }
        return $this->render('admin/hr/employee_edit', ['page_title' => 'Edit Employee', 'employee' => $employee]);
    }

    public function updateEmployee($id)
    {
        $this->requireAdmin();
        $data = [
            'name' => $_POST['name'] ?? '',
            'email' => $_POST['email'] ?? '',
            'phone' => $_POST['phone'] ?? '',
            'department' => $_POST['department'] ?? 'General',
            'designation' => $_POST['designation'] ?? '',
            'salary' => ($_POST['salary'] ?? '') === '' ? null : ($_POST['salary'] ?? 0),
            'incentive_model' => $_POST['incentive_model'] ?? 'salary_only',
            'commission_rate' => ($_POST['commission_rate'] ?? '') === '' ? null : ($_POST['commission_rate'] ?? 0.00),
            'commission_type' => $_POST['commission_type'] ?? 'percentage',
            'joining_date' => $_POST['join_date'] ?? '',
            'status' => $_POST['status'] ?? 'active',
            'address' => $_POST['address'] ?? null,
            'emergency_contact' => $_POST['emergency_contact'] ?? null,
            'date_of_birth' => $_POST['date_of_birth'] ?? null,
            'pan_number' => $_POST['pan_number'] ?? null,
            'aadhaar_number' => $_POST['aadhaar_number'] ?? null,
            'bank_account' => $_POST['bank_account'] ?? null,
            'bank_ifsc' => $_POST['bank_ifsc'] ?? null,
            'password' => $_POST['password'] ?? null,
        ];
        
        if (!$data['name']) { $this->setFlash('error', 'Name is required'); header('Location: ' . BASE_URL . "/admin/hr/users/edit/$id"); exit; }

        try {
            $empService = new \App\Services\EmployeeService();
            $result = $empService->updateEmployee($id, $data);
            
            if (!$result['success']) {
                $this->setFlash('error', 'Error: ' . $result['message']);
            } else {
                $this->setFlash('success', 'Employee updated successfully');
            }
        } catch (\Exception $e) {
            error_log("[HRController] " . __METHOD__ . "() exception: " . $e->getMessage());
            $this->setFlash('error', 'Error: ' . $e->getMessage());
        }
        header('Location: ' . BASE_URL . '/admin/hr/users');
        exit;
    }

    public function deleteEmployee($id)
    {
        $this->requireAdmin();
        try {
            $empService = new \App\Services\EmployeeService();
            $result = $empService->deleteEmployee($id);
            
            if (!$result['success']) {
                $this->setFlash('error', 'Error: ' . $result['message']);
            } else {
                $this->setFlash('success', 'Employee deactivated successfully');
            }
        } catch (\Exception $e) {
            error_log("[HRController] " . __METHOD__ . "() exception: " . $e->getMessage());
            $this->setFlash('error', 'Error: ' . $e->getMessage());
        }
        header('Location: ' . BASE_URL . '/admin/hr/users');
        exit;
    }

public function viewEmployee($id)
    {
        $this->requireAdmin();
        try {
            $empService = new \App\Services\EmployeeService();
            $employee = $empService->getEmployeeWithUser($id);
            
            if (!$employee) { $this->setFlash('error', 'Employee not found'); header('Location: ' . BASE_URL . '/admin/hr/users'); exit; }
            
            $attendance = $this->db->fetchAll("SELECT * FROM employee_attendance WHERE employee_id=? ORDER BY attendance_date DESC LIMIT 10", [$employee['id']]);
            $leaves = $this->db->fetchAll("SELECT el.*, lt.name as leave_type_name FROM employee_leaves el LEFT JOIN leave_types lt ON el.leave_type_id=lt.id WHERE el.employee_id=? ORDER BY el.created_at DESC LIMIT 5", [$id]);
        } catch (\Exception $e) {
            error_log("[HRController] " . __METHOD__ . "() exception: " . $e->getMessage());
            $employee = null; $attendance = []; $leaves = []; 
        }
        return $this->render('admin/hr/employee_view', [
            'page_title' => 'Employee: ' . ($employee['name'] ?? ''),
            'employee' => $employee,
            'attendance' => $attendance,
            'leaves' => $leaves,
        ]);
    }

    // ──────────────────────────────────────────────
    // ATTENDANCE
    // ──────────────────────────────────────────────

    public function attendance()
    {
        $this->requireAdmin();
        $date = $_GET['date'] ?? date('Y-m-d');
        $statusFilter = $_GET['status'] ?? '';
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 30;
        $offset = ($page - 1) * $perPage;
        $where = "WHERE a.attendance_date=?";
        $params = [$date];
        if ($statusFilter) { $where .= " AND a.status=?"; $params[] = $statusFilter; }
        try {
            $total = $this->db->fetch("SELECT COUNT(*) as c FROM employee_attendance a $where", $params)['c'] ?? 0;
            // employee_attendance.employee_id FK -> employees.id (never users.id)
            $records = $this->db->fetchAll("SELECT a.*, a.status AS attendance_status, u.name as employee_name, u.email, u.phone FROM employee_attendance a JOIN employees e ON a.employee_id=e.id JOIN users u ON e.user_id=u.id $where ORDER BY u.name LIMIT $perPage OFFSET $offset", $params);
            $users = $this->db->fetchAll("SELECT e.id, u.name FROM employees e JOIN users u ON e.user_id=u.id WHERE e.status='active' ORDER BY u.name");
        } catch (\Exception $e) {
            error_log("[HRController] " . __METHOD__ . "() exception: " . $e->getMessage());
 $total = 0; $records = []; $users = []; }
        $totalPages = $perPage > 0 ? max(1, ceil($total / $perPage)) : 1;
        return $this->render('admin/hr/attendance', [
            'page_title' => 'Attendance - ' . $date,
            'records' => $records,
            'users' => $users,
            'date' => $date,
            'status_filter' => $statusFilter,
            'page' => $page,
            'total_pages' => $totalPages,
            'total' => $total,
        ]);
    }

    public function markAttendance()
    {
        $this->requireAdmin();
        $tid = (int)$this->tenantId();
        $employeeId = (int)($_POST['employee_id'] ?? 0);
        $date = $_POST['date'] ?? date('Y-m-d');
        $status = $_POST['status'] ?? 'present';
        // Form sends TIME-only; columns are DATETIME. check_out was silently dropped.
        $checkInRaw = trim($_POST['check_in'] ?? '');
        $checkOutRaw = trim($_POST['check_out'] ?? '');
        $checkIn = $checkInRaw !== '' ? $date . ' ' . (strlen($checkInRaw) === 5 ? $checkInRaw . ':00' : $checkInRaw) : null;
        $checkOut = $checkOutRaw !== '' ? $date . ' ' . (strlen($checkOutRaw) === 5 ? $checkOutRaw . ':00' : $checkOutRaw) : null;
        $notes = $_POST['notes'] ?? '';
        if (!$employeeId) { $this->setFlash('error', 'Select an employee'); header('Location: ' . BASE_URL . '/admin/hr/attendance'); exit; }
        try {
            $existing = $this->db->fetch("SELECT id FROM employee_attendance WHERE employee_id=? AND attendance_date=? AND tenant_id=?", [$employeeId, $date, $tid]);
            if ($existing) {
                $this->db->execute("UPDATE employee_attendance SET status=?, check_in_time=?, check_out_time=?, remarks=? WHERE id=? AND tenant_id=?", [$status, $checkIn, $checkOut, $notes, $existing['id'], $tid]);
            } else {
                $this->db->execute("INSERT INTO employee_attendance (employee_id, attendance_date, status, check_in_time, check_out_time, remarks, tenant_id, created_at) VALUES (?,?,?,?,?,?,?,NOW())", [$employeeId, $date, $status, $checkIn, $checkOut, $notes, $tid]);
            }
            $this->setFlash('success', 'Attendance marked');
        } catch (\Exception $e) {
            error_log("[HRController] " . __METHOD__ . "() exception: " . $e->getMessage());

            $this->setFlash('error', 'Error: ' . $e->getMessage());
        }
        header('Location: ' . BASE_URL . '/admin/hr/attendance?date=' . $date);
        exit;
    }

    public function attendanceReport()
    {
        $this->requireAdmin();
        $month = $_GET['month'] ?? date('m');
        $year = $_GET['year'] ?? date('Y');
        $firstDay = "$year-$month-01";
        $lastDay = date('Y-m-t', strtotime($firstDay));
        try {
            $report = $this->db->fetchAll("SELECT u.id as user_id, u.name,
                SUM(CASE WHEN a.status='present' THEN 1 ELSE 0 END) as present,
                SUM(CASE WHEN a.status='absent' THEN 1 ELSE 0 END) as absent,
                SUM(CASE WHEN a.status='half_day' THEN 1 ELSE 0 END) as half_day,
                SUM(CASE WHEN a.status='on_leave' THEN 1 ELSE 0 END) as leave_count,
                SUM(CASE WHEN a.status='work_from_home' THEN 1 ELSE 0 END) as holiday,
                COUNT(a.id) as total_days
                FROM employee_attendance a JOIN employees e ON a.employee_id=e.id JOIN users u ON e.user_id=u.id
                WHERE a.attendance_date BETWEEN ? AND ?
                GROUP BY u.id, u.name ORDER BY u.name", [$firstDay, $lastDay]);
        } catch (\Exception $e) {
            error_log("[HRController] " . __METHOD__ . "() exception: " . $e->getMessage());
 $report = []; }
        return $this->render('admin/hr/attendance_report', [
            'page_title' => "Attendance Report - $month/$year",
            'report' => $report,
            'month' => $month,
            'year' => $year,
        ]);
    }

    // ──────────────────────────────────────────────
    // LEAVES
    // ──────────────────────────────────────────────

    public function leaves()
    {
        $this->requireAdmin();
        $statusFilter = $_GET['status'] ?? '';
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 25;
        $offset = ($page - 1) * $perPage;
        $where = "WHERE 1=1";
        $params = [];
        if ($statusFilter) { $where .= " AND el.status=?"; $params[] = $statusFilter; }
        try {
            $total = $this->db->fetch("SELECT COUNT(*) as c FROM employee_leaves el $where", $params)['c'] ?? 0;
            // employee_leaves.employee_id is employees.id (portal applyLeave maps user->employee)
            $leaves = $this->db->fetchAll("SELECT el.*, lt.name as leave_type_name, u.name as employee_name
                FROM employee_leaves el
                LEFT JOIN leave_types lt ON el.leave_type_id=lt.id
                JOIN employees e ON el.employee_id=e.id
                JOIN users u ON e.user_id=u.id
                $where ORDER BY el.created_at DESC LIMIT $perPage OFFSET $offset", $params);
        } catch (\Exception $e) {
            error_log("[HRController] leaves() exception: " . $e->getMessage());
            $total = 0;
            $leaves = [];
        }
        try {
            $users = $this->db->fetchAll("SELECT e.id, u.name FROM employees e JOIN users u ON e.user_id=u.id WHERE e.status='active' ORDER BY u.name");
        } catch (\Exception $e) {
            error_log("[HRController] leaves() employees query: " . $e->getMessage());
            $users = [];
        }
        try {
            $leave_types = $this->db->fetchAll("SELECT id, name FROM leave_types WHERE status='active' ORDER BY name");
        } catch (\Exception $e) {
            error_log("[HRController] leaves() leave_types query: " . $e->getMessage());
            $leave_types = [];
        }
        $totalPages = $perPage > 0 ? max(1, ceil($total / $perPage)) : 1;
        return $this->render('admin/hr/leaves', [
            'page_title' => 'Leave Applications',
            'leaves' => $leaves,
            'users' => $users,
            'leave_types' => $leave_types,
            'status_filter' => $statusFilter,
            'page' => $page,
            'total_pages' => $totalPages,
            'total' => $total,
        ]);
    }

    public function approveLeave($id)
    {
        $this->requireAdmin();
        $tid = (int)$this->tenantId();
        try {
            $this->db->execute("UPDATE employee_leaves SET status='approved', approved_by=?, approved_at=NOW() WHERE id=? AND tenant_id=?", [(int)($_SESSION['admin_id'] ?? 0), $id, $tid]);
            $this->setFlash('success', 'Leave approved');
        } catch (\Exception $e) {
            error_log("[HRController] " . __METHOD__ . "() exception: " . $e->getMessage());

            $this->setFlash('error', 'Error: ' . $e->getMessage());
        }
        header('Location: ' . BASE_URL . '/admin/hr/leaves');
        exit;
    }

    public function rejectLeave($id)
    {
        $this->requireAdmin();
        $tid = (int)$this->tenantId();
        try {
            $reason = $_POST['rejection_reason'] ?? '';
            $this->db->execute("UPDATE employee_leaves SET status='rejected', rejection_reason=?, approved_by=?, approved_at=NOW() WHERE id=? AND tenant_id=?", [$reason, (int)($_SESSION['admin_id'] ?? 0), $id, $tid]);
            $this->setFlash('success', 'Leave rejected');
        } catch (\Exception $e) {
            error_log("[HRController] " . __METHOD__ . "() exception: " . $e->getMessage());

            $this->setFlash('error', 'Error: ' . $e->getMessage());
        }
        header('Location: ' . BASE_URL . '/admin/hr/leaves');
        exit;
    }

    public function storeLeave()
    {
        $this->requireAdmin();
        $tid = (int)$this->tenantId();
        $employeeId = (int)($_POST['employee_id'] ?? 0);
        $leaveTypeId = (int)($_POST['leave_type_id'] ?? 0);
        $startDate = $_POST['start_date'] ?? '';
        $endDate = $_POST['end_date'] ?? '';
        $reason = $_POST['reason'] ?? '';
        if (!$employeeId || !$startDate || !$endDate) { $this->setFlash('error', 'All fields required'); header('Location: ' . BASE_URL . '/admin/hr/leaves'); exit; }
        $days = max(1, (strtotime($endDate) - strtotime($startDate)) / 86400 + 1);
        try {
            $this->db->execute("INSERT INTO employee_leaves (employee_id, leave_type_id, leave_type, start_date, end_date, total_days, reason, status, tenant_id, created_at) VALUES (?,?,?,?,?,?,?,'pending',?,NOW())", [$employeeId, $leaveTypeId, '', $startDate, $endDate, $days, $reason, $tid]);
            $this->setFlash('success', 'Leave application submitted');
        } catch (\Exception $e) {
            error_log("[HRController] " . __METHOD__ . "() exception: " . $e->getMessage());

            $this->setFlash('error', 'Error: ' . $e->getMessage());
        }
        header('Location: ' . BASE_URL . '/admin/hr/leaves');
        exit;
    }

    public function leaveTypes()
    {
        $this->requireAdmin();
        try {
            $types = $this->db->fetchAll("SELECT * FROM leave_types ORDER BY name");
        } catch (\Exception $e) {
            error_log("[HRController] " . __METHOD__ . "() exception: " . $e->getMessage());
 $types = []; }
        return $this->render('admin/hr/leave_types', ['page_title' => 'Leave Types', 'types' => $types]);
    }

    public function storeLeaveType()
    {
        $this->requireAdmin();
        $name = $_POST['name'] ?? '';
        $code = $_POST['code'] ?? '';
        $days = (int)($_POST['days_per_year'] ?? 0);
        $desc = $_POST['description'] ?? '';
        $color = $_POST['color'] ?? '#007bff';
        if (!$name || !$code) { $this->setFlash('error', 'Name and Code required'); header('Location: ' . BASE_URL . '/admin/hr/leave-types'); exit; }
        try {
            $this->db->execute("INSERT INTO leave_types (name, code, days_per_year, description, color, status, created_at) VALUES (?,?,?,?,?,'active',NOW())", [$name, $code, $days, $desc, $color]);
            $this->setFlash('success', 'Leave type created');
        } catch (\Exception $e) {
            error_log("[HRController] " . __METHOD__ . "() exception: " . $e->getMessage());

            $this->setFlash('error', 'Error: ' . $e->getMessage());
        }
        header('Location: ' . BASE_URL . '/admin/hr/leave-types');
        exit;
    }

    public function leaveBalances()
    {
        $this->requireAdmin();
        $year = (int)($_GET['year'] ?? date('Y'));
        try {
            $balances = $this->db->fetchAll("SELECT lb.*, lt.name as leave_type_name, u.name as employee_name
                FROM employee_leave_balances lb
                LEFT JOIN leave_types lt ON lb.leave_type_id=lt.id
                JOIN users e ON lb.employee_id=e.id
                JOIN users u ON e.id=u.id
                WHERE lb.year=? ORDER BY u.name, lt.name", [$year]);
        } catch (\Exception $e) {
            error_log("[HRController] " . __METHOD__ . "() exception: " . $e->getMessage());
 $balances = []; }
        return $this->render('admin/hr/leave_balances', [
            'page_title' => 'Leave Balances',
            'balances' => $balances,
            'year' => $year,
        ]);
    }

    // ──────────────────────────────────────────────
    // SHIFTS
    // ──────────────────────────────────────────────

    public function shifts()
    {
        $this->requireAdmin();
        try {
            $shifts = $this->db->fetchAll("SELECT * FROM shift_types ORDER BY name");
        } catch (\Exception $e) {
            error_log("[HRController] " . __METHOD__ . "() exception: " . $e->getMessage());
 $shifts = []; }
        return $this->render('admin/hr/shifts', ['page_title' => 'Shift Types', 'shifts' => $shifts]);
    }

    public function storeShift()
    {
        $this->requireAdmin();
        $name = $_POST['name'] ?? '';
        $startTime = $_POST['start_time'] ?? '';
        $endTime = $_POST['end_time'] ?? '';
        $desc = $_POST['description'] ?? '';
        $color = $_POST['color'] ?? '#007bff';
        if (!$name || !$startTime || !$endTime) { $this->setFlash('error', 'Name, Start and End time required'); header('Location: ' . BASE_URL . '/admin/hr/shifts'); exit; }
        try {
            $duration = round((strtotime($endTime) - strtotime($startTime)) / 3600, 2);
            $code = strtoupper(str_replace(' ', '_', $name));
            $this->db->execute("INSERT INTO shift_types (name, code, description, start_time, end_time, duration_hours, color, is_active, created_at) VALUES (?,?,?,?,?,?,?,1,NOW())", [$name, $code, $desc, $startTime, $endTime, $duration < 0 ? $duration + 24 : $duration, $color]);
            $this->setFlash('success', 'Shift created');
        } catch (\Exception $e) {
            error_log("[HRController] " . __METHOD__ . "() exception: " . $e->getMessage());

            $this->setFlash('error', 'Error: ' . $e->getMessage());
        }
        header('Location: ' . BASE_URL . '/admin/hr/shifts');
        exit;
    }

    public function assignShift()
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { $this->validateCsrfOrFail();
            $employeeId = (int)($_POST['employee_id'] ?? 0);
            $shiftTypeId = (int)($_POST['shift_type_id'] ?? 0);
            $shiftDate = $_POST['shift_date'] ?? date('Y-m-d');
            $startTime = $_POST['start_time'] ?? '';
            $endTime = $_POST['end_time'] ?? '';
            if (!$employeeId || !$shiftTypeId) { $this->setFlash('error', 'Select employee and shift'); header('Location: ' . BASE_URL . '/admin/hr/shifts/assign'); exit; }
            try {
                $duration = 0;
                if ($startTime && $endTime) { $duration = round((strtotime($endTime) - strtotime($startTime)) / 3600, 2); if ($duration < 0) $duration += 24; }
                $tid = (int)$this->tenantId();
                $this->db->execute("INSERT INTO employee_shifts (employee_id, shift_type_id, shift_date, start_time, end_time, duration_hours, status, tenant_id, created_at) VALUES (?,?,?,?,?,?,?,'scheduled',?,NOW())", [$employeeId, $shiftTypeId, $shiftDate, $startTime, $endTime, $duration, $tid]);
                $this->setFlash('success', 'Shift assigned');
            } catch (\Exception $e) {
                error_log("[HRController] " . __METHOD__ . "() exception: " . $e->getMessage());
 $this->setFlash('error', 'Error: ' . $e->getMessage()); }
            header('Location: ' . BASE_URL . '/admin/hr/shifts/schedule');
            exit;
        }
        try {
            $users = $this->db->fetchAll("SELECT e.id, u.name FROM employees e JOIN users u ON e.user_id=u.id WHERE e.status='active' ORDER BY u.name");
            $shiftTypes = $this->db->fetchAll("SELECT * FROM shift_types WHERE is_active=1 ORDER BY name");
        } catch (\Exception $e) {
            error_log("[HRController] " . __METHOD__ . "() exception: " . $e->getMessage());
 $users = []; $shiftTypes = []; }
        return $this->render('admin/hr/shift_schedule', ['page_title' => 'Assign Shift', 'users' => $users, 'shift_types' => $shiftTypes, 'mode' => 'assign']);
    }

    public function shiftSchedule()
    {
        $this->requireAdmin();
        $date = $_GET['date'] ?? date('Y-m-d');
        try {
            $schedule = $this->db->fetchAll("SELECT es.*, st.name as shift_name, u.name as employee_name
                FROM employee_shifts es
                LEFT JOIN shift_types st ON es.shift_type_id=st.id
                JOIN users e ON es.employee_id=e.id
                JOIN users u ON e.id=u.id
                WHERE es.shift_date=? ORDER BY u.name", [$date]);
        } catch (\Exception $e) {
            error_log("[HRController] " . __METHOD__ . "() exception: " . $e->getMessage());
 $schedule = []; }
        return $this->render('admin/hr/shift_schedule', [
            'page_title' => 'Shift Schedule',
            'schedule' => $schedule,
            'date' => $date,
            'mode' => 'schedule',
        ]);
    }

    // ──────────────────────────────────────────────
    // KPIs
    // ──────────────────────────────────────────────

    public function kpis()
    {
        $this->requireAdmin();
        try {
            $kpis = $this->db->fetchAll("SELECT * FROM kpis ORDER BY name");
        } catch (\Exception $e) {
            error_log("[HRController] " . __METHOD__ . "() exception: " . $e->getMessage());
 $kpis = []; }
        return $this->render('admin/hr/kpis', ['page_title' => 'KPI Definitions', 'kpis' => $kpis]);
    }

    public function storeKpi()
    {
        $this->requireAdmin();
        $name = $_POST['name'] ?? '';
        $desc = $_POST['description'] ?? '';
        $category = $_POST['category'] ?? 'productivity';
        $unit = $_POST['unit'] ?? '';
        $target = $_POST['default_target'] ?? 0;
        $weight = $_POST['weightage'] ?? 1;
        if (!$name) { $this->setFlash('error', 'Name required'); header('Location: ' . BASE_URL . '/admin/hr/kpis'); exit; }
        try {
            $this->db->execute("INSERT INTO kpis (name, description, category, unit, default_target, weightage, is_active, created_at) VALUES (?,?,?,?,?,?,1,NOW())", [$name, $desc, $category, $unit, $target, $weight]);
            $this->setFlash('success', 'KPI created');
        } catch (\Exception $e) {
            error_log("[HRController] " . __METHOD__ . "() exception: " . $e->getMessage());
 $this->setFlash('error', 'Error: ' . $e->getMessage()); }
        header('Location: ' . BASE_URL . '/admin/hr/kpis');
        exit;
    }

    // ──────────────────────────────────────────────
    // PERFORMANCE
    // ──────────────────────────────────────────────

    public function performance()
    {
        $this->requireAdmin();
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 25;
        $offset = ($page - 1) * $perPage;
        try {
            $total = $this->db->fetch("SELECT COUNT(*) as c FROM employee_kpis")['c'] ?? 0;
            $reviews = $this->db->fetchAll("SELECT ek.*, k.name as kpi_name, u.name as employee_name
                FROM employee_kpis ek
                LEFT JOIN kpis k ON ek.kpi_id=k.id
                JOIN users e ON ek.employee_id=e.id
                JOIN users u ON e.id=u.id
                ORDER BY ek.created_at DESC LIMIT $perPage OFFSET $offset", []);
        } catch (\Exception $e) {
            error_log("[HRController] " . __METHOD__ . "() exception: " . $e->getMessage());
 $total = 0; $reviews = []; }
        $totalPages = $perPage > 0 ? max(1, ceil($total / $perPage)) : 1;
        try {
            $users = $this->db->fetchAll("SELECT e.id, u.name FROM employees e JOIN users u ON e.user_id=u.id WHERE e.status='active' ORDER BY u.name");
            $kpis_list = $this->db->fetchAll("SELECT id, name FROM kpis WHERE is_active=1 ORDER BY name");
        } catch (\Exception $e) {
            error_log("[HRController] " . __METHOD__ . "() exception: " . $e->getMessage());
 $users = []; $kpis_list = []; }
        return $this->render('admin/hr/performance', [
            'page_title' => 'Performance Reviews',
            'reviews' => $reviews,
            'users' => $users,
            'kpis_list' => $kpis_list,
            'page' => $page,
            'total_pages' => $totalPages,
            'total' => $total,
        ]);
    }

    public function storeReview()
    {
        $this->requireAdmin();
        $tid = (int)$this->tenantId();
        $employeeId = (int)($_POST['employee_id'] ?? 0);
        $kpiId = (int)($_POST['kpi_id'] ?? 0);
        $targetValue = $_POST['target_value'] ?? 0;
        $actualValue = $_POST['actual_value'] ?? 0;
        $periodStart = $_POST['period_start'] ?? date('Y-m-01');
        $periodEnd = $_POST['period_end'] ?? date('Y-m-t');
        if (!$employeeId || !$kpiId) { $this->setFlash('error', 'Employee and KPI required'); header('Location: ' . BASE_URL . '/admin/hr/performance'); exit; }
        try {
            $achievement = $targetValue > 0 ? round(($actualValue / $targetValue) * 100, 2) : 0;
            $score = round($achievement / 100, 2);
            $this->db->execute("INSERT INTO employee_kpis (employee_id, kpi_id, period_start, period_end, target_value, actual_value, achievement_percentage, score, status, tenant_id, created_at) VALUES (?,?,?,?,?,?,?,?,'completed',?,NOW())", [$employeeId, $kpiId, $periodStart, $periodEnd, $targetValue, $actualValue, $achievement, $score, $tid]);
            $this->setFlash('success', 'Review created');
        } catch (\Exception $e) {
            error_log("[HRController] " . __METHOD__ . "() exception: " . $e->getMessage());
 $this->setFlash('error', 'Error: ' . $e->getMessage()); }
        header('Location: ' . BASE_URL . '/admin/hr/performance');
        exit;
    }

    // ──────────────────────────────────────────────
    // BONUSES
    // ──────────────────────────────────────────────

    public function bonuses()
    {
        $this->requireAdmin();
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 25;
        $offset = ($page - 1) * $perPage;
        try {
            $total = $this->db->fetch("SELECT COUNT(*) as c FROM employee_bonuses")['c'] ?? 0;
            $bonuses = $this->db->fetchAll("SELECT b.*, u.name as employee_name
                FROM employee_bonuses b
                JOIN users u ON b.employee_id=u.id
                ORDER BY b.created_at DESC LIMIT $perPage OFFSET $offset", []);
        } catch (\Exception $e) {
            error_log("[HRController] " . __METHOD__ . "() exception: " . $e->getMessage());
 $total = 0; $bonuses = []; }
        $totalPages = $perPage > 0 ? max(1, ceil($total / $perPage)) : 1;
        try {
            $users = $this->db->fetchAll("SELECT u.id, u.name FROM users u JOIN users e ON e.id=u.id WHERE e.status='active' ORDER BY u.name");
        } catch (\Exception $e) {
            error_log("[HRController] " . __METHOD__ . "() exception: " . $e->getMessage());
 $users = []; }
        return $this->render('admin/hr/bonuses', [
            'page_title' => 'Employee Bonuses',
            'bonuses' => $bonuses,
            'users' => $users,
            'page' => $page,
            'total_pages' => $totalPages,
            'total' => $total,
        ]);
    }

    public function storeBonus()
    {
        $this->requireAdmin();
        $tid = (int)$this->tenantId();
        $employeeId = (int)($_POST['employee_id'] ?? 0);
        $bonusType = $_POST['bonus_type'] ?? 'performance';
        $amount = $_POST['bonus_amount'] ?? 0;
        $reason = $_POST['reason'] ?? '';
        $month = (int)($_POST['bonus_month'] ?? date('m'));
        $year = (int)($_POST['bonus_year'] ?? date('Y'));
        if (!$employeeId || !$amount) { $this->setFlash('error', 'Employee and amount required'); header('Location: ' . BASE_URL . '/admin/hr/bonuses'); exit; }
        try {
            $bn = 'BNS-' . $year . str_pad($month, 2, '0', STR_PAD_LEFT) . '-' . $employeeId . '-' . time();
            $this->db->execute("INSERT INTO employee_bonuses (employee_id, bonus_type, amount, reason, given_by, tenant_id, created_at) VALUES (?,?,?,?,?,?,NOW())", [$employeeId, $bonusType, $amount, $reason, (int)($_SESSION['admin_id'] ?? 0), $tid]);
            $this->setFlash('success', 'Bonus recorded');
        } catch (\Exception $e) {
            error_log("[HRController] " . __METHOD__ . "() exception: " . $e->getMessage());
 $this->setFlash('error', 'Error: ' . $e->getMessage()); }
        header('Location: ' . BASE_URL . '/admin/hr/bonuses');
        exit;
    }

    // ──────────────────────────────────────────────
    // SALARY STRUCTURE
    // ──────────────────────────────────────────────

    public function salaryStructure()
    {
        $this->requireAdmin();
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 25;
        $offset = ($page - 1) * $perPage;
        // Canonical table is salary_structures (read by payroll batch, arrears,
        // gratuity, F&F, statutory + payslip generator). The legacy
        // employee_salary_structure table is no longer written by any UI.
        try {
            $total = $this->db->fetch("SELECT COUNT(*) as c FROM salary_structures")['c'] ?? 0;
            $structures = $this->db->fetchAll("SELECT s.*, u.name as employee_name,
                    s.conveyance AS ta, s.pf_employee AS pf_deduction, s.tds AS tds_deduction,
                    s.effective_date AS effective_from,
                    CASE WHEN s.status='active' THEN 1 ELSE 0 END AS is_active
                FROM salary_structures s
                LEFT JOIN users u ON s.employee_id=u.id
                ORDER BY s.created_at DESC LIMIT $perPage OFFSET $offset", []);
        } catch (\Exception $e) {
            error_log("[HRController] " . __METHOD__ . "() exception: " . $e->getMessage());
 $total = 0; $structures = []; }
        $totalPages = $perPage > 0 ? max(1, ceil($total / $perPage)) : 1;
        try {
            $users = $this->db->fetchAll("SELECT u.id, u.name FROM users u JOIN employees e ON e.user_id=u.id WHERE u.status='active' ORDER BY u.name");
        } catch (\Exception $e) {
            error_log("[HRController] " . __METHOD__ . "() exception: " . $e->getMessage());
 $users = []; }
        return $this->render('admin/hr/salary_structure', [
            'page_title' => 'Salary Structures',
            'structures' => $structures,
            'users' => $users,
            'page' => $page,
            'total_pages' => $totalPages,
            'total' => $total,
        ]);
    }

    public function storeSalaryStructure()
    {
        $this->requireAdmin();
        $tid = (int)$this->tenantId();
        $employeeId = (int)($_POST['employee_id'] ?? 0);
        $basic = $_POST['basic_salary'] ?? 0;
        $hraPct = $_POST['hra_percent'] ?? 0;
        $daPct = $_POST['da_percent'] ?? 0;
        $ta = $_POST['travel_allowance'] ?? 0;
        $medical = $_POST['medical_allowance'] ?? 0;
        $special = $_POST['special_allowance'] ?? 0;
        $pfPct = $_POST['pf_percent'] ?? 0;
        $tds = $_POST['tds_deduction'] ?? 0;
        $effFrom = $_POST['effective_from'] ?? date('Y-m-d');
        if (!$employeeId || !$basic) { $this->setFlash('error', 'Employee and basic salary required'); header('Location: ' . BASE_URL . '/admin/hr/salary-structure'); exit; }
        try {
            $hra = $basic * ($hraPct / 100);
            $da = $basic * ($daPct / 100);
            $pf = $basic * ($pfPct / 100);
            // Write canonical salary_structures (gross/net are STORED GENERATED).
            // Field map: travel_allowance->conveyance, pf_deduction->pf_employee,
            // tds_deduction->tds, effective_from->effective_date.
            $this->db->execute("INSERT INTO salary_structures (employee_id, basic_salary, hra, da, conveyance, medical_allowance, special_allowance, pf_employee, tds, effective_date, status, tenant_id, created_at) VALUES (?,?,?,?,?,?,?,?,?,?,'active',?,NOW())", [$employeeId, $basic, $hra, $da, $ta, $medical, $special, $pf, $tds, $effFrom, $tid]);
            $this->setFlash('success', 'Salary structure created');
        } catch (\Exception $e) {
            error_log("[HRController] " . __METHOD__ . "() exception: " . $e->getMessage());
 $this->setFlash('error', 'Error: ' . $e->getMessage()); }
        header('Location: ' . BASE_URL . '/admin/hr/salary-structure');
        exit;
    }

    public function editSalaryStructure($id)
    {
        $this->requireAdmin();
        try {
            $structure = $this->db->fetch("SELECT s.*, u.name as employee_name, s.conveyance AS ta, s.pf_employee AS pf_deduction, s.tds AS tds_deduction, s.effective_date AS effective_from FROM salary_structures s LEFT JOIN users u ON s.employee_id=u.id WHERE s.id=?", [$id]);
            if (!$structure) { $this->setFlash('error', 'Not found'); header('Location: ' . BASE_URL . '/admin/hr/salary-structure'); exit; }
        } catch (\Exception $e) {
            error_log("[HRController] " . __METHOD__ . "() exception: " . $e->getMessage());
 $structure = null; }
        return $this->render('admin/hr/salary_structure', ['page_title' => 'Edit Salary Structure', 'edit_structure' => $structure, 'mode' => 'edit']);
    }

    public function updateSalaryStructure($id)
    {
        $this->requireAdmin();
        $tid = (int)$this->tenantId();
        $basic = ($_POST['basic_salary'] ?? '') === '' ? null : ($_POST['basic_salary'] ?? 0);
        $hraPct = ($_POST['hra_percent'] ?? '') === '' ? null : ($_POST['hra_percent'] ?? 0);
        $daPct = ($_POST['da_percent'] ?? '') === '' ? null : ($_POST['da_percent'] ?? 0);
        $ta = ($_POST['travel_allowance'] ?? '') === '' ? null : ($_POST['travel_allowance'] ?? 0);
        $medical = ($_POST['medical_allowance'] ?? '') === '' ? null : ($_POST['medical_allowance'] ?? 0);
        $special = ($_POST['special_allowance'] ?? '') === '' ? null : ($_POST['special_allowance'] ?? 0);
        $pfPct = ($_POST['pf_percent'] ?? '') === '' ? null : ($_POST['pf_percent'] ?? 0);
        $tds = ($_POST['tds_deduction'] ?? '') === '' ? null : ($_POST['tds_deduction'] ?? 0);
        $effFrom = $_POST['effective_from'] ?? date('Y-m-d');
        try {
            $hra = $basic * ($hraPct / 100);
            $da = $basic * ($daPct / 100);
            $pf = $basic * ($pfPct / 100);
            $this->db->execute("UPDATE salary_structures SET basic_salary=?, hra=?, da=?, conveyance=?, medical_allowance=?, special_allowance=?, pf_employee=?, tds=?, effective_date=? WHERE id=? AND tenant_id=?", [$basic, $hra, $da, $ta, $medical, $special, $pf, $tds, $effFrom, $id, $tid]);
            $this->setFlash('success', 'Salary structure updated');
        } catch (\Exception $e) {
            error_log("[HRController] " . __METHOD__ . "() exception: " . $e->getMessage());
 $this->setFlash('error', 'Error: ' . $e->getMessage()); }
        header('Location: ' . BASE_URL . '/admin/hr/salary-structure');
        exit;
    }

    // ──────────────────────────────────────────────
    // DOCUMENTS
    // ──────────────────────────────────────────────

    public function employeeDocuments()
    {
        $this->requireAdmin();
        $empId = (int)($_GET['employee_id'] ?? 0);
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 25;
        $offset = ($page - 1) * $perPage;
        $where = "WHERE 1=1";
        $params = [];
        if ($empId) { $where .= " AND d.entity_id=? AND d.entity_type='employee'"; $params[] = $empId; }
        try {
            $total = $this->db->fetch("SELECT COUNT(*) as c FROM documents d $where", $params)['c'] ?? 0;
            // entity_id is employees.id (dropdown uses e.id); url is the file column.
            $documents = $this->db->fetchAll("SELECT d.*, d.url AS file_path, u.name as employee_name FROM documents d JOIN employees e ON d.entity_id=e.id AND d.entity_type='employee' JOIN users u ON e.user_id=u.id $where ORDER BY d.uploaded_on DESC LIMIT $perPage OFFSET $offset", $params);
            $users = $this->db->fetchAll("SELECT e.id, u.name FROM employees e JOIN users u ON e.user_id=u.id WHERE e.status='active' ORDER BY u.name");
            // Investment proofs uploaded by employees (stored keyed on users.id) — HR verifies these for TDS.
            $pw = "WHERE 1=1";
            $pp = [];
            if ($empId) {
                $empUser = $this->db->fetch("SELECT user_id FROM employees WHERE id=?", [$empId]);
                $pw .= " AND p.employee_id=?";
                $pp[] = $empUser ? (int)$empUser['user_id'] : -1;
            }
            $proofs = $this->db->fetchAll("SELECT p.*, u.name as employee_name FROM employee_investment_proofs p JOIN users u ON p.employee_id=u.id $pw ORDER BY p.uploaded_at DESC LIMIT 50", $pp);
        } catch (\Exception $e) {
            error_log("[HRController] " . __METHOD__ . "() exception: " . $e->getMessage());
 $total = 0; $documents = []; $users = []; $proofs = []; }
        $totalPages = $perPage > 0 ? max(1, ceil($total / $perPage)) : 1;
        return $this->render('admin/hr/employee_documents', [
            'page_title' => 'Employee Documents',
            'documents' => $documents,
            'users' => $users,
            'proofs' => $proofs ?? [],
            'emp_id' => $empId,
            'page' => $page,
            'total_pages' => $totalPages,
            'total' => $total,
        ]);
    }

    public function uploadEmployeeDocument()
    {
        $this->requireAdmin();
        $tid = (int)$this->tenantId();
        $employeeId = (int)($_POST['employee_id'] ?? 0);
        $docType = $_POST['document_type'] ?? '';
        $docName = $_POST['document_name'] ?? '';
        if (!$employeeId || !$docType) { $this->setFlash('error', 'Employee and document type required'); header('Location: ' . BASE_URL . '/admin/hr/documents'); exit; }
        $filePath = '';
        if (isset($_FILES['document_file']) && $_FILES['document_file']['error'] === UPLOAD_ERR_OK) {
            $validation = \UploadValidator::validate($_FILES['document_file'], ['types' => 'documents', 'max_size' => 10]);
            if ($validation['valid']) {
                // PUBLIC_PATH = webroot: list page links BASE_URL + stored path.
                $uploadDir = PUBLIC_PATH . '/assets/uploads/documents/';
                if (!is_dir($uploadDir)) { mkdir($uploadDir, 0755, true); }
                $fileName = $validation['sanitized_name'];
                move_uploaded_file($_FILES['document_file']['tmp_name'], $uploadDir . $fileName);
                $filePath = 'assets/uploads/documents/' . $fileName;
            } else {
                $this->setFlash('error', 'Upload rejected: ' . $validation['error']);
                header('Location: ' . BASE_URL . '/admin/hr/documents');
                exit;
            }
        }
        try {
            $this->db->execute("INSERT INTO documents (entity_type, entity_id, document_type, url, tenant_id, uploaded_on) VALUES ('employee',?,?,?, ?,NOW())", [$employeeId, $docType, $filePath, $tid]);
            $this->setFlash('success', 'Document uploaded');
        } catch (\Exception $e) {
            error_log("[HRController] " . __METHOD__ . "() exception: " . $e->getMessage());
 $this->setFlash('error', 'Error: ' . $e->getMessage()); }
        header('Location: ' . BASE_URL . '/admin/hr/documents');
        exit;
    }

    // ──────────────────────────────────────────────
    // ACTIVITIES
    // ──────────────────────────────────────────────

    public function activities()
    {
        $this->requireAdmin();
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 30;
        $offset = ($page - 1) * $perPage;
        try {
            $total = $this->db->fetch("SELECT COUNT(*) as c FROM employee_activities")['c'] ?? 0;
            $activities = $this->db->fetchAll("SELECT a.*, u.name as employee_name FROM employee_activities a JOIN users e ON a.employee_id=e.id JOIN users u ON e.id=u.id ORDER BY a.created_at DESC LIMIT $perPage OFFSET $offset", []);
        } catch (\Exception $e) {
            error_log("[HRController] " . __METHOD__ . "() exception: " . $e->getMessage());
 $total = 0; $activities = []; }
        $totalPages = $perPage > 0 ? max(1, ceil($total / $perPage)) : 1;
        return $this->render('admin/hr/activities', [
            'page_title' => 'Employee Activities',
            'activities' => $activities,
            'page' => $page,
            'total_pages' => $totalPages,
            'total' => $total,
        ]);
    }

    // ──────────────────────────────────────────────
    // EMPLOYEE REPORT
    // ──────────────────────────────────────────────

    public function employeeReport()
    {
        $this->requireAdmin();
        $empId = (int)($_GET['employee_id'] ?? 0);
        try {
            $users = $this->db->fetchAll("SELECT e.id, u.name FROM employees e JOIN users u ON e.user_id=u.id WHERE e.status='active' ORDER BY u.name");
            $report = null;
            $attendances = []; $leaves = []; $bonuses = [];
            if ($empId) {
                $report = $this->db->fetch("SELECT e.*, u.email, u.phone, u.created_at as user_since FROM employees e JOIN users u ON e.user_id=u.id WHERE e.id=?", [$empId]);
                if ($report) {
                    $attendances = $this->db->fetchAll("SELECT attendance_date, status AS attendance_status, check_in_time, check_out_time FROM employee_attendance WHERE employee_id=? ORDER BY attendance_date DESC LIMIT 30", [$report['id']]);
                    $leaves = $this->db->fetchAll("SELECT el.*, lt.name as leave_type_name FROM employee_leaves el LEFT JOIN leave_types lt ON el.leave_type_id=lt.id WHERE el.employee_id=? ORDER BY el.created_at DESC LIMIT 10", [$empId]);
                    $bonuses = $this->db->fetchAll("SELECT * FROM employee_bonuses WHERE employee_id=? ORDER BY created_at DESC LIMIT 10", [$report['id']]);
                }
            }
        } catch (\Exception $e) {
            error_log("[HRController] " . __METHOD__ . "() exception: " . $e->getMessage());
 $users = []; $report = null; $attendances = []; $leaves = []; $bonuses = []; }
        return $this->render('admin/hr/employee_report', [
            'page_title' => $report ? 'Report: ' . $report['name'] : 'Employee Report',
            'users' => $users,
            'report' => $report,
            'attendances' => $attendances,
            'leaves' => $leaves,
            'bonuses' => $bonuses,
            'emp_id' => $empId,
            'mode' => 'report',
        ]);
    }

    // ──────────────────────────────────────────────
    // SETTINGS
    // ──────────────────────────────────────────────

    public function settings()
    {
        $this->requireAdmin();
        try {
            $leaveTypes = $this->db->fetchAll("SELECT * FROM leave_types WHERE status='active' ORDER BY name");
            $shiftTypes = $this->db->fetchAll("SELECT * FROM shift_types WHERE is_active=1 ORDER BY name");
        } catch (\Exception $e) {
            error_log("[HRController] " . __METHOD__ . "() exception: " . $e->getMessage());
 $leaveTypes = []; $shiftTypes = []; }
        return $this->render('admin/hr/settings', [
            'page_title' => 'HR Settings',
            'leave_types' => $leaveTypes,
            'shift_types' => $shiftTypes,
        ]);
    }
}
