<?php
/**
 * Hybrid Real Estate Commission System
 * Supports both company properties (colony plotting) and resell properties
 * Integrates development cost calculation with commission structures
 */

class HybridRealEstateCommission {

    private $conn;
    private $current_plan = null;

    public function __construct($conn) {
        if ($conn === null) {
            throw new Exception("Database connection is required for HybridRealEstateCommission");
        }
        $this->conn = $conn;
        $this->loadActivePlan();
    }

    /**
     * Load the active hybrid commission plan
     */
    private function loadActivePlan() {
        try {
            $query = "SELECT * FROM hybrid_commission_plans WHERE status = 'active' LIMIT 1";
            $result = $this->conn->query($query);

            if ($result === false) {
                // Table might not exist yet, set default plan
                $this->current_plan = $this->getDefaultPlan();
                return;
            }

            $this->current_plan = $result->fetch_assoc();
        } catch (Exception $e) {
            // If table doesn't exist, use default plan
            $this->current_plan = $this->getDefaultPlan();
        }
    }

    private function getDefaultPlan() {
        return [
            'id' => 1,
            'plan_name' => 'Default Hybrid Plan',
            'company_commission_percentage' => 15,
            'resell_commission_percentage' => 5,
            'total_commission_percentage' => 20,
            'status' => 'active'
        ];
    }

    /**
     * Calculate commission for a property sale
     */
    public function calculateCommission($associate_id, $property_id, $sale_amount, $customer_id = null) {
        if (!$this->current_plan) {
            return ['success' => false, 'message' => 'No active commission plan found'];
        }

        // Get property details
        $property = $this->getPropertyDetails($property_id);
        if (!$property) {
            return ['success' => false, 'message' => 'Property not found'];
        }

        $commission_data = [
            'associate_id' => $associate_id,
            'property_id' => $property_id,
            'customer_id' => $customer_id,
            'sale_amount' => $sale_amount,
            'property_type' => $property['property_type'],
            'total_commission' => 0,
            'breakdown' => []
        ];

        if ($property['property_type'] === 'company') {
            // MLM structure for company properties
            $commission_data = $this->calculateCompanyPropertyCommission($commission_data, $property);
        } else {
            // Fixed structure for resell properties
            $commission_data = $this->calculateResellPropertyCommission($commission_data, $property);
        }

        // Save commission record
        $this->saveCommissionRecord($commission_data);

        return [
            'success' => true,
            'commission_data' => $commission_data,
            'message' => 'Commission calculated successfully'
        ];
    }

    /**
     * Calculate commission for company properties using MLM structure
     */
    private function calculateCompanyPropertyCommission($commission_data, $property) {
        $associate_level = $this->getAssociateLevel($commission_data['associate_id']);
        $level_details = $this->getLevelDetails($associate_level);

        if (!$level_details) {
            return $commission_data;
        }

        // Direct Commission
        $direct_commission = ($commission_data['sale_amount'] * $level_details['direct_commission_percentage']) / 100;
        $commission_data['breakdown']['direct'] = $direct_commission;

        // Team Commission (simplified for this example)
        $team_commission = ($commission_data['sale_amount'] * $level_details['team_commission_percentage']) / 100;
        $commission_data['breakdown']['team'] = $team_commission;

        // Level Bonus
        $level_bonus = ($commission_data['sale_amount'] * $level_details['level_bonus_percentage']) / 100;
        $commission_data['breakdown']['level_bonus'] = $level_bonus;

        // Matching Bonus
        $matching_bonus = ($commission_data['sale_amount'] * $level_details['matching_bonus_percentage']) / 100;
        $commission_data['breakdown']['matching'] = $matching_bonus;

        // Leadership Bonus
        $leadership_bonus = ($commission_data['sale_amount'] * $level_details['leadership_bonus_percentage']) / 100;
        $commission_data['breakdown']['leadership'] = $leadership_bonus;

        $commission_data['total_commission'] = array_sum($commission_data['breakdown']);
        $commission_data['commission_type'] = 'company_mlm';
        $commission_data['level_achieved'] = $associate_level;

        return $commission_data;
    }

