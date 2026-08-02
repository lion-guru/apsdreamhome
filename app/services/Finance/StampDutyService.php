<?php

namespace App\Services\Finance;

use PDO;
use Exception;
use App\Traits\ServiceTenantTrait;

class StampDutyService
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

    public function getDb(): ?PDO
    {
        return $this->db;
    }

    /**
     * Calculate stamp duty for a property
     * 
     * @param array $data {
     *     property_value: float - Agreement value
     *     circle_rate_value: float - Value based on circle rate
     *     state_code: string - State code (UP, DL, etc.)
     *     buyer_gender: string - male, female, joint
     *     property_type: string - residential, commercial
     * }
     * @return array
     */
    public function calculateStampDuty(array $data): array
    {
        $propertyValue = (float)($data['property_value'] ?? 0);
        $circleRateValue = (float)($data['circle_rate_value'] ?? 0);
        $stateCode = strtoupper($data['state_code'] ?? 'UP');
        $buyerGender = strtolower($data['buyer_gender'] ?? 'male');
        $propertyType = strtolower($data['property_type'] ?? 'residential');

        if ($propertyValue <= 0) {
            return ['success' => false, 'error' => 'Property value must be greater than 0'];
        }

        // Use higher of property value and circle rate value
        $higherValue = max($propertyValue, $circleRateValue);

        // Get state rates
        $rates = $this->getStateRates($stateCode);
        if (!$rates) {
            // Default to UP rates if state not found
            $stateCode = 'UP';
            $rates = $this->getStateRates('UP');
        }

        if (!$rates) {
            return ['success' => false, 'error' => 'Stamp duty rates not found for state'];
        }

        // Determine applicable rate based on gender
        $stampDutyRate = match ($buyerGender) {
            'female' => (float)$rates['female_rate'],
            'joint' => (float)$rates['joint_rate'],
            default => (float)$rates['male_rate'],
        };

        $registrationRate = (float)($rates['registration_rate'] ?? 1.0);
        $surcharge = (float)($rates['surcharge'] ?? 0);
        $cess = (float)($rates['cess'] ?? 0);

        // Calculate amounts
        $stampDutyAmount = round($higherValue * $stampDutyRate / 100, 2);
        $registrationFeeAmount = round($higherValue * $registrationRate / 100, 2);
        $surchargeAmount = round($stampDutyAmount * $surcharge / 100, 2);
        $cessAmount = round($stampDutyAmount * $cess / 100, 2);

        $totalAmount = $stampDutyAmount + $registrationFeeAmount + $surchargeAmount + $cessAmount;

        // Save calculation history
        $this->saveCalculation([
            'property_value' => $propertyValue,
            'circle_rate_value' => $circleRateValue,
            'higher_value' => $higherValue,
            'state_code' => $stateCode,
            'buyer_gender' => $buyerGender,
            'property_type' => $propertyType,
            'stamp_duty_rate' => $stampDutyRate,
            'stamp_duty_amount' => $stampDutyAmount,
            'registration_fee_rate' => $registrationRate,
            'registration_fee_amount' => $registrationFeeAmount,
            'surcharge_amount' => $surchargeAmount,
            'cess_amount' => $cessAmount,
            'total_amount' => $totalAmount,
        ]);

        return [
            'success' => true,
            'state_code' => $stateCode,
            'buyer_gender' => $buyerGender,
            'property_value' => $propertyValue,
            'circle_rate_value' => $circleRateValue,
            'higher_value' => $higherValue,
            'stamp_duty_rate' => $stampDutyRate,
            'stamp_duty_amount' => $stampDutyAmount,
            'registration_fee_rate' => $registrationRate,
            'registration_fee_amount' => $registrationFeeAmount,
            'surcharge_rate' => $surcharge,
            'surcharge_amount' => $surchargeAmount,
            'cess_rate' => $cess,
            'cess_amount' => $cessAmount,
            'total_amount' => $totalAmount,
        ];
    }

    /**
     * Get stamp duty rates for a state
     */
    public function getStateRates(string $stateCode): ?array
    {
        if (!$this->db) return null;

        try {
            $stateCode = strtoupper($stateCode);
            $stmt = $this->db->prepare("
                SELECT state_name, state_code, male_rate, female_rate, joint_rate, 
                       registration_rate, surcharge, cess
                FROM stamp_duty_config 
                WHERE state_code = ? AND is_active = 1
            ");
            $stmt->execute([$stateCode]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (Exception $e) {
            error_log('[StampDutyService::getStateRates] ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get all states with rates
     */
    public function getAllStates(): array
    {
        if (!$this->db) return [];

        try {
            $stmt = $this->db->query("
                SELECT state_name, state_code, male_rate, female_rate, joint_rate, 
                       registration_rate, surcharge, cess
                FROM stamp_duty_config 
                WHERE is_active = 1
                ORDER BY state_name
            ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log('[StampDutyService::getAllStates] ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get circle rate for an area
     */
    public function getCircleRate(string $stateCode, string $district, string $areaName, string $areaType = 'residential'): ?float
    {
        if (!$this->db) return null;

        try {
            $stateCode = strtoupper($stateCode);
            $areaType = strtolower($areaType);

            $stmt = $this->db->prepare("
                SELECT rate_per_sqft 
                FROM circle_rates 
                WHERE state_code = ? 
                  AND district = ? 
                  AND area_name = ? 
                  AND area_type = ?
                  AND is_active = 1
                  AND (effective_to IS NULL OR effective_to >= CURDATE())
                ORDER BY effective_from DESC
                LIMIT 1
            ");
            $stmt->execute([$stateCode, $district, $areaName, $areaType]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            return $row ? (float)$row['rate_per_sqft'] : null;
        } catch (Exception $e) {
            error_log('[StampDutyService::getCircleRate] ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Search circle rates
     */
    public function searchCircleRates(string $stateCode, string $search): array
    {
        if (!$this->db) return [];

        try {
            $stateCode = strtoupper($stateCode);
            $search = "%{$search}%";

            $stmt = $this->db->prepare("
                SELECT district, tehsil, area_name, area_type, rate_per_sqft
                FROM circle_rates 
                WHERE state_code = ? 
                  AND is_active = 1
                  AND (effective_to IS NULL OR effective_to >= CURDATE())
                  AND (area_name LIKE ? OR district LIKE ? OR tehsil LIKE ?)
                ORDER BY district, area_name
                LIMIT 50
            ");
            $stmt->execute([$stateCode, $search, $search, $search]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log('[StampDutyService::searchCircleRates] ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Save calculation to history
     */
    protected function saveCalculation(array $data): void
    {
        if (!$this->db) return;

        try {
            $userId = $_SESSION['user_id'] ?? null;
            $bookingId = $data['booking_id'] ?? null;
            $tid = $this->tenantId();

            $cols = "user_id, booking_id, state_code, district, area_name, area_type, 
                     property_value, circle_rate_value, higher_value, buyer_gender, 
                     stamp_duty_rate, stamp_duty_amount, registration_fee_rate, 
                     registration_fee_amount, surcharge_amount, cess_amount, total_amount, calculation_data";
            $vals = "?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?";
            $params = [
                $userId,
                $bookingId,
                $data['state_code'] ?? 'UP',
                $data['district'] ?? null,
                $data['area_name'] ?? null,
                $data['property_type'] ?? 'residential',
                $data['property_value'] ?? 0,
                $data['circle_rate_value'] ?? 0,
                $data['higher_value'] ?? 0,
                $data['buyer_gender'] ?? 'male',
                $data['stamp_duty_rate'] ?? 0,
                $data['stamp_duty_amount'] ?? 0,
                $data['registration_fee_rate'] ?? 0,
                $data['registration_fee_amount'] ?? 0,
                $data['surcharge_amount'] ?? 0,
                $data['cess_amount'] ?? 0,
                $data['total_amount'] ?? 0,
                json_encode($data),
            ];
            if ($tid > 1) { $cols .= ", tenant_id"; $vals .= ", ?"; $params[] = $tid; }

            $stmt = $this->db->prepare("
                INSERT INTO stamp_duty_calculations 
                ($cols)
                VALUES ($vals)
            ");
            $stmt->execute($params);
        } catch (Exception $e) {
            error_log('[StampDutyService::saveCalculation] ' . $e->getMessage());
        }
    }

    /**
     * Get calculation history
     */
    public function getHistory(int $userId, int $limit = 10): array
    {
        if (!$this->db) return [];

        try {
            $stmt = $this->db->prepare("
                SELECT * FROM stamp_duty_calculations 
                WHERE user_id = ? {$this->tenantSql()}
                ORDER BY created_at DESC 
                LIMIT ?
            ");
            $stmt->execute([$userId, $limit]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log('[StampDutyService::getHistory] ' . $e->getMessage());
            return [];
        }
    }
}