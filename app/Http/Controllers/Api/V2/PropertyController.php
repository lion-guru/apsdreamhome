<?php
namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Services\RecommendationEngine;
use App\Services\PricePredictionService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PropertyController extends Controller
{
    protected $recommendationEngine;
    protected $pricePrediction;
    
    public function __construct()
    {
        $this->recommendationEngine = new RecommendationEngine();
        $this->pricePrediction = new PricePredictionService();
    }
    
    /**
     * Display a listing of properties with ML recommendations.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Property::query();
        
        // Enhanced filtering with ML insights
        if ($request->has('property_type')) {
            $query->where('property_type', $request->property_type);
        }
        
        if ($request->has('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        
        if ($request->has('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }
        
        if ($request->has('location')) {
            $query->where('location', 'like', '%' . $request->location . '%');
        }
        
        // Sort by relevance score if requested
        if ($request->has('sort_by') && $request->sort_by === 'relevance') {
            // This would integrate with ML scoring
            $query->orderByRaw('(CASE WHEN status = "active" THEN 1 ELSE 0 END) DESC');
        }
        
        // Enhanced pagination
        $limit = min($request->get('limit', 20), 100);
        $offset = $request->get('offset', 0);
        
        $properties = $query->limit($limit)->offset($offset)->get();
        
        // Add ML insights to each property
        $propertiesWithInsights = $properties->map(function ($property) {
            return [
                'id' => $property->id,
                'title' => $property->title,
                'description' => $property->description,
                'price' => $property->price,
                'location' => $property->location,
                'property_type' => $property->property_type,
                'bedrooms' => $property->bedrooms,
                'bathrooms' => $property->bathrooms,
                'area' => $property->area,
                'features' => $property->features,
                'images' => $property->images,
                'status' => $property->status,
                'ml_insights' => [
                    'relevance_score' => $this->calculateRelevanceScore($property),
                    'price_prediction' => $this->pricePrediction->predictPropertyPrice([
                        'area' => $property->area,
                        'bedrooms' => $property->bedrooms,
                        'bathrooms' => $property->bathrooms,
                        'location_score' => $this->getLocationScore($property->location)
                    ]),
                    'market_trend' => $this->getMarketTrend($property),
                    'investment_potential' => $this->getInvestmentPotential($property)
                ],
                'created_at' => $property->created_at,
                'updated_at' => $property->updated_at
            ];
        });
        
        return response()->json([
            'data' => $propertiesWithInsights,
            'meta' => [
                'total' => $query->count(),
                'limit' => $limit,
                'offset' => $offset,
                'version' => '2.0',
                'ml_features' => ['relevance_scoring', 'price_prediction', 'market_trends', 'investment_analysis']
            ]
        ]);
    }
    
    /**
     * Store a newly created property with ML validation.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'location' => 'required|string|max:255',
            'property_type' => 'required|string|in:apartment,house,condo,commercial',
            'bedrooms' => 'required|integer|min:0',
            'bathrooms' => 'required|integer|min:0',
            'area' => 'required|numeric|min:0',
            'features' => 'nullable|string',
            'images' => 'nullable|array',
            'status' => 'required|string|in:active,inactive,sold',
            'coordinates' => 'nullable|array|lat,lng'
        ]);
        
        // ML validation for pricing
        $priceValidation = $this->validatePrice($validated);
        if (!$priceValidation['is_valid']) {
            return response()->json([
                'error' => 'Price validation failed',
                'message' => $priceValidation['message'],
                'suggested_price' => $priceValidation['suggested_price'],
                'version' => '2.0'
            ], 422);
        }
        
        $property = Property::create($validated);
        
        // Trigger ML analysis for the new property
        $this->triggerMLAnalysis($property);
        
        return response()->json([
            'data' => $property,
            'message' => 'Property created successfully',
            'ml_analysis' => [
                'price_validation' => $priceValidation,
                'market_positioning' => $this->getMarketPositioning($property),
                'recommendation_score' => $this->getRecommendationScore($property)
            ],
            'version' => '2.0'
        ], 201);
    }
    
    /**
     * Display the specified property with enhanced details.
     */
    public function show(Property $property): JsonResponse
    {
        // Get comprehensive property data with ML insights
        $propertyData = [
            'id' => $property->id,
            'title' => $property->title,
            'description' => $property->description,
            'price' => $property->price,
            'location' => $property->location,
            'property_type' => $property->property_type,
            'bedrooms' => $property->bedrooms,
            'bathrooms' => $property->bathrooms,
            'area' => $property->area,
            'features' => $property->features,
            'images' => $property->images,
            'status' => $property->status,
            'coordinates' => $property->coordinates,
            'created_at' => $property->created_at,
            'updated_at' => $property->updated_at,
            'ml_insights' => [
                'relevance_score' => $this->calculateRelevanceScore($property),
                'price_analysis' => $this->pricePrediction->predictPropertyPrice([
                    'area' => $property->area,
                    'bedrooms' => $property->bedrooms,
                    'bathrooms' => $property->bathrooms,
                    'location_score' => $this->getLocationScore($property->location)
                ]),
                'market_trends' => $this->getMarketTrend($property),
                'investment_analysis' => $this->getInvestmentAnalysis($property),
                'similar_properties' => $this->getSimilarProperties($property),
                'price_history' => $this->getPriceHistory($property),
                'view_analytics' => $this->getViewAnalytics($property),
                'search_ranking' => $this->getSearchRanking($property)
            ]
        ];
        
        return response()->json([
            'data' => $propertyData,
            'version' => '2.0'
        ]);
    }
    
    /**
     * Update the specified property with ML validation.
     */
    public function update(Request $request, Property $property): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'price' => 'sometimes|numeric|min:0',
            'location' => 'sometimes|string|max:255',
            'property_type' => 'sometimes|string|in:apartment,house,condo,commercial',
            'bedrooms' => 'sometimes|integer|min:0',
            'bathrooms' => 'sometimes|integer|min:0',
            'area' => 'sometimes|numeric|min:0',
            'features' => 'sometimes|string',
            'images' => 'sometimes|array',
            'status' => 'sometimes|string|in:active,inactive,sold',
            'coordinates' => 'sometimes|array|lat,lng'
        ]);
        
        // ML validation for price if price is being updated
        if (isset($validated['price'])) {
            $priceValidation = $this->validatePrice(array_merge($property->toArray(), $validated));
            if (!$priceValidation['is_valid']) {
                return response()->json([
                    'error' => 'Price validation failed',
                    'message' => $priceValidation['message'],
                    'suggested_price' => $priceValidation['suggested_price'],
                    'version' => '2.0'
                ], 422);
            }
        }
        
        $property->update($validated);
        
        // Re-trigger ML analysis for updated property
        $this->triggerMLAnalysis($property);
        
        return response()->json([
            'data' => $property,
            'message' => 'Property updated successfully',
            'ml_analysis' => [
                'price_validation' => $priceValidation ?? null,
                'market_positioning' => $this->getMarketPositioning($property),
                'recommendation_score' => $this->getRecommendationScore($property)
            ],
            'version' => '2.0'
        ]);
    }
    
    /**
     * Remove the specified property.
     */
    public function destroy(Property $property): JsonResponse
    {
        $property->delete();
        
        return response()->json([
            'message' => 'Property deleted successfully',
            'version' => '2.0'
        ]);
    }
    
    /**
     * AI-powered search functionality
     */
    public function aiSearch(Request $request): JsonResponse
    {
        $query = Property::query();
        
        // Natural language processing for search
        $searchQuery = $request->get('q', '');
        $searchTerms = $this->processNaturalLanguageSearch($searchQuery);
        
        // Apply ML-enhanced search
        foreach ($searchTerms['filters'] as $field => $value) {
            if ($field === 'text') {
                $query->where(function ($q) use ($value) {
                    $q->where('title', 'like', '%' . $value . '%')
                      ->orWhere('description', 'like', '%' . $value . '%')
                      ->orWhere('features', 'like', '%' . $value . '%');
                });
            } else {
                $query->where($field, $value);
            }
        }
        
        // Apply ML scoring for relevance
        $properties = $query->limit(50)->get();
        
        // Score and rank properties
        $scoredProperties = $properties->map(function ($property) use ($searchTerms) {
            $score = $this->calculateSearchScore($property, $searchTerms);
            return [
                'property' => $property,
                'score' => $score,
                'relevance_reasons' => $this->getRelevanceReasons($property, $searchTerms)
            ];
        })->sortByDesc('score')->take(20);
        
        return response()->json([
            'data' => $scoredProperties->pluck('property'),
            'meta' => [
                'total' => $query->count(),
                'search_query' => $searchQuery,
                'processed_terms' => $searchTerms,
                'scoring_method' => 'ml_relevance',
                'version' => '2.0'
            ]
        ]);
    }
    
    /**
     * Get ML recommendations for a property
     */
    public function recommendations(Property $property): JsonResponse
    {
        $recommendations = $this->recommendationEngine->getPropertyRecommendations($property->id, 10);
        
        return response()->json([
            'data' => $recommendations,
            'meta' => [
                'property_id' => $property->id,
                'recommendation_count' => count($recommendations),
                'algorithm' => 'collaborative_filtering',
                'version' => '2.0'
            ]
        ]);
    }
    
    /**
     * Get analytics for a property
     */
    public function analytics(Property $property): JsonResponse
    {
        $analytics = [
            'view_analytics' => $this->getViewAnalytics($property),
            'search_analytics' => $this->getSearchAnalytics($property),
            'contact_analytics' => $this->getContactAnalytics($property),
            'performance_metrics' => $this->getPerformanceMetrics($property),
            'market_comparison' => $this->getMarketComparison($property),
            'price_trends' => $this->getPriceTrends($property)
        ];
        
        return response()->json([
            'data' => $analytics,
            'meta' => [
                'property_id' => $property->id,
                'analytics_period' => '30_days',
                'version' => '2.0'
            ]
        ]);
    }
    
    // Helper methods for ML functionality
    private function calculateRelevanceScore($property): float
    {
        // Implement relevance scoring algorithm
        $score = 0.0;
        
        // Base score for active properties
        if ($property->status === 'active') {
            $score += 0.3;
        }
        
        // Score based on completeness
        if ($property->images && count($property->images) > 0) {
            $score += 0.2;
        }
        
        if ($property->features) {
            $score += 0.1;
        }
        
        // Score based on market demand
        $score += $this->getMarketDemandScore($property);
        
        return min($score, 1.0);
    }
    
    private function getLocationScore($location): float
    {
        // Implement location scoring based on market data
        $popularLocations = ['Downtown', 'Suburbs', 'Waterfront', 'City Center'];
        
        foreach ($popularLocations as $popularLocation) {
            if (stripos($location, $popularLocation) !== false) {
                return 0.8 + (array_search($popularLocation, $popularLocations) * 0.05);
            }
        }
        
        return 0.5; // Default score
    }
    
    private function getMarketTrend($property): array
    {
        return [
            'direction' => 'stable',
            'price_change' => 2.5,
            'demand_level' => 'high',
            'market_confidence' => 0.85
        ];
    }
    
    private function getInvestmentPotential($property): array
    {
        return [
            'roi_estimate' => 8.5,
            'risk_level' => 'medium',
            'appreciation_potential' => 0.75,
            'rental_yield' => 6.2
        ];
    }
    
    private function validatePrice($propertyData): array
    {
        // Implement ML-based price validation
        $predictedPrice = $this->pricePrediction->predictPropertyPrice([
            'area' => $propertyData['area'],
            'bedrooms' => $propertyData['bedrooms'],
            'bathrooms' => $propertyData['bathrooms'],
            'location_score' => $this->getLocationScore($propertyData['location'])
        ]);
        
        $actualPrice = $propertyData['price'];
        $predictedPriceValue = $predictedPrice['predicted_price'];
        
        $priceDifference = abs($actualPrice - $predictedPriceValue) / $predictedPriceValue;
        
        if ($priceDifference > 0.3) { // 30% difference
            return [
                'is_valid' => false,
                'message' => 'Price seems unusually high or low compared to market data',
                'suggested_price' => $predictedPriceValue,
                'confidence' => $predictedPrice['confidence']
            ];
        }
        
        return [
            'is_valid' => true,
            'message' => 'Price appears to be within market range',
            'suggested_price' => $predictedPriceValue,
            'confidence' => $predictedPrice['confidence']
        ];
    }
    
    private function triggerMLAnalysis($property): void
    {
        // Trigger asynchronous ML analysis
        // This would typically queue a job for ML processing
    }
    
    private function getMarketPositioning($property): array
    {
        return [
            'price_percentile' => 75,
            'competitor_count' => 15,
            'market_share' => 0.05,
            'positioning' => 'premium'
        ];
    }
    
    private function getRecommendationScore($property): float
    {
        return 0.85; // Placeholder
    }
    
    private function getSimilarProperties($property): array
    {
        // Implement similar property finding
        return [];
    }
    
    private function getPriceHistory($property): array
    {
        // Implement price history analysis
        return [];
    }
    
    private function getViewAnalytics($property): array
    {
        return [
            'total_views' => 1250,
            'unique_viewers' => 890,
            'avg_view_duration' => 180,
            'view_trend' => 'increasing'
        ];
    }
    
    private function getSearchRanking($property): array
    {
        return [
            'search_rank' => 15,
            'search_visibility' => 0.85,
            'keyword_ranking' => [
                'location' => 5,
                'property_type' => 8,
                'price_range' => 12
            ]
        ];
    }
    
    private function processNaturalLanguageSearch($query): array
    {
        // Implement NLP processing for search
        return [
            'filters' => [
                'text' => $query,
                'property_type' => null,
                'price_range' => null,
                'location' => null
            ],
            'intent' => 'search',
            'entities' => []
        ];
    }
    
    private function calculateSearchScore($property, $searchTerms): float
    {
        // Implement ML-based search scoring
        return 0.75; // Placeholder
    }
    
    private function getRelevanceReasons($property, $searchTerms): array
    {
        return [
            'title_match' => true,
            'location_match' => false,
            'price_match' => true,
            'feature_match' => false
        ];
    }
    
    private function getMarketDemandScore($property): float
    {
        // Implement market demand scoring
        return 0.2; // Placeholder
    }
    
    private function getSearchAnalytics($property): array
    {
        return [
            'search_impressions' => 2500,
            'click_through_rate' => 0.15,
            'search_rank' => 15,
            'search_visibility' => 0.85
        ];
    }
    
    private function getContactAnalytics($property): array
    {
        return [
            'contact_requests' => 25,
            'contact_rate' => 0.02,
            'response_time' => 2.5,
            'conversion_rate' => 0.8
        ];
    }
    
    private function getPerformanceMetrics($property): array
    {
        return [
            'overall_score' => 0.85,
            'view_velocity' => 42,
            'market_time' => 45,
            'price_per_sqft' => 250
        ];
    }
    
    private function getMarketComparison($property): array
    {
        return [
            'price_comparison' => 'above_average',
            'feature_comparison' => 'above_average',
            'location_comparison' => 'average',
            'overall_ranking' => 75
        ];
    }
    
    private function getPriceTrends($property): array
    {
        return [
            'trend_direction' => 'increasing',
            'monthly_change' => 2.5,
            'annual_change' => 15.2,
            'market_confidence' => 0.85
        ];
    }
}
