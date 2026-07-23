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

    // Missing page methods - each renders its view
    public function tasks()
    {
        $data = ['page_title' => 'My Tasks', 'page_description' => 'View and manage your tasks'];
        $this->render('users/tasks', $data);
    }

    public function activities()
    {
        $employeeId = $_SESSION['employee_id'] ?? 0;
        $activities = [];
        if ($employeeId > 0) {
            try {
                $activities = $this->db->fetchAll("SELECT * FROM activity_logs_unified WHERE user_id = ? ORDER BY created_at DESC LIMIT 100", [$employeeId]);
            } catch (\Exception $e) {
                $activities = [];
            }
        }
        $data = [
            'page_title' => 'Activities',
            'page_description' => 'Your recent activities',
            'activities' => $activities
        ];
        $this->render('users/activities', $data);
    }

    public function attendance()
    {
        $data = ['page_title' => 'Attendance', 'page_description' => 'Your attendance records'];
        $this->render('users/attendance', $data);
    }

    public function performancePage()
    {
        $data = ['page_title' => 'Performance', 'page_description' => 'Your performance metrics'];
        $this->render('users/performance', $data);
    }

    public function salary()
    {
        $employeeId = $_SESSION['employee_id'] ?? 0;
        $salary_history = [];
        if ($employeeId > 0) {
            try {
                $salary_history = $this->db->fetchAll("SELECT * FROM salary_records WHERE employee_id = ? ORDER BY pay_date DESC LIMIT 12", [$employeeId]);
            } catch (\Exception $e) {
                $salary_history = [];
            }
        }
        $data = [
            'page_title' => 'Salary History',
            'page_description' => 'Your salary records',
            'salary_history' => $salary_history
        ];
        $this->render('users/salary_history', $data);
    }

    public function documents()
    {
        $employeeId = $_SESSION['employee_id'] ?? 0;
        $documents = [];
        if ($employeeId > 0) {
            try {
                $documents = $this->db->fetchAll("SELECT * FROM documents WHERE entity_type = 'employee' AND entity_id = ? ORDER BY uploaded_on DESC", [$employeeId]);
            } catch (\Exception $e) {
                $documents = [];
            }
        }
        $data = [
            'page_title' => 'Documents',
            'page_description' => 'Your documents',
            'documents' => $documents
        ];
        $this->render('users/documents', $data);
    }

    public function leaves()
    {
        $data = ['page_title' => 'Leaves', 'page_description' => 'Your leave records'];
        $this->render('users/leaves', $data);
    }

    public function reporting()
    {
        $data = ['page_title' => 'Reporting', 'page_description' => 'Your reporting structure'];
        $this->render('users/reporting_structure', $data);
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

        return $this->render('employees/department', [
            'page_title' => $dept['title'],
            'dept_title' => $dept['title'],
            'dept_icon'  => $dept['icon'],
            'dept_desc'  => $dept['desc'],
            'dept_color' => $dept['color'],
            'dept_slug'  => $slug,
            'employee_id' => $employeeId,
            'employee_name' => $employeeName,
        ]);
    }
}
