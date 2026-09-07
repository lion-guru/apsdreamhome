<?php

namespace App\Services;

use PDO;

/**
 * Pricing Service - Handles Raghunath Nagri pricing matrix and plot value calculations
 */
class PricingService
{
    /** @var PDO */
    private $pdo;

    private const PRICING_MATRIX = [
        'block_a' => [
            'label' => 'Block A',
            'area_sqft' => 1000,
            'base_rate' => 950,
            'final_rate' => 950,
            'emi_allowed' => false,
            'payment_plan' => 'no_emi',
            'premium_type' => 'regular',
        ],
        'block_b' => [
            'label' => 'Block B',
            'area_sqft' => 1000,
            'base_rate' => 850,
            'final_rate' => 850,
            'emi_allowed' => true,
            'payment_plan' => 'emi_available',
            'premium_type' => 'regular',
        ],
        'block_c' => [
            'label' => 'Block C',
            'area_sqft' => 1000,
            'base_rate' => 750,
            'final_rate' => 750,
            'emi_allowed' => true,
            'payment_plan' => 'emi_available',
            'premium_type' => 'regular',
        ],
        'corner_1500' => [
            'label' => 'Corner 1500',
            'area_sqft' => 1500,
            'base_rate' => 1250,
            'final_rate' => 1375,
            'emi_allowed' => false,
            'payment_plan' => 'no_emi',
            'premium_type' => 'commercial_corner',
            'plc_pct' => 10,
        ],
        'corner_1000' => [
            'label' => 'Corner 1000',
            'area_sqft' => 1000,
            'base_rate' => 1000,
            'final_rate' => 1100,
            'emi_allowed' => false,
            'payment_plan' => 'no_emi',
            'premium_type' => 'corner_c',
            'plc_pct' => 10,
        ],
    ];

    private const DEFAULT_BOOKING_AMOUNT = 51000;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? \App\Core\Database\Database::getInstance()->getPdo();
    }

    public function getPricingMatrix(): array
    {
        return self::PRICING_MATRIX;
    }

    public function getBlockPricing(string $blockKey): ?array
    {
        $key = $this->normaliseBlockKey($blockKey);
        return self::PRICING_MATRIX[$key] ?? null;
    }

    private function normaliseBlockKey(string $input): string
    {
        $key = strtolower(trim($input));
        $key = str_replace([' ', '-'], '_', $key);
        return $key;
    }

    public function calculatePlotValue(string $blockKey, ?float $areaOverride = null): array
    {
        $pricing = $this->getBlockPricing($blockKey);
        if (!$pricing) {
            throw new \InvalidArgumentException("Unknown block: {$blockKey}");
        }

        $area = $areaOverride ?? $pricing['area_sqft'];
        $rate = $pricing['final_rate'];
        $total = $area * $rate;

        return [
            'block' => $pricing['label'],
            'area_sqft' => $area,
            'rate_per_sqft' => $rate,
            'total_value' => $total,
            'emi_allowed' => $pricing['emi_allowed'],
            'payment_plan' => $pricing['payment_plan'],
            'premium_type' => $pricing['premium_type'],
            'plc_pct' => $pricing['plc_pct'] ?? 0,
            'booking_amount' => self::DEFAULT_BOOKING_AMOUNT,
        ];
    }

    public function getDefaultBookingAmount(): float
    {
        return self::DEFAULT_BOOKING_AMOUNT;
    }
}