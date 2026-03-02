<?php

namespace App\Services\Property;

use App\Core\Database;
use App\Models\Property;

/**
 * Property Comparison Service
 * Side-by-side comparison of properties
 */
class PropertyComparisonService
{
    private $db;

    // Comparison criteria with weights
    private $criteria = [
        'price' => ['weight' => 20, 'type' => 'lower_better'],
        'area' => ['weight' => 15, 'type' => 'higher_better'],
        'price_per_sqft' => ['weight' => 10, 'type' => 'lower_better'],
        'bedrooms' => ['weight' => 10, 'type' => 'higher_better'],
        'bathrooms' => ['weight' => 8, 'type' => 'higher_better'],
        'location_score' => ['weight' => 15, 'type' => 'higher_better'],
        'amenities_count' => ['weight' => 10, 'type' => 'higher_better'],
        'age' => ['weight' => 5, 'type' => 'lower_better'],
        'floor' => ['weight' => 7, 'type' => 'neutral']
    ];

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Compare multiple properties
     */
    public function compare(array $propertyIds): array
    {
        if (count($propertyIds) < 2) {
            return ['success' => false, 'error' => 'At least 2 properties required for comparison'];
        }

        if (count($propertyIds) > 4) {
            return ['success' => false, 'error' => 'Maximum 4 properties can be compared'];
        }

        $properties = $this->getPropertiesForComparison($propertyIds);

        if (count($properties) < 2) {
            return ['success' => false, 'error' => 'Not enough valid properties found'];
        }

        // Build comparison matrix
        $comparison = $this->buildComparisonMatrix($properties);

        // Calculate scores
        $scores = $this->calculateComparisonScores($properties);

        // Generate recommendation
        $recommendation = $this->generateRecommendation($properties, $scores);

        return [
            'success' => true,
            'properties' => $properties,
            'comparison' => $comparison,
            'scores' => $scores,
            'winner' => $scores[0] ?? null,
            'recommendation' => $recommendation
        ];
    }

    /**
     * Get properties with all comparison data
     */
    private function getPropertiesForComparison(array $ids): array
    {
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        
        $sql = "SELECT p.*, 
                       pt.name as property_type_name,
                       l.name as locality_name,
                       c.name as city_name,
                       (SELECT COUNT(*) FROM property_amenities pa WHERE pa.property_id = p.id) as amenities_count,
                       (SELECT AVG(rating) FROM property_reviews pr WHERE pr.property_id = p.id) as avg_rating,
                       (SELECT COUNT(*) FROM property_images pi WHERE pi.property_id = p.id) as image_count,
                       b.name as builder_name
                FROM properties p
                LEFT JOIN property_types pt ON p.property_type_id = pt.id
                LEFT JOIN localities l ON p.locality_id = l.id
                LEFT JOIN cities c ON p.city_id = c.id
                LEFT JOIN builders b ON p.builder_id = b.id
                WHERE p.id IN ($placeholders)";

        $properties = $this->db->query($sql, $ids)->fetchAll(\PDO::FETCH_ASSOC);

        // Enrich with calculated fields
        foreach ($properties as &$property) {
            $property['price_per_sqft'] = $property['area'] > 0 
                ? round($property['price'] / $property['area']) 
                : 0;
            $property['location_score'] = $this->calculateLocationScore($property);
            $property['age'] = $this->calculatePropertyAge($property['year_built'] ?? null);
            $property['amenities_list'] = $this->getPropertyAmenities($property['id']);
        }

        return $properties;
    }

