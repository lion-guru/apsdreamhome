<?php

namespace App\Http\Controllers;

use App\Services\Features\CustomFeaturesService;
use App\Http\Controllers\Controller;

/**
 * Custom Features Controller
 * Handles custom real estate features operations
 */
class CustomFeaturesController extends Controller
{
    private CustomFeaturesService $customFeaturesService;

    public function __construct(CustomFeaturesService $customFeaturesService)
    {
        $this->customFeaturesService = $customFeaturesService;
        $this->middleware('auth');
    }

    /**
     * Display custom features dashboard
     */
    public function dashboard()
    {
        try {
            $stats = $this->customFeaturesService->getFeatureStats();
            
            return view('custom-features.dashboard', compact('stats'));

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to load custom features dashboard: ' . $e->getMessage());
        }
    }

    /**
     * Create virtual tour
     */
    public function createVirtualTour()
    {
        try {
            $tourData = request()->only([
                'property_id', 'title', 'description', 'tour_data', 'created_by'
            ]);

            // Validate required fields
            if (empty($tourData['property_id']) || empty($tourData['title'])) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Property ID and title are required'
                ]);
            }

            $tourId = $this->customFeaturesService->createVirtualTour($tourData);
            
            return response()->json([
                'success' => true, 
                'message' => 'Virtual tour created successfully',
                'tour_id' => $tourId
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Get virtual tour
     */
    public function getVirtualTour($propertyId)
    {
        try {
            $tour = $this->customFeaturesService->getVirtualTour($propertyId);
            
            if ($tour) {
                return response()->json(['success' => true, 'data' => $tour]);
            } else {
                return response()->json(['success' => false, 'message' => 'Virtual tour not found']);
            }

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Compare properties
     */
    public function compareProperties()
    {
        try {
            $propertyIds = request('property_ids', []);
            
            if (count($propertyIds) < 2 || count($propertyIds) > 5) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Please select 2-5 properties to compare'
                ]);
            }

            $comparison = $this->customFeaturesService->compareProperties($propertyIds);
            
            return response()->json([
                'success' => true, 
                'data' => [
                    'properties' => $comparison,
                    'comparison_count' => count($comparison)
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Get neighborhood analytics
     */
    public function getNeighborhoodAnalytics($propertyId)
    {
        try {
            $analytics = $this->customFeaturesService->getNeighborhoodAnalytics($propertyId);
            
            return response()->json([
                'success' => true, 
                'data' => $analytics
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Calculate investment
     */
    public function calculateInvestment()
    {
        try {
            $params = request()->only([
                'property_price', 'down_payment', 'interest_rate', 
                'loan_term', 'monthly_rent', 'appreciation_rate', 'holding_period'
            ]);

            // Validate required fields
            if (empty($params['property_price']) || $params['property_price'] <= 0) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Property price is required and must be greater than 0'
                ]);
            }

            $results = $this->customFeaturesService->calculateInvestment($params);
            
            return response()->json([
                'success' => true, 
                'data' => $results
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Smart search
     */
    public function smartSearch()
    {
        try {
            $criteria = request()->only([
                'location', 'property_type', 'min_price', 'max_price',
                'bedrooms', 'bathrooms', 'limit'
            ]);

            // Set default limit
            $criteria['limit'] = $criteria['limit'] ?? 20;

            $results = $this->customFeaturesService->smartSearch($criteria);
            
            return response()->json([
                'success' => true, 
                'data' => [
                    'properties' => $results,
                    'total_found' => count($results),
                    'search_criteria' => $criteria
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Get feature statistics
     */
    public function getStats()
    {
        try {
            $stats = $this->customFeaturesService->getFeatureStats();
            return response()->json(['success' => true, 'data' => $stats]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Virtual tour management page
     */
    public function virtualTours()
    {
        try {
            $tours = $this->customFeaturesService->getVirtualTours(50);
            return view('custom-features.virtual-tours', compact('tours'));
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to load virtual tours: ' . $e->getMessage());
        }
    }

    /**
     * Property comparison page
     */
    public function propertyComparison()
    {
        try {
            return view('custom-features.property-comparison');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to load property comparison: ' . $e->getMessage());
        }
    }

    /**
     * Investment calculator page
     */
    public function investmentCalculator()
    {
        try {
            return view('custom-features.investment-calculator');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to load investment calculator: ' . $e->getMessage());
        }
    }

    /**
     * Smart search page
     */
    public function smartSearchPage()
    {
        try {
            return view('custom-features.smart-search');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to load smart search: ' . $e->getMessage());
        }
    }

    /**
     * Neighborhood analytics page
     */
    public function neighborhoodAnalytics($propertyId)
    {
        try {
            $analytics = $this->customFeaturesService->getNeighborhoodAnalytics($propertyId);
            return view('custom-features.neighborhood-analytics', compact('analytics', 'propertyId'));
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to load neighborhood analytics: ' . $e->getMessage());
        }
    }

    /**
     * Save property comparison
     */
    public function saveComparison()
    {
        try {
            $comparisonData = [
                'property_ids' => request('property_ids', []),
                'user_id' => auth()->id(),
                'comparison_date' => date('Y-m-d H:i:s'),
                'notes' => request('notes', '')
            ];

            // This would save to database - placeholder implementation
            return response()->json([
                'success' => true, 
                'message' => 'Comparison saved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Get saved comparisons
     */
    public function getSavedComparisons()
    {
        try {
            $userId = auth()->id();
            
            // This would get from database - placeholder implementation
            $comparisons = [
                [
                    'id' => 1,
                    'property_ids' => [1, 2, 3],
                    'comparison_date' => '2026-03-07 10:30:00',
                    'notes' => 'Comparing 3 BHK flats'
                ]
            ];
            
            return response()->json(['success' => true, 'data' => $comparisons]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Get investment history
     */
    public function getInvestmentHistory()
    {
        try {
            $userId = auth()->id();
            
            // This would get from database - placeholder implementation
            $history = [
                [
                    'id' => 1,
                    'property_id' => 123,
                    'calculation_date' => '2026-03-07 09:15:00',
                    'property_price' => 5000000,
                    'roi_percentage' => 8.5
                ]
            ];
            
            return response()->json(['success' => true, 'data' => $history]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Export comparison results
     */
    public function exportComparison()
    {
        try {
            $propertyIds = request('property_ids', []);
            $format = request('format', 'pdf'); // pdf, excel, csv
            
            $comparison = $this->customFeaturesService->compareProperties($propertyIds);
            
            // This would generate and return file - placeholder implementation
            return response()->json([
                'success' => true, 
                'message' => 'Comparison exported successfully',
                'format' => $format,
                'download_url' => '/downloads/comparison_' . time() . '.' . $format
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Get property suggestions
     */
    public function getPropertySuggestions()
    {
        try {
            $propertyId = request('property_id');
            $limit = request('limit', 5);
            
            // Get similar properties based on current property
            $currentProperty = $this->customFeaturesService->getVirtualTour($propertyId);
            
            if (!$currentProperty) {
                return response()->json(['success' => false, 'message' => 'Property not found']);
            }

            // Search for similar properties
            $similarCriteria = [
                'location' => $currentProperty['location'] ?? '',
                'property_type' => $currentProperty['type'] ?? '',
                'min_price' => $currentProperty['price'] * 0.8,
                'max_price' => $currentProperty['price'] * 1.2,
                'limit' => $limit
            ];

            $suggestions = $this->customFeaturesService->smartSearch($similarCriteria);
            
            return response()->json([
                'success' => true, 
                'data' => $suggestions
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
