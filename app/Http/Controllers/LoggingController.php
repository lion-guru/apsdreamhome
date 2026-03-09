<?php

namespace App\Http\Controllers;

use App\Services\LoggingService;

/**
 * Custom Logging Controller - APS Dream Home
 * Custom MVC implementation without Laravel dependencies
 * Following APS Dream Home custom architecture patterns
 */
class LoggingController
{
    private $loggingService;
    private $viewRenderer;

    public function __construct()
    {
        $this->loggingService = new LoggingService();
        $this->viewRenderer = new \App\Core\View();
    }

    /**
     * Show logging dashboard
     */
    public function showDashboard($request)
    {
        // Check authentication
        $authService = new \App\Services\Auth\AuthenticationService();
        if (!$authService->isAuthenticated() || !$authService->hasPermission('view_logs')) {
            $_SESSION['errors'] = ['Access denied'];
            $this->redirect('/login');
            return;
        }

        // Get log statistics
        $stats = $this->loggingService->getLogStats(24);

        $data = [
            'title' => 'Logging Dashboard - APS Dream Home',
            'user' => $authService->getCurrentUser(),
            'stats' => $stats,
            'success' => $_SESSION['success'] ?? '',
            'errors' => $_SESSION['errors'] ?? []
        ];

        unset($_SESSION['success'], $_SESSION['errors']);

        return $this->viewRenderer->render('logging/dashboard', $data);
    }

    /**
     * Show log viewer
     */
    public function showLogs($request)
    {
        // Check authentication
        $authService = new \App\Services\Auth\AuthenticationService();
        if (!$authService->isAuthenticated() || !$authService->hasPermission('view_logs')) {
            $_SESSION['errors'] = ['Access denied'];
            $this->redirect('/login');
            return;
        }

        $category = $request['get']['category'] ?? 'system';
        $page = max(1, intval($request['get']['page'] ?? 1));
        $limit = 50;
        $offset = ($page - 1) * $limit;

        // Get logs from database
        $database = \App\Core\Database::getInstance();
        $logs = $database->select(
            "SELECT * FROM system_logs 
             WHERE category = ? 
             ORDER BY created_at DESC 
             LIMIT ? OFFSET ?",
            [$category, $limit, $offset]
        );

        // Get total count
        $total = $database->selectOne(
            "SELECT COUNT(*) as count FROM system_logs WHERE category = ?",
            [$category]
        )['count'];

        $data = [
            'title' => 'System Logs - APS Dream Home',
            'user' => $authService->getCurrentUser(),
            'logs' => $logs,
            'category' => $category,
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'totalPages' => ceil($total / $limit),
            'success' => $_SESSION['success'] ?? '',
            'errors' => $_SESSION['errors'] ?? []
        ];

        unset($_SESSION['success'], $_SESSION['errors']);

        return $this->viewRenderer->render('logging/logs', $data);
    }

    /**
     * Show security alerts
     */
    public function showSecurityAlerts($request)
    {
        // Check authentication
        $authService = new \App\Services\Auth\AuthenticationService();
        if (!$authService->isAuthenticated() || !$authService->hasPermission('view_security_alerts')) {
            $_SESSION['errors'] = ['Access denied'];
            $this->redirect('/login');
            return;
        }

        $page = max(1, intval($request['get']['page'] ?? 1));
        $limit = 25;
        $offset = ($page - 1) * $limit;

        // Get security alerts
        $database = \App\Core\Database::getInstance();
        $alerts = $database->select(
            "SELECT * FROM security_alerts 
             WHERE status = 'active' 
             ORDER BY created_at DESC 
             LIMIT ? OFFSET ?",
            [$limit, $offset]
        );

        // Get total count
        $total = $database->selectOne(
            "SELECT COUNT(*) as count FROM security_alerts WHERE status = 'active'"
        )['count'];

        $data = [
            'title' => 'Security Alerts - APS Dream Home',
            'user' => $authService->getCurrentUser(),
            'alerts' => $alerts,
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'totalPages' => ceil($total / $limit),
            'success' => $_SESSION['success'] ?? '',
            'errors' => $_SESSION['errors'] ?? []
        ];

        unset($_SESSION['success'], $_SESSION['errors']);

        return $this->viewRenderer->render('logging/security-alerts', $data);
    }

