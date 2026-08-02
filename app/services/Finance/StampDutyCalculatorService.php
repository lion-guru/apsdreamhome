<?php
/**
 * Stamp Duty Calculator Service
 * Calculates stamp duty and registration charges for all Indian states
 */

namespace App\Services\Finance;

use App\Core\Database\Database;
use Exception;
use \App\Traits\ServiceTenantTrait;

class StampDutyCalculatorService
{
    use \App\Traits\ServiceTenantTrait;

    /** @var \PDO */
    protected $db;

    protected $stateConfig = [];

    public function __construct(?\PDO $pdo = null)
    {
        if ($pdo === null) {
            try {
                $pdo = Database::getInstance();
                if (method_exists($pdo, 'getPdo')) {
                    $pdo = $pdo->getPdo();
                }
            } catch (Exception $e) {
                $pdo = null;
            }
        }
        $this->db = $pdo;
        $this->loadStateConfig();
    }

    protected function loadStateConfig(): void
    {
        // Default state configurations (as of 2024)
        $this->stateConfig = [
            'UP' => [
                'name' => 'Uttar Pradesh',
                'male_rate' => 7.0,
                'female_rate' => 6.0,
                'joint_rate' => 6.5,
                'registration_rate' => 1.0,
                'surcharge' => 0,
                'cess' => 0,
                'min_value' => 100,
            ],
            'DL' => [
                'name' => 'Delhi',
                'male_rate' => 6.0,
                'female_rate' => 4.0,
                'joint_rate' => 5.0,
                'registration_rate' => 1.0,
                'surcharge' => 0,
                'cess' => 0,
                'min_value' => 100,
            ],
            'MH' => [
                'name' => 'Maharashtra',
                'male_rate' => 6.0,
                'female_rate' => 5.0,
                'joint_rate' => 5.5,
                'registration_rate' => 1.0,
                'surcharge' => 1.0, // Metro cess for Mumbai/Pune
                'cess' => 0,
                'min_value' => 100,
            ],
            'KA' => [
                'name' => 'Karnataka',
                'male_rate' => 5.6,
                'female_rate' => 5.6,
                'joint_rate' => 5.6,
                'registration_rate' => 1.0,
                'surcharge' => 0,
                'cess' => 0,
                'min_value' => 100,
            ],
            'HR' => [
                'name' => 'Haryana',
                'male_rate' => 7.0,
                'female_rate' => 5.0,
                'joint_rate' => 6.0,
                'registration_rate' => 1.0,
                'surcharge' => 0,
                'cess' => 0,
                'min_value' => 100,
            ],
            'RJ' => [
                'name' => 'Rajasthan',
                'male_rate' => 6.0,
                'female_rate' => 5.0,
                'joint_rate' => 5.5,
                'registration_rate' => 1.0,
                'surcharge' => 0,
                'cess' => 0,
                'min_value' => 100,
            ],
            'GJ' => [
                'name' => 'Gujarat',
                'male_rate' => 4.9,
                'female_rate' => 4.9,
                'joint_rate' => 4.9,
                'registration_rate' => 1.0,
                'surcharge' => 0,
                'cess' => 0,
                'min_value' => 100,
            ],
            'TN' => [
                'name' => 'Tamil Nadu',
                'male_rate' => 7.0,
                'female_rate' => 7.0,
                'joint_rate' => 7.0,
                'registration_rate' => 1.0,
                'surcharge' => 0,
                'cess' => 0,
                'min_value' => 100,
            ],
            'WB' => [
                'name' => 'West Bengal',
                'male_rate' => 6.0,
                'female_rate' => 6.0,
                'joint_rate' => 6.0,
                'registration_rate' => 1.0,
                'surcharge' => 1.0,
                'cess' => 0,
                'min_value' => 100,
            ],
            'PB' => [
                'name' => 'Punjab',
                'male_rate' => 6.0,
                'female_rate' => 5.0,
                'joint_rate' => 5.5,
                'registration_rate' => 1.0,
                'surcharge' => 0,
                'cess' => 0,
                'min_value' => 100,
            ],
        ];

        // Try to load from database if available
        if ($this->db) {
            try {
                $stmt = $this->db->query("SELECT * FROM stamp_duty_config WHERE is_active = 1");
                $dbConfigs = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                foreach ($dbConfigs as $config) {
                    $this->stateConfig[$config['state_code']] = array_merge(
                        $this->stateConfig[$config['state_code']] ?? [],
                        $config
                    );
                }
            } catch (Exception $e) {
            // Use defaults
            error_log($e->getMessage());
            }
        }
    }

