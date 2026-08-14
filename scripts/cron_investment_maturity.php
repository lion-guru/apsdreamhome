<?php
/**
 * Investment Maturity Cron
 * 
 * Runs daily to:
 * 1. Find active investments where maturity_date <= NOW() and maturity_status = 'pending'
 * 2. Mark them as 'matured'
 * 3. Log the maturity event
 * 
 * Usage: php scripts/cron_investment_maturity.php
 */

require_once __DIR__ . '/../app/Core/autoload.php';

$db = \App\Core\Database\Database::getInstance();
$today = date('Y-m-d');
$matured = 0;
$errors = [];

echo "=== Investment Maturity Cron â€” $today ===\n\n";

// Set tenant context for TenantContext consumers
$cronTenantId = 1;
if (class_exists('\App\Core\Middleware\TenantContext')) {
    \App\Core\Middleware\TenantContext::setById($cronTenantId, $db->getConnection());
}
$cronTenantSql = $cronTenantId > 1 ? " AND tenant_id = " . (int)$cronTenantId : "";
$cronTenantCol = $cronTenantId > 1 ? ", tenant_id" : "";
$cronTenantVal = $cronTenantId > 1 ? ", " . (int)$cronTenantId : "";

try {
    // Find investments that have matured today or earlier
    $investments = $db->fetchAll(
        "SELECT i.id, i.user_id, i.investment_ref, i.principal_amount, i.current_value, 
                i.maturity_date, i.maturity_status, i.status,
                p.plan_name, p.tenure_months
         FROM investments i
         LEFT JOIN investment_plans p ON i.plan_id = p.id
         WHERE i.status = 'active' 
           AND i.maturity_status = 'pending'
           AND i.maturity_date IS NOT NULL
           AND i.maturity_date <= ?
           {$cronTenantSql}
         ORDER BY i.maturity_date ASC",
        [$today]
    );

    echo "Found " . count($investments) . " investment(s) to mature.\n\n";

    foreach ($investments as $inv) {
        try {
            $db->query(
                "UPDATE investments SET 
                    maturity_status = 'matured', 
                    updated_at = NOW(),
                    notes = CONCAT(COALESCE(notes, ''), '\n[Maturity] Marked as matured on " . $today . "')
                 WHERE id = ? AND maturity_status = 'pending'{$cronTenantSql}",
                [$inv['id']]
            );

            // Log the maturity event
            try {
                $activityLogData = [
                    'user_id' => $inv['user_id'],
                    'user_type' => 'customer',
                    'action' => "Investment {$inv['investment_ref']} matured. Principal: â‚¹" . number_format($inv['principal_amount']) . ", Final Value: â‚¹" . number_format($inv['current_value']),
                    'ip_address' => '127.0.0.1',
                    'created_at' => date('Y-m-d H:i:s')
                ];
                if ($cronTenantId > 1) $activityLogData['tenant_id'] = $cronTenantId;
                $db->insert('activity_logs_unified', $activityLogData);
            } catch (\Throwable $e) {
                // Non-fatal â€” logging failure doesn't block maturity
            }

            echo "  âœ… #{$inv['id']} {$inv['investment_ref']} â€” â‚¹" . number_format($inv['principal_amount']) . " ({$inv['plan_name']})\n";
            $matured++;

        } catch (\Throwable $e) {
            $errors[] = "Investment #{$inv['id']}: " . $e->getMessage();
            echo "  â�Œ #{$inv['id']} {$inv['investment_ref']} â€” ERROR: " . $e->getMessage() . "\n";
        }
    }

} catch (\Throwable $e) {
    echo "FATAL ERROR: " . $e->getMessage() . "\n";
    $errors[] = $e->getMessage();
}

echo "\n=== Summary ===\n";
echo "Matured: $matured\n";
echo "Errors: " . count($errors) . "\n";

if (!empty($errors)) {
    echo "\nError details:\n";
    foreach ($errors as $e) {
        echo "  - $e\n";
    }
}?>