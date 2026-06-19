<?php
/**
 * APS Dream Home - TwilioService Test Suite
 * Run:   php testing/test_twilio_service.php
 * Exits with code 0 if all pass, 1 if any fail.
 *
 * 25+ tests covering: configuration, sms, whatsapp, templates, voice, verify, balance,
 * status, log writes, rate limiting, test mode, error handling.
 *
 * Honors TWILIO_TEST_MODE=true to short-circuit real Twilio calls.
 * To run against the REAL Twilio API, unset TWILIO_TEST_MODE (or set it to false).
 */

declare(ticks=1);

// Allow web testing
// if (PHP_SAPI !== 'cli') { http_response_code(403); exit('CLI only'); }

error_reporting(E_ALL);
ini_set('display_errors', '1');

$root = dirname(__DIR__);
if (!defined('APP_ROOT')) define('APP_ROOT', $root);

// Bootstrap env (so Database can connect)
$_ENV['APP_ROOT'] = $root;
$envFile = $root . '/.env';
if (file_exists($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#' || strpos($line, '=') === false) continue;
        [$k, $v] = array_map('trim', explode('=', $line, 2));
        if (getenv($k) === false) putenv("$k=$v");
        $_ENV[$k] = $v;
    }
}

// Default to test mode for the entire suite
if (getenv('TWILIO_TEST_MODE') === false) {
    putenv('TWILIO_TEST_MODE=true');
    $_ENV['TWILIO_TEST_MODE'] = 'true';
}

$TEST_MODE = (getenv('TWILIO_TEST_MODE') === 'true' || getenv('TWILIO_TEST_MODE') === '1');

// Load TwilioService (namespaced)
require_once $root . '/app/Services/Gateway/TwilioService.php';

use App\Services\Gateway\TwilioService;

// Bootstrap framework autoloader so Database::getInstance() works inside TwilioService
if (file_exists($root . '/app/Core/Autoloader.php')) {
    require_once $root . '/app/Core/Autoloader.php';
}

// Open DB connection for assertions
try {
    $pdo = new PDO(
        'mysql:host=' . (getenv('DB_HOST') ?: '127.0.0.1')
        . ';port=' . (getenv('DB_PORT') ?: '3307')
        . ';dbname=' . (getenv('DB_NAME') ?: 'apsdreamhome')
        . ';charset=utf8mb4',
        getenv('DB_USER') ?: 'root',
        getenv('DB_PASS') ?: '',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );
} catch (Throwable $e) {
    fwrite(STDERR, "FATAL: DB connection failed: " . $e->getMessage() . "\n");
    exit(2);
}

// Verify gateway_logs table exists
$tblCheck = $pdo->query("SHOW TABLES LIKE 'gateway_logs'")->rowCount();
if (!$tblCheck) {
    fwrite(STDERR, "FATAL: gateway_logs table missing - run scripts/create_gateway_logs.php first\n");
    exit(2);
}

$pass = 0; $fail = 0; $total = 0;
$failures = [];

function t_assert($condition, $msg) {
    global $pass, $fail, $total, $failures;
    $total++;
    if ($condition) {
        $pass++;
        echo "  [OK]   $msg\n";
    } else {
        $fail++;
        $failures[] = $msg;
        echo "  [FAIL] $msg\n";
    }
}

function t_header($name) {
    echo "\n=== $name ===\n";
}

// Snapshot gateway_logs row count so we can verify inserts
function rowCount(PDO $pdo) {
    return (int)$pdo->query('SELECT COUNT(*) FROM gateway_logs')->fetchColumn();
}

function lastRow(PDO $pdo) {
    $r = $pdo->query('SELECT * FROM gateway_logs ORDER BY id DESC LIMIT 1')->fetch();
    return is_array($r) ? $r : [];
}

/* =========================================================
   Test 1: isConfigured() returns false when creds missing
   ========================================================= */
