<?php

namespace App\Http\Controllers\Api;

use App\Services\Finance\StampDutyService;

class StampDutyController extends BaseApiController
{
    protected StampDutyService $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new StampDutyService();
    }

    /**
     * Calculate stamp duty
     * POST /api/stamp-duty/calculate
     * Body: {property_value, state_code, buyer_gender, property_type, circle_rate_value}
     */
    public function calculate()
    {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            $input = $_POST;
        }

        $propertyValue = (float)($input['property_value'] ?? 0);
        $stateCode = strtoupper($input['state_code'] ?? '');
        $buyerGender = $input['buyer_gender'] ?? 'male';
        $circleRateValue = isset($input['circle_rate_value']) ? (float)$input['circle_rate_value'] : 0;
        
        if ($propertyValue <= 0) {
            return $this->jsonError('Property value must be greater than 0');
        }
        
        if (!$stateCode) {
            return $this->jsonError('State code is required');
        }
        
        try {
            $result = $this->service->calculateStampDuty([
                'property_value' => $propertyValue,
                'state_code' => $stateCode,
                'buyer_gender' => $buyerGender,
                'circle_rate_value' => $circleRateValue,
            ]);
            
            if (!empty($result['success'])) {
                return $this->jsonSuccess($result, 'Stamp duty calculated successfully');
            }
            
            return $this->jsonError($result['error'] ?? 'Calculation failed');
        } catch (\Exception $e) {
            error_log('[StampDutyController::calculate] ' . $e->getMessage());
            return $this->jsonError('Calculation failed: ' . $e->getMessage());
        }
    }

    /**
     * Get stamp duty rates for a state
     * GET /api/stamp-duty/rates?state_code=UP
     */
    public function getRates()
    {
        $stateCode = strtoupper($_GET['state_code'] ?? '');
        
        if (!$stateCode) {
            return $this->jsonError('State code is required');
        }
        
        try {
            $rates = $this->service->getStateRates($stateCode);
            
            if ($rates) {
                return $this->jsonSuccess($rates, 'Rates retrieved successfully');
            }
            
            return $this->jsonError('State not found', 404);
        } catch (\Exception $e) {
            error_log('[StampDutyController::getRates] ' . $e->getMessage());
            return $this->jsonError('Failed to fetch rates');
        }
    }

    /**
     * Get circle rate for an area
     * GET /api/stamp-duty/circle-rate?state_code=UP&district=Gorakhpur&area_name=Civil Lines&area_type=residential
     */
    public function getCircleRate()
    {
        $stateCode = strtoupper($_GET['state_code'] ?? '');
        $district = $_GET['district'] ?? '';
        $areaName = $_GET['area_name'] ?? '';
        $areaType = $_GET['area_type'] ?? 'residential';
        
        if (!$stateCode || !$district || !$areaName) {
            return $this->jsonError('state_code, district, and area_name are required');
        }
        
        try {
            $cacheKey = "circle_rate:{$stateCode}:{$district}:{$areaName}:{$areaType}";
            $rate = \App\Services\LookupCacheService::remember($cacheKey, 7200, function() use ($stateCode, $district, $areaName, $areaType) {
                return $this->service->getCircleRate($stateCode, $district, $areaName, $areaType);
            });
            
            if ($rate !== null) {
                return $this->jsonSuccess([
                    'state_code' => $stateCode,
                    'district' => $district,
                    'area_name' => $areaName,
                    'area_type' => $areaType,
                    'rate_per_sqft' => $rate,
                ], 'Circle rate retrieved');
            }
            
            return $this->jsonError('Circle rate not found for this area', 404);
        } catch (\Exception $e) {
            error_log('[StampDutyController::getCircleRate] ' . $e->getMessage());
            return $this->jsonError('Failed to fetch circle rate');
        }
    }

    /**
     * Search circle rates
     * GET /api/stamp-duty/circle-rates?state_code=UP&q=Gorakhpur
     */
    public function searchCircleRates()
    {
        $stateCode = strtoupper($_GET['state_code'] ?? '');
        $search = $_GET['q'] ?? '';
        
        if (!$stateCode) {
            return $this->jsonError('state_code is required');
        }
        
        try {
            $results = $this->service->searchCircleRates($stateCode, $search);
            return $this->jsonSuccess(['results' => $results]);
        } catch (\Exception $e) {
            error_log('[StampDutyController::searchCircleRates] ' . $e->getMessage());
            return $this->jsonError('Failed to search circle rates');
        }
    }

    /**
     * Get all states with stamp duty info
     * GET /api/stamp-duty/states
     */
    public function getStates()
    {
        try {
            $states = $this->service->getAllStates();
            return $this->jsonSuccess(['states' => $states]);
        } catch (\Exception $e) {
            error_log('[StampDutyController::getStates] ' . $e->getMessage());
            return $this->jsonError('Failed to fetch states');
        }
    }
}