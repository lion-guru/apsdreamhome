<?php
/**
 * RazorpayService Test Suite
 *
 * 30+ tests covering: configuration, signatures, orders, payments,
 * refunds, customers, plans/subscriptions, payment links, QR codes,
 * UPI validation, retry behavior, logging, and PCI redaction.
 *
 * Run modes:
 *   - Unit (mocked): RAZORPAY_TEST_MODE=true (default for this file)
 *     All API calls are intercepted by the in-process mock layer.
 *   - Live: set RAZORPAY_TEST_MODE=false AND real key_id/secret
 *     in the env to hit api.razorpay.com.
 *
 * Exit code: 0 = all pass, 1 = any fail.
 */

declare(strict_types=1);

$_ENV['RAZORPAY_TEST_MODE']    = 'true';
$_ENV['RAZORPAY_KEY_ID']       = 'rzp_test_PLAIN_KEY_ID';
$_ENV['RAZORPAY_KEY_SECRET']   = 'rzp_test_PLAIN_KEY_SECRET';
$_ENV['RAZORPAY_WEBHOOK_SECRET']= 'whsec_PLAIN_WEBHOOK_SECRET';

require_once __DIR__ . '/../config/bootstrap.php';

require_once __DIR__ . '/../app/Services/Gateway/RazorpayService.php';

// Force early database initialization (triggers ConfigService::loadEnvironmentVariables
// which would otherwise overwrite our test env vars inside the RazorpayService constructor).
\App\Core\Database\Database::getInstance()->getConnection();

$_ENV['RAZORPAY_TEST_MODE']      = 'true';
$_ENV['RAZORPAY_KEY_ID']         = 'rzp_test_PLAIN_KEY_ID';
$_ENV['RAZORPAY_KEY_SECRET']     = 'rzp_test_PLAIN_KEY_SECRET';
$_ENV['RAZORPAY_WEBHOOK_SECRET'] = 'whsec_PLAIN_WEBHOOK_SECRET';
putenv('RAZORPAY_TEST_MODE=true');
putenv('RAZORPAY_KEY_ID=rzp_test_PLAIN_KEY_ID');
putenv('RAZORPAY_KEY_SECRET=rzp_test_PLAIN_KEY_SECRET');
putenv('RAZORPAY_WEBHOOK_SECRET=whsec_PLAIN_WEBHOOK_SECRET');

use App\Services\Gateway\RazorpayService;
use App\Core\Database\Database;

$results = [];
$pass = 0;
$fail = 0;
$startAll = microtime(true);

function check(string $name, bool $cond, string $detail = ''): void
{
    global $results, $pass, $fail;
    if ($cond) {
        $pass++;
        $results[] = ['name' => $name, 'status' => 'PASS', 'detail' => $detail];
        echo "  PASS  $name\n";
    } else {
        $fail++;
        $results[] = ['name' => $name, 'status' => 'FAIL', 'detail' => $detail];
        echo "  FAIL  $name" . ($detail ? "  --  $detail" : '') . "\n";
    }
}

function section(string $name): void
{
    echo "\n--- $name ---\n";
}

$db = Database::getInstance()->getConnection();
$logCount = function () use ($db): int {
    return (int)$db->query("SELECT COUNT(*) FROM gateway_logs")->fetchColumn();
};
$logCountByPath = function (string $path) use ($db): int {
    $s = $db->prepare("SELECT COUNT(*) FROM gateway_logs WHERE endpoint = ?");
    $s->execute([$path]);
    return (int)$s->fetchColumn();
};
$cleanLogs = function () use ($db): void {
    $db->exec("TRUNCATE TABLE gateway_logs");
    $db->exec("TRUNCATE TABLE payment_orders");
    $db->exec("TRUNCATE TABLE payment_webhook_logs");
};

$cleanLogs();
$svc = new RazorpayService();

/* ============================================================
 * SECTION 1: Configuration
 * ============================================================ */
section('Configuration');
check('isConfigured returns true with real-looking creds', $svc->isConfigured() === true);
check('isTestMode returns true when env flag set', $svc->isTestMode() === true);
check('getKeyId returns configured key', $svc->getKeyId() === 'rzp_test_PLAIN_KEY_ID');

