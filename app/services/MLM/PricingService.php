<?php

namespace App\Services\MLM;

use App\Traits\ServiceTenantTrait;

/**
 * Pricing Service for MLM Commission Engine
 * Handles pricing matrix, block pricing resolution, and plot value calculations
 */
class PricingService
{
    use ServiceTenantTrait;

    /**
     * Official brochure / marketing-flyer pricing matrix.
     * All values are base ₹/SqFt BEFORE PLC.
     * 'corner_1500' and 'corner_1000' carry +10% PLC.
     */
    private const PRICING_MATRIX = [
        'block_a'       => [
            'label'      => 'Block A',
            'area_sqft'  => 1000,
            'base_rate'  => 950,
            'final_rate' => 950,
            'emi_allowed'=> false,
            'payment_plan'=> 'no_emi',
            'premium_type'=> 'regular',
        ],
        'block_b'       => [
            'label'      => 'Block B',
            'area_sqft'  => 1000,
            'base_rate'  => 850,
            'final_rate' => 850,
            'emi_allowed'=> true,
            'payment_plan'=> 'emi_available',
            'premium_type'=> 'regular',
        ],
        'block_c'       => [
            'label'      => 'Block C',
            'area_sqft'  => 1000,
            'base_rate'  => 750,
            'final_rate' => 750,
            'emi_allowed'=> true,
            'payment_plan'=> 'emi_available',
            'premium_type'=> 'regular',
        ],
        'corner_1500'   => [
            'label'      => 'Corner 1500',
            'area_sqft'  => 1500,
            'base_rate'  => 1250,
            'final_rate' => 1375,   // 1250 × 1.10
            'emi_allowed'=> false,
            'payment_plan'=> 'no_emi',
            'premium_type'=> 'commercial_corner',
            'plc_pct'    => 10,
        ],
        'corner_1000'   => [
            'label'      => 'Corner 1000',
            'area_sqft'  => 1000,
            'base_rate'  => 1000,
            'final_rate' => 1100,   // 1000 × 1.10
            'emi_allowed'=> false,
            'payment_plan'=> 'no_emi',
            'premium_type'=> 'corner_c',
            'plc_pct'    => 10,
        ],
    ];

    /** Default booking / token amount per plot. */
    private const DEFAULT_BOOKING_AMOUNT = 51000;

    /**
     * Return the full pricing matrix.
     */
    public function getPricingMatrix(): array
    {
        return self::PRICING_MATRIX;
    }

    /**
     * Resolve a block key (case-insensitive) to its pricing entry.
     *
     * @param string $blockKey  e.g. 'block_a', 'Corner 1500', 'corner_1000'
     * @return array|null
     */
    public function getBlockPricing(string $blockKey): ?array
    {
        $normalised = $this->normaliseBlockKey($blockKey);
        return self::PRICING_MATRIX[$normalised] ?? null;
    }

    /**
     * Normalise a block key string into its canonical matrix key.
     * Accepts: 'block_a', 'Block A', 'A', 'BLOCK_A', 'Corner 1500',
     *          'corner_1500', 'COMMERCIAL_CORNER', 'corner_1000', 'Corner 1000', 'b', etc.
     */
    public function normaliseBlockKey(string $input): string
    {
        $s = strtolower(trim($input));

        // Direct match
        if (isset(self::PRICING_MATRIX[$s])) {
            return $s;
        }

        // 'block_a', 'block_b', 'block_c' — strip 'block_' prefix variant
        // single letter: 'a','b','c'
        if (preg_match('/^block[_ ]?([abc])$/', $s, $m)) {
            return 'block_' . $m[1];
        }
        if (preg_match('/^([abc])$/', $s, $m)) {
            return 'block_' . $m[1];
        }

        // corner_1500 aliases
        if (in_array($s, ['corner_1500', 'corner 1500', 'commercial_corner', 'corner1500'], true)) {
            return 'corner_1500';
        }

        // corner_1000 aliases
        if (in_array($s, ['corner_1000', 'corner 1000', 'corner1000'], true)) {
            return 'corner_1000';
        }

        // 'block a' → block_a, 'block b' → block_b, etc.
        if (preg_match('/^block ([abc])$/', $s, $m)) {
            return 'block_' . $m[1];
        }

        return $s; // Let caller handle null
    }

    /**
     * Compute the total plot value for a given block key and optional area override.
     */
    public function calculatePlotValue(string $blockKey, ?float $areaOverride = null): array
    {
        $block = $this->getBlockPricing($blockKey);
        if ($block === null) {
            return ['success' => false, 'error' => "Unknown block key: {$blockKey}"];
        }

        $area       = $areaOverride ?? $block['area_sqft'];
        $totalValue = $block['final_rate'] * $area;

        return [
            'success'          => true,
            'block'            => $block['label'],
            'area_sqft'        => $area,
            'base_rate'        => $block['base_rate'],
            'final_rate'       => $block['final_rate'],
            'plc_pct'          => $block['plc_pct'] ?? 0,
            'emi_allowed'      => $block['emi_allowed'],
            'booking_amount'   => self::DEFAULT_BOOKING_AMOUNT,
            'total_plot_value' => $totalValue,
        ];
    }

    /**
     * Return the default booking / token amount.
     */
    public function getDefaultBookingAmount(): float
    {
        return self::DEFAULT_BOOKING_AMOUNT;
    }
}