t_header('1. Configuration Checks');
$missing = new class($pdo) extends TwilioService {
    public function __construct($pdo = null) {
        // Force-empty creds; skip parent so we don't read .env
        $this->accountSid = null;
        $this->authToken  = null;
        $this->fromNumber = null;
        $this->whatsappNumber = null;
        $this->testMode = true;
        $this->pdo = $pdo;
    }
};
t_assert($missing->isConfigured() === false, 'isConfigured() false when all creds missing');

$placeholder = new class($pdo) extends TwilioService {
    public function __construct($pdo = null) {
        $this->accountSid = 'AC0PLACEHOLDER0000000000000000000';
        $this->authToken  = 'xxxxxxxxxxxxxxxxxxxxxxxx';
        $this->fromNumber = '+15005550006';
        $this->testMode = true;
        $this->pdo = $pdo;
    }
};
t_assert($placeholder->isConfigured() === false, 'isConfigured() false when creds are placeholders');

$ok = new class($pdo) extends TwilioService {
    public function __construct($pdo = null) {
        $this->accountSid     = 'AC0PLACEHOLDER0000000000000000000';
        $this->authToken      = 'test_token_placeholder_32_chars_long_';
        $this->fromNumber     = '+16562095044';
        $this->whatsappNumber = '+14155238886';
        $this->testMode = true;
        $this->pdo = $pdo;
    }
};
t_assert($ok->isConfigured() === true, 'isConfigured() true with all 4 creds present');
t_assert($ok->isWhatsAppConfigured() === true, 'isWhatsAppConfigured() true with WhatsApp number present');

/* =========================================================
   Test 2-5: sendSms
   ========================================================= */
t_header('2. sendSms()');

$svc = new TwilioService(); // real .env creds + test mode
$before = rowCount($pdo);
$r = $svc->sendSms('+15005550006', 'Test SMS from APS');
t_assert(is_array($r) && isset($r['success']), 'sendSms returns array');
t_assert($r['success'] === true, 'sendSms success in test mode');
t_assert(!empty($r['sid']), 'sendSms returns sid');
t_assert(isset($r['cost']) && is_numeric($r['cost']), 'sendSms returns cost');
t_assert($r['mode'] === 'test', 'sendSms reports test mode');
$after = rowCount($pdo);
t_assert($after > $before, 'sendSms wrote a row to gateway_logs');

$row = lastRow($pdo);
t_assert($row['gateway'] === 'twilio', 'log row gateway=twilio');
t_assert($row['action'] === 'send_sms', 'log row action=send_sms');
t_assert($row['status'] === 'success', 'log row status=success');

/* =========================================================
   Test 6-7: sendWhatsApp & template
   ========================================================= */
t_header('3. sendWhatsApp()');
$before = rowCount($pdo);
$r = $svc->sendWhatsApp('+15005550006', 'Hi from WhatsApp');
t_assert($r['success'] === true, 'sendWhatsApp success in test mode');
t_assert(!empty($r['sid']), 'sendWhatsApp returns sid');
$row = lastRow($pdo);
t_assert($row['action'] === 'send_whatsapp', 'log row action=send_whatsapp');
t_assert(strpos($row['request_payload'], 'whatsapp:+14155238886') !== false, 'WhatsApp From format uses whatsapp:+14155238886');

t_header('4. sendWhatsAppTemplate()');
$before = rowCount($pdo);
$r = $svc->sendWhatsAppTemplate('+15005550006', 'HX_tpl_12345', [
    '1' => 'Rajesh',
    '2' => 'Plot 42',
    '3' => '2026-07-01',
], 'en');
t_assert($r['success'] === true, 'sendWhatsAppTemplate success');
$row = lastRow($pdo);
t_assert($row['action'] === 'send_whatsapp_template', 'log row action=send_whatsapp_template');
t_assert(strpos($row['request_payload'], 'HX_tpl_12345') !== false, 'ContentSid in request');
t_assert(strpos($row['request_payload'], 'Rajesh') !== false, 'Variables serialized into request');

