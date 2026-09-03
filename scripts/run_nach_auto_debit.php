<?php
/**
 * Cron: Process NACH auto-debits for due EMIs
 * Run daily: php scripts/run_nach_auto_debit.php
 */

$root = dirname(__DIR__);
require_once $root . '/app/Core/Autoloader.php';
new \App\Core\Autoloader();

try {
    $service = new \App\Services\Sales\BookingLifecycleService();
    $result = $service->processNachAutoDebits();

    $ts = date('Y-m-d H:i:s');
    echo "[$ts] NACH Auto-Debit: processed={$result['processed']}, failed={$result['failed']}\n";

    foreach ($result['results'] as $r) {
        echo "  Mandate #{$r['mandate_id']}: ₹{$r['amount']} â€” {$r['status']}\n";
    }

    if ($result['processed'] === 0 && $result['failed'] === 0) {
        echo "  No mandates due for debit today.\n";
    }
} catch (Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}?>