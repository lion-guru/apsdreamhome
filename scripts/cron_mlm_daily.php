<?php
/**
 * APS Dream Home - MLM Daily Automation Cron
 * Rank promotions + commission clawbacks + payout processing
 *
 * Usage:
 *   php scripts/cron_mlm_daily.php              (normal run)
 *   php scripts/cron_mlm_daily.php --dry-run    (no DB writes)
 *
 * Exit codes: 0 = success, 1 = fatal error, 2 = partial failure
 * Schedule: daily at 02:00 via Windows Task Scheduler or cron
 */

if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    exit("CLI only.\n");
}

$dryRun = in_array('--dry-run', $argv ?? [], true);
$root = dirname(__DIR__);
require $root . '/config/bootstrap.php';

$started = microtime(true);
$result = [
    'started_at'       => date('Y-m-d H:i:s'),
    'dry_run'          => $dryRun,
    'rank_promotions'  => 0,
    'clawbacks'        => 0,
    'payouts_processed'=> 0,
    'errors'           => [],
];

try {
    $svc = new \App\Services\MLM\MLMCommissionEngine();
} catch (\Throwable $e) {
    fwrite(STDERR, "FATAL: Cannot construct MLMCommissionEngine: " . $e->getMessage() . "\n");
    exit(1);
}

$cronId = null;
if (!$dryRun) {
    try {
        $cronId = $svc->startCronLog('daily', date('Y-m-d'));
    } catch (\Throwable $e) {
        $result['errors'][] = 'startCronLog: ' . $e->getMessage();
    }
}

// --- 1. Rank promotions ---
fwrite(STDOUT, "=== Rank Promotions ===\n");
try {
    $promotions = $dryRun ? [] : $svc->runRankPromotions();
    $result['rank_promotions'] = is_array($promotions) ? count($promotions) : (int)$promotions;
    if (!empty($promotions)) {
        foreach ($promotions as $p) {
            $line = "  PROMOTED user #{$p['user_id']}: {$p['from_rank']} -> {$p['new_rank']}";
            if (!$dryRun) { fwrite(STDOUT, $line . "\n"); }
        }
    } else {
        fwrite(STDOUT, "  No promotions to process.\n");
    }
} catch (\Throwable $e) {
    $result['errors'][] = 'runRankPromotions: ' . $e->getMessage();
    fwrite(STDERR, "  FAILED: " . $e->getMessage() . "\n");
}

// --- 2. Commission clawbacks ---
fwrite(STDOUT, "\n=== Commission Clawbacks ===\n");
try {
    $clawbacks = $dryRun ? ['processed' => 0, 'amount' => 0] : $svc->processClawbacks(30);
    $result['clawbacks'] = $clawbacks['processed'] ?? 0;
    $result['clawback_amount'] = $clawbacks['amount'] ?? 0;
    if ($result['clawbacks'] > 0) {
        fwrite(STDOUT, "  Clawed back {$result['clawbacks']} commissions totaling ₹" . number_format($result['clawback_amount'], 2) . "\n");
    } else {
        fwrite(STDOUT, "  No clawbacks to process (no 30+ day overdue installments).\n");
    }
} catch (\Throwable $e) {
    $result['errors'][] = 'processClawbacks: ' . $e->getMessage();
    fwrite(STDERR, "  FAILED: " . $e->getMessage() . "\n");
}

// --- 3. Payout batch auto-approve ---
fwrite(STDOUT, "\n=== Payout Processing ===\n");
try {
    $stats = $svc->getDashboardStats();
    $batches = $stats['payout_batches'] ?? [];
    $approved = 0;
    foreach ($batches as $b) {
        if (($b['status'] ?? '') === 'draft' && !$dryRun) {
            $svc->approvePayoutBatch((int)$b['id'], 0);
            $approved++;
        }
    }
    $result['payouts_processed'] = $approved;
    if ($approved > 0) {
        fwrite(STDOUT, "  Auto-approved {$approved} draft payout batch(es).\n");
    } else {
        fwrite(STDOUT, "  No draft payout batches to process.\n");
    }
} catch (\Throwable $e) {
    $result['errors'][] = 'payouts: ' . $e->getMessage();
    fwrite(STDERR, "  FAILED: " . $e->getMessage() . "\n");
}

// --- Summary ---
$duration = (int)round((microtime(true) - $started) * 1000);
$result['duration_ms'] = $duration;
$result['finished_at'] = date('Y-m-d H:i:s');
$result['status'] = empty($result['errors']) ? 'success' : 'partial';

fwrite(STDOUT, "\n=== Summary ===\n");
fwrite(STDOUT, "Rank promotions: {$result['rank_promotions']}\n");
fwrite(STDOUT, "Clawbacks: {$result['clawbacks']} (₹" . number_format($result['clawback_amount'] ?? 0, 2) . ")\n");
fwrite(STDOUT, "Payouts processed: {$result['payouts_processed']}\n");
fwrite(STDOUT, "Duration: {$duration}ms\n");
fwrite(STDOUT, "Status: {$result['status']}\n");

if (!$dryRun && $cronId !== null) {
    try {
        $svc->finishCronLog($cronId, $result);
    } catch (\Throwable $e) {
        $result['errors'][] = 'finishCronLog: ' . $e->getMessage();
    }
}

if (!empty($result['errors'])) {
    fwrite(STDERR, "\nErrors:\n");
    foreach ($result['errors'] as $err) {
        fwrite(STDERR, "  - {$err}\n");
    }
}

exit($result['status'] === 'success' ? 0 : 2);
