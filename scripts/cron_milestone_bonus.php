<?php
/**
 * Milestone Bonus Auto-Credit Cron
 * 
 * Runs daily to check if bookings have hit payment milestones (25%, 50%, 75%, 100%)
 * and credits milestone bonuses to the associate/agent.
 * 
 * Tables: plot_bookings, booking_payment_schedules, mlm_commission_ledger
 * 
 * Usage: php scripts/cron_milestone_bonus.php
 */

require_once __DIR__ . '/../app/Core/autoload.php';

$db = \App\Core\Database\Database::getInstance();
$today = date('Y-m-d');
$credited = 0;
$errors = [];

echo "=== Milestone Bonus Auto-Credit Cron — $today ===\n\n";

// Set tenant context for TenantContext consumers
$cronTenantId = 1;
if (class_exists('\App\Core\Middleware\TenantContext')) {
    \App\Core\Middleware\TenantContext::setById($cronTenantId, $db->getConnection());
}
$cronTenantSql = $cronTenantId > 1 ? " AND tenant_id = " . (int)$cronTenantId : "";
$cronTenantCol = $cronTenantId > 1 ? ", tenant_id" : "";
$cronTenantVal = $cronTenantId > 1 ? ", " . (int)$cronTenantId : "";

$milestones = [
    25 => 'quarter_milestone',
    50 => 'half_milestone',
    75 => 'three_quarter_milestone',
    100 => 'full_milestone',
];

$bonusRates = [
    'quarter_milestone'     => 1000,   // ₹1,000 at 25%
    'half_milestone'        => 2500,   // ₹2,500 at 50%
    'three_quarter_milestone' => 5000, // ₹5,000 at 75%
    'full_milestone'        => 10000,  // ₹10,000 at 100%
];

try {
    // Find active bookings with payments
    $bookings = $db->fetchAll(
        "SELECT pb.id as booking_id, pb.customer_id, pb.associate_id, pb.agent_id,
                pb.total_plot_value,
                COALESCE(SUM(bps.paid_amount), 0) as total_paid,
                (SELECT COUNT(*) FROM mlm_commission_ledger 
                 WHERE reference_id = pb.id 
                 AND commission_type IN ('quarter_milestone','half_milestone','three_quarter_milestone','full_milestone')
                 AND status != 'cancelled'
                 {$cronTenantSql}) as existing_milestones
         FROM plot_bookings pb
         LEFT JOIN booking_payment_schedules bps ON bps.booking_id = pb.id AND bps.status = 'paid'
         WHERE pb.status IN ('emi_active', 'agreement_signed', 'fully_paid')
         {$cronTenantSql}
         GROUP BY pb.id
         HAVING total_paid > 0"
    );

    echo "Found " . count($bookings) . " active booking(s) with payments.\n\n";

    foreach ($bookings as $booking) {
        $totalValue = (float)$booking['total_plot_value'];
        if ($totalValue <= 0) continue;

        $totalPaid = (float)$booking['total_paid'];
        $pctPaid = round(($totalPaid / $totalValue) * 100, 1);
        $associateId = $booking['associate_id'] ?? $booking['agent_id'] ?? 0;

        if ($associateId <= 0) continue;

        foreach ($milestones as $pct => $type) {
            if ($pctPaid >= $pct) {
                // Check if this milestone already credited
                $exists = $db->fetchOne(
                    "SELECT id FROM mlm_commission_ledger 
                     WHERE reference_id = ? AND commission_type = ? AND status != 'cancelled'{$cronTenantSql} LIMIT 1",
                    [$booking['booking_id'], $type]
                );

                if (!$exists) {
                    $bonusAmount = $bonusRates[$type];
                    try {
                        $milestoneLedgerData = [
                            'user_id' => $associateId,
                            'associate_id' => $associateId,
                            'booking_id' => $booking['booking_id'],
                            'commission_type' => $type,
                            'amount' => $bonusAmount,
                            'percentage' => 0,
                            'basis_amount' => $totalValue,
                            'status' => 'pending',
                            'description' => ucfirst(str_replace('_', ' ', $type)) . " — " . $pct . "% of ₹" . number_format($totalValue) . " paid",
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s')
                        ];
                        if ($cronTenantId > 1) $milestoneLedgerData['tenant_id'] = $cronTenantId;
                        $db->insert('mlm_commission_ledger', $milestoneLedgerData);

                        echo "  ✅ Booking #{$booking['booking_id']} — $type (₹$bonusAmount) → Associate #$associateId\n";
                        $credited++;
                    } catch (\Throwable $e) {
                        $errors[] = "Booking #{$booking['booking_id']} $type: " . $e->getMessage();
                        echo "  ❌ Booking #{$booking['booking_id']} — ERROR: " . $e->getMessage() . "\n";
                    }
                }
            }
        }
    }

} catch (\Throwable $e) {
    echo "FATAL ERROR: " . $e->getMessage() . "\n";
    $errors[] = $e->getMessage();
}

echo "\n=== Summary ===\n";
echo "Milestones credited: $credited\n";
echo "Errors: " . count($errors) . "\n";
