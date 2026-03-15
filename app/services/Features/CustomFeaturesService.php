<?php

namespace App\Services\Features;

use App\Core\Database\Database;

/**
 * Modern Custom Features Service
 * Handles specialized real estate features with proper MVC patterns
 */
class CustomFeaturesService
{
    private Database $db;

    // Feature Types
    public const FEATURE_VIRTUAL_TOUR = 'virtual_tour';
    public const FEATURE_PROPERTY_COMPARISON = 'property_comparison';
    public const FEATURE_NEIGHBORHOOD_ANALYTICS = 'neighborhood_analytics';
    public const FEATURE_INVESTMENT_CALCULATOR = 'investment_calculator';
    public const FEATURE_SMART_SEARCH = 'smart_search';

    public function __construct(Database $db = null)
    {
        $this->db = $db ?: Database::getInstance();
        $this->initializeFeatures();
    }

    /**
     * Initialize custom features
     */
    private function initializeFeatures(): void
    {
        $this->createFeatureTables();
    }

    /**
     * Create feature tables if they don't exist
     */
    private function createFeatureTables(): void
    {
        try {
            // Virtual tours table
            $this->db->execute("
                CREATE TABLE IF NOT EXISTS virtual_tours (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    property_id INT NOT NULL,
                    title VARCHAR(255) NOT NULL,
                    description TEXT,
                    tour_data JSON,
                    status ENUM('active', 'inactive') DEFAULT 'active',
                    created_by INT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX idx_property_id (property_id),
                    INDEX idx_status (status)
                )
            ");

            // Feature usage table
            $this->db->execute("
                CREATE TABLE IF NOT EXISTS feature_usage (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    feature_type VARCHAR(50) NOT NULL,
                    user_id INT,
                    property_id INT,
                    usage_data JSON,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_feature_type (feature_type),
                    INDEX idx_user_id (user_id),
                    INDEX idx_created_at (created_at)
                )
            ");

            // Activity log table
            $this->db->execute("
                CREATE TABLE IF NOT EXISTS activity_log (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT,
                    action VARCHAR(100) NOT NULL,
                    description TEXT,
                    entity_type VARCHAR(50),
                    entity_id INT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_user_id (user_id),
                    INDEX idx_action (action),
                    INDEX idx_created_at (created_at)
                )
            ");
        } catch (\Exception $e) {
            error_log('Failed to create feature tables: ' . $e->getMessage());
        }
    }

    /**
     * Virtual Tour Management
     */
    public function createVirtualTour(array $tourData): int
    {
        try {
            $sql = "INSERT INTO virtual_tours 
                    (property_id, title, description, tour_data, created_by, created_at) 
                    VALUES (?, ?, ?, ?, ?, NOW())";

            $this->db->execute($sql, [
                $tourData['property_id'],
                $tourData['title'],
                $tourData['description'] ?? '',
                json_encode($tourData['tour_data'] ?? []),
                $tourData['created_by']
            ]);

            $tourId = $this->db->lastInsertId();

            // Log activity
            $this->logActivity('virtual_tour_created', 'Virtual tour created', 'virtual_tour', $tourId);

            return $tourId;
        } catch (\Exception $e) {
            error_log('Failed to create virtual tour: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get virtual tour by property
     */
    public function getVirtualTour(int $propertyId): ?array
    {
        try {
            $sql = "SELECT * FROM virtual_tours WHERE property_id = ? AND status = 'active'";
            $tour = $this->db->fetchOne($sql, [$propertyId]);

            if ($tour) {
                $tour['tour_data'] = json_decode($tour['tour_data'], true) ?: [];
            }

            return $tour;
        } catch (\Exception $e) {
            error_log('Failed to get virtual tour: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Property Comparison
     */
    public function compareProperties(array $propertyIds): array
    {
        try {
            if (count($propertyIds) < 2 || count($propertyIds) > 5) {
                throw new \Exception('Please select between 2 and 5 properties');
            }

            $placeholders = str_repeat('?,', count($propertyIds) - 1) . '?';
            $sql = "SELECT * FROM properties WHERE id IN ($placeholders) AND status = 'active'";
            $properties = $this->db->fetchAll($sql, $propertyIds);

            $comparison = [
                'properties' => $properties,
                'comparison_matrix' => $this->buildComparisonMatrix($properties),
                'recommendations' => $this->generateComparisonRecommendations($properties)
            ];

            // Log feature usage
            $this->logFeatureUsage(self::FEATURE_PROPERTY_COMPARISON, [
                'property_ids' => $propertyIds,
                'properties_count' => count($properties)
            ]);

            return $comparison;
        } catch (\Exception $e) {
            error_log('Failed to compare properties: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Neighborhood Analytics
     */
    public function getNeighborhoodAnalytics(int $propertyId): array
    {
        try {
            $property = $this->db->fetchOne("SELECT * FROM properties WHERE id = ?", [$propertyId]);

            if (!$property) {
                throw new \Exception('Property not found');
            }

            $analytics = [
                'property' => $property,
                'nearby_properties' => $this->getNearbyProperties($propertyId),
                'price_trends' => $this->getPriceTrends($property['location']),
                'amenities' => $this->getNeighborhoodAmenities($property['location']),
                'market_analysis' => $this->getMarketAnalysis($property['location'])
            ];

            // Log feature usage
            $this->logFeatureUsage(self::FEATURE_NEIGHBORHOOD_ANALYTICS, [
                'property_id' => $propertyId,
                'location' => $property['location']
            ]);

            return $analytics;
        } catch (\Exception $e) {
            error_log('Failed to get neighborhood analytics: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Investment Calculator
     */
    public function calculateInvestment(array $params): array
    {
        try {
            $propertyPrice = $params['property_price'];
            $downPayment = $params['down_payment'] ?? 20;
            $loanTerm = $params['loan_term'] ?? 30;
            $interestRate = $params['interest_rate'] ?? 6.5;
            $monthlyRent = $params['monthly_rent'] ?? 0;
            $appreciationRate = $params['appreciation_rate'] ?? 3;

            $calculations = [
                'property_price' => $propertyPrice,
                'down_payment_amount' => $propertyPrice * ($downPayment / 100),
                'loan_amount' => $propertyPrice * (1 - $downPayment / 100),
                'monthly_payment' => $this->calculateMonthlyPayment($propertyPrice * (1 - $downPayment / 100), $interestRate, $loanTerm),
                'total_interest' => $this->calculateTotalInterest($propertyPrice * (1 - $downPayment / 100), $interestRate, $loanTerm),
                'roi_analysis' => $this->calculateROI($propertyPrice, $monthlyRent, $appreciationRate),
                'break_even_point' => $this->calculateBreakEven($propertyPrice, $monthlyRent)
            ];

            // Log feature usage
            $this->logFeatureUsage(self::FEATURE_INVESTMENT_CALCULATOR, $params);

            return $calculations;
        } catch (\Exception $e) {
            error_log('Failed to calculate investment: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Smart Search
     */
    public function smartSearch(array $criteria): array
    {
        try {
            $sql = "SELECT * FROM properties WHERE status = 'active'";
            $params = [];

            // Build dynamic query based on criteria
            if (!empty($criteria['location'])) {
                $sql .= " AND location LIKE ?";
                $params[] = "%" . $criteria['location'] . "%";
            }

            if (!empty($criteria['price_min'])) {
                $sql .= " AND price >= ?";
                $params[] = $criteria['price_min'];
            }

            if (!empty($criteria['price_max'])) {
                $sql .= " AND price <= ?";
                $params[] = $criteria['price_max'];
            }

            if (!empty($criteria['property_type'])) {
                $sql .= " AND property_type = ?";
                $params[] = $criteria['property_type'];
            }

            if (!empty($criteria['bedrooms'])) {
                $sql .= " AND bedrooms >= ?";
                $params[] = $criteria['bedrooms'];
            }

            $sql .= " ORDER BY created_at DESC LIMIT 50";

            $properties = $this->db->fetchAll($sql, $params);

            // Enhance results with relevance scoring
            $results = [];
            foreach ($properties as $property) {
                $property['relevance_score'] = $this->calculateRelevanceScore($property, $criteria);
                $results[] = $property;
            }

            // Sort by relevance score
            usort($results, function ($a, $b) {
                return $b['relevance_score'] <=> $a['relevance_score'];
            });

            // Log feature usage
            $this->logFeatureUsage(self::FEATURE_SMART_SEARCH, $criteria);

            return [
                'properties' => array_slice($results, 0, 20),
                'total_found' => count($results),
                'search_criteria' => $criteria
            ];
        } catch (\Exception $e) {
            error_log('Failed to perform smart search: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get feature statistics
     */
    public function getFeatureStats(): array
    {
        try {
            $stats = [];

            // Virtual tours count
            $stats['virtual_tours'] = $this->db->fetchOne("SELECT COUNT(*) as count FROM virtual_tours WHERE status = 'active'")['count'] ?? 0;

            // Properties count
            $stats['properties'] = $this->db->fetchOne("SELECT COUNT(*) as count FROM properties WHERE status = 'active'")['count'] ?? 0;

            // Recent activities
            $stats['recent_activities'] = $this->db->fetchAll("SELECT * FROM activity_log WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR) ORDER BY created_at DESC LIMIT 10");

            return $stats;
        } catch (\Exception $e) {
            error_log('Failed to get feature stats: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Toggle feature status (Virtual Tour)
     */
    public function toggleFeatureStatus(int $tourId): array
    {
        try {
            $tour = $this->db->fetchOne("SELECT * FROM virtual_tours WHERE id = ?", [$tourId]);

            if (!$tour) {
                throw new \Exception('Virtual tour not found');
            }

            $newStatus = $tour['status'] === 'active' ? 'inactive' : 'active';

            $this->db->execute("UPDATE virtual_tours SET status = ?, updated_at = NOW() WHERE id = ?", [$newStatus, $tourId]);

            return [
                'id' => $tourId,
                'status' => $newStatus,
                'updated_at' => date('Y-m-d H:i:s')
            ];
        } catch (\Exception $e) {
            error_log('Failed to toggle feature status: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Bulk operation on features
     */
    public function bulkOperation(string $operation, array $featureIds): array
    {
        try {
            $results = [];

            foreach ($featureIds as $featureId) {
                try {
                    switch ($operation) {
                        case 'activate':
                            $this->db->execute("UPDATE virtual_tours SET status = 'active', updated_at = NOW() WHERE id = ?", [$featureId]);
                            $results[] = ['id' => $featureId, 'success' => true, 'action' => 'activated'];
                            break;

                        case 'deactivate':
                            $this->db->execute("UPDATE virtual_tours SET status = 'inactive', updated_at = NOW() WHERE id = ?", [$featureId]);
                            $results[] = ['id' => $featureId, 'success' => true, 'action' => 'deactivated'];
                            break;

                        case 'delete':
                            $this->db->execute("DELETE FROM virtual_tours WHERE id = ?", [$featureId]);
                            $results[] = ['id' => $featureId, 'success' => true, 'action' => 'deleted'];
                            break;

                        default:
                            $results[] = ['id' => $featureId, 'success' => false, 'error' => 'Unknown operation'];
                    }
                } catch (\Exception $e) {
                    $results[] = ['id' => $featureId, 'success' => false, 'error' => $e->getMessage()];
                }
            }

            return $results;
        } catch (\Exception $e) {
            error_log('Failed to perform bulk operation: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Export features data
     */
    public function exportFeatures(string $format = 'json'): array
    {
        try {
            $features = $this->db->fetchAll("SELECT * FROM virtual_tours ORDER BY created_at DESC");

            switch ($format) {
                case 'json':
                    return [
                        'format' => 'json',
                        'data' => $features,
                        'exported_at' => date('Y-m-d H:i:s'),
                        'total_count' => count($features)
                    ];

                case 'csv':
                    $csv = "ID,Property ID,Title,Description,Status,Created At\n";
                    foreach ($features as $feature) {
                        $csv .= "{$feature['id']},{$feature['property_id']},\"{$feature['title']}\",\"{$feature['description']}\",{$feature['status']},{$feature['created_at']}\n";
                    }
                    return [
                        'format' => 'csv',
                        'data' => $csv,
                        'exported_at' => date('Y-m-d H:i:s'),
                        'total_count' => count($features)
                    ];

                default:
                    throw new \Exception('Unsupported export format');
            }
        } catch (\Exception $e) {
            error_log('Failed to export features: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Import features data
     */
    public function importFeatures(string $format, string $content): array
    {
        try {
            $imported = [];
            $errors = [];

            switch ($format) {
                case 'json':
                    $data = json_decode($content, true);
                    if (!$data) {
                        throw new \Exception('Invalid JSON format');
                    }

                    foreach ($data as $item) {
                        try {
                            $tourId = $this->createVirtualTour([
                                'property_id' => $item['property_id'],
                                'title' => $item['title'],
                                'description' => $item['description'] ?? '',
                                'tour_data' => $item['tour_data'] ?? [],
                                'created_by' => $item['created_by'] ?? 'system'
                            ]);
                            $imported[] = ['id' => $tourId, 'title' => $item['title']];
                        } catch (\Exception $e) {
                            $errors[] = ['item' => $item, 'error' => $e->getMessage()];
                        }
                    }
                    break;

                default:
                    throw new \Exception('Unsupported import format');
            }

            return [
                'format' => $format,
                'imported' => $imported,
                'errors' => $errors,
                'imported_at' => date('Y-m-d H:i:s'),
                'total_imported' => count($imported),
                'total_errors' => count($errors)
            ];
        } catch (\Exception $e) {
            error_log('Failed to import features: ' . $e->getMessage());
            throw $e;
        }
    }

    // Helper methods
    private function logActivity(string $action, string $description, string $entityType, int $entityId): void
    {
        try {
            $sql = "INSERT INTO activity_log (action, description, entity_type, entity_id, created_at) VALUES (?, ?, ?, ?, NOW())";
            $this->db->execute($sql, [$action, $description, $entityType, $entityId]);
        } catch (\Exception $e) {
            error_log('Failed to log activity: ' . $e->getMessage());
        }
    }

    private function logFeatureUsage(string $featureType, array $data): void
    {
        try {
            $sql = "INSERT INTO feature_usage (feature_type, usage_data, created_at) VALUES (?, ?, NOW())";
            $this->db->execute($sql, [$featureType, json_encode($data)]);
        } catch (\Exception $e) {
            error_log('Failed to log feature usage: ' . $e->getMessage());
        }
    }

    private function buildComparisonMatrix(array $properties): array
    {
        $matrix = [];
        $features = ['price', 'bedrooms', 'bathrooms', 'area', 'location'];

        foreach ($features as $feature) {
            $matrix[$feature] = [];
            foreach ($properties as $property) {
                $matrix[$feature][] = [
                    'property_id' => $property['id'],
                    'value' => $property[$feature] ?? 'N/A',
                    'rank' => 0 // Will be calculated
                ];
            }

            // Sort by value and assign ranks
            usort($matrix[$feature], function ($a, $b) {
                return $b['value'] <=> $a['value'];
            });

            foreach ($matrix[$feature] as $index => &$item) {
                $item['rank'] = $index + 1;
            }
        }

        return $matrix;
    }

    private function generateComparisonRecommendations(array $properties): array
    {
        $recommendations = [];

        // Best value
        $bestValue = array_reduce($properties, function ($best, $property) {
            $value = $property['price'] / ($property['area'] ?? 1);
            return $value < ($best['value'] ?? PHP_FLOAT_MAX) ? ['property_id' => $property['id'], 'value' => $value] : $best;
        });

        if ($bestValue) {
            $recommendations[] = ['type' => 'best_value', 'property_id' => $bestValue['property_id'], 'reason' => 'Best price per square foot'];
        }

        return $recommendations;
    }

    private function getNearbyProperties(int $propertyId): array
    {
        try {
            $property = $this->db->fetchOne("SELECT location FROM properties WHERE id = ?", [$propertyId]);
            if (!$property) return [];

            return $this->db->fetchAll(
                "SELECT * FROM properties WHERE location = ? AND id != ? AND status = 'active' LIMIT 10",
                [$property['location'], $propertyId]
            );
        } catch (\Exception $e) {
            return [];
        }
    }

    private function getPriceTrends(string $location): array
    {
        // Mock implementation - would integrate with real market data
        return [
            'average_price' => 350000,
            'price_change_1year' => 5.2,
            'price_change_3years' => 15.8,
            'market_trend' => 'increasing'
        ];
    }

    private function getNeighborhoodAmenities(string $location): array
    {
        // Mock implementation - would integrate with real location data
        return [
            'schools' => 3,
            'hospitals' => 2,
            'shopping_centers' => 5,
            'parks' => 4,
            'public_transport' => 'excellent'
        ];
    }

    private function getMarketAnalysis(string $location): array
    {
        // Mock implementation - would integrate with real market data
        return [
            'market_health' => 'strong',
            'inventory_level' => 'low',
            'days_on_market' => 28,
            'buyer_demand' => 'high'
        ];
    }

    private function calculateMonthlyPayment(float $loanAmount, float $annualRate, int $years): float
    {
        $monthlyRate = $annualRate / 100 / 12;
        $months = $years * 12;

        if ($monthlyRate === 0) {
            return $loanAmount / $months;
        }

        return $loanAmount * ($monthlyRate * pow(1 + $monthlyRate, $months)) / (pow(1 + $monthlyRate, $months) - 1);
    }

    private function calculateTotalInterest(float $loanAmount, float $annualRate, int $years): float
    {
        $monthlyPayment = $this->calculateMonthlyPayment($loanAmount, $annualRate, $years);
        $totalPaid = $monthlyPayment * $years * 12;
        return $totalPaid - $loanAmount;
    }

    private function calculateROI(float $propertyPrice, float $monthlyRent, float $appreciationRate): array
    {
        $annualRent = $monthlyRent * 12;
        $rentalYield = ($annualRent / $propertyPrice) * 100;

        return [
            'rental_yield' => $rentalYield,
            'annual_appreciation' => $appreciationRate,
            'total_roi' => $rentalYield + $appreciationRate
        ];
    }

    private function calculateBreakEven(float $propertyPrice, float $monthlyRent): int
    {
        if ($monthlyRent <= 0) return 0;
        return ceil($propertyPrice / ($monthlyRent * 12));
    }

    private function calculateRelevanceScore(array $property, array $criteria): float
    {
        $score = 0;

        if (!empty($criteria['location']) && stripos($property['location'], $criteria['location']) !== false) {
            $score += 30;
        }

        if (!empty($criteria['price_min']) && $property['price'] >= $criteria['price_min']) {
            $score += 20;
        }

        if (!empty($criteria['price_max']) && $property['price'] <= $criteria['price_max']) {
            $score += 20;
        }

        if (!empty($criteria['bedrooms']) && $property['bedrooms'] >= $criteria['bedrooms']) {
            $score += 15;
        }

        if (!empty($criteria['property_type']) && $property['property_type'] === $criteria['property_type']) {
            $score += 15;
        }

        return $score;
    }
}
