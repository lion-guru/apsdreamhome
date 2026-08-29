#!/usr/bin/env php
<?php
/**
 * Master Cron Runner â€” APS Dream Home
 * =====================================
 * Single entry point for ALL automated tasks. Run daily via Windows Task Scheduler.
 *
 * Tasks executed:
 *   DAILY:
 *     1. EMI Penalty Accrual (overdue installments â†’ penalty)
 *     2. Commission Clawback (30+ day defaulters â†’ debit upline)
 *     3. Rank Auto-Promotion (evaluate all associates â†’ promote)
 *     4. Investment Maturity (matured investments â†’ payout)
 *     5. Agent Auto-Deactivate (90+ days inactive â†’ deactivate)
 *     6. Milestone Bonus Auto-Credit (25/50/75/100% payment milestones)
 *
 *   MONTHLY (1st of each month):
 *     7. Royalty Pool Distribution (2% â†’ qualified site managers)
 *     8. Generation Bonus (gen volume â†’ president/site_manager)
 *     9. Infinity Override (1% â†’ VP+ leaders)
 *    10. Matching Bonus (match earnings 100%/50%/25% â†’ president+)
 *
 * Schedule (Windows Task Scheduler â€” see below):
 *   Daily:    php C:\xampp\htdocs\apsdreamhome\scripts\run_all_crons.php
 *   Monthly:  php C:\xampp\htdocs\apsdreamhome\scripts\run_all_crons.php --mode=monthly
 *
 * Usage:
 *   php scripts/run_all_crons.php                    # Auto-detect (daily on non-1st, monthly on 1st)
 *   php scripts/run_all_crons.php --mode=daily       # Force daily only
 *   php scripts/run_all_crons.php --mode=monthly     # Force monthly only
 *   php scripts/run_all_crons.php --mode=all         # Run both daily + monthly
 *   php scripts/run_all_crons.php --status           # Show summary without running
 *   php scripts/run_all_crons.php --dry-run          # Show what would run without executing
 *
 * WINDOWS TASK SCHEDULER SETUP:
 *   1. Open Task Scheduler (taskschd.msc)
 *   2. Create Task (not Basic Task)
 *   3. Name: "APS Dream Home - Daily Cron"
 *   4. Triggers â†’ New:
 *      - Daily at 1:00 AM
 *   5. Actions â†’ New:
 *      - Program: C:\xampp\php\php.exe
 *      - Arguments: C:\xampp\htdocs\apsdreamhome\scripts\run_all_crons.php --mode=daily
 *   6. Conditions â†’ Uncheck "Start only if on AC power"
 *   7. OK â†’ Enter admin password
 *
 *   For monthly (royalty pool etc.), create a second task:
 *   - Name: "APS Dream Home - Monthly Cron"
 *   - Trigger: Monthly on day 1 at 2:00 AM
 *   - Action: C:\xampp\php\php.exe C:\xampp\htdocs\apsdreamhome\scripts\run_all_crons.php --mode=monthly
 */

$root   = dirname(__DIR__);
$config = require $root . '/config/database.php';

// Parse arguments
$mode       = 'auto';
$statusOnly = false;
$dryRun     = false;
$specificTenantId = null;
foreach ($argv as $arg) {
    if ($arg === '--mode=daily')   $mode = 'daily';
    if ($arg === '--mode=monthly') $mode = 'monthly';
    if ($arg === '--mode=all')     $mode = 'all';
    if ($arg === '--status')       $statusOnly = true;
    if ($arg === '--dry-run')      $dryRun = true;
    if (preg_match('/^--tenant=(\d+)$/', $arg, $m)) $specificTenantId = (int)$m[1];
}

if ($mode === 'auto') {
    $mode = (date('j') === 1) ? 'monthly' : 'daily';
}

$startTime = microtime(true);
$log       = [];
$errors    = [];

echo "â•”â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•—" . PHP_EOL;
echo "â•‘    APS DREAM HOME â€” Master Cron Runner                   â•‘" . PHP_EOL;
echo "â• â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•£" . PHP_EOL;
echo "â•‘  Mode: " . strtoupper($mode) . str_repeat(' ', 47 - strlen($mode)) . "â•‘" . PHP_EOL;
echo "â•‘  Date: " . date('Y-m-d H:i:s') . str_repeat(' ', 40) . "â•‘" . PHP_EOL;
if ($dryRun) {
    echo "â•‘  âš   DRY RUN â€” no tasks will execute                     â•‘" . PHP_EOL;
}
echo "â•šâ•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�" . PHP_EOL . PHP_EOL;

