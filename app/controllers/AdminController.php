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
}