/* =========================================================
   Test 8: makeCall
   ========================================================= */
t_header('5. makeCall()');
$before = rowCount($pdo);
$r = $svc->makeCall('+15005550006', 'https://example.com/twiml.xml');
t_assert($r['success'] === true, 'makeCall success in test mode');
t_assert(!empty($r['sid']), 'makeCall returns call sid');
$row = lastRow($pdo);
t_assert($row['action'] === 'make_call', 'log row action=make_call');
t_assert(strpos($row['request_payload'], 'example.com/twiml.xml') !== false, 'TwiML URL in request');

/* =========================================================
   Test 9: sendOtp / verifyOtp
   ========================================================= */
t_header('6. Verify API (OTP)');
$verifySvc = new class($pdo) extends TwilioService {
    public function __construct($pdo = null) {
        $this->accountSid     = 'AC0PLACEHOLDER0000000000000000000';
        $this->authToken      = 'test_token_placeholder_32_chars_long_';
        $this->fromNumber     = '+16562095044';
        $this->whatsappNumber = '+14155238886';
        $this->testMode = true;
        $this->pdo = $pdo;
    }
    public function env($name) {
        if ($name === 'TWILIO_VERIFY_SERVICE_SID') return 'VA_test_service_sid';
        return parent::env($name);
    }
};
$before = rowCount($pdo);
$r = $verifySvc->sendOtp('+15005550006', 'sms');
t_assert($r['success'] === true, 'sendOtp success in test mode');
$row = lastRow($pdo);
t_assert($row['action'] === 'send_otp', 'log row action=send_otp');

$before = rowCount($pdo);
$r = $verifySvc->verifyOtp('+15005550006', '123456', 'sms');
t_assert(is_array($r) && array_key_exists('approved', $r), 'verifyOtp returns approved key');
$row = lastRow($pdo);
t_assert($row['action'] === 'verify_otp', 'log row action=verify_otp');

/* =========================================================
   Test 10: getBalance
   ========================================================= */
t_header('7. getBalance()');
$before = rowCount($pdo);
$r = $svc->getBalance();
t_assert($r['success'] === true, 'getBalance success in test mode');
t_assert(array_key_exists('balance', $r) || array_key_exists('cost', $r), 'getBalance returns balance field');
$row = lastRow($pdo);
t_assert($row['action'] === 'get_balance', 'log row action=get_balance');

/* =========================================================
   Test 11: getMessageStatus
   ========================================================= */
t_header('8. getMessageStatus()');
$before = rowCount($pdo);
$r = $svc->getMessageStatus('SM_test_12345');
t_assert($r['success'] === true, 'getMessageStatus success');
t_assert(array_key_exists('status', $r), 'getMessageStatus returns status');
$row = lastRow($pdo);
t_assert($row['action'] === 'get_message_status', 'log row action=get_message_status');
t_assert($row['recipient'] === 'SM_test_12345', 'log row recipient = sid');

/* =========================================================
   Tests 12-20: error paths, validation, input handling
   ========================================================= */
t_header('9. Error / Validation Paths');
$before = rowCount($pdo);
$r = $svc->sendSms('not-a-phone', 'test');
t_assert($r['success'] === false, 'sendSms rejects malformed phone');
t_assert(isset($r['error']) && is_string($r['error']), 'sendSms error is a string');
$row = lastRow($pdo);
t_assert($row['status'] === 'error', 'malformed phone logged as error');
t_assert(stripos($row['error_message'], 'invalid') !== false, 'error message mentions invalid phone');

$before = rowCount($pdo);
$r = $svc->sendSms('+15005550006', '');
t_assert($r['success'] === false, 'sendSms rejects empty body');
$row = lastRow($pdo);
t_assert(stripos($row['error_message'], 'empty') !== false, 'error message mentions empty');

