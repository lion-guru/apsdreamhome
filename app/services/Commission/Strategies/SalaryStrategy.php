<?php

namespace App\Services\Commission\Strategies;

use App\Models\Booking;
use App\Models\User;
use App\Services\Commission\CommissionStrategy;
use App\Services\Commission\CommissionResult;

/**
 * SalaryStrategy
 * Handles salary & incentive grants based on GBV tiers
 * Replaces: SalaryService + SalaryCalculationService + LeadershipSalaryService + MLMIncentiveService
 */
class SalaryStrategy implements CommissionStrategy
{
    public const SUPPORTS_TRACKS = ['salary', 'salary_grant', 'leadership_salary', 'incentive_grant', 'mlm_incentive'];

    /**
     * Salary grant tiers based on cumulative GBV
     */
    protected array $salaryTiers = [
        ['min_gbv' => 1500000, 'days' => 60, 'monthly' => 5000, 'duration' => 6],   // ₹15L in 60 days
        ['min_gbv' => 3000000, 'days' => 100, 'monthly' => 5000, 'duration' => 12],  // ₹30L in 100 days
        ['min_gbv' => 5000000, 'days' => 150, 'monthly' => 8000, 'duration' => 12],  // ₹50L in 150 days
        ['min_gbv' => 7500000, 'days' => 200, 'monthly' => 12000, 'duration' => 12], // ₹75L in 200 days
        ['min_gbv' => 10000000, 'days' => 300, 'monthly' => 20000, 'duration' => 12], // ₹1Cr in 300 days
    ];

    /**
     * Leadership salary targets
     */
    protected array $leadershipTargets = [
        ['gbv' => 1500000, 'days' => 60, 'monthly' => 5000, 'duration' => 6],   // Target 1: ₹15L in 60 days
        ['gbv' => 3000000, 'days' => 100, 'monthly' => 5000, 'duration' => 12],  // Target 2: ₹30L in 100 days
    ];

    /**
     * @inheritDoc
     */
    public function calculate(object $booking, object $user): \App\Services\Commission\CommissionResult
    {
        $track = $booking->track ?? 'salary';

        if (!in_array($track, self::SUPPORTS_TRACKS)) {
            return new \App\Services\Commission\CommissionResult(
                track: $track,
                strategyClass: static::class,
                amount: 0,
                breakdown: ['error' => "Unsupported track: {$track}"],
                metadata: ['booking_id' => $booking->id]
            );
        }

        // Salary grants are calculated monthly via cron, not per booking
        // This method returns eligibility info for the cron to process
        $gbv = (float)($booking->gbv ?? $user->gbv ?? 0);
        $tenureDays = $booking->tenure_days ?? ($user->tenure_days ?? 0);

        $eligibleTiers = [];
        $totalMonthly = 0;

        foreach ($this->salaryTiers as $tier) {
            if ($gbv >= $tier['min_gbv'] && $tenureDays >= $tier['days']) {
                $eligibleTiers[] = $tier;
                $totalMonthly += $tier['monthly'];
            }
        }

        // Leadership salary (overlap handling - cumulative)
        $leadershipMonthly = 0;
        foreach ($this->leadershipTargets as $target) {
            if ($gbv >= $target['gbv'] && $tenureDays >= $target['days']) {
                $leadershipMonthly += $target['monthly'];
            }
        }

        $totalMonthly = $totalMonthly + $leadershipMonthly;

        $breakdown = [
            'gbv' => $gbv,
            'tenure_days' => $tenureDays,
            'eligible_tiers' => $eligibleTiers,
            'salary_monthly' => $totalMonthly,
            'leadership_monthly' => $leadershipMonthly,
            'note' => 'Salary grants paid monthly via cron. Minimum ₹50K side volume required monthly.',
        ];

        // Return 0 for booking-level calculation (paid monthly via cron)
        return new \App\Services\Commission\CommissionResult(
            track: 'salary',
            strategyClass: static::class,
            amount: 0,
            breakdown: $breakdown,
            metadata: [
                'booking_id' => $booking->id,
                'user_id' => $booking->user_id ?? 0,
                'eligible_monthly' => $totalMonthly,
                'calculated_at' => date('Y-m-d H:i:s'),
            ]
        );
    }

    /**
     * @inheritDoc
     */
    public function supports(string $track): bool
    {
        return in_array($track, self::SUPPORTS_TRACKS);
    }
}