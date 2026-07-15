<?php
/**
 * PricePredictor - Self-hosted property price prediction
 * Linear regression + seasonal multipliers + feature scoring
 * No external API
 */

namespace App\Services\AI;

use PDO;

class PricePredictor
{
    private $db;
    private $pdo;

    /**
     * Indian real estate seasonal multipliers (month-indexed)
     * Diwali/wedding season = peak, monsoon = dip
     */
    private const SEASONAL_MULTIPLIERS = [
        1  => 1.00, // Jan - post-holiday lull
        2  => 0.98, // Feb - slow
        3  => 1.02, // Mar - financial year end rush
        4  => 1.03, // Apr - new FY, new projects
        5  => 1.01, // May - moderate
        6  => 0.96, // Jun - monsoon dip
        7  => 0.95, // Jul - peak monsoon, lowest demand
        8  => 0.97, // Aug - monsoon continues
        9  => 1.04, // Sep - pre-Diwali anticipation
        10 => 1.10, // Oct - Diwali peak
        11 => 1.08, // Nov - post-Diwali, wedding season
        12 => 1.05, // Dec - year-end, wedding season
    ];

    /**
     * Feature-based price multipliers for Indian real estate
     */
    private const FEATURE_MULTIPLIERS = [
        'bedroom_premium' => [
            '1RK' => 0.85, '1BHK' => 0.90, '2BHK' => 1.00,
            '3BHK' => 1.12, '4BHK' => 1.28, '5BHK+' => 1.45,
        ],
        'amenity_premium' => [
            'gym' => 1.03, 'pool' => 1.05, 'parking' => 1.04,
            'garden' => 1.02, 'security' => 1.03, 'lift' => 1.02,
            'power_backup' => 1.03, 'clubhouse' => 1.04,
            'kids_play' => 1.01, 'jogging_track' => 1.02,
        ],
        'facing_premium' => [
            'east' => 1.04, 'north' => 1.03, 'west' => 1.00,
            'south' => 0.98, 'north_east' => 1.06, 'north_west' => 1.02,
            'south_east' => 1.01, 'south_west' => 0.97,
        ],
    ];

    public function __construct($db)
    {
        $this->db = $db;
        $this->pdo = is_object($db) && method_exists($db, 'getPdo') ? $db->getPdo() : $db;
    }

    /**
     * Predict price for a property based on features + seasonal adjustment
     */
    public function predict(
        string $propertyType,
        ?int $districtId = null,
        ?int $areaSqft = null,
        int $bedrooms = 0,
        int $bathrooms = 0,
        array $amenities = [],
        string $facing = '',
        int $month = 0
    ): array {
        $data = $this->getHistoricalData($propertyType, $districtId);
        if (count($data) < 5) {
            return [
                'predicted_price' => 0,
                'confidence' => 0,
                'message' => 'Insufficient data (need at least 5 samples)',
                'sample_size' => count($data)
            ];
        }

        $coefficients = $this->train($data);

        $area = $areaSqft ?? 1000;
        $basePrice = $coefficients['intercept']
            + $coefficients['area'] * $area
            + $coefficients['bedrooms'] * $bedrooms
            + $coefficients['bathrooms'] * $bathrooms;

        // Feature multiplier: bedrooms
        $bedroomKey = $this->getBedroomKey($bedrooms);
        $bedroomMultiplier = self::FEATURE_MULTIPLIERS['bedroom_premium'][$bedroomKey] ?? 1.0;

        // Feature multiplier: amenities
        $amenityMultiplier = 1.0;
        foreach ($amenities as $amenity) {
            $amenityLower = strtolower($amenity);
            if (isset(self::FEATURE_MULTIPLIERS['amenity_premium'][$amenityLower])) {
                $amenityMultiplier *= self::FEATURE_MULTIPLIERS['amenity_premium'][$amenityLower];
            }
        }

        // Feature multiplier: facing
        $facingLower = strtolower(str_replace(' ', '_', $facing));
        $facingMultiplier = self::FEATURE_MULTIPLIERS['facing_premium'][$facingLower] ?? 1.0;

        // Seasonal multiplier
        $effectiveMonth = $month ?: (int)date('n');
        $seasonalMultiplier = self::SEASONAL_MULTIPLIERS[$effectiveMonth] ?? 1.0;

        // Apply all multipliers
        $adjustedPrice = $basePrice * $bedroomMultiplier * $amenityMultiplier * $facingMultiplier * $seasonalMultiplier;

        // Confidence
        $r2 = $this->r_squared($data, $coefficients);
        $sampleSize = count($data);
        $confidence = min(1.0, $r2 * sqrt($sampleSize / 100));

        // Save model
        $this->saveModel($propertyType, $districtId, $coefficients, $r2, $sampleSize);

        $low = $adjustedPrice * 0.85;
        $high = $adjustedPrice * 1.15;

        return [
            'predicted_price' => round($adjustedPrice, 0),
            'low_estimate' => round($low, 0),
            'high_estimate' => round($high, 0),
            'base_price' => round($basePrice, 0),
            'confidence' => round($confidence, 2),
            'r_squared' => round($r2, 3),
            'sample_size' => $sampleSize,
            'property_type' => $propertyType,
            'district_id' => $districtId,
            'multipliers' => [
                'seasonal' => round($seasonalMultiplier, 3),
                'bedroom' => round($bedroomMultiplier, 3),
                'amenity' => round($amenityMultiplier, 3),
                'facing' => round($facingMultiplier, 3),
                'month' => $effectiveMonth,
                'month_name' => date('F', mktime(0, 0, 0, $effectiveMonth, 1)),
            ],
            'market_advice' => $this->getMarketAdvice($effectiveMonth, $seasonalMultiplier),
        ];
    }

