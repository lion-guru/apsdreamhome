<?php

/**
 * Builder Dashboard Controller
 * MVC Pattern - Proper Role-based Dashboard Management
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Services\LoggingService;
use Exception;

class BuilderDashboardController extends AdminController
{
    private $loggingService;

    public function __construct()
    {
        parent::__construct();
        $this->loggingService = new LoggingService();
    }

    /**
     * Show builder dashboard
     */
    public function index()
    {
        try {
            // Get construction statistics
            try {
                $construction_stats = $this->db->fetchOne(
                    "SELECT 
                        COUNT(*) as total_projects,
                        COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_projects,
                        COUNT(CASE WHEN status = 'in_progress' THEN 1 END) as ongoing_projects,
                        COUNT(CASE WHEN status = 'planned' THEN 1 END) as planned_projects
                    FROM construction_projects"
                );
            } catch (\Exception $e) {
                $construction_stats = ['total_projects' => 0, 'completed_projects' => 0, 'ongoing_projects' => 0, 'planned_projects' => 0];
            }

            // Get material statistics
            try {
                $material_stats = $this->db->fetchOne(
                    "SELECT 
                        COUNT(*) as total_materials,
                        COALESCE(SUM(quantity * unit_price), 0) as total_material_cost,
                        COUNT(CASE WHEN stock_quantity <= reorder_level THEN 1 END) as low_stock_materials
                    FROM materials"
                );
            } catch (\Exception $e) {
                $material_stats = ['total_materials' => 0, 'total_material_cost' => 0, 'low_stock_materials' => 0];
            }

            // Get workforce statistics
            try {
                $workforce_stats = $this->db->fetchOne(
                    "SELECT 
                        COUNT(*) as total_workers,
                        COUNT(CASE WHEN status = 'active' THEN 1 END) as active_workers,
                        COUNT(CASE WHEN specialization = 'mason' THEN 1 END) as masons,
                        COUNT(CASE WHEN specialization = 'carpenter' THEN 1 END) as carpenters,
                        COUNT(CASE WHEN specialization = 'electrician' THEN 1 END) as electricians
                    FROM workforce"
                );
            } catch (\Exception $e) {
                $workforce_stats = ['total_workers' => 0, 'active_workers' => 0, 'masons' => 0, 'carpenters' => 0, 'electricians' => 0];
            }

            // Get recent activities
            try {
                $activities = $this->db->fetchAll(
                    "SELECT id, activity_type as description, created_at
                     FROM activity_logs_unified 
                     ORDER BY created_at DESC 
                     LIMIT 10"
                );
            } catch (\Exception $e) {
                $activities = [];
            }

            $this->data = [
                'page_title' => 'Builder Dashboard',
                'construction_stats' => $construction_stats,
                'material_stats' => $material_stats,
                'workforce_stats' => $workforce_stats,
                'activities' => $activities
            ];

            return $this->render('admin/dashboards/builder');
        } catch (\Exception $e) {
            $this->loggingService->error("Builder Dashboard Error: " . $e->getMessage());
            $this->setFlash('error', 'Dashboard loading failed');
            return $this->redirect('admin/dashboard');
        }
    }

    /**
     * Get construction analytics (AJAX)
     */
    public function getConstructionAnalytics()
    {
        try {
            $analytics = $this->db->query(
                "SELECT 
                    DATE(created_at) as date,
                    COUNT(*) as projects_started,
                    COUNT(CASE WHEN status = 'completed' THEN 1 END) as projects_completed
                FROM construction_projects
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                GROUP BY DATE(created_at)
                ORDER BY date DESC"
            )->fetchAll(\PDO::FETCH_ASSOC);

            return $this->jsonResponse(['success' => true, 'data' => $analytics]);
        } catch (\Exception $e) {
            $this->loggingService->error("Get Construction Analytics error: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'data' => []]);
        }
    }

    /**
     * Get material status (AJAX)
     */
    public function getMaterialStatus()
    {
        header('Content-Type: application/json');
        try {
            $result = $this->db->query(
                "SELECT 
                    name,
                    category,
                    stock_quantity,
                    reorder_level,
                    unit_price,
                    (stock_quantity - reorder_level) as stock_status
                FROM materials
                ORDER BY stock_quantity ASC
                LIMIT 20"
            );
            $materials = $result->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $this->loggingService->error("Get Material Status error: " . $e->getMessage());
            $materials = [];
        }
        echo json_encode(['success' => true, 'data' => $materials ?? []]);
        exit;
    }
}
