<?php

namespace App\Http\Controllers\Api;

use App\Services\Finance\PropertyTaxCalculatorService;

class PropertyTaxController extends BaseApiController
{
    protected PropertyTaxCalculatorService $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new PropertyTaxCalculatorService();
    }

    /**
     * Calculate property tax
     * POST /api/property-tax/calculate
     * Body: {state_code, city, zone, property_type, built_up_area_sqft, land_area_sqft, assessment_year, is_early_payment, months_overdue, property_id}
     */
    public function calculate()
    {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            $input = $_POST;
        }

        $required = ['state_code', 'city', 'property_type', 'built_up_area_sqft'];
        foreach ($required as $field) {
            if (empty($input[$field])) {
                return $this->jsonError("Missing required field: $field");
            }
        }

        try {
            $result = $this->service->calculateTax($input);
            
            if ($result['success']) {
                return $this->jsonSuccess($result, 'Tax calculated successfully');
            }
            
            return $this->jsonError($result['error']);
        } catch (\Exception $e) {
            error_log('[PropertyTaxController::calculate] ' . $e->getMessage());
            return $this->jsonError('Calculation failed');
        }
    }

    /**
     * Get tax rates for a state/city
     * GET /api/property-tax/rates?state_code=UP&city=Gorakhpur&property_type=residential
     */
    public function getRates()
    {
        $stateCode = strtoupper($_GET['state_code'] ?? '');
        $city = $_GET['city'] ?? '';
        $propertyType = strtolower($_GET['property_type'] ?? '');

        if (!$stateCode) {
            return $this->jsonError('state_code is required');
        }

        try {
            $rates = $this->service->getRates($stateCode, $city, $propertyType);
            return $this->jsonSuccess(['rates' => $rates]);
        } catch (\Exception $e) {
            error_log('[PropertyTaxController::getRates] ' . $e->getMessage());
            return $this->jsonError('Failed to fetch rates');
        }
    }

    /**
     * Search tax rates
     * GET /api/property-tax/search?state_code=UP&q=Zone A
     */
    public function search()
    {
        $stateCode = strtoupper($_GET['state_code'] ?? '');
        $search = $_GET['q'] ?? '';

        if (!$stateCode) {
            return $this->jsonError('state_code is required');
        }

        try {
            $rates = $this->service->searchRates($stateCode, $search);
            return $this->jsonSuccess(['rates' => $rates]);
        } catch (\Exception $e) {
            error_log('[PropertyTaxController::search] ' . $e->getMessage());
            return $this->jsonError('Search failed');
        }
    }

    /**
     * Get all states/cities with tax rates
     * GET /api/property-tax/states
     */
    public function getStates()
    {
        try {
            $states = $this->service->getStates();
            return $this->jsonSuccess(['states' => $states]);
        } catch (\Exception $e) {
            error_log('[PropertyTaxController::getStates] ' . $e->getMessage());
            return $this->jsonError('Failed to fetch states');
        }
    }

    /**
     * Get assessment history for a property
     * GET /api/property-tax/assessment/{propertyId}
     */
    public function getAssessment($propertyId)
    {
        $propertyId = (int)$propertyId;
        
        if ($propertyId <= 0) {
            return $this->jsonError('Invalid property ID');
        }

        try {
            $history = $this->service->getAssessmentHistory($propertyId);
            return $this->jsonSuccess(['history' => $history]);
        } catch (\Exception $e) {
            error_log('[PropertyTaxController::getAssessment] ' . $e->getMessage());
            return $this->jsonError('Failed to fetch history');
        }
    }
}