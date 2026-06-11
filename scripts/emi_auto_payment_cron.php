<?php
/**
 * EMI Auto-Payment Cron Script
 *
 * Standalone CLI script to process due EMI payments via Razorpay mandates.
 * Can also be called via HTTP with CRON_SECRET key auth.
 *
 * CLI:   php scripts/emi_auto_payment_cron.php
 * HTTP:  GET /cron/emi-auto-payment?key={CRON_SECRET}
 *
 * Exit code: 0 = success, 1 = failure
 */

$root = dirname(__DIR__);

// Bootstrap framework
require_once $root . '/config/bootstrap.php';

// HTTP key auth
$isHttp = php_sapi_name() !== 'cli';
if ($isHttp) {
    header('Content-Type: application/json');
    $expectedKey = $_ENV['CRON_SECRET'] ?? 'dev-cron-key';
    $providedKey = $_GET['key'] ?? $_SERVER['HTTP_X_CRON_KEY'] ?? '';
    if (!hash_equals($expectedKey, $providedKey)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Unauthorized']);
        exit(1);
    }
}

// Ensure storage/logs directory exists
$logDir = ($root ?: dirname(__DIR__)) . '/storage/logs';
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}

$logFile = $logDir . '/emi-cron-' . date('Y-m-d') . '.log';

function emiLog(string $msg): void
{
    static $logPath = null;
    if ($logPath === null) {
        $root = dirname(__DIR__);
        $logDir = $root . '/storage/logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        $logPath = $logDir . '/emi-cron-' . date('Y-m-d') . '.log';
    }
    $line = '[' . date('Y-m-d H:i:s') . '] ' . $msg . PHP_EOL;
    file_put_contents($logPath, $line, FILE_APPEND | LOCK_EX);
}

emiLog("=== EMI Auto-Payment Cron Started ===");

try {
    $service = new \App\Services\Payment\EMIAutoPaymentService();
    $result = $service->processDueEmiPayments();

    $summary = sprintf(
        "Processed: %d | Failed: %d | Skipped: %d",
        $result['processed'],
        $result['failed'],
        $result['skipped']
    );
    emiLog("Result: {$summary}");

    if (!empty($result['results'])) {
        foreach ($result['results'] as $r) {
            $line = sprintf(
                "  Installment #%d (Booking %s): %s — ₹%s",
                $r['installment_id'] ?? 0,
                $r['booking_number'] ?? '?',
                $r['status'] ?? 'unknown',
                number_format($r['amount'] ?? 0, 2)
            );
            if (isset($r['error'])) {
                $line .= " [Error: {$r['error']}]";
            }
            emiLog($line);
        }
    }

    emiLog("=== EMI Auto-Payment Cron Completed ===");

    if ($isHttp) {
        echo json_encode($result);
    }
    exit(0);
} catch (Exception $e) {
    emiLog("FATAL: " . $e->getMessage());
    emiLog("=== EMI Auto-Payment Cron FAILED ===");

    if ($isHttp) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit(1);
}