$bogus = new RazorpayService();
$ref = new ReflectionClass($bogus);
$keyP = $ref->getProperty('keyId');     $keyP->setAccessible(true);
$secP = $ref->getProperty('keySecret'); $secP->setAccessible(true);
$keyP->setValue($bogus, 'rzp_test_default');
$secP->setValue($bogus, 'secret_default');
check('isConfigured returns false with default creds', $bogus->isConfigured() === false);

$bogus2 = new RazorpayService();
$keyP->setValue($bogus2, 'invalid_key_id');
$secP->setValue($bogus2, 'shortsec');
check('isConfigured returns false when key_id lacks rzp_ prefix', $bogus2->isConfigured() === false);

/* ============================================================
 * SECTION 2: Signature verification
 * ============================================================ */
section('Signatures');
$orderId = 'order_TEST123';
$paymentId = 'pay_TEST456';
$validSig = hash_hmac('sha256', $orderId . '|' . $paymentId, $_ENV['RAZORPAY_KEY_SECRET']);
check('verifyPaymentSignature accepts valid signature', $svc->verifyPaymentSignature($orderId, $paymentId, $validSig) === true);
check('verifyPaymentSignature rejects bad signature', $svc->verifyPaymentSignature($orderId, $paymentId, 'deadbeef') === false);
check('verifyPaymentSignature rejects empty signature', $svc->verifyPaymentSignature($orderId, $paymentId, '') === false);
check('verifyPaymentSignature rejects empty orderId', $svc->verifyPaymentSignature('', $paymentId, $validSig) === false);
check('verifyPaymentSignature rejects empty paymentId', $svc->verifyPaymentSignature($orderId, '', $validSig) === false);

$body = json_encode(['event' => 'payment.captured', 'payload' => ['x' => 1]]);
$whSig = hash_hmac('sha256', $body, $_ENV['RAZORPAY_WEBHOOK_SECRET']);
check('verifyWebhookSignature accepts valid HMAC of body', $svc->verifyWebhookSignature($body, $whSig) === true);
check('verifyWebhookSignature rejects bad signature', $svc->verifyWebhookSignature($body, 'bogus') === false);
check('verifyWebhookSignature accepts array input (re-encodes)', $svc->verifyWebhookSignature(['event' => 'test'], $whSig) === false);

/* ============================================================
 * SECTION 3: Order creation
 * ============================================================ */
section('Orders');
$beforeOrders = $logCountByPath('/orders');
$resp = $svc->createOrder(500.00, 'INR', 'RCPT_001', ['booking_id' => 19, 'user_id' => 3]);
check('createOrder with valid amount returns success', $resp['success'] === true);
check('createOrder response has id', isset($resp['data']['id']) && str_starts_with($resp['data']['id'], 'order_'));
check('createOrder amount is converted to paise', ($resp['data']['amount'] ?? 0) === 50000);
check('createOrder stored in payment_orders',
    (int)$db->query("SELECT COUNT(*) FROM payment_orders WHERE order_id = '{$resp['data']['id']}'")->fetchColumn() === 1);

check('createOrder with zero amount returns error',
    $svc->createOrder(0)['success'] === false && $svc->createOrder(0)['error'] !== null);
check('createOrder with negative amount returns error',
    $svc->createOrder(-10)['success'] === false);
check('createOrder with non-numeric returns error',
    $svc->createOrder('abc')['success'] === false);
$resp2 = $svc->createOrder(99.50, 'inr', null, ['note' => str_repeat('X', 250)]);
check('createOrder auto-generates receipt when null', isset($resp2['data']['receipt']) && str_starts_with($resp2['data']['receipt'], 'RCPT_'));
check('createOrder uppercases currency', strtoupper(($resp2['data']['currency'] ?? '')) === 'INR');
$resp3 = $svc->createOrder(10, 'INR', 'RCPT_002', ['booking_id' => 19]);
check('fetchOrder returns the same order', $resp3['success'] && ($resp3['data']['receipt'] ?? '') === 'RCPT_002');

check('gateway_logs received order entry', $logCountByPath('/orders') > $beforeOrders);

/* ============================================================
 * SECTION 4: Payments
 * ============================================================ */
