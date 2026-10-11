<?php

namespace App\Services\Pricing;

use App\Core\Database\Database;

/**
 * PlcRuleService — DB-driven PLC (Preferential Location Charges) rules.
 *
 * Complements (does not replace) PlcService, the static service_configs-driven
 * engine used by PlotManagementController batch pricing. This service manages
 * per-colony/global PLC rules stored in plot_plc_rules and evaluates them
 * against plot attributes (corner, park-facing, road width, ...).
 */
class PlcRuleService
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Get all PLC rules with colony names
     */
    public function getAllRules(): array
    {
        $sql = "
            SELECT pcr.*, c.name as colony_name
            FROM plot_plc_rules pcr
            LEFT JOIN colonies c ON pcr.colony_id = c.id
            ORDER BY pcr.priority ASC, pcr.id ASC
        ";
        return $this->db->fetchAll($sql);
    }

    /**
     * Get a single rule by ID
     */
    public function getRuleById(int $id): ?array
    {
        $sql = "SELECT * FROM plot_plc_rules WHERE id = ?";
        return $this->db->fetchOne($sql, [$id]) ?: null;
    }

    /**
     * Create a new PLC rule
     */
    public function createRule(array $data): array
    {
        try {
            $required = ['name', 'attribute_key', 'condition_type', 'condition_value', 'charge_type', 'charge_value'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    return ['success' => false, 'error' => "Field '$field' is required"];
                }
            }

            $stmt = $this->db->prepare("
                INSERT INTO plot_plc_rules
                (colony_id, name, attribute_key, condition_type, condition_value, charge_type, charge_value, priority, is_active)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)
            ");

            $stmt->execute([
                $data['colony_id'] ?? null,
                $data['name'],
                $data['attribute_key'],
                $data['condition_type'],
                $data['condition_value'],
                $data['charge_type'],
                $data['charge_value'],
                $data['priority'] ?? 0,
            ]);

            return ['success' => true, 'id' => $this->db->lastInsertId()];
        } catch (\Exception $e) {
            error_log('PlcRuleService::createRule error: ' . $e->getMessage());
            return ['success' => false, 'error' => 'Database error: ' . $e->getMessage()];
        }
    }

    /**
     * Update an existing PLC rule
     */
    public function updateRule(int $id, array $data): array
    {
        try {
            $required = ['name', 'attribute_key', 'condition_type', 'condition_value', 'charge_type', 'charge_value'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    return ['success' => false, 'error' => "Field '$field' is required"];
                }
            }

            $stmt = $this->db->prepare("
                UPDATE plot_plc_rules SET
                    colony_id = ?,
                    name = ?,
                    attribute_key = ?,
                    condition_type = ?,
                    condition_value = ?,
                    charge_type = ?,
                    charge_value = ?,
                    priority = ?,
                    is_active = ?
                WHERE id = ?
            ");

            $stmt->execute([
                $data['colony_id'] ?? null,
                $data['name'],
                $data['attribute_key'],
                $data['condition_type'],
                $data['condition_value'],
                $data['charge_type'],
                $data['charge_value'],
                $data['priority'] ?? 0,
                $data['is_active'] ?? 1,
                $id,
            ]);

            return ['success' => true];
        } catch (\Exception $e) {
            error_log('PlcRuleService::updateRule error: ' . $e->getMessage());
            return ['success' => false, 'error' => 'Database error: ' . $e->getMessage()];
        }
    }

    /**
     * Delete (deactivate) a PLC rule
     */
    public function deleteRule(int $id): array
    {
        try {
            $stmt = $this->db->prepare("UPDATE plot_plc_rules SET is_active = 0 WHERE id = ?");
            $stmt->execute([$id]);
            return ['success' => true];
        } catch (\Exception $e) {
            error_log('PlcRuleService::deleteRule error: ' . $e->getMessage());
            return ['success' => false, 'error' => 'Database error: ' . $e->getMessage()];
        }
    }

    /**
     * Calculate PLC for a specific plot
     * Returns detailed breakdown of applicable rules and total charges
     */
    public function calculatePlotPLC(array $plot): array
    {
        $colonyId = $plot['colony_id'] ?? 0;
        $baseRate = (float)($plot['base_price_per_sqft'] ?? $plot['price_per_sqft'] ?? 0);
        $plotArea = (float)($plot['area_sqft'] ?? 0);

        // Get applicable rules (colony-specific + global)
        $sql = "
            SELECT * FROM plot_plc_rules
            WHERE is_active = 1
            AND (colony_id IS NULL OR colony_id = ?)
            ORDER BY priority ASC
        ";
        $rules = $this->db->fetchAll($sql, [$colonyId]);

        $applicableRules = [];
        $totalPercentage = 0.0;
        $totalFixedPerSqft = 0.0;
        $totalFixedTotal = 0.0;

        foreach ($rules as $rule) {
            if ($this->evaluateCondition($plot, $rule)) {
                $chargeValue = (float)$rule['charge_value'];
                $ruleDetail = [
                    'id' => $rule['id'],
                    'name' => $rule['name'],
                    'charge_type' => $rule['charge_type'],
                    'charge_value' => $chargeValue,
                    'calculated_amount' => 0,
                ];

                switch ($rule['charge_type']) {
                    case 'percentage':
                        $totalPercentage += $chargeValue;
                        $ruleDetail['calculated_amount'] = ($baseRate * $plotArea * $chargeValue) / 100;
                        break;
                    case 'fixed_per_sqft':
                        $totalFixedPerSqft += $chargeValue;
                        $ruleDetail['calculated_amount'] = $chargeValue * $plotArea;
                        break;
                    case 'fixed_total':
                        $totalFixedTotal += $chargeValue;
                        $ruleDetail['calculated_amount'] = $chargeValue;
                        break;
                }

                $applicableRules[] = $ruleDetail;
            }
        }

        $baseAmount = $baseRate * $plotArea;
        $percentageAmount = ($baseAmount * $totalPercentage) / 100;
        $fixedPerSqftAmount = $totalFixedPerSqft * $plotArea;
        $totalPlcAmount = $percentageAmount + $fixedPerSqftAmount + $totalFixedTotal;
        $finalRate = $baseRate + (($totalPercentage / 100) * $baseRate) + $totalFixedPerSqft;
        $finalAmount = $baseAmount + $totalPlcAmount;

        return [
            'plot_id' => $plot['id'],
            'plot_number' => $plot['plot_number'] ?? '',
            'colony_name' => $plot['colony_name'] ?? '',
            'base_rate' => $baseRate,
            'plot_area' => $plotArea,
            'base_amount' => $baseAmount,
            'applicable_rules' => $applicableRules,
            'summary' => [
                'total_percentage' => $totalPercentage,
                'total_fixed_per_sqft' => $totalFixedPerSqft,
                'total_fixed_total' => $totalFixedTotal,
                'percentage_amount' => $percentageAmount,
                'fixed_per_sqft_amount' => $fixedPerSqftAmount,
                'fixed_total_amount' => $totalFixedTotal,
                'total_plc_amount' => $totalPlcAmount,
                'final_rate_per_sqft' => $finalRate,
                'final_amount' => $finalAmount,
            ],
        ];
    }

    /**
     * Evaluate a rule condition against plot attributes.
     *
     * The rules UI historically offers `is_corner` / `is_park_facing` keys
     * while the live `plots` table stores `corner_plot` / `park_facing`.
     * Both spellings resolve to the same value so seeded + hand-written
     * rules match real plot rows either way.
     */
    private function evaluateCondition(array $plot, array $rule): bool
    {
        $attributeKey = $rule['attribute_key'];
        $conditionType = $rule['condition_type'];
        $conditionValue = $rule['condition_value'];

        $aliases = [
            'is_corner' => ['corner_plot'],
            'corner_plot' => ['is_corner'],
            'is_park_facing' => ['park_facing'],
            'park_facing' => ['is_park_facing'],
        ];
        $plotValue = $plot[$attributeKey] ?? null;
        if ($plotValue === null && isset($aliases[$attributeKey])) {
            foreach ($aliases[$attributeKey] as $alt) {
                if (array_key_exists($alt, $plot) && $plot[$alt] !== null) {
                    $plotValue = $plot[$alt];
                    break;
                }
            }
        }

        // Handle boolean attributes (stored as 0/1 or true/false)
        if (in_array($attributeKey, ['is_corner', 'corner_plot', 'is_park_facing', 'park_facing', 'is_commercial_front'])) {
            $plotValue = (int)$plotValue === 1 || $plotValue === 'true' || $plotValue === true ? 1 : 0;
        }

        switch ($conditionType) {
            case 'equals_true':
                return $plotValue === 1 || $plotValue === '1' || $plotValue === true;

            case 'equals':
                return $plotValue == $conditionValue;

            case 'greater_than':
                return (float)$plotValue > (float)$conditionValue;

            case 'contains':
                return stripos((string)$plotValue, (string)$conditionValue) !== false;

            case 'in':
                $values = array_map('trim', explode(',', $conditionValue));
                return in_array((string)$plotValue, $values, true);

            default:
                return false;
        }
    }

    /**
     * Get rules applicable to a specific colony (for admin UI)
     */
    public function getRulesForColony(int $colonyId): array
    {
        $sql = "
            SELECT * FROM plot_plc_rules
            WHERE is_active = 1
            AND (colony_id IS NULL OR colony_id = ?)
            ORDER BY priority ASC
        ";
        return $this->db->fetchAll($sql, [$colonyId]);
    }
}
