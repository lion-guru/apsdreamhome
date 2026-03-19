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
            $construction_stats = $this->db->fetchOne(
                "SELECT 
                    COUNT(*) as total_projects,
                    COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_projects,
                    COUNT(CASE WHEN status = 'in_progress' THEN 1 END) as ongoing_projects,
                    COUNT(CASE WHEN status = 'planned' THEN 1 END) as planned_projects
                FROM construction_projects"
            );

            // Get material statistics
            $material_stats = $this->db->fetchOne(
                "SELECT 
                    COUNT(*) as total_materials,
                    COALESCE(SUM(quantity * unit_price), 0) as total_material_cost,
                    COUNT(CASE WHEN stock_quantity <= reorder_level THEN 1 END) as low_stock_materials
                FROM materials"
            );

            // Get workforce statistics
            $workforce_stats = $this->db->fetchOne(
                "SELECT 
                    COUNT(*) as total_workers,
                    COUNT(CASE WHEN status = 'active' THEN 1 END) as active_workers,
                    COUNT(CASE WHEN specialization = 'mason' THEN 1 END) as masons,
                    COUNT(CASE WHEN specialization = 'carpenter' THEN 1 END) as carpenters,
                    COUNT(CASE WHEN specialization = 'electrician' THEN 1 END) as electricians
                FROM workforce"
            );

            // Get recent activities
            $activities = $this->db->fetchAll(
                "SELECT * FROM construction_activities 
                ORDER BY created_at DESC 
                LIMIT 10"
            );

            $this->data = [
                'page_title' => 'Builder Dashboard',
                'construction_stats' => $construction_stats,
                'material_stats' => $material_stats,
                'workforce_stats' => $workforce_stats,
                'activities' => $activities
            ];

            return $this->render('admin/dashboards/builder');
        } catch (Exception $e) {
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
        } catch (Exception $e) {
            $this->loggingService->error("Get Construction Analytics error: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Get material status (AJAX)
     */
    public function getMaterialStatus()
    {
        try {
            $materials = $this->db->query(
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
            )->fetchAll(\PDO::FETCH_ASSOC);

            return $this->jsonResponse(['success' => true, 'data' => $materials]);
        } catch (Exception $e) {
            $this->loggingService->error("Get Material Status error: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * JSON response helper
     */
    private function jsonResponse(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
