<?php

namespace App\Services\Commission\Strategies;

use App\Models\Booking;
use App\Models\User;
use App\Services\Commission\CommissionStrategy;
use App\Services\Commission\CommissionResult;

/**
 * DirectSaleStrategy
 * Handles Track A (Slab Differential 15%), Track B (Performance Rollup 3%), Track C (Milestone Escrow 2%)
 * Replaces: HybridCommissionEngine + TrackACommissionService + TrackBCommissionService + TrackCCommissionService
 */
class DirectSaleStrategy implements CommissionStrategy
{
    // Track A: Slab Differential 15%
    // Track B: Performance Rollup 3%
    // Track C: Milestone Escrow 2%
    public const SUPPORTS_TRACKS = ['direct_sale', 'track_a', 'track_b', 'track_c', 'direct_sale'];

    /**
     * @var array<string, float> Slab rates for Track A
     */
    protected array $slabRates = [
        '0-1000000' => 0.05,      // 5%
        '1000000-3500000' => 0.07, // 7%
        '3500000-7000000' => 0.10, // 10%
        '7000000-15000000' => 0.12, // 12%
        '15000000-30000000' => 0.15, // 15%
        '30000000-50000000' => 0.18, // 18%
        '50000000+' => 0.20, // 20% (cap)
    ];

    protected float $trackBPct = 0.03; // 3% Performance Rollup
    protected float $trackCPct = 0.02; // 2% Milestone Escrow

    /**
     * @inheritDoc
     */
    public function calculate(object $booking, object $user): \App\Services\Commission\CommissionResult
    {
        $track = $booking->track ?? 'direct_sale';
        $saleValue = (float)($booking->sale_value ?? $booking->price ?? 0);

        if ($saleValue <= 0) {
            return new \App\Services\Commission\CommissionResult(
                track: $booking->track ?? 'direct_sale',
                strategyClass: static::class,
                amount: 0,
                breakdown: ['error' => 'Invalid sale value'],
                metadata: ['booking_id' => $booking->id]
            );
        }

        $breakdown = [];
        $commission = 0;

        switch ($booking->track) {
            case 'track_a':
            case 'direct_sale':
                // Track A: Slab Differential 15% cap
                $commission = $this->calculateSlabCommission($saleValue);
                $breakdown['method'] = 'slab_differential';
                $breakdown['slabs'] = $this->getSlabBreakdown($saleValue);
                $breakdown['cap'] = '15%';
                break;

            case 'track_b':
                // Track B: Performance Rollup 3%
                $commission = $saleValue * $this->trackBPct;
                $breakdown['method'] = 'performance_rollup';
                $breakdown['rate'] = $this->trackBPct * 100 . '%';
                break;

            case 'track_c':
                // Track C: Milestone Escrow 2%
                $commission = $saleValue * $this->trackCPct;
                $breakdown['method'] = 'milestone_escrow';
                $breakdown['rate'] = $this->trackCPct * 100 . '%';
                break;

            default:
                // Default to Track A
                $commission = $this->calculateSlabCommission($saleValue);
                $breakdown['method'] = 'slab_differential (default)';
                $breakdown['slabs'] = $this->getSlabBreakdown($saleValue);
        }

        // Apply 20% cap
        $maxCommission = $saleValue * 0.20;
        $capped = false;
        if ($commission > $maxCommission) {
            $commission = $maxCommission;
            $capped = true;
            $breakdown['capped'] = true;
            $breakdown['cap_amount'] = $maxCommission;
        }

        $breakdown['sale_value'] = $saleValue;
        $breakdown['gross_commission'] = $commission / (1 - ($capped ? 0 : 0)); // gross before cap
        $breakdown['capped'] = $capped;

        return new \App\Services\Commission\CommissionResult(
            track: $booking->track ?? 'direct_sale',
            strategyClass: static::class,
            amount: round($commission, 2),
            breakdown: $breakdown,
            metadata: [
                'booking_id' => $booking->id,
                'user_id' => $booking->user_id ?? 0,
                'capped' => $capped,
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

    /**
     * Calculate slab-based commission.
     */
    protected function calculateSlabCommission(float $saleValue): float
    {
        $commission = 0;
        $remaining = $saleValue;

        foreach ($this->slabRates as $range => $rate) {
            if ($remaining <= 0) break;

            [$min, $max] = explode('-', $range);
            $min = (float)$min;
            $max = $max === '+' ? INF : (float)$max;
            $slabSize = max(0, min($remaining, $max - $min));
            
            if ($slabSize > 0) {
                $commission += $slabSize * $rate;
                $remaining -= $slabSize;
            }
        }

        return $commission;
    }

    /**
     * Get slab breakdown for transparency.
     */
    protected function getSlabBreakdown(float $saleValue): array
    {
        $breakdown = [];
        $remaining = $saleValue;

        foreach ($this->slabRates as $range => $rate) {
            if ($remaining <= 0) break;

            [$min, $max] = explode('-', $range);
            $min = (float)$min;
            $max = $max === '+' ? INF : (float)$max;
            $slabSize = max(0, min($remaining, $max - $min));
            
            if ($slabSize > 0) {
                $breakdown[] = [
                    'range' => $range,
                    'rate' => $rate * 100 . '%',
                    'amount' => round($slabSize, 2),
                    'commission' => round($slabSize * $rate, 2),
                ];
                $remaining -= $slabSize;
            }
        }

        return $breakdown;
    }

    }