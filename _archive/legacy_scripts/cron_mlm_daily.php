<?php
/**
 * APS Dream Home - Module 4: MLM Commission Engine
 * Daily cron: rank promotions + clawback processing
 *
 * Usage:
 *   php scripts/cron_mlm_daily.php              (normal run)
 *   php scripts/cron_mlm_daily.php --dry-run    (no DB writes)
 *
 * Exit codes: 0 = success, 1 = fatal error, 2 = partial failure
 */

if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    exit("This script must be run from the command line.\n");
}

$dryRun = in_array('--dry-run', $argv ?? [], true);

$root = dirname(__DIR__);
require $root . '/config/bootstrap.php';

$started = microtime(true);
$result = [
    'started_at'  => date('Y-m-d H:i:s'),
    'dry_run'     => $dryRun,
    'rank_promotions' => 0,
    'clawbacks'        => 0,
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

// --- 1. Rank promotions -----------------------------------------------------
try {
    $promotions = $dryRun ? [] : $svc->runRankPromotions();
    $result['rank_promotions'] = is_array($promotions) ? count($promotions) : (int)$promotions;
    if (!empty($promotions) && !$dryRun) {
        fwrite(STDOUT, "Rank promotions: " . $result['rank_promotions'] . "\n");
        foreach ($promotions as $p) {
            fwrite(STDOUT, "  + #" . ($p['user_id'] ?? '?') . " -> " . ($p['new_rank'] ?? '?') . "\n");
        }
    }
} catch (\Throwable $e) {
    $result['errors'][] = 'runRankPromotions: ' . $e->getMessage();
    fwrite(STDERR, "rank promotions failed: " . $e->getMessage() . "\n");
}

// --- 2. Clawback processing -------------------------------------------------
try {
    $clawbacks = $dryRun ? [] : $svc->processClawbacks();
    $result['clawbacks'] = is_array($clawbacks) ? count($clawbacks) : (int)$clawbacks;
    if (!empty($clawbacks) && !$dryRun) {
        fwrite(STDOUT, "Clawbacks processed: " . $result['clawbacks'] . "\n");
    }
} catch (\Throwable $e) {
    $result['errors'][] = 'processClawbacks: ' . $e->getMessage();
    fwrite(STDERR, "clawback processing failed: " . $e->getMessage() . "\n");
}

// --- 3. Payout processing (auto-approve any draft batch for current month) --
$payoutsProcessed = 0;
try {
    $batches = $svc->getDashboardStats()['payout_batches'] ?? [];
    foreach ($batches as $b) {
        if (($b['status'] ?? '') === 'draft' && !$dryRun) {
            $svc->approvePayoutBatch((int)$b['id'], (int)($_SESSION['admin_id'] ?? 0));
            $payoutsProcessed++;
        }
    }
} catch (\Throwable $e) {
    $result['errors'][] = 'payouts: ' . $e->getMessage();
}
$result['payouts_processed'] = $payoutsProcessed;

$duration = (int)round((microtime(true) - $started) * 1000);
$result['duration_ms']  = $duration;
$result['finished_at']  = date('Y-m-d H:i:s');
$result['status']       = empty($result['errors']) ? 'success' : 'partial';

if (!$dryRun && $cronId !== null) {
    try {
        $svc->finishCronLog($cronId, $result);
    } catch (\Throwable $e) {
        $result['errors'][] = 'finishCronLog: ' . $e->getMessage();
    }
}

echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";

exit($result['status'] === 'success' ? 0 : 2);?>