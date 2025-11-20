<?php

namespace App\Controllers;

use App\Services\AdminService;

class AdminController extends Controller {
    private $adminService;

    public function __construct() {
        parent::__construct();
        try {
            $this->adminService = new AdminService();
            $this->requireAdmin();
        } catch (\RuntimeException $e) {
            // If there's a database error, show error page
            $this->view('admin/error', [
                'title' => 'Database Error',
                'message' => $e->getMessage()
            ]);
            exit();
        }
    }

    /**
     * Display admin dashboard
     */
    public function dashboard() {
        $stats = $this->adminService->getDashboardStats();
        $recentActivities = $this->adminService->getRecentActivities();
        $systemHealth = $this->adminService->getSystemHealth();

        $this->view('admin/dashboard', [
            'title' => 'Admin Dashboard',
            'stats' => $stats,
            'recentActivities' => $recentActivities,
            'systemHealth' => $systemHealth
        ]);
    }

    /**
     * Display user management
     */
    public function users() {
        $filters = [
            'search' => $_GET['search'] ?? null,
            'role' => $_GET['role'] ?? null,
            'status' => $_GET['status'] ?? null,
            'page' => (int)($_GET['page'] ?? 1),
            'per_page' => (int)($_GET['per_page'] ?? 20)
        ];

        $users = $this->adminService->getUsers($filters);
        $userStats = $this->adminService->getUserStats();

        $this->view('admin/users', [
            'title' => 'User Management',
            'users' => $users,
            'filters' => $filters,
            'userStats' => $userStats
        ]);
    }

    /**
     * Display property management
     */
    public function properties() {
        $filters = [
            'search' => $_GET['search'] ?? null,
            'type' => $_GET['type'] ?? null,
            'status' => $_GET['status'] ?? null,
            'user_id' => $_GET['user_id'] ?? null,
            'page' => (int)($_GET['page'] ?? 1),
            'per_page' => (int)($_GET['per_page'] ?? 20)
        ];

        $properties = $this->adminService->getProperties($filters);
        $propertyStats = $this->adminService->getPropertyStats();

        $this->view('admin/properties', [
            'title' => 'Property Management',
            'properties' => $properties,
            'filters' => $filters,
            'propertyStats' => $propertyStats
        ]);
    }

    /**
     * Display booking management
     */
    public function bookings() {
        $filters = [
            'search' => $_GET['search'] ?? null,
            'status' => $_GET['status'] ?? null,
            'property_id' => $_GET['property_id'] ?? null,
            'user_id' => $_GET['user_id'] ?? null,
            'page' => (int)($_GET['page'] ?? 1),
            'per_page' => (int)($_GET['per_page'] ?? 20)
        ];

        $bookings = $this->adminService->getBookings($filters);
        $bookingStats = $this->adminService->getBookingStats();

        $this->view('admin/bookings', [
            'title' => 'Booking Management',
            'bookings' => $bookings,
            'filters' => $filters,
            'bookingStats' => $bookingStats
        ]);
    }

    /**
     * Display lead management
     */
    public function leads() {
        $filters = [
            'search' => $_GET['search'] ?? null,
            'status' => $_GET['status'] ?? null,
            'source' => $_GET['source'] ?? null,
            'assigned_to' => $_GET['assigned_to'] ?? null,
            'page' => (int)($_GET['page'] ?? 1),
            'per_page' => (int)($_GET['per_page'] ?? 20)
        ];

        $leads = $this->adminService->getLeads($filters);
        $leadStats = $this->adminService->getLeadStats();

        $this->view('admin/leads', [
            'title' => 'Lead Management',
            'leads' => $leads,
            'filters' => $filters,
            'leadStats' => $leadStats
        ]);
    }

    /**
     * Display reports
     */
    public function reports() {
        $reportType = $_GET['type'] ?? 'overview';
        $dateRange = [
            'start' => $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days')),
            'end' => $_GET['end_date'] ?? date('Y-m-d')
        ];

        // Simple report data for demo
        $report = [
            'total_revenue' => 0,
            'total_properties' => 0,
            'total_leads' => 0,
            'total_users' => 0,
            'report_type' => $reportType,
            'date_range' => $dateRange
        ];

        $availableReports = [
            'overview' => 'Overview Dashboard',
            'properties' => 'Property Performance',
            'leads' => 'Lead Analytics'
        ];

        $this->view('admin/reports', [
            'title' => 'Reports & Analytics',
            'report' => $report,
            'reportType' => $reportType,
            'dateRange' => $dateRange,
            'availableReports' => $availableReports
        ]);
    }

