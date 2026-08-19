<?php
/**
 * API Search Controller
 * Provides advanced search API endpoints
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Services\AdvancedSearchService;

class SearchController extends BaseController {
    
    private $searchService;
    
    public function __construct() {
        parent::__construct();
        $this->searchService = new AdvancedSearchService();
    }
    
    /**
     * Advanced property search
     * POST /api/search/properties
     */
    public function searchProperties() {
        try {
            $params = $_GET;
            
            // Validate and sanitize parameters
            $validatedParams = $this->validateSearchParams($params);
            
            // Perform search
            $results = $this->searchService->searchProperties($validatedParams);
            
            // Save to search history if user is logged in
            if (isset($_SESSION['user_id'])) {
                $this->searchService->saveSearchHistory(
                    $_SESSION['user_id'],
                    $params['search'] ?? '',
                    $validatedParams
                );
            }
            
            $this->json([
                'success' => true,
                'data' => $results,
                'params' => $validatedParams
            ]);
            
        } catch (\Exception $e) {
            error_log('SearchController::searchProperties error: ' . $e->getMessage());
            $this->json([
                'success' => false,
                'error' => 'Internal server error'
            ], 500);
        }
    }
    
    /**
     * Get search suggestions
     * GET /api/search/suggestions?q=query
     */
    public function getSuggestions() {
        try {
            $query = $_GET['q'] ?? '';
            $limit = $_GET['limit'] ?? 10;
            
            if (strlen($query) < 2) {
                $this->json([
                    'success' => true,
                    'suggestions' => []
                ]);
                return;
            }
            
            $suggestions = $this->searchService->getSearchSuggestions($query, $limit);
            
            $this->json([
                'success' => true,
                'suggestions' => $suggestions
            ]);
            
        } catch (\Exception $e) {
            error_log('SearchController::getSuggestions error: ' . $e->getMessage());
            $this->json([
                'success' => false,
                'error' => 'Internal server error'
            ], 500);
        }
    }
    
    /**
     * Get search facets
     * GET /api/search/facets
     */
    public function getFacets() {
        try {
            $facets = $this->searchService->getSearchFacets();
            
            $this->json([
                'success' => true,
                'facets' => $facets
            ]);
            
        } catch (\Exception $e) {
            error_log('SearchController::getFacets error: ' . $e->getMessage());
            $this->json([
                'success' => false,
                'error' => 'Internal server error'
            ], 500);
        }
    }
    
    /**
     * Get recent searches for logged-in user
     * GET /api/search/recent
     */
    public function getRecentSearches() {
        try {
            if (!isset($_SESSION['user_id'])) {
                $this->json([
                    'success' => false,
                    'error' => 'User not logged in'
                ], 401);
                return;
            }
            
            $limit = $_GET['limit'] ?? 10;
            $recentSearches = $this->searchService->getRecentSearches($_SESSION['user_id'], $limit);
            
            $this->json([
                'success' => true,
                'recent_searches' => $recentSearches
            ]);
            
        } catch (\Exception $e) {
            error_log('SearchController::getRecentSearches error: ' . $e->getMessage());
            $this->json([
                'success' => false,
                'error' => 'Internal server error'
            ], 500);
        }
    }
    
    /**
     * Get popular/trending searches
     * GET /api/search/popular
     */
    public function getPopularSearches() {
        try {
            $limit = $_GET['limit'] ?? 10;
            $popularSearches = $this->searchService->getPopularSearches($limit);
            
            $this->json([
                'success' => true,
                'popular_searches' => $popularSearches
            ]);
            
        } catch (\Exception $e) {
            error_log('SearchController::getPopularSearches error: ' . $e->getMessage());
            $this->json([
                'success' => false,
                'error' => 'Internal server error'
            ], 500);
        }
    }
    
    /**
     * Clear search cache (admin only)
     * POST /api/search/clear-cache
     */
    public function clearCache() {
        try {
            // Check if user is admin
            if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'super_admin'])) {
                $this->json([
                    'success' => false,
                    'error' => 'Unauthorized'
                ], 403);
                return;
            }
            
            $this->searchService->clearCache();
            
            $this->json([
                'success' => true,
                'message' => 'Search cache cleared successfully'
            ]);
            
        } catch (\Exception $e) {
            error_log('SearchController::clearCache error: ' . $e->getMessage());
            $this->json([
                'success' => false,
                'error' => 'Internal server error'
            ], 500);
        }
    }
    
    /**
     * Validate search parameters
     */
    private function validateSearchParams($params) {
        $validated = [];
        
        // Allowed parameters
        $allowedParams = [
            'search', 'property_type', 'listing_type', 
            'min_price', 'max_price', 'min_area', 'max_area',
            'state_id', 'district_id', 'location', 'bedrooms',
            'amenities', 'posted_after', 'posted_before',
            'sort', 'page', 'per_page'
        ];
        
        foreach ($allowedParams as $param) {
            if (isset($params[$param]) && !empty($params[$param])) {
                // Type conversion for numeric values
                if (in_array($param, ['min_price', 'max_price', 'min_area', 'max_area', 'state_id', 'district_id', 'bedrooms', 'page', 'per_page'])) {
                    $validated[$param] = (int)$params[$param];
                }
                // Handle array parameters
                elseif (in_array($param, ['property_type', 'amenities'])) {
                    if (is_array($params[$param])) {
                        $validated[$param] = $params[$param];
                    } else {
                        $validated[$param] = [$params[$param]];
                    }
                }
                else {
                    $validated[$param] = $params[$param];
                }
            }
        }
        
        // Set defaults
        $validated['page'] = $validated['page'] ?? 1;
        $validated['per_page'] = min($validated['per_page'] ?? 20, 100); // Max 100 per page
        
        return $validated;
    }
    
    /**
     * Return JSON response
     */
    public function json($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}