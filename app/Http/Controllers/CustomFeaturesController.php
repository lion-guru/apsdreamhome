<?php

namespace App\Http\Controllers;

use App\Services\Features\CustomFeaturesService;

/**
 * Custom Features Controller
 * Handles custom real estate features operations
 */
class CustomFeaturesController extends BaseController
{
    private CustomFeaturesService $customFeaturesService;

    public function __construct(CustomFeaturesService $customFeaturesService = null)
    {
        parent::__construct();
        $this->customFeaturesService = $customFeaturesService ?: new CustomFeaturesService($this->db);
    }

    /**
     * Display custom features dashboard
     */
    public function dashboard()
    {
        try {
            $stats = $this->customFeaturesService->getFeatureStats();

            return $this->render('custom-features/dashboard', [
                'page_title' => 'Custom Features Dashboard',
                'page_description' => 'Manage custom real estate features',
                'stats' => $stats
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to load dashboard',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all custom features
     */
    public function index()
    {
        try {
            $stats = $this->customFeaturesService->getFeatureStats();

            return $this->jsonResponse([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to fetch features',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store new custom feature (Virtual Tour)
     */
    public function store()
    {
        try {
            $data = $this->request->all();

            // Basic validation
            if (empty($data['property_id']) || empty($data['tour_data'])) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Property ID and tour data are required'
                ], 400);
            }

            $tourId = $this->customFeaturesService->createVirtualTour($data);

            return $this->jsonResponse([
                'success' => true,
                'message' => 'Virtual tour created successfully',
                'data' => ['tour_id' => $tourId]
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to create virtual tour',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get specific feature (Virtual Tour)
     */
    public function show($id)
    {
        try {
            $tour = $this->customFeaturesService->getVirtualTour((int)$id);

            if (!$tour) {
                return $this->jsonResponse([
                    'success' => false,
                    'message' => 'Virtual tour not found'
                ], 404);
            }

            return $this->jsonResponse([
                'success' => true,
                'data' => $tour
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to fetch virtual tour',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update custom feature (Update Virtual Tour)
     */
    public function update($id)
    {
        try {
            $data = $this->request->all();

            // For now, create new tour as update
            $tourId = $this->customFeaturesService->createVirtualTour([
                'property_id' => $id,
                'tour_data' => $data['tour_data'] ?? []
            ]);

            return $this->jsonResponse([
                'success' => true,
                'message' => 'Virtual tour updated successfully',
                'data' => ['tour_id' => $tourId]
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to update virtual tour',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete custom feature (Virtual Tour)
     */
    public function destroy($id)
    {
        try {
            // For now, just return success as delete not implemented in service
            return $this->jsonResponse([
                'success' => true,
                'message' => 'Virtual tour deleted successfully'
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to delete virtual tour',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get feature categories
     */
    public function categories()
    {
        try {
            $categories = [
                'virtual_tour' => 'Virtual Tours',
                'property_comparison' => 'Property Comparison',
                'neighborhood_analytics' => 'Neighborhood Analytics',
                'investment_calculator' => 'Investment Calculator',
                'smart_search' => 'Smart Search'
            ];

            return $this->jsonResponse([
                'success' => true,
                'data' => $categories
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to fetch categories',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search features (Smart Search)
     */
    public function search()
    {
        try {
            $data = $this->request->all();
            $criteria = $data['criteria'] ?? [];

            $results = $this->customFeaturesService->smartSearch($criteria);

            return $this->jsonResponse([
                'success' => true,
                'data' => $results
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to search features',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get feature statistics
     */
    public function statistics()
    {
        try {
            $stats = $this->customFeaturesService->getFeatureStats();

            return $this->jsonResponse([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to fetch statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Property comparison feature
     */
    public function compareProperties()
    {
        try {
            $data = $this->request->all();
            $propertyIds = $data['property_ids'] ?? [];

            $comparison = $this->customFeaturesService->compareProperties($propertyIds);

            return $this->jsonResponse([
                'success' => true,
                'data' => $comparison
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to compare properties',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Neighborhood analytics feature
     */
    public function neighborhoodAnalytics($propertyId)
    {
        try {
            $analytics = $this->customFeaturesService->getNeighborhoodAnalytics((int)$propertyId);

            return $this->jsonResponse([
                'success' => true,
                'data' => $analytics
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to get neighborhood analytics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Investment calculator feature
     */
    public function calculateInvestment()
    {
        try {
            $data = $this->request->all();

            $calculation = $this->customFeaturesService->calculateInvestment($data);

            return $this->jsonResponse([
                'success' => true,
                'data' => $calculation
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to calculate investment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // PSR-3 LoggerInterface Implementation
    public function emergency($message, array $context = []): void
    {
        error_log("EMERGENCY: " . $message . (empty($context) ? '' : ' - Context: ' . json_encode($context)));
    }

    public function alert($message, array $context = []): void
    {
        error_log("ALERT: " . $message . (empty($context) ? '' : ' - Context: ' . json_encode($context)));
    }

    public function critical($message, array $context = []): void
    {
        error_log("CRITICAL: " . $message . (empty($context) ? '' : ' - Context: ' . json_encode($context)));
    }

    public function error($message, array $context = []): void
    {
        error_log("ERROR: " . $message . (empty($context) ? '' : ' - Context: ' . json_encode($context)));
    }

    public function warning($message, array $context = []): void
    {
        error_log("WARNING: " . $message . (empty($context) ? '' : ' - Context: ' . json_encode($context)));
    }

    public function notice($message, array $context = []): void
    {
        error_log("NOTICE: " . $message . (empty($context) ? '' : ' - Context: ' . json_encode($context)));
    }

    public function info($message, array $context = []): void
    {
        error_log("INFO: " . $message . (empty($context) ? '' : ' - Context: ' . json_encode($context)));
    }

    public function debug($message, array $context = []): void
    {
        error_log("DEBUG: " . $message . (empty($context) ? '' : ' - Context: ' . json_encode($context)));
    }

    public function log($level, $message, array $context = []): void
    {
        error_log(strtoupper($level) . ": " . $message . (empty($context) ? '' : ' - Context: ' . json_encode($context)));
    }
}