    /**
     * Display system settings
     */
    public function settings() {
        $settings = $this->adminService->getAllSettings();
        $settingGroups = $this->adminService->getSettingGroups();

        $this->view('admin/settings', [
            'title' => 'System Settings',
            'settings' => $settings,
            'settingGroups' => $settingGroups
        ]);
    }

    /**
     * Update system settings
     */
    public function updateSettings() {
        try {
            $settings = $_POST['settings'] ?? [];

            foreach ($settings as $key => $value) {
                $this->adminService->updateSetting($key, $value);
            }

            $_SESSION['success'] = 'Settings updated successfully!';
            $this->redirect('/admin/settings');

        } catch (\Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            $this->redirect('/admin/settings');
        }
    }

    /**
     * Display database management
     */
    public function database() {
        $dbStats = $this->adminService->getDatabaseStats();
        $backupFiles = $this->adminService->getBackupFiles();

        $this->view('admin/database', [
            'title' => 'Database Management',
            'dbStats' => $dbStats,
            'backupFiles' => $backupFiles
        ]);
    }

    /**
     * Create database backup
     */
    public function createBackup() {
        try {
            $backupFile = $this->adminService->createBackup();
            $_SESSION['success'] = 'Database backup created: ' . basename($backupFile);
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Backup failed: ' . $e->getMessage();
        }

        $this->redirect('/admin/database');
    }

    /**
     * Display system logs
     */
    public function logs() {
        $logType = $_GET['type'] ?? 'error';
        $logFile = $_GET['file'] ?? 'error.log';
        $lines = (int)($_GET['lines'] ?? 100);

        $logs = $this->adminService->getLogs($logType, $lines);
        $availableLogs = $this->adminService->getAvailableLogFiles();

        $this->view('admin/logs', [
            'title' => 'System Logs',
            'logs' => $logs,
            'logType' => $logType,
            'logFile' => $logFile,
            'availableLogs' => $availableLogs
        ]);
    }

    /**
     * Clear system cache
     */
    public function clearCache() {
        try {
            $this->adminService->clearCache();
            $_SESSION['success'] = 'Cache cleared successfully!';
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Failed to clear cache: ' . $e->getMessage();
        }

        $this->redirect('/admin/dashboard');
    }

    /**
     * Export data
     */
    public function export($type) {
        try {
            $data = $this->adminService->exportData($type);
            $filename = $type . '_export_' . date('Y-m-d_H-i-s') . '.csv';

            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="' . $filename . '"');

            $output = fopen('php://output', 'w');

            if (!empty($data)) {
                // Add headers
                fputcsv($output, array_keys($data[0]));

                // Add data
                foreach ($data as $row) {
                    fputcsv($output, $row);
                }
            }

            fclose($output);
            exit;

        } catch (\Exception $e) {
            $_SESSION['error'] = 'Export failed: ' . $e->getMessage();
            $this->redirect('/admin/' . $type);
        }
    }

