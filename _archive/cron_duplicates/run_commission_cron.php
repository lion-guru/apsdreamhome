#!/usr/bin/env php
<?php
/**
 * Unified Commission & MLM Cron Runner
 * =====================================
 * Single entry point for ALL daily + monthly commission operations.
 *
 * Daily tasks (runs every day at 01:00 AM):
 *   1. EMI Penalty Accrual (overdue installments â†’ penalty)
 *   2. Commission Clawback (30+ day defaulters â†’ debit upline)
 *   3. Rank Auto-Promotion (evaluate all associates â†’ promote qualifying)
 *
 * Monthly tasks (runs on 1st of each month at 02:00 AM):
 *   4. Royalty Pool Distribution (2% of sales â†’ qualified site managers)
 *   5. Generation Bonus (5% of gen volume â†’ president/site_manager leaders)
 *   6. Infinity Override (1% of downline sales â†’ VP+ leaders)
 *   7. Matching Bonus (match leader earnings 100%/50%/25% â†’ president+)
 *
 * Schedule (Windows Task Scheduler or cron):
 *   Daily:    0 1 * * * php C:\xampp\htdocs\apsdreamhome\scripts\run_commission_cron.php
 *   Monthly:  0 2 1 * * php C:\xampp\htdocs\apsdreamhome\scripts\run_commission_cron.php --mode=monthly
 *
 * Usage:
 *   php scripts/run_commission_cron.php                    # Auto-detect (daily or monthly based on day)
 *   php scripts/run_commission_cron.php --mode=daily       # Force daily tasks only
 *   php scripts/run_commission_cron.php --mode=monthly     # Force monthly tasks only
 *   php scripts/run_commission_cron.php --status           # Show summary without running
 */

$root   = dirname(__DIR__);
$config = require $root . '/config/database.php';

$mode    = 'auto';
$statusOnly = false;
foreach ($argv as $arg) {
    if ($arg === '--mode=daily')   $mode = 'daily';
    if ($arg === '--mode=monthly') $mode = 'monthly';
    if ($arg === '--status')       $statusOnly = true;
}
if ($mode === 'auto') {
    $mode = (date('j') === 1) ? 'monthly' : 'daily';
}

$startTime = microtime(true);
$log = [];

echo "â•”â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•—" . PHP_EOL;
echo "â•‘    APS DREAM HOME â€” Unified Commission Cron Runner       â•‘" . PHP_EOL;
echo "â• â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•£" . PHP_EOL;
echo "â•‘  Mode: " . strtoupper($mode) . str_repeat(' ', 47 - strlen($mode)) . "â•‘" . PHP_EOL;
echo "â•‘  Date: " . date('Y-m-d H:i:s') . str_repeat(' ', 40) . "â•‘" . PHP_EOL;
echo "â•šâ•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�" . PHP_EOL . PHP_EOL;

