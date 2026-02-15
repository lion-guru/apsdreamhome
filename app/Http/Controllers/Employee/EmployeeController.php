<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\BaseController;
use App\Models\Employee;
use App\Models\Admin;

/**
 * Employee Controller
 * Handles all employee management operations including CRUD, attendance, tasks, and performance
 */
class EmployeeController extends BaseController
{
    private $employeeModel;
    private $adminModel;

    public function __construct()
    {
        parent::__construct();

        $this->employeeModel = new Employee();
        $this->adminModel = new Admin();
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
     * Middleware to check employee authentication
     */
    protected function middleware($middleware, array $options = [])
    {
        if ($middleware === 'employee.auth' && !$this->isEmployeeLoggedIn()) {
            $this->redirect('/employee/login');
        }
    }
}