    /**
     * Handle user authentication
     */
    public function authenticate() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new \Exception('Invalid request method');
            }

            $email = trim($_POST['email'] ?? '');
            $password = trim($_POST['password'] ?? '');

            if (empty($email) || empty($password)) {
                $_SESSION['error'] = 'Please enter both email and password';
                header('Location: admin.php');
                exit();
            }

            // Simple authentication for demo (in production, use proper password hashing)
            $demo_users = [
                'admin@apsdreamhome.com' => ['password' => 'admin123', 'role' => 'admin', 'name' => 'Administrator'],
                'rajesh@apsdreamhome.com' => ['password' => 'agent123', 'role' => 'agent', 'name' => 'Rajesh Kumar'],
                'amit@example.com' => ['password' => 'customer123', 'role' => 'customer', 'name' => 'Amit Sharma']
            ];

            if (isset($demo_users[$email]) && $demo_users[$email]['password'] === $password) {
                // Authentication successful
                $_SESSION['auser'] = $demo_users[$email]['name'];
                $_SESSION['user_id'] = array_search($email, array_keys($demo_users)) + 1;
                $_SESSION['role'] = $demo_users[$email]['role'];
                $_SESSION['email'] = $email;

                $_SESSION['success'] = 'Login successful! Welcome to APS Dream Home.';
                header('Location: admin.php');
                exit();
            } else {
                $_SESSION['error'] = 'Invalid email or password';
                header('Location: admin.php');
                exit();
            }

        } catch (\Exception $e) {
            $_SESSION['error'] = 'Authentication failed. Please try again.';
            header('Location: admin.php');
            exit();
        }
    }

    /**
     * Display employees management
     */
    public function employees()
    {
        $employeeModel = new \App\Models\Employee();

        // Get employees with filters
        $filters = [];
        if (!empty($_GET['search'])) {
            $filters['search'] = $_GET['search'];
        }
        if (!empty($_GET['department_id'])) {
            $filters['department_id'] = $_GET['department_id'];
        }
        if (!empty($_GET['role_id'])) {
            $filters['role_id'] = $_GET['role_id'];
        }
        if (!empty($_GET['status'])) {
            $filters['status'] = $_GET['status'];
        }

        $employees = $employeeModel->getEmployeesForAdmin($filters);
        $stats = $employeeModel->getEmployeeStats();
        $departments = $employeeModel->getDepartments();
        $roles = $employeeModel->getRoles();

        $data = [
            'employees' => $employees,
            'stats' => $stats,
            'departments' => $departments,
            'roles' => $roles,
            'filters' => $filters,
            'page_title' => 'Employee Management - APS Dream Home'
        ];

        $this->view('admin/employees', $data);
    }

    /**
     * Display employee creation form
     */
    public function createEmployee()
    {
        $employeeModel = new \App\Models\Employee();
        $departments = $employeeModel->getDepartments();
        $roles = $employeeModel->getRoles();

        $data = [
            'departments' => $departments,
            'roles' => $roles,
            'page_title' => 'Add New Employee - APS Dream Home'
        ];

        $this->view('admin/create_employee', $data);
    }

    /**
     * Store new employee
     */
    public function storeEmployee()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/employees');
        }

        $employeeModel = new \App\Models\Employee();

        $data = [
            'name' => $_POST['name'] ?? '',
            'email' => $_POST['email'] ?? '',
            'password' => $_POST['password'] ?? '',
            'phone' => $_POST['phone'] ?? '',
            'role_id' => $_POST['role_id'] ?? null,
            'department_id' => $_POST['department_id'] ?? null,
            'designation' => $_POST['designation'] ?? '',
            'salary' => $_POST['salary'] ?? 0,
            'joining_date' => $_POST['joining_date'] ?? date('Y-m-d'),
            'reporting_manager_id' => $_POST['reporting_manager_id'] ?? null
        ];

        $employeeId = $employeeModel->createEmployee($data);

        if ($employeeId) {
            $_SESSION['success'] = 'Employee created successfully.';
        } else {
            $_SESSION['error'] = 'Failed to create employee. Please try again.';
        }

        $this->redirect('/admin/employees');
    }

    /**
     * Display employee details
     */
    public function showEmployee($employeeId)
    {
        $employeeModel = new \App\Models\Employee();
        $employee = $employeeModel->getEmployeeById($employeeId);

        if (!$employee) {
            $_SESSION['error'] = 'Employee not found.';
            $this->redirect('/admin/employees');
        }

        // Get additional data
        $activities = $employeeModel->getEmployeeActivities($employeeId, ['per_page' => 20]);
        $tasks = $employeeModel->getEmployeeTasks($employeeId, ['per_page' => 20]);
        $attendance = $employeeModel->getEmployeeAttendance($employeeId, ['per_page' => 20]);
        $leaves = $employeeModel->getEmployeeLeaves($employeeId, ['per_page' => 20]);
        $performance = $employeeModel->getEmployeePerformance($employeeId);
        $reportingStructure = $employeeModel->getReportingStructure($employeeId);

        $data = [
            'employee' => $employee,
            'activities' => $activities,
            'tasks' => $tasks,
            'attendance' => $attendance,
            'leaves' => $leaves,
            'performance' => $performance,
            'reporting_structure' => $reportingStructure,
            'page_title' => 'Employee Details - APS Dream Home'
        ];

        $this->view('admin/employee_details', $data);
    }

    /**
     * Display employee edit form
     */
    public function editEmployee($employeeId)
    {
        $employeeModel = new \App\Models\Employee();
        $employee = $employeeModel->getEmployeeById($employeeId);

        if (!$employee) {
            $_SESSION['error'] = 'Employee not found.';
            $this->redirect('/admin/employees');
        }

        $departments = $employeeModel->getDepartments();
        $roles = $employeeModel->getRoles();

        $data = [
            'employee' => $employee,
            'departments' => $departments,
            'roles' => $roles,
            'page_title' => 'Edit Employee - APS Dream Home'
        ];

        $this->view('admin/edit_employee', $data);
    }

    /**
     * Update employee
     */
    public function updateEmployee($employeeId)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/employees');
        }

        $employeeModel = new \App\Models\Employee();

        $data = [
            'name' => $_POST['name'] ?? '',
            'phone' => $_POST['phone'] ?? '',
            'role_id' => $_POST['role_id'] ?? null,
            'department_id' => $_POST['department_id'] ?? null,
            'designation' => $_POST['designation'] ?? '',
            'salary' => $_POST['salary'] ?? 0,
            'status' => $_POST['status'] ?? 'active',
            'reporting_manager_id' => $_POST['reporting_manager_id'] ?? null,
            'emergency_contact' => $_POST['emergency_contact'] ?? '',
            'blood_group' => $_POST['blood_group'] ?? '',
            'address' => $_POST['address'] ?? '',
            'city' => $_POST['city'] ?? '',
            'state' => $_POST['state'] ?? '',
            'pincode' => $_POST['pincode'] ?? ''
        ];

        $success = $employeeModel->updateEmployee($employeeId, $data);

        if ($success) {
            $_SESSION['success'] = 'Employee updated successfully.';
        } else {
            $_SESSION['error'] = 'Failed to update employee. Please try again.';
        }

        $this->redirect('/admin/employees');
    }

    /**
     * Deactivate employee
     */
    public function deactivateEmployee($employeeId)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/employees');
        }

        $employeeModel = new \App\Models\Employee();
        $reason = $_POST['reason'] ?? 'Deactivated by admin';

        $success = $employeeModel->deactivateEmployee($employeeId, $reason);

        if ($success) {
            $_SESSION['success'] = 'Employee deactivated successfully.';
        } else {
            $_SESSION['error'] = 'Failed to deactivate employee. Please try again.';
        }

        $this->redirect('/admin/employees');
    }

    /**
     * Reactivate employee
     */
    public function reactivateEmployee($employeeId)
    {
        $employeeModel = new \App\Models\Employee();
        $success = $employeeModel->reactivateEmployee($employeeId);

        if ($success) {
            $_SESSION['success'] = 'Employee reactivated successfully.';
        } else {
            $_SESSION['error'] = 'Failed to reactivate employee. Please try again.';
        }

        $this->redirect('/admin/employees');
    }

    /**
     * Create employee task
     */
    public function createEmployeeTask($employeeId)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/employees');
        }

        $employeeModel = new \App\Models\Employee();

        $data = [
            'title' => $_POST['title'] ?? '',
            'description' => $_POST['description'] ?? '',
            'assigned_to' => $employeeId,
            'assigned_by' => $_SESSION['admin_id'],
            'project_id' => $_POST['project_id'] ?? null,
            'task_type_id' => $_POST['task_type_id'] ?? null,
            'priority' => $_POST['priority'] ?? 'medium',
            'status' => 'pending',
            'due_date' => $_POST['due_date'] ?? null,
            'estimated_hours' => $_POST['estimated_hours'] ?? null
        ];

        $success = $employeeModel->createEmployeeTask($data);

        if ($success) {
            $_SESSION['success'] = 'Task assigned successfully.';
        } else {
            $_SESSION['error'] = 'Failed to assign task. Please try again.';
        }

        $this->redirect('/admin/employee/' . $employeeId);
    }

    /**
     * Update employee password
     */
    public function updateEmployeePassword($employeeId)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/employees');
        }

        $employeeModel = new \App\Models\Employee();
        $newPassword = $_POST['new_password'] ?? '';

        if (strlen($newPassword) < 6) {
            $_SESSION['error'] = 'Password must be at least 6 characters long.';
            $this->redirect('/admin/employee/' . $employeeId);
        }

        $success = $employeeModel->updateEmployeePassword($employeeId, $newPassword);

        if ($success) {
            $_SESSION['success'] = 'Employee password updated successfully.';
        } else {
            $_SESSION['error'] = 'Failed to update password. Please try again.';
        }

        $this->redirect('/admin/employee/' . $employeeId);
    }

    /**
     * Get employees by department
     */
    public function getEmployeesByDepartment($departmentId)
    {
        $employeeModel = new \App\Models\Employee();
        $employees = $employeeModel->getEmployeesByDepartment($departmentId);

        header('Content-Type: application/json');
        echo json_encode($employees);
    }

    /**
     * Display hybrid MLM plan builder
     */
    public function hybridMLMPlanBuilder()
    {
        // Get existing plans for reference
        $existingPlans = $this->getMLMPlans();

        $data = [
            'existing_plans' => $existingPlans,
            'page_title' => 'Hybrid MLM Plan Builder - APS Dream Home'
        ];

        $this->view('admin/hybrid_mlm_plan_builder', $data);
    }

    /**
     * Create new MLM plan
     */
    public function createMLMPlan()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/mlm-plan-builder');
        }

        $planData = [
            'plan_name' => $_POST['plan_name'] ?? '',
            'plan_code' => $_POST['plan_code'] ?? '',
            'plan_type' => $_POST['plan_type'] ?? 'hybrid',
            'description' => $_POST['description'] ?? '',
            'joining_fee' => $_POST['joining_fee'] ?? 0,
            'monthly_target' => $_POST['monthly_target'] ?? 0,
            'levels' => $_POST['levels'] ?? [],
            'bonuses' => $_POST['bonuses'] ?? []
        ];

        $result = $this->createMLMPlanInDatabase($planData);

        if ($result['success']) {
            $_SESSION['success'] = 'MLM Plan created successfully!';
        } else {
            $_SESSION['error'] = 'Failed to create MLM plan: ' . $result['message'];
        }

        $this->redirect('/admin/mlm-plan-builder');
    }

    /**
     * Display MLM analytics dashboard
     */
    public function mlmAnalytics()
    {
        $analytics = $this->generateMLMAnalytics();

        $data = [
            'analytics' => $analytics,
            'page_title' => 'MLM Analytics Dashboard - APS Dream Home'
        ];

        $this->view('admin/mlm_analytics', $data);
    }

    /**
     * Get all MLM plans
     */
    private function getMLMPlans()
    {
        $sql = "SELECT * FROM mlm_commission_plans WHERE status = 'active' ORDER BY created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Create MLM plan in database
     */
    private function createMLMPlanInDatabase($planData)
    {
        try {
            $this->db->beginTransaction();

            // Insert main plan
            $sql = "INSERT INTO mlm_commission_plans
                    (plan_name, plan_code, plan_type, description, joining_fee, monthly_target, status, created_by)
                    VALUES (?, ?, ?, ?, ?, ?, 'active', ?)";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $planData['plan_name'],
                $planData['plan_code'],
                $planData['plan_type'],
                $planData['description'],
                $planData['joining_fee'],
                $planData['monthly_target'],
                $_SESSION['admin_id']
            ]);

            $planId = $this->db->lastInsertId();

            // Insert plan levels
            if (!empty($planData['levels'])) {
                foreach ($planData['levels'] as $level) {
                    if (!empty($level['level_name'])) {
                        $levelSql = "INSERT INTO mlm_plan_levels
                                    (plan_id, level_name, level_order, direct_commission, team_commission,
                                     level_bonus, matching_bonus, leadership_bonus, performance_bonus, monthly_target)
                                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

                        $levelStmt = $this->db->prepare($levelSql);
                        $levelStmt->execute([
                            $planId,
                            $level['level_name'],
                            $level['level_order'] ?? 1,
                            $level['direct_commission'] ?? 0,
                            $level['team_commission'] ?? 0,
                            $level['level_bonus'] ?? 0,
                            $level['matching_bonus'] ?? 0,
                            $level['leadership_bonus'] ?? 0,
                            $level['performance_bonus'] ?? 0,
                            $level['monthly_target'] ?? 0
                        ]);
                    }
                }
            }

            // Insert plan bonuses
            if (!empty($planData['bonuses'])) {
                foreach ($planData['bonuses'] as $bonus) {
                    if (!empty($bonus['bonus_name'])) {
                        $bonusSql = "INSERT INTO mlm_plan_bonuses
                                    (plan_id, bonus_name, bonus_type, bonus_percentage, min_achievement, max_achievement)
                                    VALUES (?, ?, ?, ?, ?, ?)";

                        $bonusStmt = $this->db->prepare($bonusSql);
                        $bonusStmt->execute([
                            $planId,
                            $bonus['bonus_name'],
                            $bonus['bonus_type'] ?? 'percentage',
                            $bonus['bonus_percentage'] ?? 0,
                            $bonus['min_achievement'] ?? 0,
                            $bonus['max_achievement'] ?? 0
                        ]);
                    }
                }
            }

            $this->db->commit();
            return ['success' => true, 'message' => 'Plan created successfully'];

        } catch (\Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Generate comprehensive MLM analytics
     */
    private function generateMLMAnalytics()
    {
        $analytics = [];

        // Network growth analytics
        $analytics['network_growth'] = $this->getNetworkGrowthAnalytics();

        // Commission analytics
        $analytics['commission_analytics'] = $this->getCommissionAnalytics();

        // Rank distribution
        $analytics['rank_distribution'] = $this->getRankDistribution();

        // Top performers
        $analytics['top_performers'] = $this->getTopPerformers();

        // Business volume trends
        $analytics['volume_trends'] = $this->getVolumeTrends();

        return $analytics;
    }

    private function getNetworkGrowthAnalytics()
    {
        $sql = "
            SELECT
                COUNT(*) as total_associates,
                SUM(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) as new_this_month,
                SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_associates,
                AVG(total_business) as avg_business_per_associate
            FROM mlm_agents
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    private function getCommissionAnalytics()
    {
        $sql = "
            SELECT
                SUM(commission_amount) as total_commissions_paid,
                AVG(commission_amount) as avg_commission_per_transaction,
                COUNT(*) as total_commission_transactions,
                SUM(CASE WHEN commission_date >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN commission_amount ELSE 0 END) as this_month_commissions
            FROM commission_history
            WHERE commission_date >= DATE_SUB(NOW(), INTERVAL 90 DAY)
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    private function getRankDistribution()
    {
        $sql = "
            SELECT current_level, COUNT(*) as count
            FROM mlm_agents
            GROUP BY current_level
            ORDER BY count DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    private function getTopPerformers()
    {
        $sql = "
            SELECT
                ma.id, ma.full_name, ma.current_level, ma.total_business,
                SUM(ch.commission_amount) as total_earned,
                COUNT(ch.id) as transaction_count
            FROM mlm_agents ma
            LEFT JOIN commission_history ch ON ma.id = ch.associate_id
            WHERE ch.commission_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY ma.id, ma.full_name, ma.current_level, ma.total_business
            ORDER BY total_earned DESC
            LIMIT 10
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    private function getVolumeTrends()
    {
        $sql = "
            SELECT
                DATE_FORMAT(created_at, '%Y-%m') as month,
                SUM(total_business) as total_volume,
                COUNT(*) as transaction_count
            FROM mlm_agents
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
            GROUP BY DATE_FORMAT(created_at, '%Y-%m')
            ORDER BY month DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Display projects management
     */
    public function projects()
    {
        $projectModel = new \App\Models\Project();

        // Get projects with filters
        $filters = [];
        if (!empty($_GET['search'])) {
            $filters['search'] = $_GET['search'];
        }
        if (!empty($_GET['city'])) {
            $filters['city'] = $_GET['city'];
        }
        if (!empty($_GET['project_type'])) {
            $filters['project_type'] = $_GET['project_type'];
        }
        if (!empty($_GET['status'])) {
            $filters['status'] = $_GET['status'];
        }

        $projects = $projectModel->searchProjects($_GET['search'] ?? '', $filters);
        $stats = $projectModel->getProjectStats();
        $cities = $projectModel->getUniqueCities();
        $projectTypes = $projectModel->getUniqueProjectTypes();

        $data = [
            'projects' => $projects,
            'stats' => $stats,
            'cities' => $cities,
            'project_types' => $projectTypes,
            'filters' => $filters,
            'page_title' => 'Project Management - APS Dream Home'
        ];

        $this->view('admin/projects', $data);
    }

    /**
     * Display project creation form
     */
    public function createProject()
    {
        $projectModel = new \App\Models\Project();
        $cities = $projectModel->getUniqueCities();

        $data = [
            'cities' => $cities,
            'page_title' => 'Add New Project - APS Dream Home'
        ];

        $this->view('admin/create_project', $data);
    }

    /**
     * Store new project
     */
    public function storeProject()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/projects');
        }

        $projectModel = new \App\Models\Project();

        $data = [
            'project_name' => $_POST['project_name'] ?? '',
            'project_code' => $_POST['project_code'] ?? '',
            'project_type' => $_POST['project_type'] ?? '',
            'location' => $_POST['location'] ?? '',
            'city' => $_POST['city'] ?? '',
            'state' => $_POST['state'] ?? '',
            'pincode' => $_POST['pincode'] ?? '',
            'description' => $_POST['description'] ?? '',
            'short_description' => $_POST['short_description'] ?? '',
            'total_area' => $_POST['total_area'] ?? 0,
            'total_plots' => $_POST['total_plots'] ?? 0,
            'available_plots' => $_POST['available_plots'] ?? 0,
            'price_per_sqft' => $_POST['price_per_sqft'] ?? 0,
            'base_price' => $_POST['base_price'] ?? 0,
            'project_status' => $_POST['project_status'] ?? 'ongoing',
            'possession_date' => $_POST['possession_date'] ?? null,
            'rera_number' => $_POST['rera_number'] ?? '',
            'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
            'latitude' => $_POST['latitude'] ?? null,
            'longitude' => $_POST['longitude'] ?? null,
            'address' => $_POST['address'] ?? '',
            'highlights' => $_POST['highlights'] ?? [],
            'amenities' => $_POST['amenities'] ?? [],
            'layout_map' => $_POST['layout_map'] ?? '',
            'brochure' => $_POST['brochure'] ?? '',
            'gallery_images' => $_POST['gallery_images'] ?? [],
            'virtual_tour' => $_POST['virtual_tour'] ?? '',
            'booking_amount' => $_POST['booking_amount'] ?? 0,
            'emi_available' => isset($_POST['emi_available']) ? 1 : 0,
            'developer_name' => $_POST['developer_name'] ?? '',
            'developer_contact' => $_POST['developer_contact'] ?? '',
            'developer_email' => $_POST['developer_email'] ?? '',
            'project_head' => $_POST['project_head'] ?? '',
            'project_manager' => $_POST['project_manager'] ?? '',
            'sales_manager' => $_POST['sales_manager'] ?? '',
            'contact_number' => $_POST['contact_number'] ?? '',
            'contact_email' => $_POST['contact_email'] ?? '',
            'website' => $_POST['website'] ?? '',
            'seo_title' => $_POST['seo_title'] ?? '',
            'seo_description' => $_POST['seo_description'] ?? '',
            'seo_keywords' => $_POST['seo_keywords'] ?? '',
            'meta_image' => $_POST['meta_image'] ?? '',
            'created_by' => $_SESSION['admin_id'] ?? 1
        ];

        $projectId = $projectModel->createProject($data);

        if ($projectId) {
            $_SESSION['success'] = 'Project created successfully.';
        } else {
            $_SESSION['error'] = 'Failed to create project. Please try again.';
        }

        $this->redirect('/admin/projects');
    }

    /**
     * Display project details
     */
    public function showProject($projectId)
    {
        $projectModel = new \App\Models\Project();
        $project = $projectModel->getProjectById($projectId);

        if (!$project) {
            $_SESSION['error'] = 'Project not found.';
            $this->redirect('/admin/projects');
        }

        // Decode JSON fields for display
        $project['amenities'] = json_decode($project['amenities'] ?? '[]', true) ?: [];
        $project['highlights'] = json_decode($project['highlights'] ?? '[]', true) ?: [];
        $project['gallery_images'] = json_decode($project['gallery_images'] ?? '[]', true) ?: [];

        $data = [
            'project' => $project,
            'page_title' => 'Project Details - APS Dream Home'
        ];

        $this->view('admin/project_details', $data);
    }

    /**
     * Display project edit form
     */
    public function editProject($projectId)
    {
        $projectModel = new \App\Models\Project();
        $project = $projectModel->getProjectById($projectId);

        if (!$project) {
            $_SESSION['error'] = 'Project not found.';
            $this->redirect('/admin/projects');
        }

        $cities = $projectModel->getUniqueCities();

        // Decode JSON fields for editing
        $project['amenities'] = json_decode($project['amenities'] ?? '[]', true) ?: [];
        $project['highlights'] = json_decode($project['highlights'] ?? '[]', true) ?: [];
        $project['gallery_images'] = json_decode($project['gallery_images'] ?? '[]', true) ?: [];

        $data = [
            'project' => $project,
            'cities' => $cities,
            'page_title' => 'Edit Project - APS Dream Home'
        ];

        $this->view('admin/edit_project', $data);
    }

    /**
     * Update project
     */
    public function updateProject($projectId)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/projects');
        }

        $projectModel = new \App\Models\Project();

        $data = [
            'project_name' => $_POST['project_name'] ?? '',
            'project_code' => $_POST['project_code'] ?? '',
            'project_type' => $_POST['project_type'] ?? '',
            'location' => $_POST['location'] ?? '',
            'city' => $_POST['city'] ?? '',
            'state' => $_POST['state'] ?? '',
            'pincode' => $_POST['pincode'] ?? '',
            'description' => $_POST['description'] ?? '',
            'short_description' => $_POST['short_description'] ?? '',
            'total_area' => $_POST['total_area'] ?? 0,
            'total_plots' => $_POST['total_plots'] ?? 0,
            'available_plots' => $_POST['available_plots'] ?? 0,
            'price_per_sqft' => $_POST['price_per_sqft'] ?? 0,
            'base_price' => $_POST['base_price'] ?? 0,
            'project_status' => $_POST['project_status'] ?? 'ongoing',
            'possession_date' => $_POST['possession_date'] ?? null,
            'rera_number' => $_POST['rera_number'] ?? '',
            'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
            'latitude' => $_POST['latitude'] ?? null,
            'longitude' => $_POST['longitude'] ?? null,
            'address' => $_POST['address'] ?? '',
            'highlights' => $_POST['highlights'] ?? [],
            'amenities' => $_POST['amenities'] ?? [],
            'layout_map' => $_POST['layout_map'] ?? '',
            'brochure' => $_POST['brochure'] ?? '',
            'gallery_images' => $_POST['gallery_images'] ?? [],
            'virtual_tour' => $_POST['virtual_tour'] ?? '',
            'booking_amount' => $_POST['booking_amount'] ?? 0,
            'emi_available' => isset($_POST['emi_available']) ? 1 : 0,
            'developer_name' => $_POST['developer_name'] ?? '',
            'developer_contact' => $_POST['developer_contact'] ?? '',
            'developer_email' => $_POST['developer_email'] ?? '',
            'project_head' => $_POST['project_head'] ?? '',
            'project_manager' => $_POST['project_manager'] ?? '',
            'sales_manager' => $_POST['sales_manager'] ?? '',
            'contact_number' => $_POST['contact_number'] ?? '',
            'contact_email' => $_POST['contact_email'] ?? '',
            'website' => $_POST['website'] ?? '',
            'seo_title' => $_POST['seo_title'] ?? '',
            'seo_description' => $_POST['seo_description'] ?? '',
            'seo_keywords' => $_POST['seo_keywords'] ?? '',
            'meta_image' => $_POST['meta_image'] ?? ''
        ];

        $success = $projectModel->updateProject($projectId, $data);

        if ($success) {
            $_SESSION['success'] = 'Project updated successfully.';
        } else {
            $_SESSION['error'] = 'Failed to update project. Please try again.';
        }

        $this->redirect('/admin/projects');
    }

    /**
     * Delete project
     */
    public function deleteProject($projectId)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/projects');
        }

        $projectModel = new \App\Models\Project();
        $success = $projectModel->deleteProject($projectId);

        if ($success) {
            $_SESSION['success'] = 'Project deleted successfully.';
        } else {
            $_SESSION['error'] = 'Failed to delete project. Please try again.';
        }

        $this->redirect('/admin/projects');
    }
}
