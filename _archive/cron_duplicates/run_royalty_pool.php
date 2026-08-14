#!/usr/bin/env php
<?php
/**
 * Royalty Pool Distribution Cron Script
 *
 * Monthly cron to distribute 2% royalty pool to qualified Site Managers.
 * Uses HybridCommissionEngine::distributeRoyaltyPool() â€” no reimplementation.
 *
 * Tables involved:
 *   mlm_royalty_pool          â€” monthly accumulator (written by contributeToRoyaltyPool)
 *   mlm_royalty_contributions â€” per-booking audit trail
 *   mlm_commission_ledger     â€” distribution entries
 *
 * Usage:
 *   php scripts/run_royalty_pool.php                          # Distribute current month
 *   php scripts/run_royalty_pool.php --month=2026-06          # Distribute specific month
 *   php scripts/run_royalty_pool.php --status                 # Show pool status only
 */

$root = dirname(__DIR__);
$config = require $root . '/config/database.php';
if (!defined('APP_ROOT')) define('APP_ROOT', $root);
require_once $root . '/app/Core/Autoloader.php';
$autoloader = \App\Core\Autoloader::getInstance();

$targetMonth = null;
$statusOnly = false;
foreach ($argv as $arg) {
    if (strpos($arg, '--month=') === 0) {
        $targetMonth = substr($arg, 9);
    }
    if ($arg === '--status') {
        $statusOnly = true;
    }
}
$targetMonth = $targetMonth ?: date('Y-m');

echo "=== Royalty Pool Distribution ===\n";
echo "Month: {$targetMonth}\n\n";

try {
    $pdo = new PDO(
        "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4",
        $config['username'],
        $config['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}

$engine = new \App\Services\HybridCommissionEngine($pdo);

// 1. Show current pool status
$status = $engine->getRoyaltyPoolStatus($targetMonth);
if (!empty($status)) {
    echo "Pool Total:       â‚¹" . number_format($status['total_pool_amount'] ?? 0) . "\n";
    echo "Status:           " . ($status['distributed_status'] ?? 'unknown') . "\n";
    echo "Qualified Mgrs:   " . ($status['total_qualified_managers'] ?? 0) . "\n";
    echo "Per Manager Share: â‚¹" . number_format($status['per_manager_share'] ?? 0) . "\n";
    if (!empty($status['distributed_at'])) {
        echo "Distributed At:   " . $status['distributed_at'] . "\n";
    }
} else {
    echo "No pool record found for {$targetMonth}.\n";
    echo "Pool accumulates automatically when bookings are processed.\n";
}

if ($statusOnly) {
    exit(0);
}

// 2. Check if already distributed
if (!empty($status) && ($status['distributed_status'] ?? '') === 'distributed') {
    echo "\nAlready distributed for {$targetMonth}. Skipping.\n";
    exit(0);
}

// 3. Check pool has contributions
if (empty($status) || (float)($status['total_pool_amount'] ?? 0) <= 0) {
    echo "\nPool is empty for {$targetMonth}. Nothing to distribute.\n";
    echo "Pool accumulates when HybridCommissionEngine::contributeToRoyaltyPool() is called.\n";
    exit(0);
}

// 4. Distribute
echo "\nDistributing pool...\n";
$result = $engine->distributeRoyaltyPool($targetMonth);

if ($result['success']) {
    echo "\nâœ“ Distribution successful!\n";
    echo "  Pool Amount:        â‚¹" . number_format($result['pool_amount']) . "\n";
    echo "  Qualified Managers: " . $result['qualified_managers'] . "\n";
    echo "  Per Manager Share:  â‚¹" . number_format($result['per_share']) . "\n";
    echo "  Ledger Entries:     " . count($result['ledger_ids']) . "\n";
} else {
    echo "\nâœ— Distribution failed: " . ($result['error'] ?? 'unknown error') . "\n";
    exit(1);
}?>