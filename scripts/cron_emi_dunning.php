<?php
/**
 * Phase 30: EMI Daily Dunning Cron
 * 
 * Runs daily (e.g., 08:00 IST via Windows Task Scheduler).
 * - Updates overdue installment statuses
 * - Applies daily penalties (18% p.a. after 5-day grace)
 * - Sends graduated dunning emails (7/14/30/60/90 day tiers)
 * - Sends upcoming payment reminders
 * - Checks for defaulted bookings
 * - Logs all actions to dunning_log
 * 
 * Usage:
 *   php scripts/cron_emi_dunning.php              (full run)
 *   php scripts/cron_emi_dunning.php --dry-run    (preview only, no emails sent)
 *   php scripts/cron_emi_dunning.php --status     (show overdue summary)
 */

$root = dirname(__DIR__);

// Bootstrap framework (same pattern as cron_mlm_daily.php)
require_once $root . '/config/bootstrap.php';

// Parse args
$dryRun = in_array('--dry-run', $argv ?? []);
$statusOnly = in_array('--status', $argv ?? []);

echo "=== APS Dream Home — EMI Dunning Cron ===\n";
echo "Date: " . date('Y-m-d H:i:s') . "\n";
echo "Mode: " . ($statusOnly ? 'STATUS ONLY' : ($dryRun ? 'DRY RUN (no emails)' : 'LIVE')) . "\n\n";

try {
    $db = \App\Core\Database\Database::getInstance();
    $pdo = $db->getConnection();
} catch (\Exception $e) {
    echo "ERROR: DB connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Tenant context
$tenantId = 1;
foreach ($argv as $arg) {
    if (strpos($arg, '--tenant=') === 0) {
        $tenantId = (int)substr($arg, 9);
    }
}
\App\Core\Middleware\TenantContext::setById($tenantId, $pdo);
$tenantSql = $tenantId > 1 ? " AND tenant_id = " . $tenantId : "";
$tenantCol = $tenantId > 1 ? ", tenant_id" : "";
$tenantVal = $tenantId > 1 ? ", " . $tenantId : "";
echo "Tenant: $tenantId\n\n";

// 1. Show status summary
$summary = [
    'total_pending'  => 0,
    'total_overdue'  => 0,
    'total_paid'     => 0,
    'overdue_amount' => 0,
    'total_penalty'  => 0,
];

$stats = $pdo->query(
    "SELECT status, COUNT(*) AS cnt, SUM(amount) AS total, SUM(accrued_penalty) AS penalty
     FROM booking_payment_schedules
     GROUP BY status"
)->fetchAll(\PDO::FETCH_ASSOC);

foreach ($stats as $row) {
    $summary["total_{$row['status']}"] = (int)$row['cnt'];
    if (in_array($row['status'], ['overdue', 'pending'])) {
        $summary['overdue_amount'] += (float)$row['total'];
        $summary['total_penalty'] += (float)($row['penalty'] ?? 0);
    }
}

echo "--- Overdue Summary ---\n";
echo "Pending installments: {$summary['total_pending']}\n";
echo "Overdue installments: {$summary['total_overdue']}\n";
echo "Paid installments:    {$summary['total_paid']}\n";
echo "Overdue amount:       " . number_format($summary['overdue_amount']) . "\n";
echo "Total penalty:        " . number_format($summary['total_penalty']) . "\n\n";

if ($statusOnly) {
    exit(0);
}

// 2. Run automation
$serviceClass = $root . '/app/Services/EMIAutomationService.php';
if (!file_exists($serviceClass)) {
    echo "ERROR: EMIAutomationService.php not found\n";
    exit(1);
}

// Simple bootstrap for the service (needs Database class)
require_once $root . '/app/Core/Database/Database.php';

// Set DB config in memory for Database::getInstance()
$_ENV['DB_HOST'] = '127.0.0.1';
$_ENV['DB_PORT'] = '3307';
$_ENV['DB_NAME'] = 'apsdreamhome';
$_ENV['DB_USER'] = 'root';
$_ENV['DB_PASS'] = '';

$service = new \App\Services\EMIAutomationService();

echo "--- Running Automation ---\n";

if ($dryRun) {
    echo "[DRY RUN] Skipping email sends.\n";
    echo "[DRY RUN] Would update overdue statuses.\n";
    echo "[DRY RUN] Would apply penalties.\n";
    echo "[DRY RUN] Would send dunning emails.\n";
    echo "[DRY RUN] Would check defaults.\n";
} else {
    $results = $service->runAll();
    echo "Status update:  " . ($results['status_update'] ? 'OK' : 'FAIL') . "\n";
    echo "Penalties:      " . ($results['penalties'] ? 'OK' : 'FAIL') . "\n";
    echo "Reminders:      " . ($results['reminders'] ? 'OK' : 'FAIL') . "\n";
    echo "Dunning emails: " . ($results['dunning'] ? 'OK' : 'FAIL') . "\n";
    echo "Default check:  " . ($results['defaults_check'] ? 'OK' : 'FAIL') . "\n";
}

// 3. Show post-run stats
$postStats = $pdo->query(
    "SELECT status, COUNT(*) AS cnt, SUM(accrued_penalty) AS penalty
     FROM booking_payment_schedules
     GROUP BY status"
)->fetchAll(\PDO::FETCH_ASSOC);

echo "\n--- Post-Run Status ---\n";
foreach ($postStats as $row) {
    echo "{$row['status']}: {$row['cnt']} installments, penalty: " . number_format((float)($row['penalty'] ?? 0)) . "\n";
}

// 4. Show dunning log today
$dunningToday = $pdo->query(
    "SELECT dunning_tier, channel, status, COUNT(*) AS cnt
     FROM dunning_log
     WHERE DATE(created_at) = CURDATE()
     GROUP BY dunning_tier, channel, status"
)->fetchAll(\PDO::FETCH_ASSOC);

if ($dunningToday) {
    echo "\n--- Dunning Log (Today) ---\n";
    foreach ($dunningToday as $row) {
        echo "{$row['dunning_tier']} ({$row['channel']}): {$row['cnt']} {$row['status']}\n";
    }
}

echo "\nDone: " . date('Y-m-d H:i:s') . "\n";