    /**
     * Export logs
     */
    public function exportLogs($request)
    {
        // Check authentication
        $authService = new \App\Services\Auth\AuthenticationService();
        if (!$authService->isAuthenticated() || !$authService->hasPermission('export_logs')) {
            return [
                'success' => false,
                'message' => 'Access denied'
            ];
        }

        $category = $request['post']['category'] ?? null;
        $startDate = $request['post']['start_date'] ?? null;
        $endDate = $request['post']['end_date'] ?? null;

        try {
            $csvFile = $this->loggingService->exportLogs($category, $startDate, $endDate);

            $_SESSION['success'] = 'Logs exported successfully';

            return [
                'success' => true,
                'message' => 'Export completed',
                'file' => basename($csvFile),
                'path' => $csvFile
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Export failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Clean old logs
     */
    public function cleanLogs($request)
    {
        // Check authentication
        $authService = new \App\Services\Auth\AuthenticationService();
        if (!$authService->isAuthenticated() || !$authService->hasPermission('clean_logs')) {
            return [
                'success' => false,
                'message' => 'Access denied'
            ];
        }

        $days = intval($request['post']['days'] ?? 30);

        if ($days < 1 || $days > 365) {
            return [
                'success' => false,
                'message' => 'Days must be between 1 and 365'
            ];
        }

        try {
            $this->loggingService->cleanOldLogs($days);

            $_SESSION['success'] = "Logs older than $days days cleaned successfully";

            return [
                'success' => true,
                'message' => 'Cleanup completed'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Cleanup failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get log statistics (AJAX)
     */
    public function getLogStats($request)
    {
        // Check authentication
        $authService = new \App\Services\Auth\AuthenticationService();
        if (!$authService->isAuthenticated() || !$authService->hasPermission('view_logs')) {
            return [
                'success' => false,
                'message' => 'Access denied'
            ];
        }

        $hours = intval($request['get']['hours'] ?? 24);

        if ($hours < 1 || $hours > 168) { // Max 7 days
            return [
                'success' => false,
                'message' => 'Hours must be between 1 and 168'
            ];
        }

        $stats = $this->loggingService->getLogStats($hours);

        return [
            'success' => true,
            'data' => $stats
        ];
    }

    /**
     * Search logs
     */
    public function searchLogs($request)
    {
        // Check authentication
        $authService = new \App\Services\Auth\AuthenticationService();
        if (!$authService->isAuthenticated() || !$authService->hasPermission('view_logs')) {
            $_SESSION['errors'] = ['Access denied'];
            $this->redirect('/login');
            return;
        }

        $search = $request['get']['search'] ?? '';
        $category = $request['get']['category'] ?? 'system';
        $page = max(1, intval($request['get']['page'] ?? 1));
        $limit = 50;
        $offset = ($page - 1) * $limit;

        if (empty($search)) {
            // Redirect to normal logs view
            $this->redirect('/logs?category=' . $category);
            return;
        }

        // Search logs
        $database = \App\Core\Database::getInstance();
        $logs = $database->select(
            "SELECT * FROM system_logs 
             WHERE category = ? AND (message LIKE ? OR context LIKE ?)
             ORDER BY created_at DESC 
             LIMIT ? OFFSET ?",
            [$category, "%$search%", "%$search%", $limit, $offset]
        );

        // Get total count
        $total = $database->selectOne(
            "SELECT COUNT(*) as count FROM system_logs 
             WHERE category = ? AND (message LIKE ? OR context LIKE ?)",
            [$category, "%$search%", "%$search%"]
        )['count'];

        $data = [
            'title' => 'Search Logs - APS Dream Home',
            'user' => $authService->getCurrentUser(),
            'logs' => $logs,
            'search' => $search,
            'category' => $category,
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'totalPages' => ceil($total / $limit),
            'success' => $_SESSION['success'] ?? '',
            'errors' => $_SESSION['errors'] ?? []
        ];

        unset($_SESSION['success'], $_SESSION['errors']);

        return $this->viewRenderer->render('logging/search', $data);
    }

    /**
     * View log details
     */
    public function viewLogDetails($request)
    {
        // Check authentication
        $authService = new \App\Services\Auth\AuthenticationService();
        if (!$authService->isAuthenticated() || !$authService->hasPermission('view_logs')) {
            $_SESSION['errors'] = ['Access denied'];
            $this->redirect('/login');
            return;
        }

        $logId = $request['params']['id'] ?? null;

        if (!$logId) {
            $_SESSION['errors'] = ['Log ID is required'];
            $this->redirect('/logs');
            return;
        }

        // Get log details
        $database = \App\Core\Database::getInstance();
        $log = $database->selectOne(
            "SELECT * FROM system_logs WHERE id = ?",
            [$logId]
        );

        if (!$log) {
            $_SESSION['errors'] = ['Log not found'];
            $this->redirect('/logs');
            return;
        }

        // Decode context
        $log['context'] = json_decode($log['context'], true) ?: [];

        $data = [
            'title' => 'Log Details - APS Dream Home',
            'user' => $authService->getCurrentUser(),
            'log' => $log,
            'success' => $_SESSION['success'] ?? '',
            'errors' => $_SESSION['errors'] ?? []
        ];

        unset($_SESSION['success'], $_SESSION['errors']);

        return $this->viewRenderer->render('logging/details', $data);
    }

    /**
     * Dismiss security alert
     */
    public function dismissAlert($request)
    {
        // Check authentication
        $authService = new \App\Services\Auth\AuthenticationService();
        if (!$authService->isAuthenticated() || !$authService->hasPermission('manage_security_alerts')) {
            return [
                'success' => false,
                'message' => 'Access denied'
            ];
        }

        $alertId = $request['post']['alert_id'] ?? null;

        if (!$alertId) {
            return [
                'success' => false,
                'message' => 'Alert ID is required'
            ];
        }

        try {
            $database = \App\Core\Database::getInstance();
            $updated = $database->update(
                'security_alerts',
                [
                    'status' => 'dismissed',
                    'dismissed_by' => $authService->getCurrentUser()['id'],
                    'dismissed_at' => date('Y-m-d H:i:s')
                ],
                'id = ?',
                [$alertId]
            );

            if ($updated) {
                return [
                    'success' => true,
                    'message' => 'Alert dismissed successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to dismiss alert'
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get real-time log stream (AJAX)
     */
    public function getLogStream($request)
    {
        // Check authentication
        $authService = new \App\Services\Auth\AuthenticationService();
        if (!$authService->isAuthenticated() || !$authService->hasPermission('view_logs')) {
            return [
                'success' => false,
                'message' => 'Access denied'
            ];
        }

        $category = $request['get']['category'] ?? 'system';
        $lastId = intval($request['get']['last_id'] ?? 0);

        // Get recent logs
        $database = \App\Core\Database::getInstance();
        $logs = $database->select(
            "SELECT * FROM system_logs 
             WHERE category = ? AND id > ? 
             ORDER BY created_at DESC 
             LIMIT 10",
            [$category, $lastId]
        );

        return [
            'success' => true,
            'data' => $logs,
            'last_id' => $lastId
        ];
    }

    /**
     * Redirect helper
     */
    private function redirect($url)
    {
        if (!headers_sent()) {
            header("Location: $url");
            exit;
        } else {
            echo '<script>window.location.href = "' . $url . '";</script>';
            exit;
        }
    }
}
