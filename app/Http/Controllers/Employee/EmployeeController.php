<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\BaseController;
use App\Models\Employee;
use App\Models\Admin;
use App\Models\EmployeeAttendance;
use App\Models\Leave;
use App\Models\Document;
use App\Models\Shift;

/**
 * Employee Controller
 * Handles all employee management operations including CRUD, attendance, tasks, and performance
 */
class EmployeeController extends BaseController
{
    private $employeeModel;
    private $adminModel;
    private $attendanceModel;
    private $leaveModel;
    private $documentModel;
    private $shiftModel;

    public function __construct()
    {
        parent::__construct();

        $this->employeeModel = new Employee();
        $this->adminModel = new Admin();
        $this->attendanceModel = new EmployeeAttendance();
        $this->leaveModel = new Leave();
        $this->documentModel = new Document();
        $this->shiftModel = new Shift();
    }

    /**
     * Check if employee is logged in
     */
    protected function isEmployeeLoggedIn()
    {
        return isset($_SESSION['employee_id']);
    }

    /**
     * Display employee login form
     */
    public function login()
    {
        // If already logged in, redirect to dashboard
        if ($this->isEmployeeLoggedIn()) {
            $this->redirect('employee/dashboard');
        }

        $this->data['page_title'] = 'Employee Login - APS Dream Home';
        $this->data['error'] = $this->getFlash('login_error');

        $this->render('employees/login');
    }

