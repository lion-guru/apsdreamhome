<?php

namespace App\Services\Features;

use App\Core\Database;
use Psr\Log\LoggerInterface;

/**
 * Modern Custom Features Service
 * Handles specialized real estate features with proper MVC patterns
 */
class CustomFeaturesService
{
    private Database $db;
    private LoggerInterface $logger;

    // Feature Types
    public const FEATURE_VIRTUAL_TOUR = 'virtual_tour';
    public const FEATURE_PROPERTY_COMPARISON = 'property_comparison';
    public const FEATURE_NEIGHBORHOOD_ANALYTICS = 'neighborhood_analytics';
    public const FEATURE_INVESTMENT_CALCULATOR = 'investment_calculator';
    public const FEATURE_SMART_SEARCH = 'smart_search';

    public function __construct(Database $db, LoggerInterface $logger)
    {
        $this->db = $db;
        $this->logger = $logger;
        $this->initializeFeatures();
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
            
            $this->logger->info("Virtual tour created", ['tour_id' => $tourId, 'property_id' => $tourData['property_id']]);
            return $tourId;

        } catch (\Exception $e) {
            $this->logger->error("Failed to create virtual tour", ['error' => $e->getMessage()]);
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
                $tour['tour_data'] = json_decode($tour['tour_data'] ?? '{}', true) ?? [];
            }
            