    /**
     * Get market advice based on seasonal trend
     */
    private function getMarketAdvice(int $month, float $multiplier): string
    {
        if ($multiplier >= 1.08) {
            return "Peak season ({$month}). Prices are high. Good time to sell, premium for buyers.";
        } elseif ($multiplier <= 0.96) {
            return "Off-season ({$month}). Prices are low. Good time to buy with negotiation leverage.";
        } elseif ($multiplier >= 1.02) {
            return "Moderate demand ({$month}). Fair time for both buyers and sellers.";
        }
        return "Stable market ({$month}). Standard pricing conditions.";
    }

    /**
     * Map bedroom count to feature key
     */
    private function getBedroomKey(int $bedrooms): string
    {
        if ($bedrooms <= 0) return '1RK';
        if ($bedrooms === 1) return '1BHK';
        if ($bedrooms === 2) return '2BHK';
        if ($bedrooms === 3) return '3BHK';
        if ($bedrooms === 4) return '4BHK';
        return '5BHK+';
    }

    /**
     * Get seasonal multiplier for a given month
     */
    public function getSeasonalMultiplier(int $month): float
    {
        return self::SEASONAL_MULTIPLIERS[$month] ?? 1.0;
    }

    /**
     * Get full seasonal calendar
     */
    public function getSeasonalCalendar(): array
    {
        $calendar = [];
        foreach (self::SEASONAL_MULTIPLIERS as $month => $multiplier) {
            $calendar[] = [
                'month' => $month,
                'name' => date('F', mktime(0, 0, 0, $month, 1)),
                'multiplier' => $multiplier,
                'trend' => $multiplier > 1.03 ? 'peak' : ($multiplier < 0.97 ? 'dip' : 'stable'),
                'advice' => $this->getMarketAdvice($month, $multiplier),
            ];
        }
        return $calendar;
    }

    private function getHistoricalData(string $type, ?int $districtId): array
    {
        $rows = [];
        $sources = [
            'user_properties' => ['price', 'area_sqft', 'property_type', 'district_id'],
            'properties'      => ['price', 'area_sqft', 'property_type', 'district_id'],
            'plots'           => ['total_price', 'area_sqft', 'district_id']
        ];

        foreach ($sources as $table => $cols) {
            try {
                $colList = implode(',', array_filter($cols, fn($c) => $this->columnExists($table, $c)));
                if (empty($colList)) continue;

                $sql = "SELECT $colList FROM $table WHERE " . ($cols[0] . ' IS NOT NULL AND ' . $cols[0] . ' > 0');
                if ($districtId && $this->columnExists($table, 'district_id')) {
                    $sql .= " AND district_id = " . (int)$districtId;
                }
                $sql .= " LIMIT 500";

                $stmt = $this->db->query($sql);
                while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    if (!empty($r[$cols[0]]) && !empty($r[$cols[1]])) {
                        $rows[] = [
                            'price' => (float)$r[$cols[0]],
                            'area'  => (float)$r[$cols[1]],
                            'type'  => $r[$cols[2]] ?? 'unknown',
                            'bedrooms'  => 0,
                            'bathrooms' => 0,
                        ];
                    }
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        return $rows;
    }

    private function columnExists(string $table, string $column): bool
    {
        try {
            $stmt = $this->db->query("SHOW COLUMNS FROM $table LIKE '$column'");
            return $stmt->rowCount() > 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * OLS regression: price = b0 + b1*area
     */
    private function train(array $data): array
    {
        $n = count($data);
        if ($n === 0) return ['intercept' => 0, 'area' => 0, 'bedrooms' => 0, 'bathrooms' => 0];

        $sumPrice = $sumArea = $sumAreaSq = $sumPriceArea = 0;

        foreach ($data as $d) {
            $sumPrice += $d['price'];
            $sumArea += $d['area'];
            $sumAreaSq += $d['area'] * $d['area'];
            $sumPriceArea += $d['price'] * $d['area'];
        }

        $denom = $n * $sumAreaSq - $sumArea * $sumArea;
        if ($denom == 0) {
            return ['intercept' => $sumPrice / $n, 'area' => 0, 'bedrooms' => 0, 'bathrooms' => 0];
        }

        $slope = ($n * $sumPriceArea - $sumPrice * $sumArea) / $denom;
        $intercept = ($sumPrice - $slope * $sumArea) / $n;

        return ['intercept' => $intercept, 'area' => $slope, 'bedrooms' => 0, 'bathrooms' => 0];
    }

    private function r_squared(array $data, array $coeff): float
    {
        $n = count($data);
        if ($n === 0) return 0;

        $sumY = 0;
        foreach ($data as $d) $sumY += $d['price'];
        $meanY = $sumY / $n;

        $ssTot = $ssRes = 0;
        foreach ($data as $d) {
            $predicted = $coeff['intercept'] + $coeff['area'] * $d['area'];
            $ssTot += ($d['price'] - $meanY) ** 2;
            $ssRes += ($d['price'] - $predicted) ** 2;
        }

        return $ssTot > 0 ? 1 - ($ssRes / $ssTot) : 0;
    }

    private function saveModel(string $type, ?int $districtId, array $coeff, float $r2, int $size): void
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO ai_price_models (property_type, location_id, model_data, coefficients, r_squared, sample_size, is_active)
                VALUES (?, ?, ?, ?, ?, ?, 1)
            ");
            $stmt->execute([
                $type,
                $districtId,
                json_encode(['algorithm' => 'linear_regression', 'features' => ['area', 'bedrooms', 'bathrooms', 'seasonal', 'amenities', 'facing']]),
                json_encode($coeff),
                $r2,
                $size
            ]);
        } catch (\Exception $e) {
            // Model save is best-effort
        }
    }
}
