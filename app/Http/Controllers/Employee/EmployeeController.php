<?php

namespace App\Http\Controllers\Employee;

require_once __DIR__ . '/../BaseController.php';

use App\Http\Controllers\BaseController;
use Exception;

/**
 * Employee Controller
 * Handles employee dashboard, authentication, and related operations.
 */
class EmployeeController extends BaseController
{
    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = \App\Core\Database\Database::getInstance();
        $this->layout = 'layouts/employee';
    }

    /**
     * Show employee login page
     */
    public function login()
    {
        // Redirect if already logged in
        if ($this->isEmployeeLoggedIn()) {
            $this->redirect('/employee/dashboard');
        }

        // Include employee login view
        $loginView = __DIR__ . '/../../../views/employees/login.php';
        if (file_exists($loginView)) { require_once $loginView; }
        else { $this->render('auth/login'); }
    }

    /**
     * Handle employee login authentication
     */
    public function authenticate()
    {
        try {
            // Validate CSRF token
            $submittedToken = $_POST['csrf_token'] ?? '';
            $sessionToken = $_SESSION['csrf_token'] ?? '';
            if (empty($submittedToken) || empty($sessionToken) || !hash_equals($sessionToken, $submittedToken)) {
                throw new Exception('Invalid CSRF token. Please try again.');
            }

            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            if (empty($email) || empty($password)) {
                throw new Exception('Please fill in all fields');
            }

            // Authenticate against unified users table (include manager role too)
            $query = "SELECT * FROM users WHERE email = ? AND role IN ('employee','manager') AND status = 'active' LIMIT 1";
            $employee = $this->db->fetchOne($query, [$email]);

            if ($employee && password_verify($password, $employee['password'])) {
                $_SESSION['employee_id'] = $employee['id'];
                $_SESSION['employee_email'] = $employee['email'];
                $_SESSION['employee_name'] = $employee['name'];
                $_SESSION['employee_role'] = $employee['role'];
                $_SESSION['employee_department'] = $employee['department'] ?? '';
                $_SESSION['login_time'] = time();
                $_SESSION['csrf_token'] = $this->getCsrfToken();

                $this->logLoginAttempt($email, true);
                $this->redirect('/employee/dashboard');
            } else {
                throw new Exception('Invalid email or password');
            }
        } catch (\Exception $e) {
            $this->logLoginAttempt($_POST['email'] ?? '', false, $e->getMessage());
            $_SESSION['error'] = $e->getMessage();
            $this->redirect('/employee/login');
        }
    }

    /**
     * Log login attempt to database
     */
    private function logLoginAttempt($email, $success, $details = '')
    {
        try {
            $query = "INSERT INTO employee_activity_logs_unified (email, success, ip_address, user_agent, details, created_at)
                       VALUES (?, ?, ?, ?, ?, NOW())";
            $this->db->execute($query, [
                $email,
                $success ? 1 : 0,
                $_SERVER['REMOTE_ADDR'] ?? '',
                $_SERVER['HTTP_USER_AGENT'] ?? '',
                $details
            ]);
        } catch (\Exception $e) {
            error_log('Login log error: ' . $e->getMessage());
        }
    }

    /**
     * Check if employee has permission for specific action
     */
    public function hasPermission($permission): bool
    {
        if (!isset($_SESSION['employee_id'])) {
            return false;
        }

        $role = $_SESSION['employee_role'] ?? 'employee';

        $permissions = [
            'employee' => ['view_dashboard', 'view_tasks', 'update_tasks'],
            'manager' => ['view_dashboard', 'view_tasks', 'update_tasks', 'manage_employees', 'view_reports'],
            'telecalling_executive' => ['view_dashboard', 'manage_leads', 'log_calls', 'view_scripts'],
            'hr_manager' => ['view_dashboard', 'manage_employees', 'process_payroll', 'schedule_reviews'],
            'legal_advisor' => ['view_dashboard', 'review_documents', 'handle_disputes', 'manage_compliance'],
            'ca' => ['view_dashboard', 'manage_finances', 'process_invoices', 'generate_reports'],
            'land_manager' => ['view_dashboard', 'manage_properties', 'schedule_visits', 'handle_acquisitions'],
            'operations_manager' => ['view_dashboard', 'manage_operations', 'approve_requests'],
            'marketing_executive' => ['view_dashboard', 'manage_campaigns', 'view_analytics'],
        ];

        return in_array($permission, $permissions[$role] ?? []);
    }

    /**
     * Get CSRF token
     */
    protected function getCsrfToken(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Show employee dashboard
     */
    public function dashboard()
    {
        if (!isset($_SESSION['employee_id'])) {
            $this->redirect('/employee/login');
        }

        $employeeId = $_SESSION['employee_id'];

        // Redirect to role-specific dashboard based on designation
        $dashboardRoute = $this->resolveDashboardRoute($employeeId);
        if ($dashboardRoute) {
            $this->redirect($dashboardRoute);
            return;
        }

        // Fallback to generic employee dashboard
        $dashboardData = $this->getEmployeeDashboardData($employeeId);
        $gamify = $this->safeGamify('forEmployee', (int)$employeeId);

        $data = [
            'page_title' => 'Employee Dashboard',
            'page_description' => 'Employee portal dashboard for APS Dream Home',
            'dashboardData' => $dashboardData,
            'gamify' => $gamify,
        ];
        $this->render('employees/dashboard', $data);
    }

    /**
     * Resolve the role-specific dashboard route for an employee based on designation
     */
    private function resolveDashboardRoute(int $employeeId): ?string
    {
        try {
            $emp = $this->db->fetchOne(
                "SELECT e.designation, e.department
                 FROM employees e WHERE e.user_id = ? LIMIT 1",
                [$employeeId]
            );
            if (!$emp || empty($emp['designation'])) {
                return null;
            }

            $mapping = $this->db->fetchOne(
                "SELECT dashboard_view FROM employee_designation_roles
                 WHERE designation = ? AND (department = ? OR department IS NULL)
                 LIMIT 1",
                [$emp['designation'], $emp['department']]
            );

            if (!$mapping || empty($mapping['dashboard_view'])) {
                return null;
            }

            // Map view path to route
            $viewRoutes = [
                'employee/hr_dashboard'               => '/employee/hr-dashboard',
                'employee/land_manager_dashboard'      => '/employee/land-dashboard',
                'employee/legal_dashboard'             => '/employee/legal-dashboard',
                'employee/telecalling_dashboard'       => '/employee/telecalling-dashboard',
                'employee/ca_dashboard'                => '/employee/ca-dashboard',
                'employee/finance_dashboard'           => '/employee/finance-dashboard',
                'employee/marketing_dashboard'         => '/employee/marketing-dashboard',
                'employee/it_dashboard'                => '/employee/it-dashboard',
                'employee/ops_dashboard'               => '/employee/ops-dashboard',
                'employee/sales_dashboard'             => '/employee/sales-dashboard',
            ];

            return $viewRoutes[$mapping['dashboard_view']] ?? null;
        } catch (\Exception $e) {
            error_log('Dashboard resolve error: ' . $e->getMessage());
            return null;
        }
    }

    private function safeGamify(string $method, int ...$args): array
    {
        try {
            $role = strtolower(str_replace('for', '', $method));
            $cacheKey1 = $args[0] ?? 0;
            $cacheKey2 = $args[1] ?? 0;
            return \App\Services\CacheService::getGamification(
                $role,
                (int)$cacheKey1,
                (int)$cacheKey2,
                function () use ($method, $args) {
                    $svc = new \App\Services\GamificationService();
                    return $svc->{$method}(...$args);
                }
            );
        } catch (\Throwable $e) {
            error_log('Gamification error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get employee dashboard data
     */
    private function getEmployeeDashboardData($employeeId)
    {
        try {
            $data = [];

            // Get employee info
            $employeeQuery = "SELECT name, email, created_at FROM users WHERE id = ?";
            $employee = $this->db->fetchOne($employeeQuery, [$employeeId]);
            $data['employee'] = $employee;

            // Get tasks
            $data['tasks'] = $this->getEmployeeTasks($employeeId);

            // Get performance
            $data['performance'] = $this->getEmployeePerformance($employeeId);

            // Get attendance
            $data['attendance'] = $this->getEmployeeAttendance($employeeId);

            // Get activities
            $data['activities'] = $this->getEmployeeActivities($employeeId);

            return $data;
        } catch (\Exception $e) {
            error_log("Dashboard data error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get employee tasks
     */
    private function getEmployeeTasks($employeeId)
    {
        $query = "SELECT * FROM tasks WHERE assigned_to = ? ORDER BY created_at DESC LIMIT 10";
        return $this->db->fetchAll($query, [$employeeId]);
    }

    /**
     * Get employee performance metrics
     */
    private function getEmployeePerformance($employeeId, $period = 'month')
    {
        try {
            $performance = [];

            // Get completed tasks count
            $completedQuery = "SELECT COUNT(*) as completed FROM tasks WHERE assigned_to = ? AND status = 'completed'";
            if ($period === 'month') {
                $completedQuery .= " AND MONTH(created_at) = MONTH(CURRENT_DATE)";
            }
            $completed = $this->db->fetchOne($completedQuery, [$employeeId]);
            $performance['completed_tasks'] = $completed['completed'] ?? 0;

            // Get pending tasks count
            $pendingQuery = "SELECT COUNT(*) as pending FROM tasks WHERE assigned_to = ? AND status = 'pending'";
            if ($period === 'month') {
                $pendingQuery .= " AND MONTH(created_at) = MONTH(CURRENT_DATE)";
            }
            $pending = $this->db->fetchOne($pendingQuery, [$employeeId]);
            $performance['pending_tasks'] = $pending['pending'] ?? 0;

            return $performance;
        } catch (\Exception $e) {
            error_log("Performance data error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get employee attendance records
     */
    private function getEmployeeAttendance($employeeId, $filters = [])
    {
        try {
            $query = "SELECT * FROM employee_attendance WHERE employee_id = ?";
            $params = [$employeeId];

            if (!empty($filters['month'])) {
                $query .= " AND MONTH(attendance_date) = ?";
                $params[] = $filters['month'];
            }

            $query .= " ORDER BY attendance_date DESC, check_in DESC LIMIT 30";

            return $this->db->fetchAll($query, $params);
        } catch (\Exception $e) {
            error_log("Attendance data error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get employee activities
     */
    private function getEmployeeActivities($employeeId)
    {
        try {
            try {
                $query = "SELECT * FROM employee_activities WHERE employee_id = ? ORDER BY created_at DESC LIMIT 10";
            } catch (\Throwable $e) {
                // Gracefully handle dropped table ref
            }
            return $this->db->fetchAll($query, [$employeeId]);
        } catch (\Exception $e) {
            error_log("Activities data error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Record employee attendance (check-in)
     */
    public function checkIn()
    {
        $this->middleware('employee.auth');

        try {
            $employeeId = $_SESSION['employee_id'];
            $checkInTime = date('Y-m-d H:i:s');

            // Check if already checked in today
            $checkQuery = "SELECT id FROM employee_attendance WHERE employee_id = ? AND attendance_date = CURDATE()";
            $existing = $this->db->fetchOne($checkQuery, [$employeeId]);

            if ($existing) {
                throw new Exception('Already checked in today');
            }

            // Insert attendance record
            $query = "INSERT INTO employee_attendance (employee_id, attendance_date, check_in, attendance_status) VALUES (?, CURDATE(), ?, 'present')";
            $this->db->execute($query, [$employeeId, $checkInTime]);

            $this->jsonResponse([
                'success' => true,
                'message' => 'Checked in successfully'
            ]);
        } catch (\Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Record employee attendance (check-out)
     */
    public function checkOut()
    {
        $this->middleware('employee.auth');

        try {
            $employeeId = $_SESSION['employee_id'];
            $checkOutTime = date('Y-m-d H:i:s');

            // Update today's attendance record
            $query = "SELECT id FROM employee_attendance WHERE employee_id = ? AND attendance_date = CURDATE()";
            $attendance = $this->db->fetchOne($query, [$employeeId]);

            if (!$attendance) {
                throw new Exception('No check-in record found for today');
            }

            $updateQuery = "UPDATE employee_attendance SET check_out = ? WHERE id = ?";
            $this->db->execute($updateQuery, [$checkOutTime, $attendance['id']]);

            $this->jsonResponse([
                'success' => true,
                'message' => 'Checked out successfully'
            ]);
        } catch (\Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Update task status
     */
    public function updateTask()
    {
        $this->middleware('employee.auth');

        try {
            $taskId = $_POST['task_id'] ?? 0;
            $status = $_POST['status'] ?? '';
            $employeeId = $_SESSION['employee_id'];

            if (empty($taskId) || empty($status)) {
                throw new Exception('Invalid request');
            }

            // Update task
            $query = "UPDATE tasks SET status = ?, updated_at = NOW() WHERE id = ? AND assigned_to = ?";
            $this->db->execute($query, [$status, $taskId, $employeeId]);

            $this->jsonResponse([
                'success' => true,
                'message' => 'Task updated successfully'
            ]);
        } catch (\Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get employee profile
     */
    public function profile()
    {
        $this->middleware('employee.auth');

        $employeeId = $_SESSION['employee_id'];

        // Get employee details
        $query = "SELECT id, name, email, phone, created_at FROM users WHERE id = ?";
        $employee = $this->db->fetchOne($query, [$employeeId]);

        // Define BASE_PATH for shared view
        if (!defined('BASE_PATH')) {
            define('BASE_PATH', dirname(__DIR__, 4));
        }

        $userRole = 'employee';
        $profileUrl = BASE_URL . '/employee/profile';
        $securityUrl = null;
        $canEdit = true;

        $this->render('shared/profile', [
            'user' => $employee,
            'userRole' => $userRole,
            'profileUrl' => $profileUrl,
            'securityUrl' => $securityUrl,
            'canEdit' => $canEdit,
        ]);
    }

    /**
     * Update employee profile
     */
    public function updateProfile()
    {
        $this->middleware('employee.auth');

        try {
            $employeeId = $_SESSION['employee_id'];
            $name = $_POST['name'] ?? '';
            $phone = $_POST['phone'] ?? '';

            if (empty($name)) {
                throw new Exception('Name is required');
            }

            // Update profile
            $query = "UPDATE users SET name = ?, phone = ?, updated_at = NOW() WHERE id = ?";
            $this->db->execute($query, [$name, $phone, $employeeId]);

            // Update session
            $_SESSION['employee_name'] = $name;

            $this->jsonResponse([
                'success' => true,
                'message' => 'Profile updated successfully'
            ]);
        } catch (\Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Show change password page
     */
    public function changePasswordView()
    {
        $this->middleware('employee.auth');
        $data = [
            'page_title' => 'Change Password',
            'page_description' => 'Update your account password',
        ];
        $this->render('employees/change_password', $data);
    }

    /**
     * Handle change password POST
     */
    public function changePassword()
    {
        $this->middleware('employee.auth');

        $employeeId = $_SESSION['employee_id'] ?? 0;
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (empty($currentPassword) || empty($newPassword)) {
            $_SESSION['error'] = 'All fields are required';
            $this->redirect('/employee/change-password');
            return;
        }

        if (strlen($newPassword) < 6) {
            $_SESSION['error'] = 'New password must be at least 6 characters';
            $this->redirect('/employee/change-password');
            return;
        }

        if ($newPassword !== $confirmPassword) {
            $_SESSION['error'] = 'New passwords do not match';
            $this->redirect('/employee/change-password');
            return;
        }

        $svc = new \App\Services\UserRegistrationService();
        $result = $svc->changePassword($employeeId, $currentPassword, $newPassword);

        if ($result['success']) {
            $_SESSION['success'] = $result['message'];
        } else {
            $_SESSION['error'] = $result['message'];
        }

        $this->redirect('/employee/change-password');
    }

    /**
     * Logout employee
     */
    public function logout()
    {
        if (isset($_SESSION['employee_email'])) {
            $this->logLoginAttempt($_SESSION['employee_email'], true, 'logout');
        }
        session_destroy();
        $this->redirect('/employee/login');
    }

    /**
     * Check if employee is logged in
     */
    private function isEmployeeLoggedIn()
    {
        return isset($_SESSION['employee_id']) && !empty($_SESSION['employee_id']);
    }

    /**
     * Middleware to check employee authentication
     */
    protected function middleware($name, $options = [])
    {
        if ($name === 'employee.auth' && !$this->isEmployeeLoggedIn()) {
            $this->redirect('/employee/login');
        }

        // Call parent middleware
        parent::middleware($name, $options);
    }

    // ── Employee Tasks ──
    public function tasks()
    {
        $employeeId = $_SESSION['employee_id'] ?? 0;
        $tasks = [];
        $stats = ['total' => 0, 'pending' => 0, 'in_progress' => 0, 'completed' => 0, 'overdue' => 0];
        if ($employeeId > 0) {
            try {
                $emp = $this->db->fetch("SELECT name, department FROM employees WHERE id = ?", [$employeeId]);
                $tasks = $this->db->fetchAll(
                    "SELECT t.*, u.name AS assigned_by_name FROM tasks t LEFT JOIN users u ON t.created_by = u.id WHERE t.assigned_to = ? ORDER BY FIELD(t.priority, 'High', 'Medium', 'Low'), t.due_date ASC",
                    [$employeeId]
                );
                $stats['total'] = count($tasks);
                $now = date('Y-m-d');
                foreach ($tasks as $t) {
                    $s = strtolower($t['status'] ?? '');
                    if ($s === 'completed') $stats['completed']++;
                    elseif ($s === 'in progress') $stats['in_progress']++;
                    else {
                        $stats['pending']++;
                        if (!empty($t['due_date']) && $t['due_date'] < $now && $s !== 'completed') $stats['overdue']++;
                    }
                }
            } catch (\Exception $e) { $tasks = []; }
        }
        $this->render('employees/tasks', [
            'page_title' => 'My Tasks',
            'page_description' => 'View and manage your assigned tasks',
            'tasks' => $tasks,
            'stats' => $stats,
        ]);
    }

    // ── Employee Activities ──
    public function activities()
    {
        $employeeId = $_SESSION['employee_id'] ?? 0;
        $activities = [];
        $stats = ['total' => 0, 'today' => 0, 'this_week' => 0, 'types' => []];
        $filter = $_GET['type'] ?? '';
        if ($employeeId > 0) {
            try {
                $where = "WHERE user_id = ? AND (log_type = 'employee' OR user_id = ?)";
                $params = [$employeeId, $employeeId];
                if ($filter && in_array($filter, ['login','task','attendance','leave','document','system'])) {
                    $where .= " AND action = ?";
                    $params[] = $filter;
                }
                $activities = $this->db->fetchAll(
                    "SELECT * FROM user_activity_logs_unified {$where} ORDER BY created_at DESC LIMIT 100",
                    $params
                );
                $stats['total'] = count($activities);
                $today = date('Y-m-d');
                $weekAgo = date('Y-m-d', strtotime('-7 days'));
                foreach ($activities as $a) {
                    $ts = substr($a['created_at'] ?? '', 0, 10);
                    if ($ts === $today) $stats['today']++;
                    if ($ts >= $weekAgo) $stats['this_week']++;
                    $act = $a['action'] ?? 'other';
                    $stats['types'][$act] = ($stats['types'][$act] ?? 0) + 1;
                }
            } catch (\Exception $e) { $activities = []; }
        }
        $this->render('employees/activities', [
            'page_title' => 'Activity Timeline',
            'page_description' => 'Your recent activity history',
            'activities' => $activities,
            'stats' => $stats,
            'filter' => $filter,
        ]);
    }

    public function attendance()
    {
        $employeeId = $_SESSION['employee_id'] ?? 0;
        $attendance = [];
        $stats = ['present' => 0, 'absent' => 0, 'late' => 0, 'half_day' => 0, 'total_hours' => 0];
        $month = $_GET['month'] ?? date('Y-m');
        if ($employeeId > 0) {
            try {
                $attendance = $this->db->fetchAll(
                    "SELECT attendance_date AS date, check_in_time AS check_in, check_out_time AS check_out, hours_worked AS hours, status FROM employee_attendance WHERE employee_id = ? AND DATE_FORMAT(attendance_date, '%Y-%m') = ? ORDER BY attendance_date DESC",
                    [$employeeId, $month]
                );
                foreach ($attendance as $a) {
                    $s = strtolower($a['status'] ?? '');
                    if ($s === 'present' || $s === 'full day') $stats['present']++;
                    elseif ($s === 'absent') $stats['absent']++;
                    elseif ($s === 'late') $stats['late']++;
                    elseif ($s === 'half day') $stats['half_day']++;
                    $stats['total_hours'] += (float)($a['hours'] ?? 0);
                }
                $stats['total_hours'] = round($stats['total_hours'], 1);
            } catch (\Exception $e) { $attendance = []; }
        }
        $this->render('users/attendance', [
            'page_title' => 'Attendance',
            'page_description' => 'Your attendance records',
            'attendance' => $attendance,
            'stats' => $stats,
            'month' => $month,
        ]);
    }

    // ── Employee Performance ──
    public function performancePage()
    {
        $employeeId = $_SESSION['employee_id'] ?? 0;
        $overall = ['tasks_completed' => 0, 'on_time_rate' => 0, 'rating' => 0, 'attendance_percent' => 0, 'total_tasks' => 0];
        $reviews = [];
        $recentTasks = [];
        if ($employeeId > 0) {
            try {
                $completed = (int)($this->db->fetch("SELECT COUNT(*) as cnt FROM tasks WHERE assigned_to = ? AND status = 'completed'", [$employeeId])['cnt'] ?? 0);
                $total = (int)($this->db->fetch("SELECT COUNT(*) as cnt FROM tasks WHERE assigned_to = ?", [$employeeId])['cnt'] ?? 0);
                $onTime = (int)($this->db->fetch("SELECT COUNT(*) as cnt FROM tasks WHERE assigned_to = ? AND status = 'completed' AND (completed_at <= due_date OR due_date IS NULL)", [$employeeId])['cnt'] ?? 0);
                $overall['tasks_completed'] = $completed;
                $overall['total_tasks'] = $total;
                $overall['on_time_rate'] = $completed > 0 ? round(($onTime / $completed) * 100) : 0;

                $reviews = $this->db->fetchAll(
                    "SELECT pr.*, u.name AS reviewer_name FROM performance_reviews pr LEFT JOIN users u ON pr.reviewer_id = u.id WHERE pr.employee_id = ? ORDER BY pr.review_date DESC LIMIT 10",
                    [$employeeId]
                );
                if (!empty($reviews)) {
                    $totalRating = 0;
                    foreach ($reviews as $r) $totalRating += (float)($r['overall_rating'] ?? 0);
                    $overall['rating'] = round($totalRating / count($reviews), 1);
                }

                $att = $this->db->fetch("SELECT COUNT(*) as present FROM employee_attendance WHERE employee_id = ? AND status = 'present' AND attendance_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)", [$employeeId]);
                $totalDays = (int)($this->db->fetch("SELECT COUNT(*) as cnt FROM employee_attendance WHERE employee_id = ? AND attendance_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)", [$employeeId])['cnt'] ?? 0);
                $presentDays = (int)($att['present'] ?? 0);
                $overall['attendance_percent'] = $totalDays > 0 ? round(($presentDays / $totalDays) * 100) : 0;

                $recentTasks = $this->db->fetchAll(
                    "SELECT title, priority, status, due_date, completed_at FROM tasks WHERE assigned_to = ? ORDER BY updated_at DESC LIMIT 8",
                    [$employeeId]
                );
            } catch (\Exception $e) { error_log("EmployeeController::" . __FUNCTION__ . " query failed: " . $e->getMessage()); }
        }
        $this->render('employees/performance', [
            'page_title' => 'Performance Overview',
            'page_description' => 'Track your performance metrics and goals',
            'overall' => $overall,
            'reviews' => $reviews,
            'recent_tasks' => $recentTasks,
        ]);
    }

    public function salary()
    {
        $employeeId = $_SESSION['employee_id'] ?? 0;
        $salary_history = [];
        $stats = ['total' => 0, 'paid' => 0, 'pending' => 0, 'total_earned' => 0];
        if ($employeeId > 0) {
            try {
                $salary_history = $this->db->fetchAll("SELECT * FROM salary_records WHERE employee_id = ? ORDER BY pay_date DESC LIMIT 12", [$employeeId]);
                $stats['total'] = count($salary_history);
                foreach ($salary_history as $s) {
                    $st = strtolower($s['status'] ?? '');
                    if ($st === 'paid') { $stats['paid']++; $stats['total_earned'] += (float)($s['net_pay'] ?? 0); }
                    else $stats['pending']++;
                }
            } catch (\Exception $e) {
                $salary_history = [];
            }
        }
        $this->render('users/salary_history', [
            'page_title' => 'Salary History',
            'page_description' => 'Your salary records',
            'salary_history' => $salary_history,
            'stats' => $stats,
        ]);
    }

    // ── Employee Documents ──
    public function documents()
    {
        $employeeId = $_SESSION['employee_id'] ?? 0;
        $documents = [];
        $stats = ['total' => 0, 'verified' => 0, 'pending' => 0, 'expired' => 0];
        if ($employeeId > 0) {
            try {
                $documents = $this->db->fetchAll(
                    "SELECT * FROM documents WHERE entity_type = 'employee' AND entity_id = ? ORDER BY uploaded_on DESC",
                    [$employeeId]
                );
                $now = date('Y-m-d');
                foreach ($documents as $d) {
                    $stats['total']++;
                    $vs = $d['verification_status'] ?? '';
                    if ($vs === 'verified') $stats['verified']++;
                    else $stats['pending']++;
                    if (!empty($d['expiry_date']) && $d['expiry_date'] < $now) $stats['expired']++;
                }
            } catch (\Exception $e) { $documents = []; }
        }
        $this->render('employees/documents', [
            'page_title' => 'My Documents',
            'page_description' => 'Manage your employee documents',
            'documents' => $documents,
            'stats' => $stats,
        ]);
    }

    public function uploadDocument()
    {
        $employeeId = $_SESSION['employee_id'] ?? 0;
        if (!$employeeId) { $this->redirect('/employee/login'); return; }
        try {
            if (!empty($_FILES['document_file']['name']) && $_FILES['document_file']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/../../../../assets/documents/employees/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                $safeName = preg_replace('/[^a-zA-Z0-9._-]/', '_', basename($_FILES['document_file']['name']));
                $newName = 'emp_' . $employeeId . '_' . time() . '_' . $safeName;
                $target = $uploadDir . $newName;
                if (move_uploaded_file($_FILES['document_file']['tmp_name'], $target)) {
                    $this->db->query(
                        "INSERT INTO documents (entity_type, entity_id, type, document_type, document_number, issued_by, issue_date, expiry_date, verification_status, uploaded_on) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())",
                        [
                            'employee_id', $employeeId,
                            $_POST['document_type'] ?? 'other',
                            $_POST['document_type'] ?? 'other',
                            $_POST['document_number'] ?? '',
                            $_POST['issued_by'] ?? '',
                            $_POST['issue_date'] ?? null,
                            $_POST['expiry_date'] ?? null,
                        ]
                    );
                    $_SESSION['success'] = 'Document uploaded successfully.';
                } else {
                    $_SESSION['error'] = 'Failed to move uploaded file.';
                }
            } else {
                $_SESSION['error'] = 'Please select a file to upload.';
            }
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Upload failed: ' . $e->getMessage();
        }
        $this->redirect('/employee/documents');
    }

    public function leaves()
    {
        $employeeId = $_SESSION['employee_id'] ?? 0;
        $leaveTypes = [];
        $leaveBalance = [];
        $leaves = [];
        $stats = ['total' => 0, 'pending' => 0, 'approved' => 0, 'rejected' => 0];
        try {
            $db = $this->db;
            $leaveTypes = $db->fetchAll("SELECT * FROM leave_types WHERE status = 'active' ORDER BY name ASC");
            if ($employeeId > 0) {
                $balanceRows = $db->fetchAll(
                    "SELECT elb.*, lt.name as type_name, lt.code as type_code, lt.color
                     FROM employee_leave_balances elb
                     JOIN leave_types lt ON elb.leave_type_id = lt.id
                     WHERE elb.employee_id = ? AND elb.year = YEAR(CURDATE())",
                    [$employeeId]
                );
                foreach ($balanceRows as $b) {
                    $leaveBalance[] = $b;
                }
                $leaves = $db->fetchAll(
                    "SELECT el.*, lt.name as type_name, lt.color as type_color
                     FROM employee_leaves el
                     LEFT JOIN leave_types lt ON el.leave_type_id = lt.id
                     WHERE el.employee_id = ?
                     ORDER BY el.created_at DESC LIMIT 50",
                    [$employeeId]
                );
                $stats['total'] = count($leaves);
                foreach ($leaves as $l) {
                    $st = strtolower($l['status'] ?? 'pending');
                    if (isset($stats[$st])) $stats[$st]++;
                }
            }
        } catch (\Exception $e) {
            error_log("Employee leaves error: " . $e->getMessage());
        }
        $this->render('users/leaves', [
            'page_title' => 'Leave Management',
            'page_description' => 'Apply for leaves and track your leave history',
            'leaveTypes' => $leaveTypes,
            'leaveBalance' => $leaveBalance,
            'leaves' => $leaves,
            'stats' => $stats,
        ]);
    }

    public function leaveApply()
    {
        $employeeId = $_SESSION['employee_id'] ?? 0;
        if ($employeeId <= 0) {
            $_SESSION['flash_error'] = 'Invalid session.';
            $this->redirect('/employee/leaves');
            return;
        }
        $leaveTypeId = (int)($_POST['leave_type_id'] ?? 0);
        $startDate = trim($_POST['start_date'] ?? '');
        $endDate = trim($_POST['end_date'] ?? '');
        $reason = trim($_POST['reason'] ?? '');
        $emergencyContact = trim($_POST['emergency_contact'] ?? '');
        $workCoverage = trim($_POST['work_coverage'] ?? '');
        if ($leaveTypeId <= 0 || !$startDate || !$endDate || !$reason) {
            $_SESSION['flash_error'] = 'Please fill all required fields (leave type, dates, reason).';
            $this->redirect('/employee/leaves');
            return;
        }
        if ($endDate < $startDate) {
            $_SESSION['flash_error'] = 'End date cannot be before start date.';
            $this->redirect('/employee/leaves');
            return;
        }
        $start = new \DateTime($startDate);
        $end = new \DateTime($endDate);
        $totalDays = $end->diff($start)->days + 1;
        try {
            $db = $this->db;
            $typeRow = $db->fetch("SELECT * FROM leave_types WHERE id = ? AND status = 'active'", [$leaveTypeId]);
            if (!$typeRow) {
                $_SESSION['flash_error'] = 'Invalid leave type selected.';
                $this->redirect('/employee/leaves');
                return;
            }
            $balance = $db->fetch(
                "SELECT * FROM employee_leave_balances WHERE employee_id = ? AND leave_type_id = ? AND year = YEAR(CURDATE())",
                [$employeeId, $leaveTypeId]
            );
            if ($balance && $balance['remaining_days'] < $totalDays) {
                $_SESSION['flash_error'] = 'Insufficient leave balance. You have ' . $balance['remaining_days'] . ' days remaining for ' . htmlspecialchars($typeRow['name']) . '.';
                $this->redirect('/employee/leaves');
                return;
            }
            $db->insert('employee_leaves', [
                'leave_type_id' => $leaveTypeId,
                'employee_id' => $employeeId,
                'leave_type' => strtolower($typeRow['code']),
                'start_date' => $startDate,
                'end_date' => $endDate,
                'total_days' => $totalDays,
                'reason' => $reason,
                'emergency_contact' => $emergencyContact ?: null,
                'work_coverage' => $workCoverage ?: null,
                'status' => 'pending',
            ]);
            $_SESSION['flash_success'] = 'Leave application submitted successfully! Waiting for approval.';
        } catch (\Exception $e) {
            error_log("Leave apply error: " . $e->getMessage());
            $_SESSION['flash_error'] = 'Failed to submit leave application. Please try again.';
        }
        $this->redirect('/employee/leaves');
    }

    public function leaveDetail($id = 0)
    {
        $employeeId = $_SESSION['employee_id'] ?? 0;
        $id = (int)$id;
        if ($id <= 0 || $employeeId <= 0) {
            $_SESSION['flash_error'] = 'Invalid request.';
            $this->redirect('/employee/leaves');
            return;
        }
        $leave = null;
        try {
            $leave = $this->db->fetch(
                "SELECT el.*, lt.name as type_name, lt.color as type_color,
                        u.name as approved_by_name
                 FROM employee_leaves el
                 LEFT JOIN leave_types lt ON el.leave_type_id = lt.id
                 LEFT JOIN users u ON el.approved_by = u.id
                 WHERE el.id = ? AND el.employee_id = ?",
                [$id, $employeeId]
            );
        } catch (\Exception $e) {
            error_log("Leave detail error: " . $e->getMessage());
        }
        if (!$leave) {
            $_SESSION['flash_error'] = 'Leave record not found.';
            $this->redirect('/employee/leaves');
            return;
        }
        $this->render('users/leave_detail', [
            'page_title' => 'Leave Details',
            'page_description' => 'View leave application details',
            'leave' => $leave,
        ]);
    }

    public function leaveCancel($id = 0)
    {
        $employeeId = $_SESSION['employee_id'] ?? 0;
        $id = (int)$id;
        if ($id <= 0 || $employeeId <= 0) {
            $_SESSION['flash_error'] = 'Invalid request.';
            $this->redirect('/employee/leaves');
            return;
        }
        try {
            $leave = $this->db->fetch(
                "SELECT * FROM employee_leaves WHERE id = ? AND employee_id = ? AND status = 'pending'",
                [$id, $employeeId]
            );
            if (!$leave) {
                $_SESSION['flash_error'] = 'Leave record not found or cannot be cancelled (only pending leaves can be cancelled).';
                $this->redirect('/employee/leaves');
                return;
            }
            $this->db->update('employee_leaves', ['status' => 'cancelled'], ['id' => $id]);
            $_SESSION['flash_success'] = 'Leave application cancelled successfully.';
        } catch (\Exception $e) {
            error_log("Leave cancel error: " . $e->getMessage());
            $_SESSION['flash_error'] = 'Failed to cancel leave application.';
        }
        $this->redirect('/employee/leaves');
    }

    // ── Employee Reporting Structure ──
    public function reporting()
    {
        $employeeId = $_SESSION['employee_id'] ?? 0;
        $employee = null;
        $manager = null;
        $subordinates = [];
        $departmentMembers = [];
        if ($employeeId > 0) {
            try {
                $employee = $this->db->fetch("SELECT * FROM employees WHERE id = ?", [$employeeId]);
                if ($employee) {
                    $dept = $employee['department'] ?? '';
                    $departmentMembers = $this->db->fetchAll(
                        "SELECT id, name, designation, role, email, phone, status FROM employees WHERE department = ? AND id != ? ORDER BY designation ASC",
                        [$dept, $employeeId]
                    );
                    $myDesig = $employee['designation'] ?? '';
                    $desigRow = $this->db->fetch("SELECT level FROM designations WHERE name = ? LIMIT 1", [$myDesig]);
                    $myLevel = (int)($desigRow['level'] ?? 3);
                    if ($myLevel > 1) {
                        $managerDesig = $this->db->fetch("SELECT name FROM designations WHERE department_id = (SELECT id FROM departments WHERE name = ? LIMIT 1) AND level = ? LIMIT 1", [$dept, $myLevel - 1]);
                        if ($managerDesig) {
                            $manager = $this->db->fetch("SELECT id, name, designation, role, email, phone FROM employees WHERE department = ? AND designation = ? LIMIT 1", [$dept, $managerDesig['name']]);
                        }
                        if (!$manager) {
                            $manager = $this->db->fetch("SELECT id, name, designation, role, email, phone FROM employees WHERE department = ? AND id != ? ORDER BY id ASC LIMIT 1", [$dept, $employeeId]);
                        }
                    }
                    $subordinates = $this->db->fetchAll(
                        "SELECT e.id, e.name, e.designation, e.role, e.email, e.phone, e.status,
                         (SELECT COUNT(*) FROM tasks t WHERE t.assigned_to = e.id AND t.status = 'completed') AS tasks_completed,
                         (SELECT COUNT(*) FROM tasks t WHERE t.assigned_to = e.id) AS total_tasks
                         FROM employees e WHERE e.department = ? AND e.id != ? ORDER BY e.designation ASC",
                        [$dept, $employeeId]
                    );
                    foreach ($subordinates as &$sub) {
                        $sub['performance_score'] = $sub['total_tasks'] > 0 ? round(($sub['tasks_completed'] / $sub['total_tasks']) * 100) : 0;
                    }
                    unset($sub);
                }
            } catch (\Exception $e) { error_log("EmployeeController::" . __FUNCTION__ . " query failed: " . $e->getMessage()); }
        }
        $this->render('users/reporting_structure', [
            'page_title' => 'Reporting Structure',
            'page_description' => 'Your team hierarchy and department',
            'employee' => $employee,
            'manager' => $manager,
            'subordinates' => $subordinates,
            'department_members' => $departmentMembers,
        ]);
    }

    public function userProperties()
    {
        $this->middleware('employee.auth');

        $status = $_GET['status'] ?? '';
        $search = trim($_GET['search'] ?? '');
        $page = (int)($_GET['page'] ?? 1);
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        $where = "WHERE 1=1";
        $params = [];

        if ($status) {
            $where .= " AND up.status = ?";
            $params[] = $status;
        }

        if ($search) {
            $where .= " AND (up.name LIKE ? OR up.phone LIKE ? OR up.email LIKE ? OR up.address LIKE ?)";
            $s = '%' . $search . '%';
            $params[] = $s; $params[] = $s; $params[] = $s; $params[] = $s;
        }

        try {
            $countSql = "SELECT COUNT(*) as total FROM user_properties up $where";
            $countStmt = $this->db->prepare($countSql);
            $countStmt->execute($params);
            $total = $countStmt->fetch(\PDO::FETCH_ASSOC)['total'] ?? 0;
        } catch (\Exception $e) {
            $total = 0;
        }
        $totalPages = max(1, ceil($total / $perPage));

        try {
            $sql = "SELECT up.*, s.name as state_name, d.name as district_name FROM user_properties up LEFT JOIN states s ON up.state_id = s.id LEFT JOIN districts d ON up.district_id = d.id $where ORDER BY up.created_at DESC LIMIT $perPage OFFSET $offset";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $properties = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $properties = [];
        }

        $data = [
            'page_title' => 'User Properties - Employee',
            'page_description' => 'Manage user-submitted properties',
            'properties' => $properties,
            'status' => $status,
            'search' => $search,
            'page' => $page,
            'totalPages' => $totalPages,
            'total' => $total
        ];
        $this->render('users/user_properties', $data);
    }

    public function updatePropertyStatus()
    {
        $this->middleware('employee.auth');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/employee/user-properties');
        }

        $id = (int)($_POST['id'] ?? 0);
        $action = $_POST['action'] ?? '';
        $adminNotes = trim($_POST['admin_notes'] ?? '');
        $employeeId = $_SESSION['employee_id'] ?? 0;

        if (!$id || !in_array($action, ['verify', 'approve', 'reject', 'mark_sold'])) {
            $_SESSION['error'] = 'Invalid request';
            $this->redirect('/employee/user-properties');
        }

        $statusMap = ['verify' => 'verified', 'approve' => 'approved', 'reject' => 'rejected', 'mark_sold' => 'sold'];
        $status = $statusMap[$action] ?? 'verified';

        try {
            if ($action === 'mark_sold') {
                $this->db->execute("UPDATE user_properties SET status = ?, verified_by = ?, verified_at = NOW(), sold_at = NOW(), admin_notes = ?, updated_at = NOW() WHERE id = ?", [$status, $employeeId, $adminNotes, $id]);
            } else {
                $this->db->execute("UPDATE user_properties SET status = ?, verified_by = ?, verified_at = NOW(), admin_notes = ?, updated_at = NOW() WHERE id = ?", [$status, $employeeId, $adminNotes, $id]);
            }

            // Notify property owner
            try {
                $prop = $this->db->fetchOne("SELECT * FROM user_properties WHERE id = ?", [$id]);
                if ($prop && !empty($prop['email'])) {
                    $subjects = ['approved' => 'Your property has been approved!', 'rejected' => 'Your property listing has been rejected', 'verified' => 'Your property has been verified'];
                    $msgs = ['approved' => "Congratulations! Your property '{$prop['name']}' has been approved and is now visible to buyers.", 'rejected' => "Your property '{$prop['name']}' has been rejected. Reason: $adminNotes", 'verified' => "Your property '{$prop['name']}' has been verified by our team."];
                    $to = $prop['email'];
                    $subject = $subjects[$action] ?? 'Property Status Update';
                    $message = ($msgs[$action] ?? 'Your property status has been updated.') . "\n\nContact: +91 92771 21112 | info@apsdreamhome.com";
                    @mail($to, $subject, $message, "From: info@apsdreamhome.com\r\nReply-To: info@apsdreamhome.com");
                }
            } catch (\Exception $e2) {
                error_log("Employee property notification error: " . $e2->getMessage());
            }

            $_SESSION['success'] = "Property #$id status updated to: $status";
        } catch (\Exception $e) {
            error_log("Employee property action error: " . $e->getMessage());
            $_SESSION['error'] = "Failed to update property status";
        }

        $this->redirect('/employee/user-properties');
    }

    public function getTasks()
    {
        header('Content-Type: application/json');
        try {
            echo json_encode(['success' => true, 'tasks' => $this->getEmployeeTasks($_SESSION['employee_id'] ?? 0)]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'tasks' => []]);
        }
        exit;
    }

    public function getPerformance()
    {
        header('Content-Type: application/json');
        try {
            echo json_encode(['success' => true, 'performance' => $this->getEmployeePerformance($_SESSION['employee_id'] ?? 0)]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'performance' => []]);
        }
        exit;
    }

    public function getAttendanceRecords()
    {
        header('Content-Type: application/json');
        try {
            echo json_encode(['success' => true, 'attendance' => $this->getEmployeeAttendance($_SESSION['employee_id'] ?? 0)]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'attendance' => []]);
        }
        exit;
    }

    public function notifications()
    {
        if (!isset($_SESSION['employee_id'])) {
            $this->redirect('/employee/login');
            return;
        }

        $employeeId = $_SESSION['employee_id'];

        $notifService = new \App\Services\Communication\NotificationService();
        $notifications = $notifService->getCustomerNotifications($employeeId);
        $unreadCount = $notifService->getUnreadCount($employeeId);

        $this->render('pages/user_notifications', [
            'page_title' => 'Notifications - Employee Portal',
            'user' => ['id' => $employeeId, 'name' => $_SESSION['employee_name'] ?? 'Employee'],
            'notifications' => $notifications,
            'unread_count' => $unreadCount,
        ]);
    }

    public function markNotificationRead($notificationId)
    {
        if (!isset($_SESSION['employee_id'])) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Not authenticated']);
            exit;
        }

        $notifService = new \App\Services\Communication\NotificationService();
        $notifService->markAsRead($notificationId);

        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit;
    }

    public function markAllNotificationsRead()
    {
        if (!isset($_SESSION['employee_id'])) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Not authenticated']);
            exit;
        }

        $employeeId = $_SESSION['employee_id'];
        $notifService = new \App\Services\Communication\NotificationService();
        $notifService->markAllAsRead($employeeId);

        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit;
    }

    /**
     * Generic department page — serves 16 missing employee sidebar routes
     * Maps slug → department config and renders shared department view
     */
    public function departmentPage($slug = 'reports')
    {
        if (!$this->isEmployeeLoggedIn()) {
            $this->redirect('/employee/login');
            return;
        }

        $departments = [
            'reports'               => ['title' => 'Reports & Analytics', 'icon' => 'fas fa-chart-bar', 'desc' => 'View and generate business reports, analytics dashboards, and performance metrics.', 'color' => '#3b82f6'],
            'tax'                   => ['title' => 'TDS & GST', 'icon' => 'fas fa-file-invoice-dollar', 'desc' => 'Manage TDS deductions, GST filings, tax compliance, and financial documentation.', 'color' => '#8b5cf6'],
            'leads'                 => ['title' => 'My Leads', 'icon' => 'fas fa-user-plus', 'desc' => 'View and manage assigned leads, track follow-ups, and update lead status.', 'color' => '#06b6d4'],
            'deals'                 => ['title' => 'Deals Pipeline', 'icon' => 'fas fa-handshake', 'desc' => 'Track deals through the sales pipeline, view value, and manage negotiations.', 'color' => '#10b981'],
            'employees'             => ['title' => 'Employees', 'icon' => 'fas fa-users', 'desc' => 'View team members, department structure, and employee information.', 'color' => '#f59e0b'],
            'recruitment'           => ['title' => 'Recruitment', 'icon' => 'fas fa-user-tie', 'desc' => 'Manage job postings, review applications, and track hiring pipeline.', 'color' => '#ef4444'],
            'infrastructure'        => ['title' => 'Infrastructure', 'icon' => 'fas fa-server', 'desc' => 'Monitor IT infrastructure, network status, and system health.', 'color' => '#6366f1'],
            'compliance'            => ['title' => 'Compliance', 'icon' => 'fas fa-shield-alt', 'desc' => 'Track regulatory compliance, KYC status, and audit requirements.', 'color' => '#14b8a6'],
            'surveys'               => ['title' => 'Site Surveys', 'icon' => 'fas fa-map-marked-alt', 'desc' => 'Schedule and track site visits, survey reports, and location assessments.', 'color' => '#f97316'],
            'construction-dashboard'=> ['title' => 'Construction Dashboard', 'icon' => 'fas fa-hard-hat', 'desc' => 'Monitor ongoing construction, progress tracking, and milestone management.', 'color' => '#84cc16'],
            'projects'              => ['title' => 'Projects', 'icon' => 'fas fa-project-diagram', 'desc' => 'Track project timelines, deliverables, and resource allocation.', 'color' => '#ec4899'],
            'quality'               => ['title' => 'Quality Control', 'icon' => 'fas fa-clipboard-check', 'desc' => 'Track quality audits, defect reports, and improvement actions.', 'color' => '#22c55e'],
            'campaigns'             => ['title' => 'Marketing Campaigns', 'icon' => 'fas fa-bullhorn', 'desc' => 'Manage marketing campaigns, track performance, and ROI analytics.', 'color' => '#a855f7'],
            'vendors'               => ['title' => 'Vendors', 'icon' => 'fas fa-truck', 'desc' => 'Manage vendor relationships, contracts, and payment tracking.', 'color' => '#0ea5e9'],
            'cs-dashboard'          => ['title' => 'Customer Success', 'icon' => 'fas fa-smile-beam', 'desc' => 'Track customer satisfaction, support tickets, and retention metrics.', 'color' => '#f43f5e'],
            'complaints'            => ['title' => 'Complaints', 'icon' => 'fas fa-exclamation-triangle', 'desc' => 'View and resolve customer complaints, track resolution time.', 'color' => '#dc2626'],
            'hr-dashboard'          => ['title' => 'HR Dashboard', 'icon' => 'fas fa-users-cog', 'desc' => 'Human resources overview: attendance, leaves, payroll, and employee management.', 'color' => '#f59e0b'],
            'it-dashboard'          => ['title' => 'IT Dashboard', 'icon' => 'fas fa-laptop-code', 'desc' => 'IT operations: system health, network status, and technology infrastructure.', 'color' => '#6366f1'],
            'legal-dashboard'       => ['title' => 'Legal Dashboard', 'icon' => 'fas fa-gavel', 'desc' => 'Legal operations: document management, compliance tracking, and case management.', 'color' => '#8b5cf6'],
            'land-dashboard'        => ['title' => 'Land Dashboard', 'icon' => 'fas fa-map', 'desc' => 'Land acquisition: parcel tracking, survey status, and land bank overview.', 'color' => '#10b981'],
            'marketing-dashboard'   => ['title' => 'Marketing Dashboard', 'icon' => 'fas fa-bullseye', 'desc' => 'Marketing overview: campaigns, lead sources, and performance metrics.', 'color' => '#a855f7'],
            'ops-dashboard'         => ['title' => 'Operations Dashboard', 'icon' => 'fas fa-cogs', 'desc' => 'Operations overview: daily tasks, vendor management, and workflow tracking.', 'color' => '#0ea5e9'],
        ];

        $dept = $departments[$slug] ?? null;
        if (!$dept) {
            $this->redirect('/employee/dashboard');
            return;
        }

        $employeeId = $_SESSION['employee_id'] ?? $_SESSION['user_id'] ?? 0;
        $employeeName = $_SESSION['employee_name'] ?? $_SESSION['user_name'] ?? 'Employee';

        // Department-specific stats
        $deptStats = $this->getDepartmentStats($slug);

        return $this->render('employees/department', [
            'page_title' => $dept['title'],
            'dept_title' => $dept['title'],
            'dept_icon'  => $dept['icon'],
            'dept_desc'  => $dept['desc'],
            'dept_color' => $dept['color'],
            'dept_slug'  => $slug,
            'employee_id' => $employeeId,
            'employee_name' => $employeeName,
            'stats' => $deptStats,
        ]);
    }

    private function getDepartmentStats($slug)
    {
        $default = ['total' => 0, 'active' => 0, 'pending' => 0, 'completed' => 0];
        try {
            $db = $this->db;
            switch ($slug) {
                case 'leads':
                    $stmt = $db->prepare("SELECT COUNT(*) as total, SUM(CASE WHEN status='new' THEN 1 ELSE 0 END) as pending, SUM(CASE WHEN status IN ('contacted','qualified') THEN 1 ELSE 0 END) as active, SUM(CASE WHEN status='converted' THEN 1 ELSE 0 END) as completed FROM leads");
                    $stmt->execute(); return $stmt->fetch() + $default;
                case 'deals':
                    $stmt = $db->prepare("SELECT COUNT(*) as total, SUM(CASE WHEN status='open' THEN 1 ELSE 0 END) as active, SUM(CASE WHEN status='negotiation' THEN 1 ELSE 0 END) as pending, SUM(CASE WHEN status='won' THEN 1 ELSE 0 END) as completed FROM lead_deals");
                    $stmt->execute(); return $stmt->fetch() + $default;
                case 'employees':
                    $stmt = $db->prepare("SELECT COUNT(*) as total, SUM(CASE WHEN status='active' THEN 1 ELSE 0 END) as active, SUM(CASE WHEN status='on_leave' THEN 1 ELSE 0 END) as pending, 0 as completed FROM users WHERE role IN ('employee','telecaller','backoffice_staff')");
                    $stmt->execute(); return $stmt->fetch() + $default;
                case 'campaigns':
                    $stmt = $db->prepare("SELECT COUNT(*) as total, SUM(CASE WHEN status='active' THEN 1 ELSE 0 END) as active, SUM(CASE WHEN status='draft' THEN 1 ELSE 0 END) as pending, SUM(CASE WHEN status='completed' THEN 1 ELSE 0 END) as completed FROM marketing_campaigns");
                    $stmt->execute(); return $stmt->fetch() + $default;
                case 'complaints':
                    $stmt = $db->prepare("SELECT COUNT(*) as total, SUM(CASE WHEN status='open' THEN 1 ELSE 0 END) as active, SUM(CASE WHEN status='in_progress' THEN 1 ELSE 0 END) as pending, SUM(CASE WHEN status='resolved' THEN 1 ELSE 0 END) as completed FROM support_tickets WHERE type='complaint'");
                    $stmt->execute(); return $stmt->fetch() + $default;
                case 'compliance':
                    $stmt = $db->prepare("SELECT COUNT(*) as total, SUM(CASE WHEN kyc_verified=1 THEN 1 ELSE 0 END) as active, SUM(CASE WHEN kyc_verified=0 OR kyc_verified IS NULL THEN 1 ELSE 0 END) as pending, 0 as completed FROM users WHERE role='customer'");
                    $stmt->execute(); return $stmt->fetch() + $default;
                case 'vendors':
                    $stmt = $db->prepare("SELECT COUNT(*) as total, SUM(CASE WHEN status='active' THEN 1 ELSE 0 END) as active, 0 as pending, 0 as completed FROM vendors");
                    $stmt->execute(); return $stmt->fetch() + $default;
                case 'recruitment':
                    $stmt = $db->prepare("SELECT COUNT(*) as total, SUM(CASE WHEN status='open' THEN 1 ELSE 0 END) as active, SUM(CASE WHEN status='on_hold' THEN 1 ELSE 0 END) as pending, SUM(CASE WHEN status='filled' THEN 1 ELSE 0 END) as completed FROM career_postings");
                    $stmt->execute(); return $stmt->fetch() + $default;
                case 'projects':
                    $stmt = $db->prepare("SELECT COUNT(*) as total, SUM(CASE WHEN status='active' THEN 1 ELSE 0 END) as active, SUM(CASE WHEN status='planning' THEN 1 ELSE 0 END) as pending, SUM(CASE WHEN status='completed' THEN 1 ELSE 0 END) as completed FROM projects");
                    $stmt->execute(); return $stmt->fetch() + $default;
                case 'reports':
                    $stmt = $db->prepare("SELECT COUNT(*) as total FROM user_activity_logs_unified WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
                    $stmt->execute(); $r = $stmt->fetch(); return ['total' => $r['total'] ?? 0, 'active' => 0, 'pending' => 0, 'completed' => 0];
                default:
                    return $default;
            }
        } catch (\Exception $e) {
            error_log("DeptStats error for {$slug}: " . $e->getMessage());
            return $default;
        }
    }

    /**
     * Employee leads list — shows only leads assigned to this employee
     */
    public function leads()
    {
        if (!isset($_SESSION['employee_id'])) {
            $this->redirect('/employee/login');
            return;
        }
        $employeeId = $_SESSION['employee_id'];
        $status = $_GET['status'] ?? '';
        $search = trim($_GET['search'] ?? '');
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        $leads = [];
        $total = 0;

        try {
            $where = 'WHERE l.assigned_to = ?';
            $params = [$employeeId];

            if ($status && $status !== 'all') {
                $where .= ' AND l.status = ?';
                $params[] = $status;
            }
            if ($search) {
                $where .= ' AND (l.name LIKE ? OR l.phone LIKE ? OR l.email LIKE ?)';
                $searchTerm = "%{$search}%";
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }

            $countRow = $this->db->fetchOne(
                "SELECT COUNT(*) as cnt FROM leads l {$where}",
                $params
            );
            $total = (int)($countRow['cnt'] ?? 0);

            $params[] = $perPage;
            $params[] = $offset;
            $leads = $this->db->fetchAll(
                "SELECT l.*, u.name as assigned_by_name
                 FROM leads l
                 LEFT JOIN users u ON u.id = l.assigned_by
                 {$where}
                 ORDER BY l.created_at DESC
                 LIMIT ? OFFSET ?",
                $params
            ) ?: [];
        } catch (\Throwable $e) {
            error_log('Employee leads error: ' . $e->getMessage());
        }

        $totalPages = max(1, (int)ceil($total / $perPage));

        $this->render('employee/leads', [
            'page_title' => 'My Leads',
            'leads' => $leads,
            'total' => $total,
            'page' => $page,
            'totalPages' => $totalPages,
            'status' => $status,
            'search' => $search,
        ]);
    }

    /**
     * Employee lead detail — shows single lead with timeline + notes
     */
    public function leadDetail(int $id)
    {
        if (!isset($_SESSION['employee_id'])) {
            $this->redirect('/employee/login');
            return;
        }
        $employeeId = $_SESSION['employee_id'];
        $lead = null;
        $activities = [];
        $notes = [];

        try {
            $lead = $this->db->fetchOne(
                "SELECT l.*, u.name as assigned_by_name
                 FROM leads l
                 LEFT JOIN users u ON u.id = l.assigned_by
                 WHERE l.id = ? AND l.assigned_to = ?",
                [$id, $employeeId]
            );

            if (!$lead) {
                $_SESSION['error'] = 'Lead not found or not assigned to you';
                $this->redirect('/employee/leads');
                return;
            }

            $activities = $this->db->fetchAll(
                "SELECT * FROM lead_activities WHERE lead_id = ? ORDER BY created_at DESC LIMIT 30",
                [$id]
            ) ?: [];

            $notes = $this->db->fetchAll(
                "SELECT * FROM lead_notes WHERE lead_id = ? ORDER BY created_at DESC",
                [$id]
            ) ?: [];
        } catch (\Throwable $e) {
            error_log('Employee leadDetail error: ' . $e->getMessage());
        }

        $statuses = ['new', 'contacted', 'qualified', 'site_visit', 'proposal', 'negotiation', 'booking', 'won', 'lost', 'nurture'];

        $this->render('employee/lead_detail', [
            'page_title' => 'Lead: ' . ($lead['name'] ?? 'Detail'),
            'lead' => $lead,
            'activities' => $activities,
            'notes' => $notes,
            'statuses' => $statuses,
        ]);
    }

    /**
     * Update lead status (AJAX POST)
     */
    public function updateLeadStatus(int $id)
    {
        if (!isset($_SESSION['employee_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }
        $employeeId = $_SESSION['employee_id'];
        $newStatus = trim($_POST['status'] ?? '');

        if (empty($newStatus)) {
            $_SESSION['error'] = 'Status is required';
            $this->redirect('/employee/leads/' . $id);
            return;
        }

        try {
            $lead = $this->db->fetchOne(
                "SELECT id, status FROM leads WHERE id = ? AND assigned_to = ?",
                [$id, $employeeId]
            );

            if (!$lead) {
                $_SESSION['error'] = 'Lead not found or not assigned to you';
                $this->redirect('/employee/leads');
                return;
            }

            $oldStatus = $lead['status'];
            $this->db->execute(
                "UPDATE leads SET status = ?, updated_at = NOW() WHERE id = ?",
                [$newStatus, $id]
            );

            $this->db->execute(
                "INSERT INTO lead_activities (lead_id, activity_type, description, created_by, created_at)
                 VALUES (?, 'status_change', ?, ?, NOW())",
                [$id, "Status changed from " . ucfirst($oldStatus) . " to " . ucfirst($newStatus), $employeeId]
            );

            $_SESSION['success'] = 'Lead status updated to ' . ucfirst($newStatus);
        } catch (\Throwable $e) {
            error_log('Employee updateLeadStatus error: ' . $e->getMessage());
            $_SESSION['error'] = 'Failed to update status';
        }

        $this->redirect('/employee/leads/' . $id);
    }

    /**
     * Add note to lead
     */
    public function addLeadNote(int $id)
    {
        if (!isset($_SESSION['employee_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }
        $employeeId = $_SESSION['employee_id'];
        $note = trim($_POST['note'] ?? '');

        if (empty($note)) {
            $_SESSION['error'] = 'Note cannot be empty';
            $this->redirect('/employee/leads/' . $id);
            return;
        }

        try {
            $lead = $this->db->fetchOne(
                "SELECT id FROM leads WHERE id = ? AND assigned_to = ?",
                [$id, $employeeId]
            );

            if (!$lead) {
                $_SESSION['error'] = 'Lead not found';
                $this->redirect('/employee/leads');
                return;
            }

            $this->db->execute(
                "INSERT INTO lead_notes (lead_id, note, content, created_by, created_at)
                 VALUES (?, ?, ?, ?, NOW())",
                [$id, $note, $note, $employeeId]
            );

            $this->db->execute(
                "INSERT INTO lead_activities (lead_id, activity_type, description, created_by, created_at)
                 VALUES (?, 'note_added', ?, ?, NOW())",
                [$id, 'Note added: ' . substr($note, 0, 100), $employeeId]
            );

            $_SESSION['success'] = 'Note added successfully';
        } catch (\Throwable $e) {
            error_log('Employee addLeadNote error: ' . $e->getMessage());
            $_SESSION['error'] = 'Failed to add note';
        }

        $this->redirect('/employee/leads/' . $id);
    }

    public function addLead()
    {
        if (!isset($_SESSION['employee_id'])) { $this->redirect('/employee/login'); return; }

        $sources = [];
        $assignees = [];
        try {
            $sources = $this->db->fetchAll("SELECT id, name FROM lead_sources ORDER BY name") ?: [];
            $users = $this->db->fetchAll("SELECT id, name FROM users WHERE role IN ('employee','admin','manager') AND deleted_at IS NULL ORDER BY name") ?: [];
            $assignees = $users;
        } catch (\Throwable $e) { error_log("EmployeeController::" . __FUNCTION__ . " query failed: " . $e->getMessage()); }

        return $this->render('employee/lead_form', [
            'page_title' => 'Add New Lead',
            'sources' => $sources,
            'assignees' => $assignees,
            'mode' => 'create',
        ]);
    }

    public function storeLead()
    {
        if (!isset($_SESSION['employee_id'])) { $this->redirect('/employee/login'); return; }
        $employeeId = $_SESSION['employee_id'];

        $guard = \App\Services\CRMGuard::getInstance();
        if (!$guard->isCrmEnabled()) {
            $_SESSION['error'] = 'CRM is currently disabled';
            $this->redirect('/employee/leads');
            return;
        }
        if (!$guard->canCreateLead('employee')) {
            $_SESSION['error'] = 'You do not have permission to create leads';
            $this->redirect('/employee/leads');
            return;
        }

        $name = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $source = trim($_POST['source'] ?? 'manual');
        $budget = (float)($_POST['budget'] ?? 0);
        $city = trim($_POST['city'] ?? '');
        $notes = trim($_POST['notes'] ?? '');
        $propertyInterest = trim($_POST['property_interest'] ?? '');

        if (empty($name) || empty($phone)) {
            $_SESSION['error'] = 'Name and phone are required';
            $this->redirect('/employee/leads/add');
            return;
        }

        try {
            $existing = $this->db->fetchOne("SELECT id FROM leads WHERE phone = ? AND deleted_at IS NULL", [$phone]);
            if ($existing) {
                $_SESSION['error'] = 'A lead with this phone already exists';
                $this->redirect('/employee/leads/add');
                return;
            }

            $this->db->execute(
                "INSERT INTO leads (name, phone, email, source, budget, city, notes, property_interest, status, assigned_to, created_by, created_at, updated_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'new', ?, ?, NOW(), NOW())",
                [$name, $phone, $email, $source, $budget > 0 ? $budget : null, $city, $notes, $propertyInterest, $employeeId, $employeeId]
            );
            $leadId = $this->db->lastInsertId();

            $this->db->execute(
                "INSERT INTO lead_activities (lead_id, activity_type, description, created_by, created_at)
                 VALUES (?, 'created', 'Lead created by employee', ?, NOW())",
                [$leadId, $employeeId]
            );

            $_SESSION['success'] = 'Lead created successfully';
            $this->redirect('/employee/leads');
        } catch (\Throwable $e) {
            error_log('Employee storeLead error: ' . $e->getMessage());
            $_SESSION['error'] = 'Failed to create lead: ' . $e->getMessage();
            $this->redirect('/employee/leads/add');
        }
    }

    public function deleteLead(int $id)
    {
        if (!isset($_SESSION['employee_id'])) { $this->redirect('/employee/login'); return; }
        $employeeId = $_SESSION['employee_id'];

        $guard = \App\Services\CRMGuard::getInstance();
        if (!$guard->isCrmEnabled() || !$guard->canDeleteLead('employee')) {
            $_SESSION['error'] = 'You do not have permission to delete leads';
            $this->redirect('/employee/leads');
            return;
        }

        try {
            $lead = $this->db->fetchOne("SELECT id, assigned_to, created_by FROM leads WHERE id = ? AND deleted_at IS NULL", [$id]);
            if (!$lead || ((int)$lead['assigned_to'] !== $employeeId && (int)$lead['created_by'] !== $employeeId)) {
                $_SESSION['error'] = 'Lead not found or access denied';
                $this->redirect('/employee/leads');
                return;
            }

            $this->db->execute("UPDATE leads SET deleted_at = NOW() WHERE id = ?", [$id]);
            $this->db->execute(
                "INSERT INTO lead_activities (lead_id, activity_type, description, created_by, created_at)
                 VALUES (?, 'delete', 'Lead soft-deleted by employee', ?, NOW())",
                [$id, $employeeId]
            );

            $_SESSION['success'] = 'Lead moved to trash';
        } catch (\Throwable $e) {
            error_log('Employee deleteLead error: ' . $e->getMessage());
            $_SESSION['error'] = 'Failed to delete lead';
        }

        $this->redirect('/employee/leads');
    }
}