            return $tour;

        } catch (\Exception $e) {
            $this->logger->error("Failed to get virtual tour", ['property_id' => $propertyId, 'error' => $e->getMessage()]);
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
                throw new \InvalidArgumentException('Compare 2-5 properties');
            }

            $placeholders = str_repeat('?,', count($propertyIds) - 1) . '?';
            $sql = "SELECT * FROM properties WHERE id IN ($placeholders) AND status = 'active'";
            
            $properties = $this->db->fetchAll($sql, $propertyIds);
            
            // Add comparison metrics
            foreach ($properties as &$property) {
                $property['comparison_score'] = $this->calculateComparisonScore($property);
                $property['features_count'] = $this->getPropertyFeaturesCount($property['id']);
                $property['nearby_amenities'] = $this->getNearbyAmenities($property['id']);
            }

            return $properties;

        } catch (\Exception $e) {
            $this->logger->error("Failed to compare properties", ['property_ids' => $propertyIds, 'error' => $e->getMessage()]);
            return [];
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
                return [];
            }

            $analytics = [
                'property' => $property,
                'demographics' => $this->getDemographics($property['location']),
                'schools' => $this->getNearbySchools($property['location']),
                'hospitals' => $this->getNearbyHospitals($property['location']),
                'transportation' => $this->getTransportationOptions($property['location']),
                'shopping' => $this->getNearbyShopping($property['location']),
                'restaurants' => $this->getNearbyRestaurants($property['location']),
                'crime_rate' => $this->getCrimeRate($property['location']),
                'property_values' => $this->getPropertyValueTrends($property['location']),
                'walk_score' => $this->calculateWalkScore($property['location'])
            ];

            return $analytics;

        } catch (\Exception $e) {
            $this->logger->error("Failed to get neighborhood analytics", ['property_id' => $propertyId, 'error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Investment Calculator
     */
    public function calculateInvestment(array $params): array
    {
        try {
            $propertyPrice = $params['property_price'];
            $downPayment = $params['down_payment'] ?? ($propertyPrice * 0.2);
            $loanAmount = $propertyPrice - $downPayment;
            $interestRate = $params['interest_rate'] ?? 7.5;
            $loanTerm = $params['loan_term'] ?? 30;
            $monthlyRent = $params['monthly_rent'] ?? 0;
            $appreciationRate = $params['appreciation_rate'] ?? 3;
            $holdingPeriod = $params['holding_period'] ?? 10;

            // Calculate monthly mortgage payment
            $monthlyRate = $interestRate / 100 / 12;
            $numPayments = $loanTerm * 12;
            $monthlyPayment = $loanAmount * ($monthlyRate * pow(1 + $monthlyRate, $numPayments)) / (pow(1 + $monthlyRate, $numPayments) - 1);

            // Calculate ROI and other metrics
            $totalPayments = $monthlyPayment * $numPayments;
            $totalRentIncome = $monthlyRent * 12 * $holdingPeriod;
            $futureValue = $propertyPrice * pow(1 + $appreciationRate / 100, $holdingPeriod);
            $totalReturn = ($futureValue - $propertyPrice) + $totalRentIncome;
            $totalInvestment = $downPayment + ($monthlyPayment * 12 * $holdingPeriod);

            $roi = $totalInvestment > 0 ? (($totalReturn / $totalInvestment) * 100) : 0;
            $cashFlow = $monthlyRent - $monthlyPayment;
            $capRate = $propertyPrice > 0 ? (($monthlyRent * 12) / $propertyPrice) * 100 : 0;

            return [
                'monthly_payment' => round($monthlyPayment, 2),
                'total_payments' => round($totalPayments, 2),
                'total_rent_income' => round($totalRentIncome, 2),
                'future_value' => round($futureValue, 2),
                'total_return' => round($totalReturn, 2),
                'roi_percentage' => round($roi, 2),
                'monthly_cash_flow' => round($cashFlow, 2),
                'cap_rate_percentage' => round($capRate, 2),
                'break_even_point' => $monthlyPayment > 0 ? round($downPayment / $monthlyPayment, 1) : 0
            ];

        } catch (\Exception $e) {
            $this->logger->error("Failed to calculate investment", ['params' => $params, 'error' => $e->getMessage()]);
            return [];
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

            if (!empty($criteria['property_type'])) {
                $sql .= " AND type = ?";
                $params[] = $criteria['property_type'];
            }

            if (!empty($criteria['min_price'])) {
                $sql .= " AND price >= ?";
                $params[] = $criteria['min_price'];
            }

            if (!empty($criteria['max_price'])) {
                $sql .= " AND price <= ?";
                $params[] = $criteria['max_price'];
            }

            if (!empty($criteria['bedrooms'])) {
                $sql .= " AND bedrooms = ?";
                $params[] = $criteria['bedrooms'];
            }

            if (!empty($criteria['bathrooms'])) {
                $sql .= " AND bathrooms = ?";
                $params[] = $criteria['bathrooms'];
            }

            // Add smart scoring
            $sql .= " ORDER BY featured DESC, created_at DESC";

            $properties = $this->db->fetchAll($sql, $params);

            // Add smart search scores
            foreach ($properties as &$property) {
                $property['search_score'] = $this->calculateSearchScore($property, $criteria);
                $property['match_percentage'] = $this->calculateMatchPercentage($property, $criteria);
            }

            // Sort by search score
            usort($properties, function ($a, $b) {
                return $b['search_score'] - $a['search_score'];
            });

            return array_slice($properties, 0, $criteria['limit'] ?? 20);

        } catch (\Exception $e) {
            $this->logger->error("Failed to perform smart search", ['criteria' => $criteria, 'error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Get feature statistics
     */
    public function getFeatureStats(): array
    {
        try {
            $stats = [];

            // Virtual tours
            $stats['virtual_tours'] = $this->db->fetchOne("SELECT COUNT(*) FROM virtual_tours WHERE status = 'active'") ?? 0;

            // Property comparisons
            $stats['comparisons_today'] = $this->db->fetchOne(
                "SELECT COUNT(*) FROM property_comparisons WHERE DATE(created_at) = CURDATE()"
            ) ?? 0;

            // Investment calculations
            $stats['calculations_today'] = $this->db->fetchOne(
                "SELECT COUNT(*) FROM investment_calculations WHERE DATE(created_at) = CURDATE()"
            ) ?? 0;

            // Smart searches
            $stats['searches_today'] = $this->db->fetchOne(
                "SELECT COUNT(*) FROM smart_searches WHERE DATE(created_at) = CURDATE()"
            ) ?? 0;

            // Most popular features
            $popularFeatures = $this->db->fetchAll("
                SELECT feature_type, COUNT(*) as usage_count 
                FROM feature_usage 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                GROUP BY feature_type 
                ORDER BY usage_count DESC
            ");

            $stats['popular_features'] = $popularFeatures;

            return $stats;

        } catch (\Exception $e) {
            $this->logger->error("Failed to get feature stats", ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Initialize custom features
     */
    private function initializeFeatures(): void
    {
        try {
            $this->createCustomTables();
            $this->logger->info("Custom features initialized");
        } catch (\Exception $e) {
            $this->logger->error("Failed to initialize custom features", ['error' => $e->getMessage()]);
        }
    }

    /**
     * Create custom feature tables
     */
    private function createCustomTables(): void
    {
        $tables = [
            "CREATE TABLE IF NOT EXISTS virtual_tours (
                id INT AUTO_INCREMENT PRIMARY KEY,
                property_id INT NOT NULL,
                title VARCHAR(255) NOT NULL,
                description TEXT,
                tour_data JSON,
                status ENUM('active', 'inactive') DEFAULT 'active',
                created_by INT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_property_id (property_id),
                INDEX idx_status (status)
            )",
            
            "CREATE TABLE IF NOT EXISTS property_comparisons (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT,
                property_ids JSON,
                comparison_data JSON,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_user_id (user_id)
            )",
            
            "CREATE TABLE IF NOT EXISTS investment_calculations (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT,
                property_id INT,
                calculation_params JSON,
                calculation_results JSON,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_user_id (user_id),
                INDEX idx_property_id (property_id)
            )",
            
            "CREATE TABLE IF NOT EXISTS smart_searches (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT,
                search_criteria JSON,
                search_results JSON,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_user_id (user_id)
            )",
            
            "CREATE TABLE IF NOT EXISTS feature_usage (
                id INT AUTO_INCREMENT PRIMARY KEY,
                feature_type VARCHAR(50) NOT NULL,
                user_id INT,
                usage_data JSON,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_feature_type (feature_type),
                INDEX idx_user_id (user_id)
            )"
        ];

        foreach ($tables as $sql) {
            $this->db->execute($sql);
        }
    }

    /**
     * Helper methods
     */
    private function calculateComparisonScore(array $property): float
    {
        $score = 0;
        
        // Price score (lower is better)
        $score += max(0, 100 - ($property['price'] / 10000));
        
        // Size score
        $score += min(50, $property['area'] / 10);
        
        // Featured bonus
        if ($property['featured']) {
            $score += 25;
        }
        
        return min(100, $score);
    }

    private function getPropertyFeaturesCount(int $propertyId): int
    {
        return $this->db->fetchOne("SELECT COUNT(*) FROM property_features WHERE property_id = ?", [$propertyId]) ?? 0;
    }

    private function getNearbyAmenities(int $propertyId): array
    {
        return $this->db->fetchAll("SELECT * FROM nearby_amenities WHERE property_id = ? LIMIT 10", [$propertyId]);
    }

    private function getDemographics(string $location): array
    {
        // Placeholder implementation
        return [
            'population' => 50000,
            'median_income' => 45000,
            'education_level' => 'High',
            'employment_rate' => 95.5
        ];
    }

    private function getNearbySchools(string $location): array
    {
        return $this->db->fetchAll("SELECT * FROM schools WHERE location LIKE ? LIMIT 5", ["%{$location}%"]);
    }

    private function getNearbyHospitals(string $location): array
    {
        return $this->db->fetchAll("SELECT * FROM hospitals WHERE location LIKE ? LIMIT 5", ["%{$location}%"]);
    }

    private function getTransportationOptions(string $location): array
    {
        return [
            'bus_stations' => 3,
            'metro_access' => true,
            'highway_access' => true,
            'airport_distance' => '15 km'
        ];
    }

    private function getNearbyShopping(string $location): array
    {
        return $this->db->fetchAll("SELECT * FROM shopping_centers WHERE location LIKE ? LIMIT 5", ["%{$location}%"]);
    }

    private function getNearbyRestaurants(string $location): array
    {
        return $this->db->fetchAll("SELECT * FROM restaurants WHERE location LIKE ? LIMIT 10", ["%{$location}%"]);
    }

    private function getCrimeRate(string $location): array
    {
        return [
            'overall_rate' => 'Low',
            'property_crime' => 2.1,
            'violent_crime' => 0.8,
            'safety_index' => 85
        ];
    }

    private function getPropertyValueTrends(string $location): array
    {
        return [
            'current_avg_price' => 250000,
            'last_year_change' => 5.2,
            '5_year_trend' => 'Upward',
            'predicted_growth' => 3.5
        ];
    }

    private function calculateWalkScore(string $location): int
    {
        return 78; // Placeholder walk score
    }

    private function calculateSearchScore(array $property, array $criteria): float
    {
        $score = 50; // Base score
        
        // Price matching
        if (!empty($criteria['min_price']) && !empty($criteria['max_price'])) {
            if ($property['price'] >= $criteria['min_price'] && $property['price'] <= $criteria['max_price']) {
                $score += 20;
            }
        }
        
        // Type matching
        if (!empty($criteria['property_type']) && $property['type'] === $criteria['property_type']) {
            $score += 15;
        }
        
        // Bedroom matching
        if (!empty($criteria['bedrooms']) && $property['bedrooms'] === $criteria['bedrooms']) {
            $score += 10;
        }
        
        return min(100, $score);
    }

    private function calculateMatchPercentage(array $property, array $criteria): float
    {
        $matched = 0;
        $total = count($criteria);
        
        foreach ($criteria as $key => $value) {
            if ($key === 'location' && !empty($value)) {
                if (stripos($property['location'], $value) !== false) {
                    $matched++;
                }
            } elseif ($key === 'property_type' && $property['type'] === $value) {
                $matched++;
            } elseif ($key === 'bedrooms' && $property['bedrooms'] == $value) {
                $matched++;
            } elseif ($key === 'bathrooms' && $property['bathrooms'] == $value) {
                $matched++;
            }
        }
        
        return $total > 0 ? ($matched / $total) * 100 : 0;
    }
}