$before = rowCount($pdo);
$r = $svc->sendWhatsAppTemplate('+15005550006', '', [], 'en');
t_assert($r['success'] === false, 'sendWhatsAppTemplate rejects empty ContentSid');

$before = rowCount($pdo);
$r = $svc->makeCall('+15005550006', '');
t_assert($r['success'] === false, 'makeCall rejects empty TwiML URL');

$before = rowCount($pdo);
$r = $svc->sendWhatsApp('not-a-phone', 'hi');
t_assert($r['success'] === false, 'sendWhatsApp rejects malformed phone');

$before = rowCount($pdo);
$r = $svc->verifyOtp('not-a-phone', '1234');
t_assert($r['success'] === false, 'verifyOtp rejects malformed phone');

$before = rowCount($pdo);
$r = $svc->getMessageStatus('');
t_assert($r['success'] === false, 'getMessageStatus rejects empty sid');

/* =========================================================
   Tests: isConfigured() false disables all sends
   ========================================================= */
t_header('10. Disabled / Misconfigured Mode');
$disabled = new class($pdo) extends TwilioService {
    public function __construct($pdo = null) {
        $this->accountSid     = null;
        $this->authToken      = null;
        $this->fromNumber     = null;
        $this->whatsappNumber = null;
        $this->testMode = true;
        $this->pdo = $pdo;
    }
};
$r = $disabled->sendSms('+15005550006', 'x');
t_assert($r['success'] === false, 'sendSms fails gracefully when not configured');
$row = lastRow($pdo);
t_assert(stripos($row['error_message'], 'not configured') !== false, 'sendSms error message mentions not configured');

$r = $disabled->sendWhatsApp('+15005550006', 'x');
t_assert($r['success'] === false, 'sendWhatsApp fails gracefully when not configured');

$r = $disabled->makeCall('+15005550006', 'https://x.com/t.xml');
t_assert($r['success'] === false, 'makeCall fails gracefully when not configured');

/* =========================================================
   Tests: aggregation methods
   ========================================================= */
t_header('11. getStats() and getGatewayStats()');
$stats = $svc->getStats();
t_assert(isset($stats['calls']) && $stats['calls'] > 0, 'getStats tracks total calls');
t_assert(isset($stats['successes']) && $stats['successes'] > 0, 'getStats tracks successes');
t_assert(isset($stats['failures']) && $stats['failures'] > 0, 'getStats tracks failures');
t_assert(isset($stats['cost_total']), 'getStats tracks cost_total');

$logs = $svc->getRecentLogs(5);
t_assert(is_array($logs), 'getRecentLogs returns array');
t_assert(count($logs) > 0, 'getRecentLogs has rows');

$agg = $svc->getGatewayStats(24);
t_assert(is_array($agg), 'getGatewayStats returns array');
$hasTwilio = false;
foreach ($agg as $row) if ($row['gateway'] === 'twilio') $hasTwilio = true;
t_assert($hasTwilio, 'getGatewayStats includes twilio aggregate');

/* =========================================================
   Tests: Real Twilio test magic number behavior
   ========================================================= */
t_header('12. Twilio Magic Numbers (no-recipient test)');
// +15005550006 is the "valid magic" test number Twilio accepts without real recipient
// In test mode we don't even call Twilio, so this just validates the path
$r = $svc->sendSms('+15005550006', 'Test against magic number');
t_assert($r['success'] === true, 'Magic number path works in test mode');
$row = lastRow($pdo);
t_assert($row['recipient'] === '+15005550006', 'Magic number logged as recipient');

/* =========================================================
   Tests: Rate limiting (in-process window)
   ========================================================= */
