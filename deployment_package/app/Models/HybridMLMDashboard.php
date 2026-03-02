<?php

namespace App\Models;

/**
 * Hybrid MLM Dashboard Calculator
 */
class HybridMLMDashboard
{

    public static function getDashboardData($associateId, $planId = 'starter')
    {
        // Get plan configuration
        $planConfig = \App\Models\MLMPlanTemplates::getPlan($planId);

        $calculator = new HybridMLMCalculator('hybrid', $planConfig ?: []);

        // Get current month business volume
        $businessVolume = self::getCurrentMonthVolume($associateId);

        // Calculate current commission
        $commissionData = $calculator->calculateCommission($associateId, $businessVolume);

        // Get rank information
        $rankInfo = $calculator->generateCommissionReport($associateId);

        // Get payout schedule
        $payoutSchedule = $calculator->calculatePayoutSchedule($associateId, $commissionData['total_commission']);

        return [
            'current_commission' => $commissionData,
            'rank_information' => $rankInfo,
            'payout_schedule' => $payoutSchedule,
            'business_volume' => $businessVolume,
            'next_rank_requirements' => $calculator->getNextRankRequirements($associateId)
        ];
    }

    private static function getCurrentMonthVolume($associateId)
    {
        // Get current month's business volume
        return 2500000; // Example: 25 lakhs
    }
}
