<?php

namespace App\Models;

/**
 * MLM Plan Templates
 */
class MLMPlanTemplates
{

    public static function getPlanTemplates()
    {
        return [
            [
                'id' => 'starter',
                'name' => 'Starter Plan',
                'type' => 'hybrid',
                'description' => 'Perfect for new associates',
                'joining_fee' => 1000,
                'monthly_target' => 50000,
                'commission_rates' => [
                    'binary' => ['rate' => 10, 'weight' => 40],
                    'unilevel' => ['rate' => 8, 'weight' => 35],
                    'matrix' => ['rate' => 6, 'weight' => 25]
                ],
                'bonuses' => [
                    'leadership_bonus' => 2,
                    'team_building_bonus' => 1000,
                    'volume_bonus' => 0.5
                ]
            ],
            [
                'id' => 'premium',
                'name' => 'Premium Plan',
                'type' => 'hybrid',
                'description' => 'For serious network builders',
                'joining_fee' => 5000,
                'monthly_target' => 200000,
                'commission_rates' => [
                    'binary' => ['rate' => 12, 'weight' => 45],
                    'unilevel' => ['rate' => 10, 'weight' => 30],
                    'matrix' => ['rate' => 8, 'weight' => 25]
                ],
                'bonuses' => [
                    'leadership_bonus' => 3,
                    'team_building_bonus' => 2000,
                    'volume_bonus' => 1.0
                ]
            ]
        ];
    }

    /**
     * Get a specific plan by ID
     */
    public static function getPlan($planId)
    {
        $templates = self::getPlanTemplates();
        foreach ($templates as $template) {
            if ($template['id'] === $planId) {
                return $template;
            }
        }
        return null;
    }
}