    /**
     * Build comparison matrix
     */
    private function buildComparisonMatrix(array $properties): array
    {
        $matrix = [];

        // Basic Info
        $matrix['basic'] = [
            'title' => 'Basic Information',
            'fields' => [
                'title' => ['label' => 'Property Name', 'values' => array_column($properties, 'title')],
                'property_type_name' => ['label' => 'Type', 'values' => array_column($properties, 'property_type_name')],
                'locality_name' => ['label' => 'Locality', 'values' => array_column($properties, 'locality_name')],
                'city_name' => ['label' => 'City', 'values' => array_column($properties, 'city_name')],
                'builder_name' => ['label' => 'Builder', 'values' => array_column($properties, 'builder_name')]
            ]
        ];

        // Price Details
        $matrix['price'] = [
            'title' => 'Price Details',
            'fields' => [
                'price' => [
                    'label' => 'Total Price',
                    'values' => array_map(function($p) {
                        return '₹' . number_format($p['price']);
                    }, $properties),
                    'raw_values' => array_column($properties, 'price')
                ],
                'price_per_sqft' => [
                    'label' => 'Price per Sq.Ft',
                    'values' => array_map(function($p) {
                        return '₹' . number_format($p['price_per_sqft']);
                    }, $properties),
                    'raw_values' => array_column($properties, 'price_per_sqft')
                ]
            ]
        ];

        // Area Details
        $matrix['area'] = [
            'title' => 'Area & Configuration',
            'fields' => [
                'area' => [
                    'label' => 'Built-up Area',
                    'values' => array_map(function($p) {
                        return $p['area'] . ' sq.ft';
                    }, $properties),
                    'raw_values' => array_column($properties, 'area')
                ],
                'bedrooms' => ['label' => 'Bedrooms', 'values' => array_column($properties, 'bedrooms')],
                'bathrooms' => ['label' => 'Bathrooms', 'values' => array_column($properties, 'bathrooms')],
                'balconies' => ['label' => 'Balconies', 'values' => array_column($properties, 'balconies')],
                'floor' => ['label' => 'Floor', 'values' => array_column($properties, 'floor')],
                'total_floors' => ['label' => 'Total Floors', 'values' => array_column($properties, 'total_floors')]
            ]
        ];

        // Features
        $matrix['features'] = [
            'title' => 'Features',
            'fields' => [
                'facing' => ['label' => 'Facing', 'values' => array_column($properties, 'facing')],
                'age' => ['label' => 'Property Age', 'values' => array_map(function($p) {
                    return $p['age'] . ' years';
                }, $properties)],
                'possession_status' => ['label' => 'Possession', 'values' => array_column($properties, 'possession_status')],
                'furnished_status' => ['label' => 'Furnished', 'values' => array_column($properties, 'furnished_status')]
            ]
        ];

        // Amenities
        $matrix['amenities'] = [
            'title' => 'Amenities',
            'fields' => [
                'amenities' => [
                    'label' => 'Available Amenities',
                    'values' => array_column($properties, 'amenities_list'),
                    'count' => array_column($properties, 'amenities_count')
                ]
            ]
        ];

        // Ratings
        $matrix['ratings'] = [
            'title' => 'Ratings & Reviews',
            'fields' => [
                'avg_rating' => [
                    'label' => 'Average Rating',
                    'values' => array_map(function($p) {
                        return $p['avg_rating'] ? round($p['avg_rating'], 1) . '/5' : 'N/A';
                    }, $properties)
                ]
            ]
        ];

        return $matrix;
    }

    /**
     * Calculate comparison scores
     */
    private function calculateComparisonScores(array $properties): array
    {
        $scores = [];

        foreach ($properties as $index => $property) {
            $score = 0;
            $breakdown = [];

            foreach ($this->criteria as $key => $config) {
                $value = $property[$key] ?? 0;
                $normalized = $this->normalizeValue($key, $value, $properties);
                $weighted = $normalized * $config['weight'];
                $score += $weighted;
                $breakdown[$key] = [
                    'value' => $value,
                    'normalized' => $normalized,
                    'weighted' => $weighted
                ];
            }

            $scores[] = [
                'property_id' => $property['id'],
                'property_title' => $property['title'],
                'total_score' => round($score, 2),
                'breakdown' => $breakdown
            ];
        }

        // Sort by score descending
        usort($scores, function($a, $b) {
            return $b['total_score'] <=> $a['total_score'];
        });

        return $scores;
    }

