<?php

// TODO: Add proper error handling with try-catch blocks

namespace App\Services\AI\Modules;

/**
 * AI Module - DecisionEngine
 * Handles complex decision making, task assignment, and prioritization.
 */
class DecisionEngine {
    /**
     * Evaluate a situation and make a decision
     *
     * @param string $type
     * @param array $input
     * @return array
     */
    public function evaluate($type, $input) {
        switch ($type) {
            case 'smart_task_assignment':
                return $this->assignTask($input);
            case 'lead_prioritization':
                return $this->prioritizeLead($input);
            case 'investment_risk':
                return $this->assessRisk($input);
            default:
                return ['status' => 'success', 'decision' => 'no_action_needed'];
        }
    }

    private function assignTask($input) {
        $taskType = $input['task_type'] ?? '';
        $users = $input['available_agents'] ?? [];
        
        // Simple logic: find agent with matching capability
        foreach ($users as $agent) {
            $capabilities = \is_string($agent['capabilities']) ? \json_decode($agent['capabilities'], true) : $agent['capabilities'];
            if (\in_array($taskType, $capabilities)) {
                return ['agent_id' => $agent['id'], 'confidence' => 0.9];
            }
        }

        return ['agent_id' => null, 'confidence' => 0];
    }

    private function prioritizeLead($input) {
        $score = 0;
        if (($input['budget'] ?? 0) > 1000000) $score += 40;
        if (($input['timeline'] ?? '') === 'immediate') $score += 30;
        if (($input['verified'] ?? false)) $score += 30;

        return [
            'priority' => $score >= 70 ? 'high' : ($score >= 40 ? 'medium' : 'low'),
            'score' => $score / 100,
            'recommended_action' => $score >= 70 ? 'immediate_callback' : 'nurture'
        ];
    }

    private function assessRisk($input) {
        $riskScore = 0;
        $factors = [];
        $mitigations = [];

        $propertyValue = (float)($input['property_value'] ?? $input['price'] ?? 0);
        $loanAmount = (float)($input['loan_amount'] ?? 0);
        $buyerIncome = (float)($input['annual_income'] ?? $input['income'] ?? 0);
        $buyerAge = (int)($input['age'] ?? 30);
        $colonyStatus = $input['colony_status'] ?? $input['project_status'] ?? 'active';
        $reraApproved = (bool)($input['rera_approved'] ?? false);
        $builderReputation = (float)($input['builder_reputation'] ?? 0.7);
        $locationDemand = (float)($input['location_demand'] ?? 0.6);

        // Loan-to-Value ratio
        if ($propertyValue > 0) {
            $ltv = $loanAmount / $propertyValue;
            if ($ltv > 0.8) { $riskScore += 25; $factors[] = 'high_ltv_' . round($ltv * 100) . '%'; $mitigations[] = 'Increase down payment to reduce LTV below 80%'; }
            elseif ($ltv > 0.6) { $riskScore += 10; $factors[] = 'moderate_ltv_' . round($ltv * 100) . '%'; }
            else { $factors[] = 'safe_ltv_' . round($ltv * 100) . '%'; }
        }

        // FOIR (Fixed Obligation to Income Ratio) — max 50% in India
        if ($buyerIncome > 0 && $loanAmount > 0) {
            $monthlyEmi = $loanAmount * (0.08 / 12) / (1 - pow(1 + 0.08 / 12, -240)); // approx 20yr EMI
            $foir = $monthlyEmi / ($buyerIncome / 12);
            if ($foir > 0.5) { $riskScore += 20; $factors[] = 'high_foir_' . round($foir * 100) . '%'; $mitigations[] = 'Income documentation may be insufficient for loan approval'; }
            elseif ($foir > 0.4) { $riskScore += 8; $factors[] = 'moderate_foir_' . round($foir * 100) . '%'; }
        }

        // Colony/project status risk
        if ($colonyStatus === 'planning') { $riskScore += 15; $factors[] = 'pre_launch_risk'; $mitigations[] = 'Verify land acquisition and approvals before booking'; }
        elseif ($colonyStatus === 'under_construction') { $riskScore += 8; $factors[] = 'construction_risk'; }
        elseif ($colonyStatus === 'ready') { $factors[] = 'low_risk_ready_to_move'; }

        // RERA compliance
        if ($reraApproved) { $factors[] = 'rera_protected'; $riskScore -= 5; }
        else { $riskScore += 10; $factors[] = 'no_rera'; $mitigations[] = 'Request RERA registration details before investing'; }

        // Builder reputation
        if ($builderReputation < 0.4) { $riskScore += 15; $factors[] = 'low_builder_reputation'; $mitigations[] = 'Verify builder credentials and past project delivery'; }
        elseif ($builderReputation > 0.8) { $factors[] = 'reputable_builder'; $riskScore -= 5; }

        // Location demand
        if ($locationDemand < 0.3) { $riskScore += 10; $factors[] = 'low_location_demand'; $mitigations[] = 'Consider locations with higher appreciation potential'; }
        elseif ($locationDemand > 0.8) { $factors[] = 'high_demand_location'; $riskScore -= 5; }

        // Age factor (younger buyers = longer repayment = higher risk)
        if ($buyerAge > 55) { $riskScore += 5; $factors[] = 'near_retirement'; }

        // Clamp and classify
        $riskScore = max(0, min(100, $riskScore));
        if ($riskScore >= 60) $level = 'high';
        elseif ($riskScore >= 30) $level = 'medium';
        else $level = 'low';

        return [
            'risk_level'    => $level,
            'risk_score'    => $riskScore,
            'confidence'    => round(0.7 + ($riskScore > 0 ? 0.15 : 0), 2),
            'factors'       => $factors,
            'mitigations'   => $mitigations,
        ];
    }
}