try {
    $pdo = new PDO(
        "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4",
        $config['username'],
        $config['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "[âœ“] Database connected" . PHP_EOL . PHP_EOL;

    define('APP_ROOT', $root);
    require_once $root . '/app/Core/Autoloader.php';
    $autoloader = \App\Core\Autoloader::getInstance();
    $autoloader->register();

    // â”€â”€ DAILY TASKS â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    if ($mode === 'daily') {

        // 1. EMI PENALTY ACCRUAL
        echo "â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�" . PHP_EOL;
        echo "1/3  EMI Penalty Accrual" . PHP_EOL;
        echo "â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�" . PHP_EOL;
        try {
            $penaltyService = new \App\Services\Accounting\MoneyWorkflowService();
            $penaltyResult  = $penaltyService->applyDailyPenalties();
            if ($penaltyResult['success']) {
                echo "  âœ… Penalties applied: {$penaltyResult['penalties_applied']} installments, â‚¹" . number_format($penaltyResult['total_penalty'], 2) . PHP_EOL;
                $log['penalties'] = $penaltyResult;
            } else {
                echo "  âš ï¸�  Penalty engine: " . ($penaltyResult['error'] ?? 'no overdue installments') . PHP_EOL;
                $log['penalties'] = ['success' => false, 'error' => $penaltyResult['error'] ?? 'none'];
            }
        } catch (\Throwable $e) {
            echo "  â�Œ Penalty error: " . $e->getMessage() . PHP_EOL;
            $log['penalties'] = ['success' => false, 'error' => $e->getMessage()];
        }
        echo PHP_EOL;

        // 2. COMMISSION CLAWBACK
        echo "â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�" . PHP_EOL;
        echo "2/3  Commission Clawback (30+ day defaulters)" . PHP_EOL;
        echo "â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�" . PHP_EOL;
        try {
            $clawbackEngine = new \App\Services\MLM\MLMCommissionEngine($pdo);
            $clawbackResult = $clawbackEngine->processClawbacks();
            echo "  âœ… Clawback processed: {$clawbackResult['processed']} entries, â‚¹" . number_format($clawbackResult['amount'], 2) . PHP_EOL;
            $log['clawback'] = $clawbackResult;
            if (!empty($clawbackResult['errors'])) {
                foreach ($clawbackResult['errors'] as $err) {
                    echo "  âš ï¸�  $err" . PHP_EOL;
                }
            }
        } catch (\Throwable $e) {
            echo "  â�Œ Clawback error: " . $e->getMessage() . PHP_EOL;
            $log['clawback'] = ['processed' => 0, 'amount' => 0, 'error' => $e->getMessage()];
        }
        echo PHP_EOL;

        // 3. RANK AUTO-PROMOTION
        echo "â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�" . PHP_EOL;
        echo "3/3  Rank Auto-Promotion" . PHP_EOL;
        echo "â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�" . PHP_EOL;
        try {
            $rankEngine = new \App\Services\MLM\MLMCommissionEngine($pdo);
            $rankResult = $rankEngine->runRankPromotions();
            echo "  âœ… Rank promotions: {$rankResult['promoted']} promoted, {$rankResult['unchanged']} unchanged" . PHP_EOL;
            $log['rank_promotions'] = $rankResult;
            if (!empty($rankResult['errors'])) {
                foreach ($rankResult['errors'] as $err) {
                    echo "  âš ï¸�  $err" . PHP_EOL;
                }
            }
        } catch (\Throwable $e) {
            echo "  â�Œ Rank promotion error: " . $e->getMessage() . PHP_EOL;
            $log['rank_promotions'] = ['promoted' => 0, 'unchanged' => 0, 'error' => $e->getMessage()];
        }
        echo PHP_EOL;
    }

    // â”€â”€ MONTHLY TASKS â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    if ($mode === 'monthly') {
        // Fallback: try last month first, then current month if last month has no pool
        $periodStart = date('Y-m-01', strtotime('-1 month'));
        $periodEnd   = date('Y-m-t', strtotime('-1 month'));
        $currentPeriodStart = date('Y-m-01');
        $currentPeriodEnd   = date('Y-m-t');

        // 4. ROYALTY POOL
        echo "â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�" . PHP_EOL;
        echo "1/4  Royalty Pool Distribution" . PHP_EOL;
        echo "â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�" . PHP_EOL;
        try {
            $lastMonth = date('Y-m', strtotime('-1 month'));
            $thisMonth = date('Y-m');
            $royaltyPool = $pdo->prepare("SELECT total_pool_amount, distributed_status, month_year FROM mlm_royalty_pool WHERE month_year = ?");
            $royaltyPool->execute([$lastMonth]);
            $poolRow = $royaltyPool->fetch(PDO::FETCH_ASSOC);
            $poolAmount = (float)($poolRow['total_pool_amount'] ?? 0);
            $poolMonth = $lastMonth;

            // Fallback: if last month has no pool, check current month
            if ($poolAmount <= 0) {
                echo "  No royalty pool for {$lastMonth}, checking {$thisMonth}..." . PHP_EOL;
                $royaltyPool->execute([$thisMonth]);
                $poolRow = $royaltyPool->fetch(PDO::FETCH_ASSOC);
                $poolAmount = (float)($poolRow['total_pool_amount'] ?? 0);
                $poolMonth = $thisMonth;
                if ($poolAmount > 0) {
                    // Use current month's period for downstream calculations
                    $periodStart = $currentPeriodStart;
                    $periodEnd   = $currentPeriodEnd;
                }
            }
            echo "  Royalty pool for {$poolMonth}: â‚¹" . number_format($poolAmount, 2) . PHP_EOL;

            if ($poolAmount > 0) {
                $engine = new \App\Services\HybridCommissionEngine($pdo);
                $royaltyResult = $engine->distributeRoyaltyPool($poolMonth);
                echo "  âœ… Royalty distributed: " . json_encode($royaltyResult) . PHP_EOL;
                $log['royalty_pool'] = $royaltyResult;
            } else {
                echo "  âš ï¸�  No royalty pool accumulated for this month" . PHP_EOL;
                $log['royalty_pool'] = ['distributed' => 0, 'amount' => 0];
            }
        } catch (\Throwable $e) {
            echo "  â�Œ Royalty pool error: " . $e->getMessage() . PHP_EOL;
            $log['royalty_pool'] = ['error' => $e->getMessage()];
        }
        echo PHP_EOL;

        // 5. GENERATION BONUS
        echo "â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�" . PHP_EOL;
        echo "2/4  Generation Bonus" . PHP_EOL;
        echo "â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�" . PHP_EOL;
        try {
            $genEngine = new \App\Services\MLM\GenerationBonusEngine($pdo);
            $genResult = $genEngine->calculateMonthlyGenerations($periodStart, $periodEnd);
            if (!empty($genResult['entries'])) {
                $persisted = $genEngine->persistGenerationBonuses($genResult['entries']);
                echo "  âœ… Generation bonus: " . count($genResult['entries']) . " entries, â‚¹" . number_format($genResult['total'], 2) . PHP_EOL;
                $newCount = count($persisted['created_ids'] ?? []);
                echo "     Persisted: " . $newCount . " new to mlm_generation_commissions" . PHP_EOL;
                $log['generation_bonus'] = ['entries' => count($genResult['entries']), 'total' => $genResult['total'], 'persisted' => $newCount];
            } else {
                echo "  âš ï¸�  No generation bonus entries (leaders need qualifying volume)" . PHP_EOL;
                $log['generation_bonus'] = ['entries' => 0, 'total' => 0];
            }
        } catch (\Throwable $e) {
            echo "  â�Œ Generation bonus error: " . $e->getMessage() . PHP_EOL;
            $log['generation_bonus'] = ['error' => $e->getMessage()];
        }
        echo PHP_EOL;

        // 6. INFINITY OVERRIDE
        echo "â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�" . PHP_EOL;
        echo "3/4  Infinity Override" . PHP_EOL;
        echo "â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�" . PHP_EOL;
        try {
            $infService = new \App\Services\MLM\InfinityOverrideService($pdo);
            $infPctSetting = $pdo->query("SELECT setting_value FROM mlm_settings WHERE setting_key = 'infinity_override_pct'")->fetchColumn();
            $infPct = (float)($infPctSetting ?? 1.0) / 100;
            $infResult = $infService->calculateMonthlyOverrides($periodStart, $periodEnd);
            if (!empty($infResult['entries'])) {
                $persisted = $infService->persistOverrides($infResult['entries']);
                echo "  âœ… Infinity override: " . count($infResult['entries']) . " entries, â‚¹" . number_format($infResult['total'], 2) . PHP_EOL;
                $newCount = count($persisted['created_ids'] ?? []);
                echo "     Persisted: " . $newCount . " new to mlm_infinity_overrides" . PHP_EOL;
                $log['infinity_override'] = ['entries' => count($infResult['entries']), 'total' => $infResult['total'], 'persisted' => $newCount];
            } else {
                echo "  âš ï¸�  No infinity override entries (VP+ leaders need downline sales)" . PHP_EOL;
                $log['infinity_override'] = ['entries' => 0, 'total' => 0];
            }
        } catch (\Throwable $e) {
            echo "  â�Œ Infinity override error: " . $e->getMessage() . PHP_EOL;
            $log['infinity_override'] = ['error' => $e->getMessage()];
        }
        echo PHP_EOL;

        // 7. MATCHING BONUS
        echo "â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�" . PHP_EOL;
        echo "4/4  Matching Bonus" . PHP_EOL;
        echo "â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�â”�" . PHP_EOL;
        try {
            $matchService = new \App\Services\MLM\MatchingBonusService($pdo);
            $matchResult = $matchService->calculateMonthlyMatching($periodStart, $periodEnd);
            if (!empty($matchResult['entries'])) {
                $persisted = $matchService->persistMatchingBonuses($matchResult['entries']);
                echo "  âœ… Matching bonus: " . count($matchResult['entries']) . " entries, â‚¹" . number_format($matchResult['total'], 2) . PHP_EOL;
                $newCount = count($persisted['created_ids'] ?? []);
                echo "     Persisted: " . $newCount . " new to mlm_matching_bonuses" . PHP_EOL;
                $log['matching_bonus'] = ['entries' => count($matchResult['entries']), 'total' => $matchResult['total'], 'persisted' => $newCount];
            } else {
                echo "  âš ï¸�  No matching bonus entries (leaders need earnings to match)" . PHP_EOL;
                $log['matching_bonus'] = ['entries' => 0, 'total' => 0];
            }
        } catch (\Throwable $e) {
            echo "  â�Œ Matching bonus error: " . $e->getMessage() . PHP_EOL;
            $log['matching_bonus'] = ['error' => $e->getMessage()];
        }
        echo PHP_EOL;
    }

    // â”€â”€ SUMMARY â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
    $elapsed = round(microtime(true) - $startTime, 2);

    echo "â•”â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•—" . PHP_EOL;
    echo "â•‘    SUMMARY                                              â•‘" . PHP_EOL;
    echo "â• â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•£" . PHP_EOL;

    if ($mode === 'daily') {
        $pCount = $log['penalties']['penalties_applied'] ?? 0;
        $pAmt   = $log['penalties']['total_penalty'] ?? 0;
        $cCount = $log['clawback']['processed'] ?? 0;
        $cAmt   = $log['clawback']['amount'] ?? 0;
        $rProm  = $log['rank_promotions']['promoted'] ?? 0;
        echo "â•‘  Penalties:    {$pCount} installments, â‚¹" . number_format($pAmt, 0) . PHP_EOL;
        echo "â•‘  Clawback:     {$cCount} entries, â‚¹" . number_format($cAmt, 0) . PHP_EOL;
        echo "â•‘  Promotions:   {$rProm} associates" . PHP_EOL;
    } else {
        $gbEntries = $log['generation_bonus']['entries'] ?? 0;
        $gbTotal   = $log['generation_bonus']['total'] ?? 0;
        $ioEntries = $log['infinity_override']['entries'] ?? 0;
        $ioTotal   = $log['infinity_override']['total'] ?? 0;
        $mbEntries = $log['matching_bonus']['entries'] ?? 0;
        $mbTotal   = $log['matching_bonus']['total'] ?? 0;
        echo "â•‘  Generation:   {$gbEntries} entries, â‚¹" . number_format($gbTotal, 0) . PHP_EOL;
        echo "â•‘  Infinity:     {$ioEntries} entries, â‚¹" . number_format($ioTotal, 0) . PHP_EOL;
        echo "â•‘  Matching:     {$mbEntries} entries, â‚¹" . number_format($mbTotal, 0) . PHP_EOL;
    }

    echo "â•‘  Elapsed:      {$elapsed}s" . PHP_EOL;
    echo "â•šâ•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�â•�" . PHP_EOL;

    // Write to log file
    $logFile = $root . '/storage/logs/commission_cron.log';
    $logDir = dirname($logFile);
    if (!is_dir($logDir)) { @mkdir($logDir, 0775, true); }
    $logEntry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'mode'      => $mode,
        'elapsed'   => $elapsed,
        'results'   => $log,
    ];
    @file_put_contents($logFile, date('Y-m-d H:i:s') . " | " . strtoupper($mode) . " | " . json_encode($logEntry) . PHP_EOL, FILE_APPEND | LOCK_EX);

    echo PHP_EOL . "[âœ“] Log written to storage/logs/commission_cron.log" . PHP_EOL;

} catch (\Throwable $e) {
    echo PHP_EOL . "â�Œ FATAL: " . $e->getMessage() . PHP_EOL;
    echo "   File: " . $e->getFile() . ":" . $e->getLine() . PHP_EOL;
    exit(1);
}?>