<?php

namespace App\Http\Controllers\Admin;

use App\Core\App;

/**
 * CM Dashboard Controller
 * Chief Manager Dashboard
 */
class CMDashboardController extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        // $this->db is already set by BaseController::__construct()
    }

    /**
     * CM Dashboard Index
     */
    public function index()
    {
        // Check admin authentication
        if (!isset($_SESSION['admin_id'])) {
            header('Location: ' . BASE_URL . '/admin/login');
            exit;
        }

        // Check if user has CM role
        if ($_SESSION['admin_role'] !== 'cm' && $_SESSION['admin_role'] !== 'super_admin') {
            header('Location: ' . BASE_URL . '/admin/dashboard');
            exit;
        }

        // Get CM specific stats
        $stats = $this->getCMStats();
        $teamPerformance = $this->getTeamPerformance();
        $recentActivities = $this->getCmRecentActivities();
        $projectsOverview = $this->getProjectsOverview();

        $this->render('dashboard/cm_dashboard', [
            'page_title' => 'CM Dashboard - APS Dream Home',
            'stats' => $stats,
            'team_performance' => $teamPerformance,
            'recentActivities' => $recentActivities,
            'projectsOverview' => $projectsOverview
        ]);
    }

    /**
     * Get CM specific statistics
     */
    private function getCMStats()
    {
        try {
            $stats = [];

            // Team size
            $stmt = $this->db->prepare("SELECT COUNT(*) as team_size FROM users WHERE role IN ('employee', 'associate') AND status = 'active'");
            $stmt->execute();
            $result = $stmt->fetch();
            $stats['team_size'] = $result['team_size'];

            // Active projects
            $stmt = $this->db->prepare("SELECT COUNT(*) as active_projects FROM properties WHERE status = 'active'");
            $stmt->execute();
            $result = $stmt->fetch();
            $stats['active_projects'] = $result['active_projects'];

            // Monthly targets
            $stmt = $this->db->prepare("SELECT COUNT(*) as monthly_sales FROM properties WHERE status = 'sold' AND MONTH(updated_at) = MONTH(CURRENT_DATE()) AND YEAR(updated_at) = YEAR(CURRENT_DATE())");
            $stmt->execute();
            $result = $stmt->fetch();
            $stats['monthly_sales'] = $result['monthly_sales'];

            // Performance score
            $stats['performance_score'] = $this->calculatePerformanceScore();

            return $stats;
        } catch (\Exception $e) {
            error_log("CM Stats Error: " . $e->getMessage());
            return [
                'team_size' => 0,
                'active_projects' => 0,
                'monthly_sales' => 0,
                'performance_score' => 0
            ];
        }
    }

    /**
     * Get team performance data
     */
    private function getTeamPerformance()
    {
        try {
            $stmt = $this->db->prepare("
                SELECT u.name, u.email, u.role, COUNT(p.id) as properties_managed, 
                       SUM(CASE WHEN p.status = 'sold' THEN 1 ELSE 0 END) as sales_count
                FROM users u 
                LEFT JOIN properties p ON u.id = p.created_by 
                WHERE u.role IN ('employee', 'associate') AND u.status = 'active'
                GROUP BY u.id, u.name, u.email, u.role
                ORDER BY sales_count DESC
                LIMIT 10
            ");
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (\Exception $e) {
            error_log("Team Performance Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get recent activities
     */
    private function getCmRecentActivities()
    {
        try {
            $stmt = $this->db->prepare("
                SELECT action, description, created_at 
                FROM activity_logs_unified 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                ORDER BY created_at DESC 
                LIMIT 10
            ");
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (\Exception $e) {
            error_log("Recent Activities Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get projects overview
     */
    private function getProjectsOverview()
    {
        try {
            $stmt = $this->db->prepare("
                SELECT status, COUNT(*) as count 
                FROM properties 
                GROUP BY status
                ORDER BY count DESC
            ");
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (\Exception $e) {
            error_log("Projects Overview Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Calculate performance score
     */
    private function calculatePerformanceScore()
    {
        try {
            // Get current month performance
            $stmt = $this->db->prepare("
                SELECT 
                    (SELECT COUNT(*) FROM properties WHERE status = 'sold' AND MONTH(updated_at) = MONTH(CURRENT_DATE())) as monthly_sales,
                    (SELECT AVG(CASE WHEN status = 'sold' THEN 1 ELSE 0 END) * 100 FROM properties WHERE MONTH(updated_at) = MONTH(CURRENT_DATE())) as conversion_rate
            ");
            $stmt->execute();
            $data = $stmt->fetch();

            $score = 0;

            // Sales performance (40% weight)
            $salesTarget = 10; // Target: 10 sales per month
            $salesScore = min(($data['monthly_sales'] / $salesTarget) * 100, 100) * 0.4;

            // Conversion rate (30% weight)
            $conversionScore = min($data['conversion_rate'] * 0.3, 30);

            // Base score (30% weight)
            $baseScore = 30;

            $score = $salesScore + $conversionScore + $baseScore;

            return round($score, 1);
        } catch (\Exception $e) {
            error_log("Performance Score Error: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get team analytics
     */
    public function getTeamAnalytics()
    {
        // Check authentication
        if (!isset($_SESSION['admin_id'])) {
            $this->jsonResponse(['error' => 'Unauthorized'], 401);
            return;
        }

        $analytics = $this->getTeamPerformance();
        $this->jsonResponse(['success' => true, 'data' => $analytics]);
    }

    /**
     * Get performance metrics
     */
    public function getPerformanceMetrics()
    {
        // Check authentication
        if (!isset($_SESSION['admin_id'])) {
            $this->jsonResponse(['error' => 'Unauthorized'], 401);
            return;
        }

        $metrics = [
            'current_score' => $this->calculatePerformanceScore(),
            'monthly_target' => 85,
            'trend' => '+5.2%',
            'breakdown' => [
                'sales_performance' => 40,
                'team_management' => 30,
                'project_completion' => 30
            ]
        ];

        $this->jsonResponse(['success' => true, 'data' => $metrics]);
    }

    /**
     * JSON response helper
     */
    public function jsonResponse($data, $status = 200)
    {
        header('Content-Type: application/json');
        http_response_code($status);
        echo json_encode($data);
        exit;
    }

    /**
     * Get CSS class for activity type
     */
    public function getActivityClass($type)
    {
        $map = [
            'call' => 'bg-primary',
            'meeting' => 'bg-success',
            'email' => 'bg-info',
            'visit' => 'bg-warning',
            'note' => 'bg-secondary',
        ];
        return $map[$type] ?? 'bg-secondary';
    }

    /**
     * Get CSS class for project status
     */
    public function getProjectStatusClass($status)
    {
        $map = [
            'active' => 'badge bg-success',
            'pending' => 'badge bg-warning',
            'completed' => 'badge bg-primary',
            'on_hold' => 'badge bg-secondary',
            'cancelled' => 'badge bg-danger',
        ];
        return $map[$status] ?? 'badge bg-secondary';
    }
}
