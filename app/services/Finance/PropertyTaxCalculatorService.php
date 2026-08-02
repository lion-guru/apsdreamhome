<?php

namespace App\Services\Finance;

use PDO;
use Exception;
use App\Traits\ServiceTenantTrait;

class PropertyTaxCalculatorService
{
    use ServiceTenantTrait;

    /** @var PDO */
    protected $db;

    public function __construct(?PDO $pdo = null)
    {
        if ($pdo === null) {
            try {
                $pdo = \App\Core\Database\Database::getInstance();
                if (method_exists($pdo, 'getPdo')) {
                    $pdo = $pdo->getPdo();
                }
            } catch (Exception $e) {
                $pdo = null;
            }
        }
        if (!$pdo instanceof PDO) {
            $pdo = null;
        }
        $this->db = $pdo;
    }

    /**
     * Calculate property tax for a property
     * 
     * @param array $data {
     *     state_code: string,
     *     city: string,
     *     zone: string,
     *     property_type: string (residential/commercial/industrial/vacant_land/institutional),
     *     built_up_area_sqft: float,
     *     land_area_sqft: float,
     *     assessment_year: int,
     *     is_early_payment: bool,
     *     months_overdue: int
     * }
     * @return array
     */
    public function calculateTax(array $data): array
    {
        $stateCode = strtoupper($data['state_code'] ?? '');
        $city = $data['city'] ?? '';
        $zone = $data['zone'] ?? '';
        $propertyType = strtolower($data['property_type'] ?? 'residential');
        $builtUpArea = (float)($data['built_up_area_sqft'] ?? 0);
        $landArea = (float)($data['land_area_sqft'] ?? 0);
        $assessmentYear = (int)($data['assessment_year'] ?? date('Y'));
        $isEarlyPayment = (bool)($data['is_early_payment'] ?? false);
        $monthsOverdue = (int)($data['months_overdue'] ?? 0);

        if (!$stateCode || !$city || !$propertyType) {
            return ['success' => false, 'error' => 'Missing required fields: state_code, city, property_type'];
        }

        if (!$this->db) {
            return ['success' => false, 'error' => 'Database not available'];
        }

        try {
            // Find applicable tax rate
            $sql = "
                SELECT * FROM property_tax_rates 
                WHERE state_code = ? 
                  AND city = ? 
                  AND property_type = ? 
                  AND is_active = 1
                  AND effective_from <= ?
                  AND (effective_to IS NULL OR effective_to >= ?)
            ";
            $params = [$stateCode, $city, $propertyType, date('Y-m-d'), date('Y-m-d')];

            if ($zone) {
                $sql .= " AND (zone = ? OR zone = '')";
                $params[] = $zone;
            }

            $sql .= " ORDER BY 
                CASE WHEN zone = ? THEN 0 ELSE 1 END,
                effective_from DESC 
                LIMIT 1";

            $params[] = $zone;

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $rate = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$rate) {
                return ['success' => false, 'error' => "No tax rate found for $stateCode/$city/$zone/$propertyType"];
            }

            // Calculate tax
            $ratePerSqft = (float)$rate['tax_rate_per_sqft'];
            $minTax = (float)$rate['min_tax_amount'];
            $maxTax = $rate['max_tax_amount'] ? (float)$rate['max_tax_amount'] : null;
            $rebatePercent = (float)$rate['rebate_percent'];
            $penaltyPercent = (float)$rate['penalty_percent_per_month'];

            // Tax is calculated on built-up area for residential/commercial, land area for vacant land
            $taxableArea = ($propertyType === 'vacant_land') ? $landArea : $builtUpArea;
            
            if ($taxableArea <= 0) {
                return ['success' => false, 'error' => 'Built-up area or land area must be greater than 0'];
            }

            $baseTax = $ratePerSqft * $taxableArea;

            // Apply min/max caps
            if ($baseTax < $minTax) {
                $baseTax = $minTax;
            }
            if ($maxTax && $baseTax > $maxTax) {
                $baseTax = $maxTax;
            }

            // Calculate rebate for early payment
            $rebateAmount = 0;
            if ($isEarlyPayment && $rebatePercent > 0) {
                $rebateAmount = $baseTax * ($rebatePercent / 100);
            }

            // Calculate penalty for overdue
            $penaltyAmount = 0;
            if ($monthsOverdue > 0 && $penaltyPercent > 0) {
                $penaltyAmount = $baseTax * ($penaltyPercent / 100) * $monthsOverdue;
            }

            $annualTax = round($baseTax - $rebateAmount + $penaltyAmount, 2);

            // Save assessment if property_id provided
            $assessmentId = null;
            if (!empty($data['property_id'])) {
                $assessmentId = $this->saveAssessment([
                    'property_id' => (int)$data['property_id'],
                    'assessment_year' => $assessmentYear,
                    'property_type' => $propertyType,
                    'built_up_area_sqft' => $builtUpArea,
                    'land_area_sqft' => $landArea,
                    'tax_rate_applied' => $ratePerSqft,
                    'annual_tax_amount' => $baseTax,
                    'rebate_amount' => $rebateAmount,
                    'penalty_amount' => $penaltyAmount,
                    'total_due' => $annualTax,
                    'status' => $monthsOverdue > 0 ? 'overdue' : 'pending',
                    'due_date' => $data['due_date'] ?? date('Y-03-31'),
                ]);
            }

            return [
                'success' => true,
                'state_code' => $stateCode,
                'city' => $city,
                'zone' => $zone ?: $rate['zone'],
                'property_type' => $propertyType,
                'taxable_area_sqft' => $taxableArea,
                'rate_per_sqft' => $ratePerSqft,
                'base_tax' => round($baseTax, 2),
                'rebate_percent' => $rebatePercent,
                'rebate_amount' => round($rebateAmount, 2),
                'penalty_percent' => $penaltyPercent,
                'penalty_amount' => round($penaltyAmount, 2),
                'months_overdue' => $monthsOverdue,
                'annual_tax' => $annualTax,
                'assessment_id' => $assessmentId,
                'rate_details' => [
                    'min_tax' => $minTax,
                    'max_tax' => $maxTax,
                    'effective_from' => $rate['effective_from'],
                ],
            ];

        } catch (Exception $e) {
            error_log('[PropertyTaxCalculatorService::calculateTax] ' . $e->getMessage());
            return ['success' => false, 'error' => 'Calculation failed: ' . $e->getMessage()];
        }
    }

    /**
     * Get tax rates for a state/city
     */
    public function getRates(string $stateCode, string $city = '', string $propertyType = ''): array
    {
        if (!$this->db) return [];

        try {
            $sql = "SELECT * FROM property_tax_rates WHERE state_code = ? AND is_active = 1";
            $params = [strtoupper($stateCode)];

            if ($city) {
                $sql .= " AND city = ?";
                $params[] = $city;
            }

            if ($propertyType) {
                $sql .= " AND property_type = ?";
                $params[] = strtolower($propertyType);
            }

            $sql .= " ORDER BY city, zone, property_type";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Search tax rates
     */
    public function searchRates(string $stateCode, string $search = ''): array
    {
        if (!$this->db) return [];

        try {
            $sql = "SELECT * FROM property_tax_rates WHERE state_code = ? AND is_active = 1";
            $params = [strtoupper($stateCode)];

            if ($search) {
                $sql .= " AND (city LIKE ? OR zone LIKE ? OR property_type LIKE ?)";
                $params[] = "%$search%";
                $params[] = "%$search%";
                $params[] = "%$search%";
            }

            $sql .= " ORDER BY city, zone, property_type LIMIT 50";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Save tax assessment
     */
    protected function saveAssessment(array $data): ?int
    {
         if (!$this->db) return null;

        try {
            $tid = $this->tenantId();
            $cols = "property_id, assessment_year, property_type, built_up_area_sqft, land_area_sqft,
                     tax_rate_applied, annual_tax_amount, rebate_amount, penalty_amount, total_due, status, due_date";
            $vals = "?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?";
            $params = [
                $data['property_id'],
                $data['assessment_year'],
                $data['property_type'],
                $data['built_up_area_sqft'],
                $data['land_area_sqft'],
                $data['tax_rate_applied'],
                $data['annual_tax_amount'],
                $data['rebate_amount'],
                $data['penalty_amount'],
                $data['total_due'],
                $data['status'],
                $data['due_date'],
            ];
            if ($tid > 1) { $cols .= ", tenant_id"; $vals .= ", ?"; $params[] = $tid; }
            $stmt = $this->db->prepare("
                INSERT INTO property_tax_assessments 
                ($cols)
                VALUES ($vals)
                ON DUPLICATE KEY UPDATE
                    tax_rate_applied = VALUES(tax_rate_applied),
                    annual_tax_amount = VALUES(annual_tax_amount),
                    rebate_amount = VALUES(rebate_amount),
                    penalty_amount = VALUES(penalty_amount),
                    total_due = VALUES(total_due),
                    status = VALUES(status),
                    updated_at = CURRENT_TIMESTAMP
            ");
            $stmt->execute($params);
            return (int)$this->db->lastInsertId();
        } catch (Exception $e) {
            error_log('[PropertyTaxCalculatorService::saveAssessment] ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get assessment history for a property
     */
    public function getAssessmentHistory(int $propertyId): array
    {
        if (!$this->db) return [];

        try {
            $stmt = $this->db->prepare("
                SELECT * FROM property_tax_assessments 
                WHERE property_id = ? {$this->tenantSql()}
                ORDER BY assessment_year DESC
            ");
            $stmt->execute([$propertyId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Get all states with tax rates
     */
    public function getStates(): array
    {
        if (!$this->db) return [];

        try {
            $stmt = $this->db->query("
                SELECT DISTINCT state_code, city, COUNT(*) as rate_count
                FROM property_tax_rates 
                WHERE is_active = 1
                GROUP BY state_code, city
                ORDER BY state_code, city
            ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            return [];
        }
    }
}