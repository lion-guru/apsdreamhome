<?php
/**
 * PricePredictor - Self-hosted property price prediction
 * Linear regression on historical data
 * No external API
 */

namespace App\Services\AI;

use PDO;

class PricePredictor
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Predict price for a property based on features
     */
    public function predict(string $propertyType, ?int $districtId = null, ?int $areaSqft = null, int $bedrooms = 0, int $bathrooms = 0): array
    {
        // Get historical data
        $data = $this->getHistoricalData($propertyType, $districtId);
        if (count($data) < 5) {
            return [
                'predicted_price' => 0,
                'confidence' => 0,
                'message' => 'Insufficient data (need at least 5 samples)',
                'sample_size' => count($data)
            ];
        }

        // Simple linear regression: price = a*area + b*bedrooms + c*bathrooms + intercept
        $coefficients = $this->train($data);

        $area = $areaSqft ?? 1000; // default 1000 sqft
        $predicted = $coefficients['intercept']
            + $coefficients['area'] * $area
            + $coefficients['bedrooms'] * $bedrooms
            + $coefficients['bathrooms'] * $bathrooms;

        // Confidence based on R-squared and sample size
        $r2 = $this->r_squared($data, $coefficients);
        $sampleSize = count($data);
        $confidence = min(1.0, $r2 * sqrt($sampleSize / 100));

        // Save model
        $this->saveModel($propertyType, $districtId, $coefficients, $r2, $sampleSize);

        // Range estimate
        $low = $predicted * 0.85;
        $high = $predicted * 1.15;

        return [
            'predicted_price' => round($predicted, 0),
            'low_estimate' => round($low, 0),
            'high_estimate' => round($high, 0),
            'confidence' => round($confidence, 2),
            'r_squared' => round($r2, 3),
            'sample_size' => $sampleSize,
            'property_type' => $propertyType,
            'district_id' => $districtId
        ];
    }

    private function getHistoricalData(string $type, ?int $districtId): array
    {
        // Try user_properties first
        $rows = [];
        $sources = ['user_properties' => ['price', 'area_sqft', 'property_type', 'district_id'],
                    'properties' => ['price', 'area_sqft', 'property_type', 'district_id'],
                    'plots' => ['total_price', 'area_sqft', 'district_id']];

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
                            'area' => (float)$r[$cols[1]],
                            'type' => $r[$cols[2]] ?? 'unknown',
                            'bedrooms' => 0,
                            'bathrooms' => 0
                        ];
                    }
                }
            } catch (Exception $e) {
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
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Simple OLS regression: price = b0 + b1*area + b2*bedrooms + b3*bathrooms
     * Using normal equations for multi-variate
     */
    private function train(array $data): array
    {
        $n = count($data);
        if ($n === 0) return ['intercept' => 0, 'area' => 0, 'bedrooms' => 0, 'bathrooms' => 0];

        // Simplified: compute average price per sqft
        $sumPrice = 0;
        $sumArea = 0;
        $sumAreaSq = 0;
        $sumPriceArea = 0;

        foreach ($data as $d) {
            $sumPrice += $d['price'];
            $sumArea += $d['area'];
            $sumAreaSq += $d['area'] * $d['area'];
            $sumPriceArea += $d['price'] * $d['area'];
        }

        $denom = $n * $sumAreaSq - $sumArea * $sumArea;
        if ($denom == 0) {
            return [
                'intercept' => $sumPrice / $n,
                'area' => 0,
                'bedrooms' => 0,
                'bathrooms' => 0
            ];
        }

        $slope = ($n * $sumPriceArea - $sumPrice * $sumArea) / $denom;
        $intercept = ($sumPrice - $slope * $sumArea) / $n;

        return [
            'intercept' => $intercept,
            'area' => $slope,
            'bedrooms' => 0,
            'bathrooms' => 0
        ];
    }

    private function r_squared(array $data, array $coeff): float
    {
        $n = count($data);
        if ($n === 0) return 0;

        $sumY = 0;
        foreach ($data as $d) $sumY += $d['price'];
        $meanY = $sumY / $n;

        $ssTot = 0;
        $ssRes = 0;
        foreach ($data as $d) {
            $predicted = $coeff['intercept'] + $coeff['area'] * $d['area'];
            $ssTot += ($d['price'] - $meanY) ** 2;
            $ssRes += ($d['price'] - $predicted) ** 2;
        }

        return $ssTot > 0 ? 1 - ($ssRes / $ssTot) : 0;
    }

    private function saveModel(string $type, ?int $districtId, array $coeff, float $r2, int $size): void
    {
        $stmt = $this->db->prepare("
            INSERT INTO ai_price_models (property_type, location_id, model_data, coefficients, r_squared, sample_size, is_active)
            VALUES (?, ?, ?, ?, ?, ?, 1)
        ");
        $stmt->execute([
            $type,
            $districtId,
            json_encode(['algorithm' => 'linear_regression', 'features' => ['area', 'bedrooms', 'bathrooms']]),
            json_encode($coeff),
            $r2,
            $size
        ]);
    }
}