if ($statusOnly || $dryRun) {
    // Show what would run
    echo "Tasks that would execute:" . PHP_EOL;
    if (in_array($mode, ['daily', 'all'])) {
        echo "  DAILY:" . PHP_EOL;
        echo "    1. EMI Penalty Accrual" . PHP_EOL;
        echo "    2. Commission Clawback" . PHP_EOL;
        echo "    3. Rank Auto-Promotion" . PHP_EOL;
        echo "    4. Investment Maturity" . PHP_EOL;
        echo "    5. Agent Auto-Deactivate" . PHP_EOL;
        echo "    6. Milestone Bonus" . PHP_EOL;
    }
    if (in_array($mode, ['monthly', 'all'])) {
        echo "  MONTHLY:" . PHP_EOL;
        echo "    7. Royalty Pool Distribution" . PHP_EOL;
        echo "    8. Generation Bonus" . PHP_EOL;
        echo "    9. Infinity Override" . PHP_EOL;
        echo "   10. Matching Bonus" . PHP_EOL;
    }
    if ($statusOnly) {
        // Also show DB status
        try {
            $pdo = new PDO(
                "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4",
                $config['username'], $config['password'],
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
            echo PHP_EOL . "=== Current Status ===" . PHP_EOL;

            // Last cron run
            $logFile = $root . '/storage/logs/commission_cron.log';
            if (file_exists($logFile)) {
                $lines = file($logFile, FILE_IGNORE_NEW_LINES);
                $last  = end($lines);
                echo "Last commission cron: " . ($last ? explode(' | ', $last)[0] : 'never') . PHP_EOL;
            }

            // Pending penalties
            try {
                $r = $pdo->query("SELECT COUNT(*) FROM booking_payment_schedules WHERE status = 'overdue' AND accrued_penalty > 0")->fetchColumn();
                echo "Overdue installments with penalty: $r" . PHP_EOL;
            } catch (\Throwable $e) { error_log(__METHOD__ . ': ' . $e->getMessage()); }

            // Active investments nearing maturity
            try {
                $r = $pdo->query("SELECT COUNT(*) FROM investments WHERE maturity_status = 'active' AND maturity_date <= DATE_ADD(NOW(), INTERVAL 7 DAY)")->fetchColumn();
                echo "Investments maturing in 7 days: $r" . PHP_EOL;
            } catch (\Throwable $e) { error_log(__METHOD__ . ': ' . $e->getMessage()); }

            // Inactive agents (90+ days)
            try {
                $r = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'agent' AND status = 'active' AND last_login_at < DATE_SUB(NOW(), INTERVAL 90 DAY)")->fetchColumn();
                echo "Agents inactive 90+ days: $r" . PHP_EOL;
            } catch (\Throwable $e) { error_log(__METHOD__ . ': ' . $e->getMessage()); }

            // Royalty pool
            try {
                $thisMonth = date('Y-m');
                $r = $pdo->prepare("SELECT total_pool_amount, distributed_status FROM mlm_royalty_pool WHERE month_year = ?");
                $r->execute([$thisMonth]);
                $pool = $r->fetch(PDO::FETCH_ASSOC);
                echo "This month royalty pool: â‚¹" . number_format($pool['total_pool_amount'] ?? 0) . " (" . ($pool['distributed_status'] ?? 'none') . ")" . PHP_EOL;
            } catch (\Throwable $e) { error_log(__METHOD__ . ': ' . $e->getMessage()); }
        } catch (\Throwable $e) { error_log(__METHOD__ . ': ' . $e->getMessage()); }
    }
    exit(0);
}

try {
    $pdo = new PDO(
        "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4",
        $config['username'], $config['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "[âœ“] Database connected" . PHP_EOL . PHP_EOL;

    define('APP_ROOT', $root);
    require_once $root . '/app/Core/Autoloader.php';
    $autoloader = \App\Core\Autoloader::getInstance();
    $autoloader->register();

    // Set tenant context for all tasks
    $tenantId = $specificTenantId ?? 1;
    \App\Core\Middleware\TenantContext::setById($tenantId, $pdo);
    echo "[âœ“] Tenant context: ID {$tenantId}" . PHP_EOL . PHP_EOL;

    // Tenant SQL helper: returns scoped WHERE clause for tenant-specific tables
    $tenantSql = $tenantId > 1 ? " AND tenant_id = " . (int)$tenantId : "";
    $tenantCol = $tenantId > 1 ? ", tenant_id" : "";
    $tenantVal = $tenantId > 1 ? ", " . (int)$tenantId : "";

    $taskNum = 0;

    // â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�
    // DAILY TASKS
    // â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�
    if (in_array($mode, ['daily', 'all'])) {

        // 1. EMI PENALTY ACCRUAL
        $taskNum++;
        echo "â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�" . PHP_EOL;
        echo "{$taskNum}/10  EMI Penalty Accrual" . PHP_EOL;
        echo "â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�" . PHP_EOL;
        try {
            $penaltyService = new \App\Services\Accounting\MoneyWorkflowService();
            $penaltyResult  = $penaltyService->applyDailyPenalties();
            if ($penaltyResult['success']) {
                echo "  âœ… {$penaltyResult['penalties_applied']} installments, â‚¹" . number_format($penaltyResult['total_penalty'], 2) . PHP_EOL;
                $log['penalties'] = $penaltyResult;
            } else {
                echo "  âš ï¸�  " . ($penaltyResult['error'] ?? 'no overdue installments') . PHP_EOL;
            }
        } catch (\Throwable $e) {
            echo "  â�Œ " . $e->getMessage() . PHP_EOL;
            $errors[] = 'penalties: ' . $e->getMessage();
        }
        echo PHP_EOL;

        // 2. COMMISSION CLAWBACK
        $taskNum++;
        echo "â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�" . PHP_EOL;
        echo "{$taskNum}/10  Commission Clawback" . PHP_EOL;
        echo "â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�" . PHP_EOL;
        try {
            $clawbackEngine = new \App\Services\MLM\MLMCommissionEngine($pdo);
            $clawbackResult = $clawbackEngine->processClawbacks();
            echo "  âœ… {$clawbackResult['processed']} entries, â‚¹" . number_format($clawbackResult['amount'], 2) . PHP_EOL;
            $log['clawback'] = $clawbackResult;
        } catch (\Throwable $e) {
            echo "  â�Œ " . $e->getMessage() . PHP_EOL;
            $errors[] = 'clawback: ' . $e->getMessage();
        }
        echo PHP_EOL;

        // 3. RANK AUTO-PROMOTION
        $taskNum++;
        echo "â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�" . PHP_EOL;
        echo "{$taskNum}/10  Rank Auto-Promotion" . PHP_EOL;
        echo "â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�" . PHP_EOL;
        try {
            $rankEngine = new \App\Services\MLM\MLMCommissionEngine($pdo);
            $rankResult = $rankEngine->runRankPromotions();
            echo "  âœ… {$rankResult['promoted']} promoted, {$rankResult['unchanged']} unchanged" . PHP_EOL;
            $log['rank_promotions'] = $rankResult;
        } catch (\Throwable $e) {
            echo "  â�Œ " . $e->getMessage() . PHP_EOL;
            $errors[] = 'rank_promotion: ' . $e->getMessage();
        }
        echo PHP_EOL;

        // 4. INVESTMENT MATURITY
        $taskNum++;
        echo "â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�" . PHP_EOL;
        echo "{$taskNum}/10  Investment Maturity" . PHP_EOL;
        echo "â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�" . PHP_EOL;
        try {
            $stmt = $pdo->prepare("
                SELECT i.*, u.name as investor_name
                FROM investments i
                JOIN users u ON i.user_id = u.id
                WHERE i.maturity_status = 'active'
                  AND i.maturity_date <= NOW()
                  {$tenantSql}
                ORDER BY i.maturity_date ASC
                LIMIT 50
            ");
            $stmt->execute();
            $matured = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($matured)) {
                $count = 0;
                foreach ($matured as $inv) {
                    $pdo->prepare("UPDATE investments SET maturity_status = 'matured', updated_at = NOW() WHERE id = ?{$tenantSql}")->execute([$inv['id']]);
                    $count++;
                }
                echo "  âœ… {$count} investments marked as matured" . PHP_EOL;
                $log['investment_maturity'] = ['matured' => $count];
            } else {
                echo "  âš ï¸�  No investments reaching maturity today" . PHP_EOL;
            }
        } catch (\Throwable $e) {
            echo "  â�Œ " . $e->getMessage() . PHP_EOL;
            $errors[] = 'investment_maturity: ' . $e->getMessage();
        }
        echo PHP_EOL;

        // 5. AGENT AUTO-DEACTIVATE
        $taskNum++;
        echo "â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�" . PHP_EOL;
        echo "{$taskNum}/10  Agent Auto-Deactivate (90+ days)" . PHP_EOL;
        echo "â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�" . PHP_EOL;
        try {
            $stmt = $pdo->prepare("
                SELECT id, name, email, last_login_at
                FROM users
                WHERE role = 'agent'
                  AND status = 'active'
                  AND last_login_at < DATE_SUB(NOW(), INTERVAL 90 DAY)
            ");
            $stmt->execute();
            $inactive = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($inactive)) {
                $count = 0;
                foreach ($inactive as $agent) {
                    $pdo->prepare("UPDATE users SET status = 'inactive', updated_at = NOW() WHERE id = ?")->execute([$agent['id']]);
                    $count++;
                }
                echo "  âœ… {$count} agents deactivated" . PHP_EOL;
                $log['agent_deactivate'] = ['deactivated' => $count];
            } else {
                echo "  âš ï¸�  No agents inactive for 90+ days" . PHP_EOL;
            }
        } catch (\Throwable $e) {
            echo "  â�Œ " . $e->getMessage() . PHP_EOL;
            $errors[] = 'agent_deactivate: ' . $e->getMessage();
        }
        echo PHP_EOL;

        // 6. MILESTONE BONUS
        $taskNum++;
        echo "â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�" . PHP_EOL;
        echo "{$taskNum}/11  Milestone Bonus Auto-Credit" . PHP_EOL;
        echo "â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�" . PHP_EOL;
        try {
            $milestones = [
                25 => 1000,
                50 => 2500,
                75 => 5000,
                100 => 10000,
            ];

            $stmt = $pdo->prepare("
                SELECT pb.id, pb.customer_id, pb.associate_id, pb.total_plot_value,
                       COALESCE(SUM(bp.amount), 0) AS total_paid
                FROM plot_bookings pb
                LEFT JOIN booking_payments bp ON bp.booking_id = pb.id AND bp.status = 'completed'
                WHERE pb.status NOT IN ('cancelled')
                GROUP BY pb.id
                HAVING total_paid > 0
            ");
            $stmt->execute();
            $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $credited = 0;
            foreach ($bookings as $booking) {
                $pctPaid = ($booking['total_plot_value'] > 0)
                    ? ($booking['total_paid'] / $booking['total_plot_value']) * 100
                    : 0;

                foreach ($milestones as $threshold => $bonusAmount) {
                    if ($pctPaid >= $threshold) {
                        $existing = $pdo->prepare("SELECT id FROM mlm_commission_ledger WHERE booking_id = ? AND commission_type = 'milestone_bonus' AND JSON_EXTRACT(metadata, '$.milestone_pct') = ?{$tenantSql}");
                        $existing->execute([$booking['id'], $threshold]);
                        if (!$existing->fetch()) {
                            $pdo->prepare("
                                INSERT INTO mlm_commission_ledger
                                (user_id, booking_id, commission_type, amount, status, description, metadata{$tenantCol}, created_at)
                                VALUES (?, ?, 'milestone_bonus', ?, 'pending', ?, ?{$tenantVal}, NOW())
                            ")->execute([
                                $booking['associate_id'] ?? $booking['customer_id'],
                                $booking['id'],
                                $bonusAmount,
                                "Milestone bonus: {$threshold}% payment reached",
                                json_encode(['milestone_pct' => $threshold, 'bonus_amount' => $bonusAmount])
                            ]);
                            $credited++;
                        }
                    }
                }
            }
            echo "  âœ… {$credited} milestone bonuses credited" . PHP_EOL;
            $log['milestone_bonus'] = ['credited' => $credited];
        } catch (\Throwable $e) {
            echo "  â�Œ " . $e->getMessage() . PHP_EOL;
            $errors[] = 'milestone_bonus: ' . $e->getMessage();
        }
        echo PHP_EOL;

        // 7. FOLLOW-UP REMINDERS
        $taskNum++;
        echo "â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�" . PHP_EOL;
        echo "{$taskNum}/11  Follow-up Reminders" . PHP_EOL;
        echo "â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�" . PHP_EOL;
        try {
            $reminderStmt = $pdo->prepare("
                SELECT COUNT(*) FROM crm_tasks 
                WHERE status = 'pending' AND reminder_at IS NOT NULL AND reminder_at <= NOW() AND reminder_sent = 0{$tenantSql}
            ");
            $reminderStmt->execute();
            $pendingReminders = (int)$reminderStmt->fetchColumn();

            if ($pendingReminders > 0) {
                // Process reminders inline
                $tasks = $pdo->prepare("
                    SELECT t.id, t.lead_id, t.title, t.due_date, t.assigned_to, t.created_by
                    FROM crm_tasks t
                    WHERE t.status = 'pending' AND t.reminder_at IS NOT NULL AND t.reminder_at <= NOW() AND t.reminder_sent = 0{$tenantSql}
                    LIMIT 50
                ");
                $tasks->execute();
                $taskRows = $tasks->fetchAll(PDO::FETCH_ASSOC);
                $sent = 0;
                foreach ($taskRows as $task) {
                    $pdo->prepare("INSERT INTO lead_activities (lead_id, activity_type, description, created_by{$tenantCol}, created_at) VALUES (?, 'reminder', ?, ?{$tenantVal}, NOW())")
                        ->execute([$task['lead_id'], "Follow-up reminder: {$task['title']} (due: {$task['due_date']})", $task['assigned_to'] ?: $task['created_by']]);
                    $pdo->prepare("UPDATE crm_tasks SET reminder_sent = 1, updated_at = NOW() WHERE id = ?{$tenantSql}")->execute([$task['id']]);
                    $sent++;
                }
                echo "  âœ… {$sent} reminders processed" . PHP_EOL;
                $log['followup_reminders'] = ['sent' => $sent];
            } else {
                echo "  âš ï¸�  No pending reminders" . PHP_EOL;
            }
        } catch (\Throwable $e) {
            echo "  â�Œ " . $e->getMessage() . PHP_EOL;
            $errors[] = 'followup_reminders: ' . $e->getMessage();
        }
        echo PHP_EOL;
    }

    // â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�
    // MONTHLY TASKS
    // â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�
    if (in_array($mode, ['monthly', 'all'])) {

        $periodStart = date('Y-m-01', strtotime('-1 month'));
        $periodEnd   = date('Y-m-t', strtotime('-1 month'));

        // 8. ROYALTY POOL
        $taskNum++;
        echo "â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�" . PHP_EOL;
        echo "{$taskNum}/12  Royalty Pool Distribution" . PHP_EOL;
        echo "â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�" . PHP_EOL;
        try {
            $lastMonth = date('Y-m', strtotime('-1 month'));
            $thisMonth = date('Y-m');
            $royaltyPool = $pdo->prepare("SELECT total_pool_amount, distributed_status FROM mlm_royalty_pool WHERE month_year = ?");
            $royaltyPool->execute([$lastMonth]);
            $poolRow  = $royaltyPool->fetch(PDO::FETCH_ASSOC);
            $poolAmt  = (float)($poolRow['total_pool_amount'] ?? 0);
            $poolMonth = $lastMonth;

            if ($poolAmt <= 0) {
                $royaltyPool->execute([$thisMonth]);
                $poolRow  = $royaltyPool->fetch(PDO::FETCH_ASSOC);
                $poolAmt  = (float)($poolRow['total_pool_amount'] ?? 0);
                $poolMonth = $thisMonth;
                if ($poolAmt > 0) {
                    $periodStart = date('Y-m-01');
                    $periodEnd   = date('Y-m-t');
                }
            }

            if ($poolAmt > 0) {
                $engine = new \App\Services\HybridCommissionEngine($pdo);
                $result = $engine->distributeRoyaltyPool($poolMonth);
                echo "  âœ… Pool â‚¹" . number_format($poolAmt) . " distributed to " . ($result['qualified_managers'] ?? 0) . " managers" . PHP_EOL;
                $log['royalty_pool'] = $result;
            } else {
                echo "  âš ï¸�  No pool accumulated for {$poolMonth}" . PHP_EOL;
            }
        } catch (\Throwable $e) {
            echo "  â�Œ " . $e->getMessage() . PHP_EOL;
            $errors[] = 'royalty_pool: ' . $e->getMessage();
        }
        echo PHP_EOL;

        // 9. GENERATION BONUS
        $taskNum++;
        echo "â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�" . PHP_EOL;
        echo "{$taskNum}/12  Generation Bonus" . PHP_EOL;
        echo "â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�" . PHP_EOL;
        try {
            $genEngine = new \App\Services\MLM\GenerationBonusEngine($pdo);
            $genResult = $genEngine->calculateMonthlyGenerations($periodStart, $periodEnd);
            if (!empty($genResult['entries'])) {
                $persisted = $genEngine->persistGenerationBonuses($genResult['entries']);
                $newCount  = count($persisted['created_ids'] ?? []);
                echo "  âœ… " . count($genResult['entries']) . " entries, â‚¹" . number_format($genResult['total'], 2) . ", {$newCount} persisted" . PHP_EOL;
                $log['generation_bonus'] = ['entries' => count($genResult['entries']), 'total' => $genResult['total']];
            } else {
                echo "  âš ï¸�  No qualifying leaders" . PHP_EOL;
            }
        } catch (\Throwable $e) {
            echo "  â�Œ " . $e->getMessage() . PHP_EOL;
            $errors[] = 'generation_bonus: ' . $e->getMessage();
        }
        echo PHP_EOL;

        // 10. INFINITY OVERRIDE
        $taskNum++;
        echo "â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�" . PHP_EOL;
        echo "{$taskNum}/12  Infinity Override" . PHP_EOL;
        echo "â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�" . PHP_EOL;
        try {
            $infService = new \App\Services\MLM\InfinityOverrideService($pdo);
            $infResult  = $infService->calculateMonthlyOverrides($periodStart, $periodEnd);
            if (!empty($infResult['entries'])) {
                $persisted = $infService->persistOverrides($infResult['entries']);
                $newCount  = count($persisted['created_ids'] ?? []);
                echo "  âœ… " . count($infResult['entries']) . " entries, â‚¹" . number_format($infResult['total'], 2) . ", {$newCount} persisted" . PHP_EOL;
                $log['infinity_override'] = ['entries' => count($infResult['entries']), 'total' => $infResult['total']];
            } else {
                echo "  âš ï¸�  No VP+ leaders with qualifying volume" . PHP_EOL;
            }
        } catch (\Throwable $e) {
            echo "  â�Œ " . $e->getMessage() . PHP_EOL;
            $errors[] = 'infinity_override: ' . $e->getMessage();
        }
        echo PHP_EOL;

        // 11. MATCHING BONUS
        $taskNum++;
        echo "â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�" . PHP_EOL;
        echo "{$taskNum}/12  Matching Bonus" . PHP_EOL;
        echo "â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�" . PHP_EOL;
        try {
            $matchService = new \App\Services\MLM\MatchingBonusService($pdo);
            $matchResult  = $matchService->calculateMonthlyMatching($periodStart, $periodEnd);
            if (!empty($matchResult['entries'])) {
                $persisted = $matchService->persistMatchingBonuses($matchResult['entries']);
                $newCount  = count($persisted['created_ids'] ?? []);
                echo "  âœ… " . count($matchResult['entries']) . " entries, â‚¹" . number_format($matchResult['total'], 2) . ", {$newCount} persisted" . PHP_EOL;
                $log['matching_bonus'] = ['entries' => count($matchResult['entries']), 'total' => $matchResult['total']];
            } else {
                echo "  âš ï¸�  No leaders with earnings to match" . PHP_EOL;
            }
        } catch (\Throwable $e) {
            echo "  â�Œ " . $e->getMessage() . PHP_EOL;
            $errors[] = 'matching_bonus: ' . $e->getMessage();
        }
echo PHP_EOL;

        // 12. SALARY INCENTIVE GRANTS (Tiered: 15L->5K, 30L->5K, 50L->8K, 75L->12K, 1Cr->20K)
        $taskNum++;
        echo "==========================================================================================================" . PHP_EOL;
        echo "{$taskNum}/14  Salary Incentive Grants" . PHP_EOL;
        echo "==========================================================================================================" . PHP_EOL;
        try {
            $salaryService = new \App\Services\MLM\SalaryIncentiveService($pdo);
            $monthYear   = date('Y-m');
            $grantResult = $salaryService->processMonthlyGrants($monthYear);
            if ($grantResult['success']) {
                echo "  [OK] {$grantResult['processed']} grants, Rs" . number_format($grantResult['amount'], 2) . PHP_EOL;
                if (!empty($grantResult['errors'])) {
                    foreach ($grantResult['errors'] as $err) {
                    echo "  [WARN] " . $err . PHP_EOL;
                    }
                }
                $log['salary_grants'] = $grantResult;
            } else {
            echo "  [FAIL] " . ($grantResult['error'] ?? 'unknown') . PHP_EOL;
            }
        } catch (\Throwable $e) {
            echo "  [FAIL] " . $e->getMessage() . PHP_EOL;
            $errors[] = 'salary_grants: ' . $e->getMessage();
        }
        echo PHP_EOL;

        // 13. LEADERSHIP SALARY PAYOUTS (15L/60d->5K, 30L/100d->5K, overlap combined)
        $taskNum++;
        echo "==========================================================================================================" . PHP_EOL;
        echo "{$taskNum}/14  Leadership Salary Payouts" . PHP_EOL;
        echo "==========================================================================================================" . PHP_EOL;
        try {
            $leadershipSalary = new \App\Services\MLM\LeadershipSalaryService($pdo);
            $salaryResult     = $leadershipSalary->processMonthlyPayouts();
            if ($salaryResult['processed'] > 0) {
                echo "  [OK] {$salaryResult['processed']} users paid, Rs" . number_format($salaryResult['total_amount'], 2) . PHP_EOL;
                $log['leadership_salary'] = $salaryResult;
            } else {
                echo "  [INFO] No active salary targets this month" . PHP_EOL;
            }
        } catch (\Throwable $e) {
            echo "  [FAIL] " . $e->getMessage() . PHP_EOL;
            $errors[] = 'leadership_salary: ' . $e->getMessage();
        }
        echo PHP_EOL;
    }

    // A��?����A��?����A��?����A��?����A��?����A��?����A��?����A��?����A��?����A��?����A��?����A��?����A��?����A��?����A��?����A��?����A��?����A��?����A��?����A��?����A��?����A��?����A��?����A��?����A��?����A��?����A��?����A��?����A��?����A��?����A��?����A��?����A��?����A��?����A��?����A��?����A��?����A��?����A��?����A��?����A��?����A��?����A��?����A��?����A��?����A��?����A��?����A��?����A��?����A��?����
    // SUMMARY
    // â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�
    $elapsed = round(microtime(true) - $startTime, 2);
    $totalTasks = $taskNum;

    echo "â•”â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•—" . PHP_EOL;
    echo "â•‘    SUMMARY                                              â•‘" . PHP_EOL;
    echo "â• â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•£" . PHP_EOL;
    echo "â•‘  Tasks run:     {$totalTasks}" . PHP_EOL;
    echo "â•‘  Errors:        " . count($errors) . PHP_EOL;
    echo "â•‘  Elapsed:       {$elapsed}s" . PHP_EOL;
    echo "â•šâ•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�" . PHP_EOL;

    // Write log
    $logFile = $root . '/storage/logs/master_cron.log';
    $logDir  = dirname($logFile);
    if (!is_dir($logDir)) { @mkdir($logDir, 0775, true); }
    $logEntry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'mode'      => $mode,
        'tasks'     => $totalTasks,
        'errors'    => count($errors),
        'elapsed'   => $elapsed,
        'results'   => $log,
    ];
    @file_put_contents($logFile, date('Y-m-d H:i:s') . " | " . strtoupper($mode) . " | " . json_encode($logEntry) . PHP_EOL, FILE_APPEND | LOCK_EX);

    echo PHP_EOL . "[âœ“] Log written to storage/logs/master_cron.log" . PHP_EOL;

    if (!empty($errors)) {
        echo PHP_EOL . "ERRORS:" . PHP_EOL;
        foreach ($errors as $e) {
            echo "  â�Œ $e" . PHP_EOL;
        }
    }

} catch (\Throwable $e) {
    echo PHP_EOL . "â�Œ FATAL: " . $e->getMessage() . PHP_EOL;
    echo "   File: " . $e->getFile() . ":" . $e->getLine() . PHP_EOL;
    exit(1);
}?>