<?php

namespace App\Services\Commission\Strategies;

use App\Models\Booking;
use App\Models\User;
use App\Services\Commission\CommissionStrategy;
use App\Services\Commission\CommissionResult;

/**
 * InvestmentStrategy
 * Handles investment plan commission (3% flat)
 * Replaces: InvestmentCommissionService
 */
class InvestmentStrategy implements CommissionStrategy
{
    public const SUPPORTS_TRACKS = ['investment', 'investment_sale', 'investment_plan'];

    protected float $rate = 0.03; // 3% flat

    /**
     * @inheritDoc
     */
    public function calculate(object $booking, object $user): \App\Services\Commission\CommissionResult
    {
        $saleValue = (float)($booking->sale_value ?? $booking->price ?? 0);

        if ($saleValue <= 0) {
            return new \App\Services\Commission\CommissionResult(
                track: $booking->track ?? 'investment',
                strategyClass: static::class,
                amount: 0,
                breakdown: ['error' => 'Invalid sale value'],
                metadata: ['booking_id' => $booking->id]
            );
        }

        $commission = $saleValue * $this->rate;

        $breakdown = [
            'method' => 'investment_flat_rate',
            'rate' => $this->rate * 100 . '%',
            'sale_value' => $saleValue,
            'commission' => round($commission, 2),
        ];

        return new \App\Services\Commission\CommissionResult(
            track: $booking->track ?? 'investment',
            strategyClass: static::class,
            amount: round($commission, 2),
            breakdown: $breakdown,
            metadata: [
                'booking_id' => $booking->id,
                'user_id' => $booking->user_id ?? 0,
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