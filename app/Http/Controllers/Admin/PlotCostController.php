<?php
/**
 * Plot Cost Calculator Controller
 * Admin interface for plot development cost management
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;

class PlotCostController extends AdminController
{
    protected $db;
    
    public function __construct()
    {
        parent::__construct();
        $this->db = \App\Core\Database\Database::getInstance();
    }
    
    /**
     * List all colonies with cost summary
     */
    public function index()
    {
        $colonies = $this->db->fetchAll("
            SELECT c.*, 
                   COUNT(p.id) as total_plots,
                   SUM(p.area_sqft) as total_area_sqft,
                   (SELECT SUM(amount) FROM plot_development_costs WHERE colony_id = c.id) as total_cost
            FROM colonies c
            LEFT JOIN plots p ON p.colony_id = c.id
            GROUP BY c.id
            ORDER BY c.name
        ");
        
        $data = [
            'page_title' => 'Plot Development Cost Calculator',
            'colonies' => $colonies
        ];
        
        $this->render('admin/plot-costs/index', $data);
    }
    
    /**
     * Colony detail with cost breakdown
     */
    public function colony($id)
    {
        $colony = $this->db->fetch("SELECT * FROM colonies WHERE id = ?", [$id]);
        
        if (!$colony) {
            $_SESSION['error'] = 'Colony not found';
            header('Location: /admin/plot-costs');
            exit;
        }
        
        // Get plots
        $plots = $this->db->fetchAll("
            SELECT p.*, 
                   (p.total_price / NULLIF(p.area_sqft, 0)) as price_per_sqft
            FROM plots p
            WHERE p.colony_id = ?
            ORDER BY p.plot_number
        ", [$id]);
        
        // Get cost breakdown
        $costBreakdown = $this->db->fetchAll("
            SELECT cost_type, SUM(amount) as total, COUNT(*) as entries
            FROM plot_development_costs
            WHERE colony_id = ?
            GROUP BY cost_type
        ", [$id]);
        
        // Calculate totals
        $costs = [
            'land' => 0,
            'development' => 0,
            'amenities' => 0,
            'legal' => 0,
            'misc' => 0
        ];
        
        foreach ($costBreakdown as $c) {
            $costs[$c['cost_type']] = floatval($c['total']);
        }
        $costs['total'] = array_sum($costs);
        
        $data = [
            'page_title' => 'Colony Cost: ' . $colony['name'],
            'colony' => $colony,
            'plots' => $plots,
            'costBreakdown' => $costBreakdown,
            'costs' => $costs
        ];
        
        $this->render('admin/plot-costs/colony', $data);
    }
    
    /**
     * Add cost entry
     */
    public function addCost()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin/plot-costs');
            exit;
        }
        
        $colonyId = intval($_POST['colony_id'] ?? 0);
        $costType = $_POST['cost_type'] ?? '';
        $description = $_POST['description'] ?? '';
        $amount = floatval($_POST['amount'] ?? 0);
        $perSqft = floatval($_POST['per_sqft_rate'] ?? 0);
        $area = floatval($_POST['total_area'] ?? 0);
        
        if (!$colonyId || !$costType || $amount <= 0) {
            $_SESSION['error'] = 'Please fill all required fields';
            header('Location: /admin/plot-costs/colony/' . $colonyId);
            exit;
        }
        
        $this->db->execute(
            "INSERT INTO plot_development_costs 
             (colony_id, cost_type, description, amount, per_sqft_rate, total_area_sqft, created_at)
             VALUES (?, ?, ?, ?, ?, ?, NOW())",
            [$colonyId, $costType, $description, $amount, $perSqft, $area]
        );
        
        $_SESSION['success'] = 'Cost added successfully!';
        header('Location: /admin/plot-costs/colony/' . $colonyId);
        exit;
    }
    
    /**
     * Calculate all plot prices for a colony
     */
    public function calculateAll()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin/plot-costs');
            exit;
        }
        
        $colonyId = intval($_POST['colony_id'] ?? 0);
        $marginPercent = floatval($_POST['margin_percent'] ?? 25);
        
        if (!$colonyId) {
            $_SESSION['error'] = 'Invalid colony';
            header('Location: /admin/plot-costs');
            exit;
        }
        
        require_once __DIR__ . '/../../../Services/PlotDevelopmentCostService.php';
        $service = new \App\Services\PlotDevelopmentCostService();
        
        $updated = $service->updateAllPlotPrices($colonyId, $marginPercent);
        
        $_SESSION['success'] = "Updated $updated plot prices with $marginPercent% margin!";
        header('Location: /admin/plot-costs/colony/' . $colonyId);
        exit;
    }
    
    /**
     * Generate cost report
     */
    public function report($id)
    {
        require_once __DIR__ . '/../../../Services/PlotDevelopmentCostService.php';
        
        $service = new \App\Services\PlotDevelopmentCostService();
        $report = $service->generateCostReport($id);
        
        if (!$report) {
            $_SESSION['error'] = 'Colony not found';
            header('Location: /admin/plot-costs');
            exit;
        }
        
        $data = [
            'page_title' => 'Cost Report: ' . $report['colony']['name'],
            'report' => $report
        ];
        
        $this->render('admin/plot-costs/report', $data);
    }
}
