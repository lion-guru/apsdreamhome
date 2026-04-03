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
        $this->requireLogin();
        
        $propertyId = $_POST['property_id'] ?? null;
        
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
        $result = $this->valuationEngine->generateValuation($propertyId);
        
        $this->jsonResponse($result);
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