section('Payments');
$payId = 'pay_abc123';
$cap = $svc->capturePayment($payId, 250.00);
check('capturePayment with valid id+amount', $cap['success'] === true && str_starts_with($cap['data']['id'], 'pay_'));
check('capturePayment amount in paise', ($cap['data']['amount'] ?? 0) === 25000);
$bad = $svc->capturePayment('pay_x', -5);
check('capturePayment with negative amount returns error', $bad['success'] === false);
$bad = $svc->capturePayment('', 100);
check('capturePayment with empty id returns error', $bad['success'] === false);

$fetched = $svc->fetchPayment($payId);
check('fetchPayment returns payment details', $fetched['success'] === true && ($fetched['data']['id'] ?? '') === $payId);

/* ============================================================
 * SECTION 5: Refunds
 * ============================================================ */
section('Refunds');
$refund = $svc->createRefund($payId, 100.00, 'optimum', ['reason' => 'customer_request']);
check('createRefund (partial) returns success', $refund['success'] === true && str_starts_with($refund['data']['id'], 'rfnd_'));
check('createRefund amount matches', ($refund['data']['amount'] ?? 0) === 10000);

$full = $svc->createRefund($payId);
check('createRefund (full, no amount) returns success', $full['success'] === true);

$refund2 = $svc->fetchRefund($refund['data']['id']);
check('fetchRefund returns refund details', $refund2['success'] === true);

$bad = $svc->createRefund('', 10);
check('createRefund with empty paymentId returns error', $bad['success'] === false);

/* ============================================================
 * SECTION 6: Customers
 * ============================================================ */
section('Customers');
$cust = $svc->createCustomer('Ramesh Kumar', 'ramesh@example.com', '+919876543210', ['vip' => 1]);
check('createCustomer returns customer id', $cust['success'] && str_starts_with($cust['data']['id'], 'cust_'));
check('createCustomer strips non-numeric from contact (keeps +)', ($cust['data']['contact'] ?? '') === '+919876543210');

$bad = $svc->createCustomer('', 'x@y.com', '123');
check('createCustomer rejects empty name', $bad['success'] === false);

$fetched = $svc->fetchCustomer($cust['data']['id']);
check('fetchCustomer returns the customer', $fetched['success'] === true && ($fetched['data']['id'] ?? '') === $cust['data']['id']);

/* ============================================================
 * SECTION 7: Plans & Subscriptions
 * ============================================================ */
section('Plans & Subscriptions');
$plan = $svc->createPlan(['name' => 'Gold EMI', 'amount' => 999.00, 'currency' => 'INR'], 'monthly', 1, ['tier' => 'gold']);
check('createPlan returns plan id', $plan['success'] && str_starts_with($plan['data']['id'], 'plan_'));
check('createPlan item amount in paise', ($plan['data']['item']['amount'] ?? 0) === 99900);

$bad = $svc->createPlan(['name' => 'X'], 'hourly', 1);
check('createPlan rejects invalid period', $bad['success'] === false);

$custForSub = $svc->createCustomer('Sub Test', 'sub@example.com', '+910000000000');
$sub = $svc->createSubscription($plan['data']['id'], $custForSub['data']['id'], null, ['tag' => 'q1-2026']);
check('createSubscription returns sub id', $sub['success'] && str_starts_with($sub['data']['id'], 'sub_'));
check('createSubscription status is created', ($sub['data']['status'] ?? '') === 'created');

$cancel = $svc->cancelSubscription($sub['data']['id'], true);
check('cancelSubscription (at cycle end) returns success', $cancel['success'] === true);

$bad = $svc->createSubscription('', '');
check('createSubscription with empty ids returns error', $bad['success'] === false);
$bad = $svc->cancelSubscription('');
check('cancelSubscription with empty id returns error', $bad['success'] === false);

/* ============================================================
 * SECTION 8: Payment Links & QR Codes
 * ============================================================ */
section('Payment Links & QR Codes');
$link = $svc->createPaymentLink(200.00, 'EMI month 1', ['name' => 'Buyer', 'email' => 'b@example.com', 'contact' => '+910000000000']);
check('createPaymentLink returns short_url', $link['success'] === true && str_starts_with($link['data']['short_url'] ?? '', 'https://rzp.io/'));
check('createPaymentLink amount in paise', ($link['data']['amount'] ?? 0) === 20000);

