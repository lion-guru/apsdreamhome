<?php

namespace App\Http\Controllers\AI;

use App\Http\Controllers\BaseController;
use App\Services\AI\PropertyValuationEngine;

/**
 * APS Dream Home - Property Valuation Controller
 * AI-powered property valuation and market analysis
 */
class PropertyValuationController extends BaseController
{
    private $valuationEngine;
    private $security;

    public function __construct()
    {
        parent::__construct();
        $this->valuationEngine = new PropertyValuationEngine();
        $this->security = new \App\Core\Security();
    }

    /**
     * Generate property valuation
     */
    public function generateValuation()
    {
        // Temporarily skip authentication for testing
        // if (!$this->isLoggedIn()) {
        //     $this->jsonResponse([
        //         'success' => false,
        //         'message' => 'Authentication required'
        //     ]);
        //     return;
        // }

        // Read JSON data from request body
        $jsonInput = file_get_contents('php://input');
        $data = json_decode($jsonInput, true);
        $propertyId = $data['property_id'] ?? null;

        if (!$propertyId) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Property ID is required'
            ]);
            return;
        }

        // Sanitize input
        $propertyId = $this->security->sanitize($propertyId, 'int');

        // Generate valuation
        try {
            $result = $this->valuationEngine->generateValuation($propertyId);
            $this->jsonResponse($result);
        } catch (\Exception $e) {
            error_log("Valuation engine error: " . $e->getMessage());
            $this->jsonResponse([
                'success' => false,
                'message' => 'Valuation failed: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Display valuation reports dashboard
     */
    public function reports()
    {
        $this->requireAdmin();

        $stats = $this->valuationEngine->getValuationStats();
        $reports = $this->valuationEngine->getReports([
            'date_from' => $_GET['date_from'] ?? null,
            'date_to' => $_GET['date_to'] ?? null,
            'min_value' => $_GET['min_value'] ?? null,
            'max_value' => $_GET['max_value'] ?? null,
        ]);

        $this->render('admin.property-valuations.reports', [
            'page_title' => 'Property Valuation Reports',
            'stats' => $stats,
            'reports' => $reports,
        ]);
    }

    /**
     * Generate and store new valuation
     */
    public function generateAndStore()
    {
        $this->requireLogin();

        $propertyId = $_POST['property_id'] ?? $_GET['property_id'] ?? null;
        $propertyId = $this->security->sanitize($propertyId, 'int');
        $userId = (int)($_SESSION['user_id'] ?? $_SESSION['admin_id'] ?? 0);

        try {
            $result = $this->valuationEngine->generateValuation($propertyId);
            if ($result['success']) {
                $reportId = $this->valuationEngine->storeValuation($propertyId, $result, $userId);
                $result['report_id'] = $reportId;
            }
            $this->jsonResponse($result);
        } catch (\Throwable $e) {
            error_log("Valuation generation error: " . $e->getMessage());
            $this->jsonResponse([
                'success' => false,
                'message' => 'Valuation failed: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Skip CSRF protection for this controller
     */
    protected function skipCsrfProtection(): bool
    {
        return true;
    }

    /**
     * Get valuation history
     */
    public function getValuationHistory()
    {
        $this->requireLogin();

        $propertyId = $_GET['property_id'] ?? null;

        if (!$propertyId) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Property ID is required'
            ]);
            return;
        }

        // Sanitize input
        $propertyId = $this->security->sanitize($propertyId, 'int');

        $history = $this->valuationEngine->getValuationHistory($propertyId);

        $this->jsonResponse([
            'success' => true,
            'data' => $history
        ]);
    }

    /**
     * Batch valuation for multiple properties
     */
    public function batchValuation()
    {
        $this->requireLogin();

        $propertyIds = $_POST['property_ids'] ?? [];

        if (empty($propertyIds)) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Property IDs are required'
            ]);
            return;
        }

        // Sanitize inputs
        $sanitizedIds = [];
        foreach ($propertyIds as $id) {
            $sanitizedIds[] = $this->security->sanitize($id, 'int');
        }

        $results = $this->valuationEngine->batchValuation($sanitizedIds);

        $this->jsonResponse([
            'success' => true,
            'data' => $results
        ]);
    }

    /**
     * View single valuation report
     */
    public function viewReport($id)
    {
        $this->requireAdmin();

        $report = $this->valuationEngine->getReport((int)$id);

        if (!$report) {
            $_SESSION['error'] = 'Report not found';
            header('Location: /admin/property-valuations');
            exit;
        }

        $this->render('admin.property-valuations.view', [
            'page_title' => 'Valuation Report #' . $report['id'],
            'report' => $report,
        ]);
    }

    /**
     * Show form to generate new valuation
     */
    public function showGenerateForm()
    {
        $this->requireAdmin();

        $this->render('admin.property-valuations.generate', [
            'page_title' => 'Generate Property Valuation',
        ]);
    }

    /**
     * Generate valuation for a specific property
     */
    public function valuationByProperty($propertyId)
    {
        $this->requireLogin();

        $propertyId = $this->security->sanitize($propertyId, 'int');
        $result = $this->valuationEngine->generateValuation($propertyId);

        if ($result['success']) {
            $userId = (int)($_SESSION['user_id'] ?? $_SESSION['admin_id'] ?? 0);
            $reportId = $this->valuationEngine->storeValuation($propertyId, $result, $userId);
            $result['report_id'] = $reportId;
        }

        $this->jsonResponse($result);
    }

    /**
     * Display valuation interface
     */
    public function index()
    {
        $this->requireLogin();

        $this->render('ai/property-valuation', [
            'page_title' => 'AI Property Valuation - APS Dream Home',
            'page_description' => 'Advanced AI-powered property valuation and market analysis'
        ]);
    }

    /**
     * API endpoint for property valuation
     */
    public function apiValuation()
    {
        // Allow external API access with API key validation
        $apiKey = $_SERVER['HTTP_X_API_KEY'] ?? null;

        if (!$apiKey || !$this->validateApiKey($apiKey)) {
            http_response_code(401);
            $this->jsonResponse([
                'success' => false,
                'message' => 'Invalid API key'
            ]);
            return;
        }

        $propertyId = $_POST['property_id'] ?? null;

        if (!$propertyId) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Property ID is required'
            ]);
            return;
        }

        // Generate valuation
        $result = $this->valuationEngine->generateValuation($propertyId);

        $this->jsonResponse($result);
    }

    /**
     * Validate API key
     */
    private function validateApiKey($apiKey)
    {
        // Implement API key validation logic
        $validKeys = [
            'aps2024-ai-key-1',
            'aps2024-ai-key-2'
        ];

        return in_array($apiKey, $validKeys);
    }
}
