<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Services\AI\PredictiveAnalyticsService;

class PredictiveAnalyticsController extends AdminController
{
    private $analytics;

    public function __construct()
    {
        parent::__construct();
        $this->analytics = new PredictiveAnalyticsService();
    }

    /**
     * Predictive Analytics Dashboard
     */
    public function index()
    {
        $this->requireAdmin();
        $this->data['page_title'] = 'Predictive Analytics';
        
        // Get overall colony trends
        $trends = $this->analytics->getAllColonyTrends();
        $this->data['trends'] = $trends['trends'] ?? [];
        $this->data['summary'] = $trends['summary'] ?? [];
        
        $this->render('admin/ai/predictive_analytics');
    }

    /**
     * Get price trend for specific colony
     */
    public function colonyTrend()
    {
        $this->requireAdmin();
        header('Content-Type: application/json');
        
        $colonyId = (int)($_GET['colony_id'] ?? 0);
        $months = (int)($_GET['months'] ?? 6);
        
        if (!$colonyId) {
            echo json_encode(['success' => false, 'error' => 'colony_id required']);
            exit;
        }
        
        $result = $this->analytics->predictPriceTrend($colonyId, $months);
        echo json_encode($result);
        exit;
    }

    /**
     * Score a specific lead
     */
    public function scoreLead()
    {
        $this->requireAdmin();
        header('Content-Type: application/json');
        
        $leadId = (int)($_GET['lead_id'] ?? 0);
        
        if (!$leadId) {
            echo json_encode(['success' => false, 'error' => 'lead_id required']);
            exit;
        }
        
        $result = $this->analytics->scoreLead($leadId);
        echo json_encode($result);
        exit;
    }

    /**
     * Batch score leads
     */
    public function batchScore()
    {
        $this->requireAdmin();
        header('Content-Type: application/json');
        
        $input = json_decode(file_get_contents('php://input'), true);
        $filters = $input ?? [];
        
        $result = $this->analytics->predictConversions($filters);
        echo json_encode($result);
        exit;
    }

    /**
     * Get all colony trends
     */
    public function allTrends()
    {
        $this->requireAdmin();
        header('Content-Type: application/json');
        
        $result = $this->analytics->getAllColonyTrends();
        echo json_encode($result);
        exit;
    }
}