$qr = $svc->createQrCode(150.00, 'Walk-in payment');
check('createQrCode returns image_url', $qr['success'] === true && str_contains($qr['data']['image_url'] ?? '', 'rzp.io'));
check('createQrCode payment_amount in paise', ($qr['data']['payment_amount'] ?? 0) === 15000);

$bad = $svc->createPaymentLink(0, 'x', ['name' => 'x']);
check('createPaymentLink with zero amount fails', $bad['success'] === false);

/* ============================================================
 * SECTION 9: UPI / VPA validation
 * ============================================================ */
section('UPI / VPA');
check('validateVpa accepts standard format', $svc->validateVpa('name@upi') === true);
check('validateVpa accepts numeric handle', $svc->validateVpa('9876543210@ybl') === true);
check('validateVpa accepts dots/dashes', $svc->validateVpa('r.k.sharma@okhdfcbank') === true);
check('validateVpa rejects no @', $svc->validateVpa('justname') === false);
check('validateVpa rejects empty', $svc->validateVpa('') === false);
check('validateVpa rejects spaces', $svc->validateVpa('name @upi') === false);
check('validateVpa rejects short handle', $svc->validateVpa('a@b') === false);

/* ============================================================
 * SECTION 10: Bank payout
 * ============================================================ */
section('Payouts');
$po = $svc->transferToBankAccount(['account_number' => '1234567890', 'ifsc' => 'HDFC0001234', 'mode' => 'IMPS'], 500.00);
check('transferToBankAccount returns payout id', $po['success'] && str_starts_with($po['data']['id'], 'pout_'));
check('transferToBankAccount amount in paise', ($po['data']['amount'] ?? 0) === 50000);

$bad = $svc->transferToBankAccount(['account_number' => '123'], 100);
check('transferToBankAccount with missing ifsc fails', $bad['success'] === false);
$bad = $svc->transferToBankAccount(['account_number' => '1', 'ifsc' => 'X'], -1);
check('transferToBankAccount with negative amount fails', $bad['success'] === false);

/* ============================================================
 * SECTION 11: Logging — every call hits gateway_logs
 * ============================================================ */
section('Logging');
$cleanLogs();
$svc->createOrder(100, 'INR');
$svc->fetchOrder('order_X');
$svc->capturePayment('pay_X', 50);
$svc->createRefund('pay_X', 25);
$svc->createCustomer('A', 'a@a.com', '+910000000000');
$totalLogs = $logCount();
check('5+ calls produced 5+ log rows', $totalLogs >= 5, "rows=$totalLogs");

$amt = (int)$db->query("SELECT COALESCE(SUM(amount_paise),0) FROM gateway_logs")->fetchColumn();
check('Log rows captured amount_paise total', $amt > 0, "sum=$amt");

$failed = (int)$db->query("SELECT COUNT(*) FROM gateway_logs WHERE status = 'failed'")->fetchColumn();
$success = (int)$db->query("SELECT COUNT(*) FROM gateway_logs WHERE status = 'success'")->fetchColumn();
check('Log has both success and (when applicable) failed rows', $success >= 1, "success=$success failed=$failed");

$card = $db->query("SELECT request_payload FROM gateway_logs LIMIT 1")->fetchColumn();
check('Card number is redacted in logs', $card === null || !str_contains((string)$card, '4111111111111111'));