    /**
     * Calculate stamp duty for a property transaction
     *
     * @param array $params
     *   - state_code (string): State code (e.g., 'UP', 'DL', 'MH')
     *   - property_value (float): Agreement value or circle rate value (whichever is higher)
     *   - buyer_gender (string): 'male', 'female', 'joint', 'other'
     *   - property_type (string): 'residential', 'commercial', 'agricultural', 'industrial'
     *   - area_sqft (float): Property area in sq ft
     *   - circle_rate_per_sqft (float): Circle rate per sq ft (optional)
     *   - is_first_buyer (bool): First-time buyer concession
     * @return array
     */
    public function calculate(array $params): array
    {
        $stateCode = strtoupper($params['state_code'] ?? 'UP');
        $propertyValue = (float)($params['property_value'] ?? 0);
        $buyerGender = strtolower($params['buyer_gender'] ?? 'male');
        $propertyType = strtolower($params['property_type'] ?? 'residential');
        $areaSqft = (float)($params['area_sqft'] ?? 0);
        $circleRatePerSqft = (float)($params['circle_rate_per_sqft'] ?? 0);
        $isFirstBuyer = (bool)($params['is_first_buyer'] ?? false);

        if (!isset($this->stateConfig[$stateCode])) {
            return ['success' => false, 'error' => "State '$stateCode' not configured"];
        }

        if ($propertyValue <= 0) {
            return ['success' => false, 'error' => 'Property value must be greater than 0'];
        }

        $config = $this->stateConfig[$stateCode];

        // Determine applicable rate based on gender
        $rate = match ($buyerGender) {
            'female' => $config['female_rate'],
            'joint' => $config['joint_rate'],
            default => $config['male_rate'],
        };

        // First-time buyer concession (typically 1% for women in some states)
        if ($isFirstBuyer && $buyerGender === 'female' && in_array($stateCode, ['UP', 'DL', 'HR'])) {
            $rate = max(0, $rate - 1.0);
        }

        // Commercial property surcharge (additional 1-2% in some states)
        if ($propertyType === 'commercial') {
            $rate += 1.0;
        }

        // Calculate stamp duty
        $stampDuty = ($propertyValue * $rate) / 100;

        // Minimum stamp duty
        $minDuty = $config['min_value'] ?? 100;
        $stampDuty = max($stampDuty, $minDuty);

        // Registration charges
        $registrationRate = $config['registration_rate'] ?? 1.0;
        $registrationCharges = ($propertyValue * $registrationRate) / 100;

        // Surcharge (metro cess, etc.)
        $surchargeRate = $config['surcharge'] ?? 0;
        $surcharge = ($stampDuty * $surchargeRate) / 100;

        // Cess
        $cessRate = $config['cess'] ?? 0;
        $cess = ($stampDuty * $cessRate) / 100;

        // Total
        $totalCharges = $stampDuty + $registrationCharges + $surcharge + $cess;

        // Circle rate valuation
        $circleRateValue = 0;
        if ($circleRatePerSqft > 0 && $areaSqft > 0) {
            $circleRateValue = $circleRatePerSqft * $areaSqft;
        }

        // Higher of agreement value or circle rate value
        $dutiableValue = max($propertyValue, $circleRateValue);
        if ($dutiableValue !== $propertyValue) {
            // Recalculate with higher value
            $stampDuty = ($dutiableValue * $rate) / 100;
            $stampDuty = max($stampDuty, $minDuty);
            $registrationCharges = ($dutiableValue * $registrationRate) / 100;
            $surcharge = ($stampDuty * $surchargeRate) / 100;
            $cess = ($stampDuty * $cessRate) / 100;
            $totalCharges = $stampDuty + $registrationCharges + $surcharge + $cess;
        }

        return [
            'success' => true,
            'state' => $config['name'],
            'state_code' => $stateCode,
            'property_value' => round($propertyValue, 2),
            'dutiable_value' => round($dutiableValue, 2),
            'circle_rate_value' => round($circleRateValue, 2),
            'buyer_gender' => $buyerGender,
            'property_type' => $propertyType,
            'applicable_rate' => round($rate, 2),
            'stamp_duty' => round($stampDuty, 2),
            'registration_charges' => round($registrationCharges, 2),
            'surcharge' => round($surcharge, 2),
            'cess' => round($cess, 2),
            'total_charges' => round($totalCharges, 2),
            'breakdown' => [
                'Stamp Duty' => round($stampDuty, 2),
                'Registration Charges' => round($registrationCharges, 2),
                'Surcharge/Metro Cess' => round($surcharge, 2),
                'Cess' => round($cess, 2),
            ],
        ];
    }

    /**
     * Get all supported states
     */
    public function getSupportedStates(): array
    {
        $states = [];
        foreach ($this->stateConfig as $code => $config) {
            $states[] = [
                'code' => $code,
                'name' => $config['name'],
                'male_rate' => $config['male_rate'],
                'female_rate' => $config['female_rate'],
                'joint_rate' => $config['joint_rate'],
                'registration_rate' => $config['registration_rate'] ?? 1.0,
            ];
        }
        return $states;
    }

    /**
     * Get circle rate for an area
     */
    public function getCircleRate(string $stateCode, string $district, string $areaName, string $areaType = 'residential'): ?float
    {
        if (!$this->db) return null;

        try {
            $stmt = $this->db->prepare("
                SELECT rate_per_sqft FROM circle_rates 
                WHERE state_code = ? AND district = ? AND area_name = ? AND area_type = ? AND is_active = 1
                ORDER BY effective_from DESC LIMIT 1
            ");
            $stmt->execute([strtoupper($stateCode), $district, $areaName, $areaType]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $result ? (float)$result['rate_per_sqft'] : null;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Search circle rates
     */
    public function searchCircleRates(string $stateCode, string $search = ''): array
    {
        if (!$this->db) return [];

        try {
            $sql = "SELECT * FROM circle_rates WHERE state_code = ? AND is_active = 1";
            $params = [strtoupper($stateCode)];
            
            if ($search) {
                $sql .= " AND (area_name LIKE ? OR district LIKE ?)";
                $params[] = "%$search%";
                $params[] = "%$search%";
            }
            
            $sql .= " ORDER BY district, area_name LIMIT 100";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            return [];
        }
    }
}