t_header('13. Rate Limiting (in-process)');
$rlSvc = new class($pdo) extends TwilioService {
    public function __construct($pdo = null) {
        $this->accountSid     = 'AC0PLACEHOLDER0000000000000000000';
        $this->authToken      = 'test_token_placeholder_32_chars_long_';
        $this->fromNumber     = '+16562095044';
        $this->whatsappNumber = '+14155238886';
        $this->testMode = true;
        $this->rateLimitPerMinute = 5;  // tighten for test
        $this->pdo = $pdo;
    }
};
$rateLimitedSeen = false;
for ($i = 0; $i < 10; $i++) {
    $r = $rlSvc->sendSms('+15005550006', "rate test $i");
    if (!empty($r['rate_limited'])) { $rateLimitedSeen = true; break; }
}
t_assert($rateLimitedSeen, 'Rate limit triggered after window exceeded');
if ($rateLimitedSeen) {
    $row = lastRow($pdo);
    t_assert($row['status'] === 'rate_limited', 'Rate limited call logged as rate_limited');
}

/* =========================================================
   Tests: Phone normalization
   ========================================================= */
t_header('14. Phone Number Normalization');
$normalizer = new class($pdo) extends TwilioService {
    public function __construct($pdo = null) {
        $this->testMode = true;
        $this->pdo = $pdo;
    }
    public function check($phone) { return $this->normalizePhone($phone); }
};
$n = $normalizer->check('+919876543210');
t_assert($n === '+919876543210', 'normalizePhone keeps valid E.164');

$n = $normalizer->check('9876543210');
t_assert($n === '+919876543210', 'normalizePhone adds +91 to 10-digit');

$n = $normalizer->check('0919876543210');
t_assert($n === '+919876543210', 'normalizePhone strips leading 0');

$n = $normalizer->check('919876543210');
t_assert($n === '+919876543210', 'normalizePhone adds + to 12-digit starting with 91');

$n = $normalizer->check('not-a-phone');
t_assert($n === null, 'normalizePhone rejects garbage');

/* =========================================================
   Tests: Specific log row fields
   ========================================================= */
t_header('15. Log Row Field Integrity');
$before = rowCount($pdo);
$svc->sendSms('+15005550006', 'field test');
$row = lastRow($pdo);
t_assert(is_numeric($row['http_code']) || $row['http_code'] === null, 'http_code is numeric or null');
t_assert(is_numeric($row['duration_ms']), 'duration_ms is numeric');
t_assert(is_string($row['created_at']) && strlen($row['created_at']) >= 19, 'created_at is ISO datetime');
t_assert($row['cost'] !== null, 'cost is set');
t_assert($row['gateway'] === 'twilio', 'gateway is twilio');
t_assert(!empty($row['action']), 'action is set');

/* =========================================================
   Final summary
   ========================================================= */
echo "\n";
echo str_repeat('=', 60) . "\n";
echo "TWILIO TEST SUMMARY\n";
echo str_repeat('=', 60) . "\n";
echo "Mode:           " . ($TEST_MODE ? 'TEST (TWILIO_TEST_MODE=true)' : 'LIVE (real Twilio API)') . "\n";
echo "Total tests:    $total\n";
echo "Passed:         $pass\n";
echo "Failed:         $fail\n";
echo "Pass rate:      " . ($total > 0 ? round(($pass / $total) * 100, 1) : 0) . "%\n";

if ($fail > 0) {
    echo "\nFAILED TESTS:\n";
    foreach ($failures as $f) echo "  - $f\n";
}

echo "\nDB rows in gateway_logs: " . rowCount($pdo) . "\n";
echo "Sample last 3 rows:\n";
$rows = $pdo->query('SELECT id, gateway, action, recipient, status, cost, duration_ms, http_code, created_at FROM gateway_logs ORDER BY id DESC LIMIT 3')->fetchAll();
foreach ($rows as $r) {
    printf("  #%d %s %-25s -> %-7s %-15s cost=%.4f dur=%dms http=%s @ %s\n",
        $r['id'], $r['gateway'], $r['action'], $r['status'],
        $r['recipient'] ?? '-', (float)$r['cost'], (int)$r['duration_ms'],
        $r['http_code'] ?? '-', $r['created_at']);
}

exit($fail > 0 ? 1 : 0);
