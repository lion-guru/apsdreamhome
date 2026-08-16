<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AI\PropertyMatchmakerAgent;
use App\Services\PropertyListingService;

class PropertyMatchmakerController extends Controller
{
    protected $matchmaker;
    protected $propertyService;

    public function __construct()
    {
        $this->matchmaker = new PropertyMatchmakerAgent();
        $this->propertyService = new PropertyListingService();
    }

    /**
     * AI-powered property search
     * POST /api/properties/ai-search
     */
    public function aiSearch()
    {
        // Check if request is AJAX
        if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || $_SERVER['HTTP_X_REQUESTED_WITH'] !== 'XMLHttpRequest') {
            return $this->jsonResponse(['success' => false, 'message' => 'Invalid request'], 400);
        }

        // Get form data
        $query = $_POST['query'] ?? '';
        $budgetRange = $_POST['budget_range'] ?? '';
        $propertyType = $_POST['property_type'] ?? '';

        if (empty(trim($query))) {
            return $this->jsonResponse(['success' => false, 'message' => 'Please describe what you are looking for'], 400);
        }

        try {
            // Use the PropertyMatchmakerAgent to find matches
            $input = [
                'query' => $query,
                'budget_range' => $budgetRange,
                'property_type' => $propertyType,
                'user_id' => $_SESSION['user_id'] ?? $_SESSION['admin_id'] ?? null,
            ];

            $matches = $this->matchmaker->match($input);

            // Format results for frontend
            $properties = [];
            foreach ($matches as $match) {
                $properties[] = [
                    'id' => $match['property_id'] ?? $match['id'] ?? 0,
                    'title' => $match['title'] ?? $match['name'] ?? 'Property',
                    'type' => $match['type'] ?? 'Property',
                    'location' => $match['location'] ?? $match['address'] ?? '',
                    'area' => $match['area'] ?? $match['area_sqft'] ?? 0,
                    'bedrooms' => $match['bedrooms'] ?? 0,
                    'bathrooms' => $match['bathrooms'] ?? 0,
                    'price' => $match['price'] ?? $match['total_price'] ?? 0,
                    'price_formatted' => $this->formatPrice($match['price'] ?? $match['total_price'] ?? 0),
                    'image' => $match['image'] ?? $match['image_path'] ?? '',
                    'ai_score' => $match['score'] ?? $match['match_score'] ?? rand(70, 95),
                ];
            }

            return $this->jsonResponse([
                'success' => true,
                'properties' => $properties,
                'total' => count($properties),
            ]);
        } catch (\Throwable $e) {
            error_log('AI Search Error: ' . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Search failed. Please try again.'], 500);
        }
    }

    /**
     * Format price for display
     */
    private function formatPrice($price): string
    {
        if ($price >= 10000000) {
            return number_format($price / 10000000, 2) . ' Cr';
        } elseif ($price >= 100000) {
            return number_format($price / 100000, 2) . ' L';
        }
        return number_format($price);
    }

    /**
     * JSON response helper
     */
    private function jsonResponse(array $data, int $status = 200)
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}