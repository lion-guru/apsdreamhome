<?php
/**
 * Cron job: Reconcile Razorpay orders with our local payment_orders table.
 *
 * Why this exists:
 *   Webhooks are not 100% reliable. Networks fail, Razorpay retries can be
 *   lost, our app can be down at the wrong moment. This cron is the safety
 *   net: every N hours it walks all `created` payment_orders older than
 *   ~30 minutes (the standard Razorpay order expiry window), calls
 *   Razorpay's GET /orders/{id} API, and updates the local row to match.
 *
 * What it does:
 *   1. SELECT payment_orders WHERE status='created' AND created_at < NOW()-30min
 *   2. For each, call RazorpayService::fetchOrder()
 *   3. Diff API status with our DB status and apply the transition:
 *        paid        -> mark paid, set paid_at + payment_id, insert into payments
 *        expired     -> mark expired (no payment record)
 *        cancelled   -> mark cancelled (no payment record)
 *        attempted   -> leave as 'created' (user started checkout, didn't pay yet)
 *        created     -> no change (still waiting)
 *   4. Log every call to gateway_logs with action='reconciliation'
 *   5. Print + log a stats summary
 *
 * Idempotency:
 *   The UPDATE in step 3 is conditional on `status='created'`, so re-running
 *   the cron against the same row is a no-op once the row transitions.
 *
 * CLI usage:
 *   C:\xampp\php\php.exe scripts/payment_reconciliation_cron.php
 *
 * Flags:
 *   --min-age-minutes=N   Override the 30-min default (e.g. 15 for testing)
 *   --limit=N             Process at most N orders (default 500)
 *   --dry-run             Read orders + call API but skip the UPDATE/INSERT
 *   --gateway=razorpay    Only reconcile this gateway (default razorpay)
 *   --log-file=PATH       Override the log file path
 *
 * Schedule (Windows Task Scheduler / Linux cron):
 *   Every 2 hours during business hours, every 6 hours overnight
 *   Example:  0 8,10,12,14,16,18,20,22 * * *
 *
 * Exit codes:
 *   0  success (no errors)
 *   1  unrecoverable failure (DB down, autoloader missing, etc.)
 *   2  partial success (some rows failed; see log)
 */

declare(strict_types=1);

// ----- 1. Parse CLI flags -------------------------------------------------

$opts = [
    'min-age-minutes' => 30,
    'limit'           => 500,
    'dry-run'         => false,
    'gateway'         => 'razorpay',
    'log-file'        => null,
];

foreach (array_slice($argv, 1) as $arg) {
    if ($arg === '--dry-run') { $opts['dry-run'] = true; continue; }
    if (preg_match('/^--([a-z0-9-]+)=(.*)$/i', $arg, $m)) {
        $key = strtolower($m[1]);
        if (array_key_exists($key, $opts)) {
            $opts[$key] = is_numeric($opts[$key]) ? (int)$m[2] : $m[2];
        }
    }
}
$opts['min-age-minutes'] = max(1, (int)$opts['min-age-minutes']);
$opts['limit']           = max(1, min(5000, (int)$opts['limit']));

// ----- 2. Bootstrap: env + autoloader + DB -------------------------------

$root = dirname(__DIR__);
if (!defined('APP_ROOT')) define('APP_ROOT', $root);

try {
    $envFile = $root . '/.env';
    if (is_file($envFile)) {
        foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $line = trim($line);
            if ($line === '' || $line[0] === '#' || strpos($line, '=') === false) continue;
            [$k, $v] = array_map('trim', explode('=', $line, 2));
            $v = trim($v, "\"' \t");
            if (getenv($k) === false) putenv("$k=$v");
            $_ENV[$k] = $v;
        }
    }
} catch (\Throwable $e) { /* non-fatal */ }

// PSR-4 autoloader for App\* (mirrors web/index.php)
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/../app/';
    if (strpos($class, $prefix) !== 0) return;
    $relative = substr($class, strlen($prefix));
    $file = $baseDir . str_replace('\\', '/', $relative) . '.php';
    if (is_file($file)) require_once $file;
});

chdir($root);

$logFile = $opts['log-file'] ?: $root . '/logs/payment_reconciliation.log';
$logDir  = dirname($logFile);
if (!is_dir($logDir)) @mkdir($logDir, 0775, true);

$start = microtime(true);

function logMsg(string $msg) {
    global $logFile, $start;
    $elapsed = number_format(microtime(true) - $start, 2);
    $line = '[' . date('Y-m-d H:i:s') . " +{$elapsed}s] " . $msg . PHP_EOL;
    echo $line;
    @file_put_contents($logFile, $line, FILE_APPEND);
}