    /**
     * Calculate commission for resell properties
     */
    private function calculateResellPropertyCommission($commission_data, $property) {
        $commission_structure = $this->getResellCommissionStructure($property['property_category'], $commission_data['sale_amount']);

        if ($commission_structure) {
            if ($commission_structure['commission_type'] === 'percentage') {
                $commission_amount = ($commission_data['sale_amount'] * $commission_structure['commission_percentage']) / 100;
            } elseif ($commission_structure['commission_type'] === 'fixed') {
                $commission_amount = $commission_structure['fixed_commission'];
            } else {
                // Both - take the higher one
                $percentage_commission = ($commission_data['sale_amount'] * $commission_structure['commission_percentage']) / 100;
                $commission_amount = max($percentage_commission, $commission_structure['fixed_commission']);
            }

            $commission_data['total_commission'] = $commission_amount;
            $commission_data['breakdown']['resell_commission'] = $commission_amount;
            $commission_data['commission_type'] = 'resell_fixed';
        }

        return $commission_data;
    }

    /**
     * Calculate plot rate including development cost and commission
     */
    public function calculatePlotRate($land_cost, $development_cost, $area_sqft, $profit_margin_percentage = 25) {
        // Calculate total cost
        $total_cost = $land_cost + $development_cost;

        // Calculate commission cost (based on current plan)
        $commission_percentage = $this->current_plan ? $this->current_plan['company_commission_percentage'] : 15;
        $commission_cost = ($total_cost * $commission_percentage) / 100;

        // Add commission to total cost
        $total_cost_with_commission = $total_cost + $commission_cost;

        // Calculate profit
        $profit_amount = ($total_cost_with_commission * $profit_margin_percentage) / 100;

        // Final rate per sqft
        $final_rate_per_sqft = ($total_cost_with_commission + $profit_amount) / $area_sqft;

        return [
            'land_cost' => $land_cost,
            'development_cost' => $development_cost,
            'commission_cost' => $commission_cost,
            'total_cost_with_commission' => $total_cost_with_commission,
            'profit_amount' => $profit_amount,
            'profit_margin_percentage' => $profit_margin_percentage,
            'final_rate_per_sqft' => $final_rate_per_sqft,
            'total_value' => $final_rate_per_sqft * $area_sqft
        ];
    }