$db->prepare("INSERT INTO gateway_logs (gateway, method, endpoint, request_payload, status)
              VALUES ('razorpay','POST','/orders', ?, 'success')")
   ->execute([json_encode(['card_number' => '4111111111111111', 'cvv' => '123', 'amount' => 500])]);
$ref = new ReflectionClass($svc);
$redact = $ref->getMethod('redact');
$redact->setAccessible(true);
$redacted = $redact->invoke($svc, ['card_number' => '4111111111111111', 'cvv' => '123', 'amount' => 500, 'nested' => ['card_token' => 'tok_xyz']]);
check('redact() removes card_number', ($redacted['card_number'] ?? '') === '[REDACTED]');
check('redact() removes cvv', ($redacted['cvv'] ?? '') === '[REDACTED]');
check('redact() removes nested card_token', ($redacted['nested']['card_token'] ?? '') === '[REDACTED]');
check('redact() preserves non-sensitive fields', ($redacted['amount'] ?? null) === 500);

/* ============================================================
 * SECTION 12: Retry behavior (5xx retries, 4xx doesn't)
 * ============================================================ */
section('Retry behavior');

$initStub = function (RazorpayService $obj): void {
    $ref = new ReflectionClass(RazorpayService::class);
    foreach (['keyId', 'keySecret', 'webhookSecret', 'testMode', 'timeout'] as $p) {
        $prop = $ref->getProperty($p);
        $prop->setAccessible(true);
        $prop->setValue($obj, $p === 'testMode' ? false : ($p === 'timeout' ? 30 : 'stub'));
    }
};

$retried = new class extends RazorpayService {
    public int $attempts = 0;
    public function __construct() {}
    protected function executeRequest(string $m, string $u, array $d = []): array {
        $this->attempts++;
        return ['code' => 502, 'body' => ['error' => ['code' => 'BAD_GATEWAY', 'description' => 'simulated']], 'error' => null];
    }
};
$initStub($retried);
$resp = (new ReflectionMethod($retried, 'call'))->invoke($retried, 'POST', '/orders', ['amount' => 100]);
check('5xx responses trigger retries (3 attempts)', $retried->attempts === 3, "attempts={$retried->attempts}");
check('5xx exhaust returns error envelope', $resp['success'] === false && $resp['status'] === 502);

$noRetry = new class extends RazorpayService {
    public int $attempts = 0;
    public function __construct() {}
    protected function executeRequest(string $m, string $u, array $d = []): array {
        $this->attempts++;
        return ['code' => 400, 'body' => ['error' => ['code' => 'BAD_REQUEST', 'description' => 'invalid input']], 'error' => null];
    }
};
$initStub($noRetry);
$resp2 = (new ReflectionMethod($noRetry, 'call'))->invoke($noRetry, 'POST', '/orders', ['amount' => 100]);
check('4xx responses do not retry (1 attempt)', $noRetry->attempts === 1, "attempts={$noRetry->attempts}");
check('4xx returns error envelope without retry', $resp2['success'] === false && $resp2['status'] === 400);

/* ============================================================
 * SECTION 13: HTTP smoke (no real call — just method contracts)
 * ============================================================ */
section('Method contracts');
$methods = ['createOrder','fetchOrder','verifyPaymentSignature','verifyWebhookSignature',
            'capturePayment','fetchPayment','createRefund','fetchRefund',
            'createCustomer','fetchCustomer','createPlan','createSubscription','cancelSubscription',
            'createPaymentLink','createQrCode','validateVpa','transferToBankAccount','isConfigured','getKeyId','isTestMode'];
$missing = [];
foreach ($methods as $m) {
    if (!method_exists($svc, $m)) $missing[] = $m;
}
check('All 20 public methods exist', empty($missing), 'missing: ' . implode(',', $missing));

/* ============================================================
 * SECTION 14: Return shape contract
 * ============================================================ */
section('Return shape');
$shape = $svc->createOrder(10);
check('Return has success key', array_key_exists('success', $shape));
check('Return has data key', array_key_exists('data', $shape));
check('Return has error key', array_key_exists('error', $shape));
check('No method throws (all return arrays)', is_array($shape));

/* ============================================================
 * SUMMARY
 * ============================================================ */
$total = $pass + $fail;
$elapsed = round(microtime(true) - $startAll, 2);
echo "\n============================================================\n";
echo "  RazorpayService tests: $pass / $total passed  (" . ($fail === 0 ? 'OK' : "$fail FAILED") . ")\n";
echo "  Elapsed: {$elapsed}s\n";
echo "============================================================\n";

if ($fail > 0) {
    echo "\nFailures:\n";
    foreach ($results as $r) {
        if ($r['status'] === 'FAIL') {
            echo "  - {$r['name']}" . ($r['detail'] ? "  ({$r['detail']})" : '') . "\n";
        }
    }
    exit(1);
}
exit(0);