// In dev/CI, default to test mode unless explicitly set, so we don't
// hammer the real Razorpay API. Production deploys should set
// RAZORPAY_TEST_MODE=false in .env.
if (getenv('RAZORPAY_TEST_MODE') === false || getenv('RAZORPAY_TEST_MODE') === '') {
    putenv('RAZORPAY_TEST_MODE=true');
    $_ENV['RAZORPAY_TEST_MODE'] = 'true';
}

try {
    $pdo = new PDO(
        'mysql:host=' . (getenv('DB_HOST') ?: '127.0.0.1')
        . ';port=' . (getenv('DB_PORT') ?: '3307')
        . ';dbname=' . (getenv('DB_NAME') ?: 'apsdreamhome')
        . ';charset=utf8mb4',
        getenv('DB_USER') ?: 'root',
        getenv('DB_PASS') ?: '',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Align PHP timezone with the application (env says Asia/Kolkata, which
    // matches the MySQL server's session timezone). This is critical because
    // `created_at` columns are stored as DATETIME without TZ and compared
    // via `created_at < ?`. If PHP uses Europe/Berlin and MySQL uses IST,
    // the cutoff string PHP builds will be hours off.
    $tz = getenv('APP_TIMEZONE') ?: 'Asia/Kolkata';
    if (!@date_default_timezone_set($tz)) {
        date_default_timezone_set('UTC');
    }
    $pdo->exec("SET time_zone = '+05:30'");
} catch (\Throwable $e) {
    fwrite(STDERR, "DB connection failed: " . $e->getMessage() . PHP_EOL);
    @file_put_contents($logFile, '[' . date('Y-m-d H:i:s') . "] DB connection failed: " . $e->getMessage() . PHP_EOL, FILE_APPEND);
    exit(1);
}

// Set tenant context for TenantContext consumers
$cronTenantId = 1;
if (class_exists('\App\Core\Middleware\TenantContext')) {
    \App\Core\Middleware\TenantContext::setById($cronTenantId, $pdo);
}
$cronTenantSql = $cronTenantId > 1 ? " AND tenant_id = " . (int)$cronTenantId : "";
$cronTenantCol = $cronTenantId > 1 ? ", tenant_id" : "";
$cronTenantVal = $cronTenantId > 1 ? ", " . (int)$cronTenantId : "";

// Verify gateway_logs has the columns we need (safe additive migration)
try {
    $cols = [];
    foreach ($pdo->query('DESCRIBE gateway_logs') as $r) {
        $cols[strtolower($r['Field'])] = true;
    }
    $needed = [
        'action'         => "VARCHAR(100) NULL AFTER gateway",
        'amount_paise'   => "BIGINT(20) UNSIGNED NULL",
        'transaction_id' => "VARCHAR(80) NULL",
    ];
    foreach ($needed as $col => $def) {
        if (empty($cols[$col])) {
            $pdo->exec("ALTER TABLE gateway_logs ADD COLUMN `{$col}` {$def}");
            logMsg("  + ADD COLUMN gateway_logs.{$col}");
        }
    }
} catch (\Throwable $e) {
    logMsg("WARN: could not verify gateway_logs columns: " . $e->getMessage());
}

// ----- 3. Load RazorpayService ------------------------------------------

try {
    if (!class_exists('App\\Services\\Gateway\\RazorpayService')) {
        throw new \RuntimeException('RazorpayService class not found (autoloader not registered)');
    }
    $razorpay = new \App\Services\Gateway\RazorpayService($pdo, function ($method, $path, $status, $code, $duration) {
        // No-op: logCall in RazorpayService already writes to gateway_logs
    });
} catch (\Throwable $e) {
    logMsg("FATAL: " . $e->getMessage());
    exit(1);
}

// ----- 4. Find candidates -------------------------------------------------

$cutoff = date('Y-m-d H:i:s', time() - $opts['min-age-minutes'] * 60);

try {
    $sql = "SELECT id, order_id, gateway, booking_id, user_id, customer_email, customer_phone,
                   amount, currency, status, created_at, expires_at
              FROM payment_orders
             WHERE status = 'created'
               AND gateway = ?{$cronTenantSql}
               AND created_at < ?
             ORDER BY id ASC
             LIMIT " . (int)$opts['limit'];
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$opts['gateway'], $cutoff]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (\Throwable $e) {
    logMsg("FATAL: select failed: " . $e->getMessage());
    exit(1);
}

logMsg("=== Payment reconciliation cron started ===");
logMsg("Cutoff: {$opts['min-age-minutes']} min ago ({$cutoff})");
logMsg("Gateway: {$opts['gateway']}");
logMsg("Limit: {$opts['limit']} | Dry-run: " . ($opts['dry-run'] ? 'yes' : 'no'));
logMsg("Candidates: " . count($orders));

if (count($orders) === 0) {
    logMsg("Nothing to do. Exiting cleanly.");
    exit(0);
}

// ----- 5. Reconcile each order -------------------------------------------

$stats = [
    'total'        => count($orders),
    'paid'         => 0,
    'expired'      => 0,
    'cancelled'    => 0,
    'still_created'=> 0,
    'attempted'    => 0,
    'errors'       => 0,
    'unchanged'    => 0,
    'api_failures' => 0,
];

foreach ($orders as $o) {
    $orderId   = $o['order_id'];
    $dbStatus  = $o['status'];
    $localId   = (int)$o['id'];

    logMsg("  -> {$orderId} (local id {$localId}, amount {$o['amount']} {$o['currency']})");

    $resp = $razorpay->fetchOrder($orderId);
    if (empty($resp['success']) || !is_array($resp['data'])) {
        $stats['api_failures']++;
        $stats['errors']++;
        logMsg("     ! API call failed: " . ($resp['error'] ?? 'unknown'));
        if (!$opts['dry-run']) {
            logReconciliation($pdo, 'razorpay', 'reconciliation', $orderId, 'failed',
                $resp['status'] ?? 0, null, $resp['error'] ?? 'API call failed');
        }
        continue;
    }

    $apiStatus = (string)($resp['data']['status'] ?? 'unknown');
    $apiAmountPaidPaise = (int)($resp['data']['amount_paid'] ?? 0);
    $apiAmountDuePaise  = (int)($resp['data']['amount_due']  ?? 0);
    $apiPaymentId = $resp['data']['id'] ?? null; // order id from API (echo)
    // Note: Razorpay doesn't include payment_id on the order; it comes from a payment object
    $paymentsList = $resp['data']['payments'] ?? null;

    logMsg("     API status: {$apiStatus} (paid {$apiAmountPaidPaise} / due {$apiAmountDuePaise})");

    if ($apiStatus === $dbStatus) {
        $stats['unchanged']++;
        logMsg("     = no change (both are '{$apiStatus}')");
        if (!$opts['dry-run']) {
            logReconciliation($pdo, 'razorpay', 'reconciliation_unchanged', $orderId, 'success',
                200, $apiAmountPaidPaise, null);
        }
        continue;
    }

    if ($opts['dry-run']) {
        logMsg("     [dry-run] would update {$dbStatus} -> {$apiStatus}");
        $stats['unchanged']++; // count as no-op
        continue;
    }

    try {
        $pdo->beginTransaction();

        if ($apiStatus === 'paid') {
            // 1) mark the order as paid
            $upd = $pdo->prepare("UPDATE payment_orders
                                      SET status = 'paid',
                                          paid_at = COALESCE(paid_at, NOW()),
                                          updated_at = NOW()
                                    WHERE 1=1{$cronTenantSql}
                                      AND id = ?
                                      AND status = 'created'");
            $upd->execute([$localId]);

            // 2) if a payment_id is known, store it
            $firstPaymentId = null;
            if (is_array($paymentsList) && !empty($paymentsList['items'][0]['id'])) {
                $firstPaymentId = $paymentsList['items'][0]['id'];
                $pdo->prepare("UPDATE payment_orders SET payment_id = ? WHERE 1=1{$cronTenantSql} AND id = ? AND payment_id IS NULL")
                    ->execute([$firstPaymentId, $localId]);
            }

            // 3) insert into payments table (one row per order, idempotent on order_id)
            $exists = $pdo->prepare("SELECT id FROM payments WHERE gateway = 'razorpay' AND gateway_transaction_id = ?");
            $exists->execute([$orderId]);
            if (!$exists->fetchColumn()) {
                $totalAmount = (float)$o['amount']; // rupees
                $ins = $pdo->prepare("INSERT INTO payments
                    (payment_id, transaction_id, customer_id, booking_id, property_type, payment_type,
                     amount, currency, tax_amount, discount_amount, total_amount, gateway,
                     gateway_transaction_id, status, payment_date, payment_time,
                     description, user_id, created_at, updated_at{$cronTenantCol})
                    VALUES (?, ?, ?, ?, 'plot', 'booking',
                            ?, ?, 0, 0, ?, 'razorpay',
                            ?, 'completed', CURDATE(), CURTIME(),
                            ?, ?, NOW(), NOW(){$cronTenantVal})");
                $ins->execute([
                    $firstPaymentId ?: $orderId, // payment_id
                    $orderId,                     // transaction_id = order id for traceability
                    $o['user_id'] ?: null,
                    $o['booking_id'] ?: null,
                    $totalAmount,
                    $o['currency'] ?: 'INR',
                    $totalAmount,
                    $orderId,                     // gateway_transaction_id (unique via order_id)
                    'Auto-created by reconciliation cron for order ' . $orderId,
                    $o['user_id'] ?: null,
                ]);
            }

            $stats['paid']++;
            logMsg("     + marked PAID, payments row " . ($exists->fetchColumn() ?: 'created'));
            logReconciliation($pdo, 'razorpay', 'reconciliation_paid', $orderId, 'success', 200, $apiAmountPaidPaise, null);

        } elseif ($apiStatus === 'expired') {
            $pdo->prepare("UPDATE payment_orders SET status = 'expired', updated_at = NOW() WHERE 1=1{$cronTenantSql} AND id = ? AND status = 'created'")
                ->execute([$localId]);
            $stats['expired']++;
            logMsg("     + marked EXPIRED");
            logReconciliation($pdo, 'razorpay', 'reconciliation_expired', $orderId, 'success', 200, null, null);

        } elseif ($apiStatus === 'cancelled') {
            $pdo->prepare("UPDATE payment_orders SET status = 'cancelled', updated_at = NOW() WHERE 1=1{$cronTenantSql} AND id = ? AND status = 'created'")
                ->execute([$localId]);
            $stats['cancelled']++;
            logMsg("     + marked CANCELLED");
            logReconciliation($pdo, 'razorpay', 'reconciliation_cancelled', $orderId, 'success', 200, null, null);

        } elseif ($apiStatus === 'attempted') {
            $stats['attempted']++;
            logMsg("     ~ still 'attempted' (user started checkout, not paid). Leaving row as 'created'.");
            logReconciliation($pdo, 'razorpay', 'reconciliation_attempted', $orderId, 'success', 200, $apiAmountPaidPaise, null);
            // intentional no-op: will be re-checked next cron run

        } else {
            $stats['unchanged']++;
            logMsg("     ? unhandled API status '{$apiStatus}', no change made");
            logReconciliation($pdo, 'razorpay', 'reconciliation_unknown', $orderId, 'success', 200, null, 'unhandled api status: ' . $apiStatus);
        }

        $pdo->commit();
    } catch (\Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $stats['errors']++;
        logMsg("     X DB error: " . $e->getMessage());
        logReconciliation($pdo, 'razorpay', 'reconciliation_db_error', $orderId, 'failed', 0, null, $e->getMessage());
    }
}

// ----- 6. Summary --------------------------------------------------------

logMsg("=== Summary ===");
logMsg("  total candidates: {$stats['total']}");
logMsg("  -> paid:          {$stats['paid']}");
logMsg("  -> expired:       {$stats['expired']}");
logMsg("  -> cancelled:     {$stats['cancelled']}");
logMsg("  -> attempted:     {$stats['attempted']} (left as 'created')");
logMsg("  -> unchanged:     {$stats['unchanged']}");
logMsg("  -> API failures:  {$stats['api_failures']}");
logMsg("  -> errors:        {$stats['errors']}");

$elapsed = round(microtime(true) - $start, 2);
logMsg("Elapsed: {$elapsed}s");

exit($stats['errors'] > 0 ? 2 : 0);

// ========================================================================
// helper: insert a gateway_logs row summarising one reconciliation action
// ========================================================================
function logReconciliation(PDO $pdo, string $gateway, string $action, string $orderId, string $status, int $httpCode, ?int $amountPaise, ?string $error): void
{
    global $cronTenantCol, $cronTenantVal;
    try {
        $stmt = $pdo->prepare("INSERT INTO gateway_logs
            (gateway, action, method, endpoint, transaction_id, status, response_code, amount_paise, error_message{$cronTenantCol})
            VALUES (?, ?, 'GET', ?, ?, ?, ?, ?, ?{$cronTenantVal})");
        $stmt->execute([
            $gateway,
            $action,
            '/orders/' . $orderId,
            $orderId,
            $status,
            $httpCode,
            $amountPaise,
            $error,
        ]);
    } catch (\Throwable $e) {
        // swallow: never let logging kill the cron
        @file_put_contents(__DIR__ . '/../logs/payment_reconciliation.log',
            '[' . date('Y-m-d H:i:s') . "] logReconciliation failed: " . $e->getMessage() . PHP_EOL, FILE_APPEND);
    }
}