    /**
     * Normalize value for comparison
     */
    private function normalizeValue(string $key, $value, array $allProperties): float
    {
        $allValues = array_filter(array_column($allProperties, $key));
        if (empty($allValues)) return 0;

        $min = min($allValues);
        $max = max($allValues);
        $range = $max - $min;

        if ($range == 0) return 0.5;

        $type = $this->criteria[$key]['type'] ?? 'higher_better';

        if ($type === 'higher_better') {
            return ($value - $min) / $range;
        } elseif ($type === 'lower_better') {
            return ($max - $value) / $range;
        }

        return 0.5; // neutral
    }

    /**
     * Generate recommendation
     */
    private function generateRecommendation(array $properties, array $scores): string
    {
        if (empty($scores)) return '';

        $winner = $scores[0];
        $runnerUp = $scores[1] ?? null;

        $reasons = [];
        foreach ($winner['breakdown'] as $key => $data) {
            if ($data['weighted'] > 10) {
                $reasons[] = $this->getReasonText($key, $data['value']);
            }
        }

        $recommendation = "Based on our analysis, **{$winner['property_title']}** offers the best value";
        
        if (!empty($reasons)) {
            $recommendation .= " due to " . implode(', ', array_slice($reasons, 0, 3));
        }

        if ($runnerUp && ($winner['total_score'] - $runnerUp['total_score']) < 10) {
            $recommendation .= ". However, {$runnerUp['property_title']} is a close alternative and worth considering.";
        }

        return $recommendation;
    }

    /**
     * Get reason text for recommendation
     */
    private function getReasonText(string $key, $value): string
    {
        $texts = [
            'price' => "competitive pricing at ₹" . number_format($value),
            'area' => "spacious area of {$value} sq.ft",
            'price_per_sqft' => "attractive price per sq.ft",
            'bedrooms' => "{$value} bedrooms",
            'location_score' => "excellent location",
            'amenities_count' => "{$value} amenities"
        ];

        return $texts[$key] ?? $key;
    }

    /**
     * Calculate location score
     */
    private function calculateLocationScore(array $property): int
    {
        $score = 50; // Base score

        // Add points for nearby amenities
        if (!empty($property['nearby_schools'])) $score += 10;
        if (!empty($property['nearby_hospitals'])) $score += 10;
        if (!empty($property['nearby_metro'])) $score += 15;
        if (!empty($property['nearby_mall'])) $score += 5;

        return min(100, $score);
    }

    /**
     * Calculate property age
     */
    private function calculatePropertyAge(?string $yearBuilt): int
    {
        if (!$yearBuilt) return 0;
        return date('Y') - (int)$yearBuilt;
    }

    /**
     * Get property amenities
     */
    private function getPropertyAmenities(int $propertyId): array
    {
        return $this->db->query(
            "SELECT a.name FROM amenities a
             JOIN property_amenities pa ON a.id = pa.amenity_id
             WHERE pa.property_id = ?",
            [$propertyId]
        )->fetchAll(\PDO::FETCH_COLUMN);
    }

    /**
     * Save comparison for user
     */
    public function saveComparison(int $userId, array $propertyIds): int
    {
        $this->db->query(
            "INSERT INTO property_comparisons (user_id, property_ids, created_at) VALUES (?, ?, NOW())",
            [$userId, json_encode($propertyIds)]
        );

        return (int)$this->db->lastInsertId();
    }

    /**
     * Get user's saved comparisons
     */
    public function getUserComparisons(int $userId): array
    {
        return $this->db->query(
            "SELECT * FROM property_comparisons WHERE user_id = ? ORDER BY created_at DESC",
            [$userId]
        )->fetchAll(\PDO::FETCH_ASSOC);
    }
}
