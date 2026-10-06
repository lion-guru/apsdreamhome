<?php
/**
 * Seed dynamic referral/wallet knobs into service_configs (group: referral).
 * Idempotent: set() upserts without touching existing values.
 * Run: php scripts/seed_referral_configs.php
 */
require_once __DIR__ . '/../config/bootstrap.php';

use App\Services\ServiceConfigService;

try {
    $svc = ServiceConfigService::getInstance();

    $knobs = [
        // key => [value, type, description, sort]
        'customer_booking_pct' => [2.0, 'number', 'Customer referrer commission: % of booking value on paid bookings. Guardrail: <= 5.', 10],
        'associate_team_bonus' => [500.00, 'number', 'One-time bonus to referrer when a referred associate/agent adds their first team member. Guardrail: <= 2000.', 20],
        'wallet_l1_default_pct' => [20.00, 'number', 'Default L1 referral % of wallet package price (used when a package row has no pct). Guardrail: L1+L2 <= 30.', 30],
        'wallet_l2_default_pct' => [5.00, 'number', 'Default L2 referral % of wallet package price (referrer\u2019s sponsor). Guardrail: L1+L2 <= 30.', 40],
    ];

    foreach ($knobs as $key => [$value, $type, $desc, $sort]) {
        $existing = $svc->get('referral', $key, null);
        if ($existing === null) {
            $svc->set('referral', $key, $value, [
                'config_type' => $type,
                'description' => $desc,
                'group_name' => 'referral',
                'sort_order' => $sort,
            ]);
            echo "seeded referral.$key = $value\n";
        } else {
            echo "kept   referral.$key = $existing (not overwritten)\n";
        }
    }

    // Verify reads with fallbacks
    echo 'verify customer_booking_pct = ' . var_export(ServiceConfigService::getVal('referral', 'customer_booking_pct', 2.0), true) . "\n";
    echo 'verify associate_team_bonus = ' . var_export(ServiceConfigService::getVal('referral', 'associate_team_bonus', 500.00), true) . "\n";
    echo "OK\n";
} catch (Throwable $e) {
    echo 'FAIL: ' . $e->getMessage() . "\n";
    exit(1);
}