    /**
     * Save development cost breakdown
     */
    public function saveDevelopmentCosts($property_id, $cost_breakdown) {
        try {
            // First, delete existing costs for this property
            $delete_query = "DELETE FROM property_development_costs WHERE property_id = ?";
            $delete_stmt = $this->conn->prepare($delete_query);
            $delete_stmt->bind_param("i", $property_id);
            $delete_stmt->execute();

            $total_cost = 0;
            foreach ($cost_breakdown as $cost) {
                $total_cost += $cost['amount'];
            }

            foreach ($cost_breakdown as $cost) {
                $percentage = ($cost['amount'] / $total_cost) * 100;

                $query = "INSERT INTO property_development_costs
                         (property_id, cost_type, description, amount, percentage_of_total)
                         VALUES (?, ?, ?, ?, ?)";

                $stmt = $this->conn->prepare($query);
                if (!$stmt) {
                    throw new Exception("Failed to prepare statement: " . $this->conn->error);
                }

                $stmt->bind_param("issdd",
                    $property_id,
                    $cost['type'],
                    $cost['description'],
                    $cost['amount'],
                    $percentage
                );

                if (!$stmt->execute()) {
                    throw new Exception("Failed to execute statement: " . $stmt->error);
                }
            }

            return ['success' => true, 'message' => 'Development costs saved successfully'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error saving development costs: ' . $e->getMessage()];
        }
    }

    /**
     * Get property details
     */
    private function getPropertyDetails($property_id) {
        try {
            $query = "SELECT * FROM real_estate_properties WHERE id = ?";
            $stmt = $this->conn->prepare($query);

            if (!$stmt) {
                return null;
            }

            $stmt->bind_param("i", $property_id);
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->fetch_assoc();
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Get associate level
     */
    private function getAssociateLevel($associate_id) {
        // This would typically check the associate's performance and assign level
        // For now, return a default level
        return 'Associate';
    }

    /**
     * Get level details
     */
    private function getLevelDetails($level_name) {
        try {
            if (!$this->current_plan) return null;

            $query = "SELECT * FROM company_property_levels
                     WHERE plan_id = ? AND level_name = ?
                     ORDER BY level_order LIMIT 1";

            $stmt = $this->conn->prepare($query);

            if (!$stmt) {
                return $this->getDefaultLevelDetails($level_name);
            }

            $stmt->bind_param("is", $this->current_plan['id'], $level_name);
            $stmt->execute();
            $result = $stmt->get_result();
            $level_details = $result->fetch_assoc();

            return $level_details ?: $this->getDefaultLevelDetails($level_name);
        } catch (Exception $e) {
            return $this->getDefaultLevelDetails($level_name);
        }
    }

    private function getDefaultLevelDetails($level_name) {
        $default_levels = [
            'Associate' => [
                'level_name' => 'Associate',
                'direct_commission_percentage' => 6,
                'team_commission_percentage' => 2,
                'level_bonus_percentage' => 0,
                'matching_bonus_percentage' => 0,
                'leadership_bonus_percentage' => 0,
                'level_order' => 1
            ],
            'Sr. Associate' => [
                'level_name' => 'Sr. Associate',
                'direct_commission_percentage' => 8,
                'team_commission_percentage' => 3,
                'level_bonus_percentage' => 1,
                'matching_bonus_percentage' => 0,
                'leadership_bonus_percentage' => 0,
                'level_order' => 2
            ],
            'BDM' => [
                'level_name' => 'BDM',
                'direct_commission_percentage' => 10,
                'team_commission_percentage' => 4,
                'level_bonus_percentage' => 2,
                'matching_bonus_percentage' => 3,
                'leadership_bonus_percentage' => 1,
                'level_order' => 3
            ]
        ];

        return $default_levels[$level_name] ?? $default_levels['Associate'];
    }

    /**
     * Get resell commission structure
     */
    private function getResellCommissionStructure($property_category, $sale_amount) {
        try {
            if (!$this->current_plan) return null;

            $query = "SELECT * FROM resell_commission_structure
                     WHERE plan_id = ? AND property_category = ?
                     AND ? BETWEEN min_value AND max_value
                     ORDER BY commission_percentage DESC LIMIT 1";

            $stmt = $this->conn->prepare($query);

            if (!$stmt) {
                return $this->getDefaultResellStructure($property_category, $sale_amount);
            }

            $stmt->bind_param("isd", $this->current_plan['id'], $property_category, $sale_amount);
            $stmt->execute();
            $result = $stmt->get_result();
            $structure = $result->fetch_assoc();

            return $structure ?: $this->getDefaultResellStructure($property_category, $sale_amount);
        } catch (Exception $e) {
            return $this->getDefaultResellStructure($property_category, $sale_amount);
        }
    }

    private function getDefaultResellStructure($property_category, $sale_amount) {
        $default_structures = [
            'plot' => ['commission_percentage' => 5, 'commission_type' => 'percentage', 'fixed_commission' => 0],
            'flat' => ['commission_percentage' => 3, 'commission_type' => 'percentage', 'fixed_commission' => 0],
            'house' => ['commission_percentage' => 3, 'commission_type' => 'percentage', 'fixed_commission' => 0],
            'commercial' => ['commission_percentage' => 4, 'commission_type' => 'percentage', 'fixed_commission' => 0],
            'land' => ['commission_percentage' => 2, 'commission_type' => 'percentage', 'fixed_commission' => 0]
        ];

        return $default_structures[$property_category] ?? $default_structures['plot'];
    }

    /**
     * Save commission record
     */
    private function saveCommissionRecord($commission_data) {
        try {
            $query = "INSERT INTO hybrid_commission_records
                     (associate_id, property_id, customer_id, sale_amount, commission_amount,
                      commission_type, commission_breakdown, level_achieved, payout_status)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = $this->conn->prepare($query);

            if (!$stmt) {
                // If table doesn't exist, just skip saving for now
                return;
            }

            $breakdown_json = json_encode($commission_data['breakdown']);
            $payout_status = 'pending';

            $stmt->bind_param("iiidddsss",
                $commission_data['associate_id'],
                $commission_data['property_id'],
                $commission_data['customer_id'],
                $commission_data['sale_amount'],
                $commission_data['total_commission'],
                $commission_data['commission_type'],
                $breakdown_json,
                $commission_data['level_achieved'],
                $payout_status
            );

            $stmt->execute();
        } catch (Exception $e) {
            // If there's an error saving, just continue - the commission calculation is still valid
            return;
        }
    }

    /**
     * Get commission summary for an associate
     */
    public function getCommissionSummary($associate_id, $start_date = null, $end_date = null) {
        try {
            $where_clause = "";
            $params = [$associate_id];
            $types = "i";

            if ($start_date && $end_date) {
                $where_clause = " AND created_at BETWEEN ? AND ?";
                $params[] = $start_date;
                $params[] = $end_date;
                $types .= "ss";
            }

            $query = "SELECT
                        SUM(commission_amount) as total_commission,
                        SUM(CASE WHEN payout_status = 'paid' THEN commission_amount ELSE 0 END) as paid_commission,
                        SUM(CASE WHEN payout_status = 'pending' THEN commission_amount ELSE 0 END) as pending_commission,
                        COUNT(*) as total_sales,
                        AVG(commission_amount) as avg_commission,
                        commission_type,
                        COUNT(DISTINCT DATE(created_at)) as active_days
                     FROM hybrid_commission_records
                     WHERE associate_id = ? $where_clause
                     GROUP BY commission_type";

            $stmt = $this->conn->prepare($query);

            if (!$stmt) {
                return [];
            }

            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();

            $summary = [];
            while ($row = $result->fetch_assoc()) {
                $summary[] = $row;
            }

            return $summary;
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Get development cost analysis
     */
    public function getDevelopmentCostAnalysis($property_id) {
        try {
            $query = "SELECT
                        cost_type,
                        description,
                        amount,
                        percentage_of_total,
                        SUM(amount) OVER () as total_cost
                     FROM property_development_costs
                     WHERE property_id = ?
                     ORDER BY amount DESC";

            $stmt = $this->conn->prepare($query);

            if (!$stmt) {
                return [
                    'costs' => [],
                    'total_cost' => 0,
                    'analysis' => ['error' => 'Database table not available']
                ];
            }

            $stmt->bind_param("i", $property_id);
            $stmt->execute();
            $result = $stmt->get_result();

            $costs = [];
            $total_cost = 0;
            while ($row = $result->fetch_assoc()) {
                $costs[] = $row;
                $total_cost = $row['total_cost'];
            }

            return [
                'costs' => $costs,
                'total_cost' => $total_cost,
                'analysis' => $this->analyzeCosts($costs)
            ];
        } catch (Exception $e) {
            return [
                'costs' => [],
                'total_cost' => 0,
                'analysis' => ['error' => 'Error analyzing costs: ' . $e->getMessage()]
            ];
        }
    }

    /**
     * Analyze development costs
     */
    private function analyzeCosts($costs) {
        $analysis = [
            'highest_cost' => null,
            'lowest_cost' => null,
            'cost_distribution' => []
        ];

        if (empty($costs)) return $analysis;

        $analysis['highest_cost'] = $costs[0];
        $analysis['lowest_cost'] = end($costs);

        foreach ($costs as $cost) {
            $analysis['cost_distribution'][$cost['cost_type']] = $cost['percentage_of_total'];
        }

        return $analysis;
    }
}
?>
