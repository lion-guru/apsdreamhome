<?php

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
        $agents = $input['available_agents'] ?? [];
        
        // Simple logic: find agent with matching capability
        foreach ($agents as $agent) {
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
        return [
            'risk_level' => 'low',
            'confidence' => 0.88,
            'factors' => ['stable_market', 'verified_builder']
        ];
    }
}