    /**
     * Handle employee login
     */
    public function authenticate()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('employee/login');
        }

        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $this->setFlash('login_error', 'Please enter both email and password.');
            $this->redirect('employee/login');
        }

        $employee = $this->employeeModel->getEmployeeByEmail($email);

        if ($employee && password_verify($password, $employee['password'])) {
            // Set session variables
            $_SESSION['employee_id'] = $employee['employee_id'];
            $_SESSION['employee_name'] = $employee['name'];
            $_SESSION['employee_email'] = $employee['email'];
            $_SESSION['employee_role'] = $employee['role_name'];
            $_SESSION['employee_department'] = $employee['department_name'];

            // Update last login
            $this->employeeModel->updateEmployee($employee['employee_id'], [
                'last_login' => date('Y-m-d H:i:s')
            ]);

            $this->redirect('employee/dashboard');
        } else {
            $this->setFlash('login_error', 'Invalid email or password.');
            $this->redirect('employee/login');
        }
    }

    /**
     * Employee logout
     */
    public function logout()
    {
        unset($_SESSION['employee_id']);
        unset($_SESSION['employee_name']);
        unset($_SESSION['employee_email']);
        unset($_SESSION['employee_role']);
        unset($_SESSION['employee_department']);

        $this->redirect('employee/login');
    }

    /**
     * Display employee dashboard
     */
    public function dashboard()
    {
        $employeeId = $_SESSION['employee_id'];

        // Get employee details
        $employee = $this->employeeModel->getEmployeeById($employeeId);

        if (!$employee) {
            $this->logout();
        }

        // Get dashboard data
        $dashboardData = $this->employeeModel->getEmployeeDashboardData($employeeId);

        // Get today's tasks count
        $todayTasks = $this->employeeModel->getEmployeeTasks($employeeId, [
            'status' => 'pending',
            'date_to' => date('Y-m-d')
        ]);

        // Get this week's performance
        $weeklyPerformance = $this->employeeModel->getEmployeePerformance($employeeId, 'week');

        $this->data['employee'] = $employee;
        $this->data['dashboard_data'] = $dashboardData;
        $this->data['today_tasks_count'] = count($todayTasks);
        $this->data['weekly_performance'] = $weeklyPerformance;
        $this->data['page_title'] = 'Employee Dashboard - APS Dream Home';

        $this->render('employees/dashboard');
    }

    /**
     * Display employee profile
     */
    public function profile()
    {
        $employeeId = $_SESSION['employee_id'];
        $employee = $this->employeeModel->getEmployeeById($employeeId);

        // Get additional profile data
        $activities = $this->employeeModel->getEmployeeActivities($employeeId, ['per_page' => 10]);
        $tasks = $this->employeeModel->getEmployeeTasks($employeeId, ['per_page' => 10]);
        $attendance = $this->employeeModel->getEmployeeAttendance($employeeId, ['per_page' => 10]);

        $this->data['employee'] = $employee;
        $this->data['activities'] = $activities;
        $this->data['tasks'] = $tasks;
        $this->data['attendance'] = $attendance;
        $this->data['page_title'] = 'My Profile - APS Dream Home';

        $this->render('employees/profile');
    }

    /**
     * Update employee profile
     */
    public function updateProfile()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('employee/profile');
        }

        $employeeId = $_SESSION['employee_id'];

        $data = [
            'name' => $_POST['name'] ?? '',
            'phone' => $_POST['phone'] ?? '',
            'emergency_contact' => $_POST['emergency_contact'] ?? '',
            'blood_group' => $_POST['blood_group'] ?? '',
            'address' => $_POST['address'] ?? '',
            'city' => $_POST['city'] ?? '',
            'state' => $_POST['state'] ?? '',
            'pincode' => $_POST['pincode'] ?? ''
        ];

        $success = $this->employeeModel->updateEmployee($employeeId, $data);

        if ($success) {
            $this->setFlash('success', 'Profile updated successfully.');
            $_SESSION['employee_name'] = $data['name'];
        } else {
            $this->setFlash('error', 'Failed to update profile. Please try again.');
        }

        $this->redirect('employee/profile');
    }

    /**
     * Display employee's tasks
     */
    public function tasks()
    {
        $employeeId = $_SESSION['employee_id'];

        // Get tasks with filters
        $filters = [];
        if (!empty($_GET['status'])) {
            $filters['status'] = $_GET['status'];
        }
        if (!empty($_GET['priority'])) {
            $filters['priority'] = $_GET['priority'];
        }
        if (!empty($_GET['date_from'])) {
            $filters['date_from'] = $_GET['date_from'];
        }
        if (!empty($_GET['date_to'])) {
            $filters['date_to'] = $_GET['date_to'];
        }

        $tasks = $this->employeeModel->getEmployeeTasks($employeeId, $filters);

        $this->data['tasks'] = $tasks;
        $this->data['filters'] = $filters;
        $this->data['page_title'] = 'My Tasks - APS Dream Home';

        $this->render('employees/tasks');
    }

    /**
     * Update task status
     */
    public function updateTask($taskId)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('employee/tasks');
        }

        $employeeId = $_SESSION['employee_id'];

        $data = [
            'status' => $_POST['status'] ?? 'pending',
            'actual_hours' => $_POST['actual_hours'] ?? null,
            'completion_notes' => $_POST['completion_notes'] ?? null
        ];

        // If marking as completed, set completed_at
        if ($data['status'] === 'completed') {
            $data['completed_at'] = date('Y-m-d H:i:s');
        }

        $success = $this->employeeModel->updateEmployeeTask($taskId, $data);

        if ($success) {
            $this->setFlash('success', 'Task updated successfully.');
        } else {
            $this->setFlash('error', 'Failed to update task. Please try again.');
        }

        $this->redirect('employee/tasks');
    }

    /**
     * Display employee's attendance
     */
    public function attendance()
    {
        $employeeId = $_SESSION['employee_id'];

        // Get attendance with filters
        $filters = [];
        if (!empty($_GET['month'])) {
            $filters['month'] = $_GET['month'];
        }

        $attendance = $this->employeeModel->getEmployeeAttendance($employeeId, $filters);

        // Calculate attendance statistics
        $totalDays = count($attendance);
        $presentDays = count(array_filter($attendance, function ($a) {
            return $a['status'] === 'present';
        }));
        $absentDays = count(array_filter($attendance, function ($a) {
            return $a['status'] === 'absent';
        }));
        $lateDays = count(array_filter($attendance, function ($a) {
            return $a['status'] === 'late';
        }));

        $attendanceRate = $totalDays > 0 ? round(($presentDays / $totalDays) * 100, 2) : 0;

        $this->data['attendance'] = $attendance;
        $this->data['filters'] = $filters;
        $this->data['stats'] = [
            'total_days' => $totalDays,
            'present_days' => $presentDays,
            'absent_days' => $absentDays,
            'late_days' => $lateDays,
            'attendance_rate' => $attendanceRate
        ];
        $this->data['page_title'] = 'My Attendance - APS Dream Home';

        $this->render('employees/attendance');
    }

    /**
     * Record attendance (check in/out)
     */
    public function recordAttendance()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('employee/dashboard');
        }

        $employeeId = $_SESSION['employee_id'];
        $action = $_POST['action'] ?? 'check_in';
        $location = $_POST['location'] ?? null;
        $notes = $_POST['notes'] ?? null;

        // Check if already checked in today
        $todayAttendance = $this->employeeModel->getEmployeeAttendance($employeeId, [
            'date_from' => date('Y-m-d'),
            'date_to' => date('Y-m-d')
        ]);

        $existingAttendance = !empty($todayAttendance) ? $todayAttendance[0] : null;

        if ($action === 'check_in') {
            if ($existingAttendance && $existingAttendance['check_in']) {
                $this->setFlash('error', 'Already checked in today.');
            } else {
                $success = $this->employeeModel->recordAttendance($employeeId, [
                    'check_in' => date('Y-m-d H:i:s'),
                    'status' => 'present',
                    'location' => $location,
                    'notes' => $notes
                ]);

                if ($success) {
                    $this->setFlash('success', 'Checked in successfully.');
                } else {
                    $this->setFlash('error', 'Failed to check in. Please try again.');
                }
            }
        } elseif ($action === 'check_out') {
            if (!$existingAttendance || !$existingAttendance['check_in']) {
                $this->setFlash('error', 'Please check in first.');
            } elseif ($existingAttendance['check_out']) {
                $this->setFlash('error', 'Already checked out today.');
            } else {
                $success = $this->employeeModel->recordAttendance($employeeId, [
                    'check_out' => date('Y-m-d H:i:s'),
                    'status' => 'present',
                    'location' => $location,
                    'notes' => $notes
                ]);

                if ($success) {
                    $this->setFlash('success', 'Checked out successfully.');
                } else {
                    $this->setFlash('error', 'Failed to check out. Please try again.');
                }
            }
        }

        $this->redirect('employee/dashboard');
    }

    /**
     * Display employee's leaves
     */
    public function leaves()
    {
        $employeeId = $_SESSION['employee_id'];

        // Get leaves with filters
        $filters = [];
        if (!empty($_GET['status'])) {
            $filters['status'] = $_GET['status'];
        }
        if (!empty($_GET['leave_type'])) {
            $filters['leave_type'] = $_GET['leave_type'];
        }

        $leaves = $this->employeeModel->getEmployeeLeaves($employeeId, $filters);
        $leaveTypes = $this->employeeModel->getLeaveTypes();

        $this->data['leaves'] = $leaves;
        $this->data['leave_types'] = $leaveTypes;
        $this->data['filters'] = $filters;
        $this->data['page_title'] = 'My Leaves - APS Dream Home';

        $this->render('employees/leaves');
    }

    /**
     * Apply for leave
     */
    public function applyLeave()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('employee/leaves');
        }

        $employeeId = $_SESSION['employee_id'];

        $data = [
            'leave_type_id' => $_POST['leave_type_id'] ?? null,
            'start_date' => $_POST['start_date'] ?? '',
            'end_date' => $_POST['end_date'] ?? '',
            'reason' => $_POST['reason'] ?? '',
            'status' => 'pending'
        ];

        // Calculate number of days
        $startDate = new \DateTime($data['start_date']);
        $endDate = new \DateTime($data['end_date']);
        $interval = $startDate->diff($endDate);
        $data['total_days'] = $interval->days + 1;

        // Create leave record
        $sql = "
            INSERT INTO employee_leaves (
                employee_id, leave_type_id, start_date, end_date, total_days,
                reason, status, applied_date, created_at
            ) VALUES (
                :employee_id, :leave_type_id, :start_date, :end_date, :total_days,
                :reason, :status, NOW(), NOW()
            )
        ";

        $stmt = $this->db->prepare($sql);
        $success = $stmt->execute([
            ':employee_id' => $employeeId,
            ':leave_type_id' => $data['leave_type_id'],
            ':start_date' => $data['start_date'],
            ':end_date' => $data['end_date'],
            ':total_days' => $data['total_days'],
            ':reason' => $data['reason'],
            ':status' => $data['status']
        ]);

        if ($success) {
            $this->setFlash('success', 'Leave application submitted successfully.');
        } else {
            $this->setFlash('error', 'Failed to submit leave application. Please try again.');
        }

        $this->redirect('employee/leaves');
    }

    /**
     * Display employee's documents
     */
    public function documents()
    {
        $employeeId = $_SESSION['employee_id'];

        // Get documents with filters
        $filters = [];
        if (!empty($_GET['document_type'])) {
            $filters['document_type'] = $_GET['document_type'];
        }
        if (!empty($_GET['status'])) {
            $filters['status'] = $_GET['status'];
        }

        $documents = $this->employeeModel->getEmployeeDocuments($employeeId, $filters);
        $documentTypes = $this->employeeModel->getDocumentTypes();

        $this->data['documents'] = $documents;
        $this->data['document_types'] = $documentTypes;
        $this->data['filters'] = $filters;
        $this->data['page_title'] = 'My Documents - APS Dream Home';

        $this->render('employees/documents');
    }

    /**
     * Display employee's activities
     */
    public function activities()
    {
        $employeeId = $_SESSION['employee_id'];

        // Get activities with filters
        $filters = [];
        if (!empty($_GET['activity_type'])) {
            $filters['activity_type'] = $_GET['activity_type'];
        }
        if (!empty($_GET['date_from'])) {
            $filters['date_from'] = $_GET['date_from'];
        }
        if (!empty($_GET['date_to'])) {
            $filters['date_to'] = $_GET['date_to'];
        }

        $activities = $this->employeeModel->getEmployeeActivities($employeeId, $filters);

        $this->data['activities'] = $activities;
        $this->data['filters'] = $filters;
        $this->data['page_title'] = 'My Activities - APS Dream Home';

        $this->render('employees/activities');
    }

    /**
     * Display employee's performance
     */
    public function performance()
    {
        $employeeId = $_SESSION['employee_id'];

        // Get performance data for different periods
        $monthlyPerformance = $this->employeeModel->getEmployeePerformance($employeeId, 'month');
        $quarterlyPerformance = $this->employeeModel->getEmployeePerformance($employeeId, 'quarter');
        $yearlyPerformance = $this->employeeModel->getEmployeePerformance($employeeId, 'year');

        $this->data['monthly_performance'] = $monthlyPerformance;
        $this->data['quarterly_performance'] = $quarterlyPerformance;
        $this->data['yearly_performance'] = $yearlyPerformance;
        $this->data['page_title'] = 'My Performance - APS Dream Home';

        $this->render('employees/performance');
    }

    /**
     * Display employee's salary history
     */
    public function salaryHistory()
    {
        $employeeId = $_SESSION['employee_id'];

        $salaryHistory = $this->employeeModel->getEmployeeSalaryHistory($employeeId);

        $this->data['salary_history'] = $salaryHistory;
        $this->data['page_title'] = 'Salary History - APS Dream Home';

        $this->render('employees/salary_history');
    }

    /**
     * Display reporting structure
     */
    public function reportingStructure()
    {
        $employeeId = $_SESSION['employee_id'];

        $reportingStructure = $this->employeeModel->getReportingStructure($employeeId);

        $this->data['reporting_structure'] = $reportingStructure;
        $this->data['page_title'] = 'Reporting Structure - APS Dream Home';

        $this->render('employees/reporting_structure');
    }

    /**
     * Change employee password
     */
    public function changePassword()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('employee/profile');
        }

        $employeeId = $_SESSION['employee_id'];
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        // Validate current password
        $employee = $this->employeeModel->getEmployeeById($employeeId);
        if (!password_verify($currentPassword, $employee['password'])) {
            $this->setFlash('error', 'Current password is incorrect.');
            $this->redirect('employee/profile');
        }

        // Validate new password
        if (strlen($newPassword) < 6) {
            $this->setFlash('error', 'New password must be at least 6 characters long.');
            $this->redirect('employee/profile');
        }

        if ($newPassword !== $confirmPassword) {
            $this->setFlash('error', 'New password and confirmation do not match.');
            $this->redirect('employee/profile');
        }

        $success = $this->employeeModel->updateEmployeePassword($employeeId, $newPassword);

        if ($success) {
            $this->setFlash('success', 'Password changed successfully.');
        } else {
            $this->setFlash('error', 'Failed to change password. Please try again.');
        }

        $this->redirect('employee/profile');
    }

    /**
     * Display employee attendance page
     */
    public function attendance()
    {
        $this->middleware('employee.auth');

        $employeeId = $_SESSION['employee_id'];
        $currentMonth = date('m');
        $currentYear = date('Y');

        // Get attendance history for current month
        $startDate = date('Y-m-01');
        $endDate = date('Y-m-t');
        $attendanceHistory = $this->attendanceModel->getHistory($employeeId, $startDate, $endDate);

        // Get monthly summary
        $summary = $this->attendanceModel->getMonthlySummary($employeeId, $currentMonth, $currentYear);

        $this->data['page_title'] = 'My Attendance - APS Dream Home';
        $this->data['attendance_history'] = $attendanceHistory;
        $this->data['summary'] = $summary;
        $this->data['current_month'] = $currentMonth;
        $this->data['current_year'] = $currentYear;

        $this->render('employees/attendance');
    }

    /**
     * API endpoint for check-in
     */
    public function checkIn()
    {
        $this->middleware('employee.auth');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid request method'], 405);
        }

        $employeeId = $_SESSION['employee_id'];
        $data = json_decode(file_get_contents('php://input'), true);

        if (!$data) {
            $data = $_POST;
        }

        $checkInData = [
            'latitude' => $data['latitude'] ?? null,
            'longitude' => $data['longitude'] ?? null,
            'address' => $data['address'] ?? null,
            'photo' => $data['photo'] ?? null,
            'notes' => $data['notes'] ?? null,
            'status' => $data['status'] ?? null
        ];

        $result = $this->attendanceModel->checkIn($employeeId, $checkInData);

        if ($result['success']) {
            $this->jsonResponse([
                'success' => true,
                'message' => 'Checked in successfully!',
                'data' => $result
            ]);
        } else {
            $this->jsonResponse([
                'success' => false,
                'message' => $result['message'] ?? 'Check-in failed'
            ], 400);
        }
    }

    /**
     * API endpoint for check-out
     */
    public function checkOut()
    {
        $this->middleware('employee.auth');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid request method'], 405);
        }

        $employeeId = $_SESSION['employee_id'];
        $data = json_decode(file_get_contents('php://input'), true);

        if (!$data) {
            $data = $_POST;
        }

        $checkOutData = [
            'latitude' => $data['latitude'] ?? null,
            'longitude' => $data['longitude'] ?? null,
            'address' => $data['address'] ?? null,
            'photo' => $data['photo'] ?? null
        ];

        $result = $this->attendanceModel->checkOut($employeeId, $checkOutData);

        if ($result['success']) {
            $this->jsonResponse([
                'success' => true,
                'message' => 'Checked out successfully!',
                'data' => $result
            ]);
        } else {
            $this->jsonResponse([
                'success' => false,
                'message' => $result['message'] ?? 'Check-out failed'
            ], 400);
        }
    }

    /**
     * Get today's attendance status
     */
    public function getAttendanceStatus()
    {
        $this->middleware('employee.auth');

        $employeeId = $_SESSION['employee_id'];

        // Check if already checked in today
        $todayRecord = $this->attendanceModel->where('employee_id', $employeeId)
            ->where('DATE(check_in_time)', date('Y-m-d'))
            ->first();

        $status = [
            'checked_in' => false,
            'checked_out' => false,
            'check_in_time' => null,
            'check_out_time' => null,
            'work_hours' => 0,
            'status' => null
        ];

        if ($todayRecord) {
            $status['checked_in'] = true;
            $status['check_in_time'] = $todayRecord['check_in_time'];
            $status['status'] = $todayRecord['status'];

            if ($todayRecord['check_out_time']) {
                $status['checked_out'] = true;
                $status['check_out_time'] = $todayRecord['check_out_time'];
                $status['work_hours'] = $todayRecord['work_hours'];
            }
        }

        $this->jsonResponse(['success' => true, 'data' => $status]);
    }

    /**
     * Get attendance history for a specific period
     */
    public function getAttendanceHistory()
    {
        $this->middleware('employee.auth');

        $employeeId = $_SESSION['employee_id'];
        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate = $_GET['end_date'] ?? date('Y-m-t');

        $history = $this->attendanceModel->getHistory($employeeId, $startDate, $endDate);

        $this->jsonResponse(['success' => true, 'data' => $history]);
    }

    /**
     * Get attendance statistics
     */
    public function getAttendanceStats()
    {
        $this->middleware('employee.auth');

        $employeeId = $_SESSION['employee_id'];
        $month = (int)($_GET['month'] ?? date('m'));
        $year = (int)($_GET['year'] ?? date('Y'));

        $stats = $this->attendanceModel->getMonthlySummary($employeeId, $month, $year);

        $this->jsonResponse(['success' => true, 'data' => $stats]);
    }

    /**
     * Display employee leave page
     */
    public function leaves()
    {
        $this->middleware('employee.auth');

        $employeeId = $_SESSION['employee_id'];

        // Initialize leave balance if not exists
        $this->leaveModel->initializeLeaveBalance($employeeId);

        // Get leave balance
        $leaveBalance = $this->leaveModel->getLeaveBalance($employeeId);

        // Get leave requests
        $leaveRequests = $this->leaveModel->getEmployeeRequests($employeeId);

        // Get leave types
        $leaveTypes = $this->leaveModel->getActiveLeaveTypes();

        $this->data['page_title'] = 'My Leaves - APS Dream Home';
        $this->data['leave_balance'] = $leaveBalance;
        $this->data['leave_requests'] = $leaveRequests;
        $this->data['leave_types'] = $leaveTypes;

        $this->render('employees/leaves');
    }

    /**
     * API endpoint to submit leave request
     */
    public function applyLeave()
    {
        $this->middleware('employee.auth');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid request method'], 405);
        }

        $employeeId = $_SESSION['employee_id'];
        $data = json_decode(file_get_contents('php://input'), true);

        if (!$data) {
            $data = $_POST;
        }

        // Validate required fields
        $required = ['leave_type_id', 'start_date', 'end_date', 'reason'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                $this->jsonResponse(['success' => false, 'message' => "Field '$field' is required"], 400);
            }
        }

        $leaveData = [
            'employee_id' => $employeeId,
            'leave_type_id' => $data['leave_type_id'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'reason' => $data['reason'],
            'emergency_contact' => $data['emergency_contact'] ?? null,
            'work_coverage' => $data['work_coverage'] ?? null
        ];

        $result = $this->leaveModel->submitRequest($leaveData);

        if ($result['success']) {
            $this->jsonResponse([
                'success' => true,
                'message' => $result['message'],
                'data' => $result
            ]);
        } else {
            $this->jsonResponse([
                'success' => false,
                'message' => $result['message']
            ], 400);
        }
    }

    /**
     * Get leave balance
     */
    public function getLeaveBalance()
    {
        $this->middleware('employee.auth');

        $employeeId = $_SESSION['employee_id'];
        $year = (int)($_GET['year'] ?? date('Y'));

        $balance = $this->leaveModel->getLeaveBalance($employeeId, $year);

        $this->jsonResponse(['success' => true, 'data' => $balance]);
    }

    /**
     * Get leave calendar data
     */
    public function getLeaveCalendar()
    {
        $this->middleware('employee.auth');

        $employeeId = $_SESSION['employee_id'];
        $year = (int)($_GET['year'] ?? date('Y'));
        $month = (int)($_GET['month'] ?? date('m'));

        $calendar = $this->leaveModel->getLeaveCalendar($employeeId, $year, $month);

        $this->jsonResponse(['success' => true, 'data' => $calendar]);
    }

    /**
     * Cancel leave request
     */
    public function cancelLeave()
    {
        $this->middleware('employee.auth');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid request method'], 405);
        }

        $employeeId = $_SESSION['employee_id'];
        $requestId = $_POST['request_id'] ?? null;

        if (!$requestId) {
            $this->jsonResponse(['success' => false, 'message' => 'Request ID is required'], 400);
        }

        // Check if request belongs to employee and is pending
        $request = $this->leaveModel->find($requestId);

        if (!$request || $request['employee_id'] != $employeeId) {
            $this->jsonResponse(['success' => false, 'message' => 'Leave request not found'], 404);
        }

        if ($request['status'] !== 'pending') {
            $this->jsonResponse(['success' => false, 'message' => 'Only pending requests can be cancelled'], 400);
        }

        // Update status to cancelled
        $this->leaveModel->update($requestId, [
            'status' => Leave::STATUS_CANCELLED,
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        $this->jsonResponse([
            'success' => true,
            'message' => 'Leave request cancelled successfully'
        ]);
    }

    /**
     * Display employee documents page
     */
    public function documents()
    {
        $this->middleware('employee.auth');

        $employeeId = $_SESSION['employee_id'];

        // Get document statistics
        $stats = $this->documentModel->getDocumentStats($employeeId);

        // Get employee documents
        $documents = $this->documentModel->getEmployeeDocuments($employeeId);

        // Get document categories and types
        $categories = $this->documentModel->getCategories();
        $documentTypes = $this->documentModel->getDocumentTypes();

        $this->data['page_title'] = 'My Documents - APS Dream Home';
        $this->data['documents'] = $documents;
        $this->data['stats'] = $stats;
        $this->data['categories'] = $categories;
        $this->data['document_types'] = $documentTypes;

        $this->render('employees/documents');
    }

    /**
     * Upload a document
     */
    public function uploadDocument()
    {
        $this->middleware('employee.auth');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid request method'], 405);
        }

        $employeeId = $_SESSION['employee_id'];

        // Check if file was uploaded
        if (!isset($_FILES['document']) || $_FILES['document']['error'] !== UPLOAD_ERR_OK) {
            $this->jsonResponse(['success' => false, 'message' => 'No file uploaded or upload failed'], 400);
        }

        $file = $_FILES['document'];
        $data = $_POST;

        $uploadData = [
            'employee_id' => $employeeId,
            'document_type_id' => $data['document_type_id'] ?? null,
            'title' => $data['title'] ?? $file['name'],
            'description' => $data['description'] ?? null,
            'uploaded_by' => $_SESSION['employee_id'], // Employee uploading their own document
            'expires_at' => !empty($data['expires_at']) ? $data['expires_at'] : null,
            'metadata' => [
                'uploaded_via' => 'employee_portal',
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null
            ]
        ];

        $result = $this->documentModel->uploadDocument($uploadData, $file);

        if ($result['success']) {
            $this->jsonResponse([
                'success' => true,
                'message' => $result['message'],
                'data' => $result
            ]);
        } else {
            $this->jsonResponse([
                'success' => false,
                'message' => $result['message']
            ], 400);
        }
    }

    /**
     * Download a document
     */
    public function downloadDocument($documentId)
    {
        $this->middleware('employee.auth');

        $userId = $_SESSION['employee_id'];

        $result = $this->documentModel->downloadDocument((int)$documentId, $userId);

        if ($result['success']) {
            // Set headers for file download
            header('Content-Type: ' . $result['mime_type']);
            header('Content-Disposition: attachment; filename="' . $result['file_name'] . '"');
            header('Content-Length: ' . filesize($result['file_path']));
            header('Cache-Control: private, max-age=0, must-revalidate');
            header('Pragma: public');

            // Clear output buffer
            ob_clean();
            flush();

            // Output file
            readfile($result['file_path']);
            exit;
        } else {
            $this->setFlash('error', $result['message']);
            $this->redirect('/employee/documents');
        }
    }

    /**
     * Get document categories and types (AJAX)
     */
    public function getDocumentCategories()
    {
        $this->middleware('employee.auth');

        $categories = $this->documentModel->getCategories();
        $documentTypes = $this->documentModel->getDocumentTypes();

        $this->jsonResponse([
            'success' => true,
            'categories' => $categories,
            'document_types' => $documentTypes
        ]);
    }

    /**
     * Get employee documents (AJAX)
     */
    public function getDocuments()
    {
        $this->middleware('employee.auth');

        $employeeId = $_SESSION['employee_id'];
        $filters = [
            'document_type_id' => $_GET['document_type_id'] ?? null,
            'category_id' => $_GET['category_id'] ?? null,
            'search' => $_GET['search'] ?? null,
            'limit' => $_GET['limit'] ?? 50
        ];

        $documents = $this->documentModel->getEmployeeDocuments($employeeId, $filters);

        $this->jsonResponse(['success' => true, 'data' => $documents]);
    }

    /**
     * Delete a document (soft delete)
     */
    public function deleteDocument()
    {
        $this->middleware('employee.auth');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid request method'], 405);
        }

        $documentId = $_POST['document_id'] ?? null;
        $userId = $_SESSION['employee_id'];

        if (!$documentId) {
            $this->jsonResponse(['success' => false, 'message' => 'Document ID is required'], 400);
        }

        $result = $this->documentModel->deleteDocument((int)$documentId, $userId);

        if ($result['success']) {
            $this->jsonResponse([
                'success' => true,
                'message' => $result['message']
            ]);
        } else {
            $this->jsonResponse([
                'success' => false,
                'message' => $result['message']
            ], 400);
        }
    }

    /**
     * Get document statistics
     */
    public function getDocumentStats()
    {
        $this->middleware('employee.auth');

        $employeeId = $_SESSION['employee_id'];
        $stats = $this->documentModel->getDocumentStats($employeeId);

        $this->jsonResponse(['success' => true, 'data' => $stats]);
    }

    /**
     * Display employee shifts page
     */
    public function shifts()
    {
        $this->middleware('employee.auth');

        $employeeId = $_SESSION['employee_id'];
        $currentMonth = date('m');
        $currentYear = date('Y');

        // Get shifts for current month
        $startDate = date('Y-m-01');
        $endDate = date('Y-m-t');
        $shifts = $this->shiftModel->getEmployeeShifts($employeeId, $startDate, $endDate);

        // Get current shift status
        $currentShift = $this->shiftModel->getCurrentShift($employeeId);

        // Get shift types for reference
        $shiftTypes = $this->shiftModel->getShiftTypes();

        $this->data['page_title'] = 'My Shifts - APS Dream Home';
        $this->data['shifts'] = $shifts;
        $this->data['current_shift'] = $currentShift;
        $this->data['shift_types'] = $shiftTypes;
        $this->data['current_month'] = $currentMonth;
        $this->data['current_year'] = $currentYear;

        $this->render('employees/shifts');
    }

    /**
     * API endpoint for clock in/out
     */
    public function clockInOut()
    {
        $this->middleware('employee.auth');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid request method'], 405);
        }

        $employeeId = $_SESSION['employee_id'];
        $action = $_POST['action'] ?? null;

        if (!$action || !in_array($action, ['clock_in', 'clock_out'])) {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid action'], 400);
        }

        $result = $this->shiftModel->clockInOut($employeeId, $action);

        if ($result['success']) {
            $this->jsonResponse([
                'success' => true,
                'message' => $result['message'],
                'data' => $result
            ]);
        } else {
            $this->jsonResponse([
                'success' => false,
                'message' => $result['message']
            ], 400);
        }
    }

    /**
     * Get current shift status
     */
    public function getCurrentShiftStatus()
    {
        $this->middleware('employee.auth');

        $employeeId = $_SESSION['employee_id'];
        $currentShift = $this->shiftModel->getCurrentShift($employeeId);

        $this->jsonResponse([
            'success' => true,
            'data' => $currentShift
        ]);
    }

    /**
     * Get shifts for a specific period
     */
    public function getShifts()
    {
        $this->middleware('employee.auth');

        $employeeId = $_SESSION['employee_id'];
        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate = $_GET['end_date'] ?? date('Y-m-t');

        $shifts = $this->shiftModel->getEmployeeShifts($employeeId, $startDate, $endDate);

        $this->jsonResponse(['success' => true, 'data' => $shifts]);
    }

    /**
     * Display time-off requests page
     */
    public function timeOff()
    {
        $this->middleware('employee.auth');

        $employeeId = $_SESSION['employee_id'];

        // Get time-off requests
        $timeOffRequests = $this->shiftModel->getTimeOffRequests($employeeId);

        $this->data['page_title'] = 'Time Off Requests - APS Dream Home';
        $this->data['time_off_requests'] = $timeOffRequests;

        $this->render('employees/time_off');
    }

    /**
     * Request time off
     */
    public function requestTimeOff()
    {
        $this->middleware('employee.auth');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid request method'], 405);
        }

        $employeeId = $_SESSION['employee_id'];
        $data = json_decode(file_get_contents('php://input'), true);

        if (!$data) {
            $data = $_POST;
        }

        // Validate required fields
        $required = ['request_type', 'start_date', 'end_date', 'reason'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                $this->jsonResponse(['success' => false, 'message' => "Field '$field' is required"], 400);
            }
        }

        $timeOffData = [
            'employee_id' => $employeeId,
            'request_type' => $data['request_type'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'start_time' => $data['start_time'] ?? null,
            'end_time' => $data['end_time'] ?? null,
            'reason' => $data['reason']
        ];

        $result = $this->shiftModel->requestTimeOff($timeOffData);

        if ($result['success']) {
            $this->jsonResponse([
                'success' => true,
                'message' => $result['message'],
                'data' => $result
            ]);
        } else {
            $this->jsonResponse([
                'success' => false,
                'message' => $result['message']
            ], 400);
        }
    }

    /**
     * Get time-off requests
     */
    public function getTimeOffRequests()
    {
        $this->middleware('employee.auth');

        $employeeId = $_SESSION['employee_id'];
        $requests = $this->shiftModel->getTimeOffRequests($employeeId);

        $this->jsonResponse(['success' => true, 'data' => $requests]);
    }

    /**
     * Middleware to check employee authentication
     */
    protected function middleware($middleware, array $options = [])
    {
        if ($middleware === 'employee.auth' && !$this->isEmployeeLoggedIn()) {
            $this->redirect('/employee/login');
        }
    }
}
