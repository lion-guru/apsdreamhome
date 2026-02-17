<?php

namespace App\Models;

/**
 * MLM Plan Comparison Tool
 */
class MLMPlanComparison
{

    public static function comparePlans($planIds)
    {
        $plans = [];

        foreach ($planIds as $planId) {
            $plans[] = self::getPlanDetails($planId);
        }

        return [
            'plans' => $plans,
            'comparison' => self::generateComparison($plans),
            'recommendation' => self::generateRecommendation($plans)
        ];
    }

    private static function getPlanDetails($planId)
    {
        // Get plan details from database
        return [
            'id' => $planId,
            'name' => 'Sample Plan',
            'type' => 'hybrid',
            'joining_fee' => 5000,
            'potential_earnings' => 50000,
            'risk_level' => 'medium',
            'time_to_profit' => '3-6 months'
        ];
    }

    private static function generateComparison($plans)
    {
        return [
            'best_earnings' => $plans[0]['id'],
            'lowest_risk' => $plans[0]['id'],
            'fastest_roi' => $plans[0]['id'],
            'most_suitable' => $plans[0]['id']
        ];
    }

    private static function generateRecommendation($plans)
    {
        return [
            'recommended_plan' => $plans[0]['id'],
            'reason' => 'Best balance of risk and reward',
            'expected_earnings' => '₹50,000/month',
            'timeframe' => '6-12 months'
        ];
    }
}
