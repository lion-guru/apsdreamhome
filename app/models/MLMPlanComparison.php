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
            $planDetails = self::getPlanDetails($planId);
            if ($planDetails) {
                $plans[] = $planDetails;
            }
        }

        return [
            'plans' => $plans,
            'comparison' => self::generateComparison($plans),
            'recommendation' => self::generateRecommendation($plans)
        ];
    }

    private static function getPlanDetails($planId)
    {
        $plan = MLMPlanTemplates::getPlan($planId);

        if (!$plan) {
            return null;
        }

        // Enrich with comparison metrics
        return array_merge($plan, [
            'potential_earnings' => self::calculatePotentialEarnings($plan),
            'risk_level' => self::calculateRiskLevel($plan),
            'time_to_profit' => self::estimateTimeToProfit($plan)
        ]);
    }

    private static function calculatePotentialEarnings($plan)
    {
        // Simple estimation based on commission rates and targets
        $baseRate = 0;
        foreach ($plan['commission_rates'] as $rate) {
            $baseRate += $rate['rate'];
        }

        // Hypothetical scenario: 100 associates with monthly target volume
        $networkVolume = 100 * ($plan['monthly_target'] ?? 10000);
        return $networkVolume * ($baseRate / 100);
    }

    private static function calculateRiskLevel($plan)
    {
        // Higher joining fee = higher risk
        $fee = $plan['joining_fee'] ?? 0;
        if ($fee > 10000) return 'high';
        if ($fee > 2000) return 'medium';
        return 'low';
    }

    private static function estimateTimeToProfit($plan)
    {
        // Lower target = faster profit
        $target = $plan['monthly_target'] ?? 0;
        if ($target > 100000) return '6-12 months';
        if ($target > 50000) return '3-6 months';
        return '1-3 months';
    }

    private static function generateComparison($plans)
    {
        if (empty($plans)) return [];

        // Find best in each category
        usort($plans, function ($a, $b) {
            return $b['potential_earnings'] <=> $a['potential_earnings'];
        });
        $bestEarnings = $plans[0]['id'];

        usort($plans, function ($a, $b) {
            return ($a['joining_fee'] ?? 0) <=> ($b['joining_fee'] ?? 0);
        });
        $lowestRisk = $plans[0]['id']; // Lowest fee

        return [
            'best_earnings' => $bestEarnings,
            'lowest_risk' => $lowestRisk,
            // Add more comparisons as needed
        ];
    }

    private static function generateRecommendation($plans)
    {
        if (empty($plans)) return [];

        $recommended = $plans[0];
        $reason = 'Best overall value based on potential earnings and risk';

        foreach ($plans as $plan) {
            // Logic: High earnings, low fee
            if (($plan['joining_fee'] ?? 0) < 2000 && ($plan['potential_earnings'] ?? 0) > 100000) {
                $recommended = $plan;
                $reason = 'High potential earnings with low entry barrier';
                break;
            }
        }

        return [
            'recommended_plan' => $recommended['id'],
            'reason' => $reason,
            'expected_earnings' => '₹' . number_format($recommended['potential_earnings'], 0),
            'timeframe' => $recommended['time_to_profit']
        ];
    }
}
