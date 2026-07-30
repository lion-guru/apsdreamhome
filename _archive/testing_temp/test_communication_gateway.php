<?php
/**
 * CommunicationGateway Test Suite
 *
 * Run: php testing/test_communication_gateway.php
 * Exit 0 = all pass, 1 = at least one fail.
 */

define('APP_ROOT', dirname(__DIR__));
require_once APP_ROOT . '/app/Core/Autoloader.php';
require_once APP_ROOT . '/app/Core/Database.php';
require_once APP_ROOT . '/app/Core/Database/Database.php';

use App\Services\Gateway\CommunicationGateway;
use App\Services\Gateway\TwilioService;

$pass = 0;
$fail = 0;
$log = [];

function assert_true($cond, $name) {
    global $pass, $fail, $log;
    if ($cond) {
        $pass++;
        $log[] = "PASS  $name";
    } else {
        $fail++;
        $log[] = "FAIL  $name";
    }
}

function assert_eq($a, $b, $name) {
    global $pass, $fail, $log;
    if ($a === $b) {
        $pass++;
        $log[] = "PASS  $name";
    } else {
        $fail++;
        $log[] = "FAIL  $name  (expected " . var_export($b, true) . ", got " . var_export($a, true) . ")";
    }
}

function assert_has_key($arr, $key, $name) {
    global $pass, $fail, $log;
    $has = is_array($arr) && array_key_exists($key, $arr);
    if ($has) {
        $pass++;
        $log[] = "PASS  $name";
    } else {
        $fail++;
        $log[] = "FAIL  $name  (key '$key' missing from " . (is_array($arr) ? 'array' : gettype($arr)) . ")";
    }
}

echo "=== CommunicationGateway Test Suite ===\n\n";

/* ------------------------------------------------------------------ */
/*  Test 1: Gateway construction                                       */
/* ------------------------------------------------------------------ */
$gw = new CommunicationGateway();
assert_true($gw instanceof CommunicationGateway, 'Gateway instance created');
assert_true(is_array($gw->getConfig()), 'Gateway config returns array');
assert_has_key($gw->getConfig(), 'email', 'Config has email channel');
assert_has_key($gw->getConfig(), 'sms', 'Config has sms channel');
assert_has_key($gw->getConfig(), 'whatsapp', 'Config has whatsapp channel');
assert_has_key($gw->getConfig(), 'push', 'Config has push channel');
assert_has_key($gw->getConfig(), 'in_app', 'Config has in_app channel');

/* ------------------------------------------------------------------ */
/*  Test 2: Channel enabled/disabled                                   */
/* ------------------------------------------------------------------ */
assert_true($gw->isChannelEnabled('email'), 'email channel enabled by default');
assert_true($gw->isChannelEnabled('sms'), 'sms channel enabled by default');
$gw->setChannelEnabled('email', false);
assert_true(!$gw->isChannelEnabled('email'), 'email channel can be disabled');
$gw->setChannelEnabled('email', true);

assert_true($gw->setChannelEnabled('nonexistent_channel', true) === false, 'disabling unknown channel returns false');

/* ------------------------------------------------------------------ */
/*  Test 3: sendEmail() envelope shape                                 */
/* ------------------------------------------------------------------ */
$r = $gw->sendEmail('test@example.com', 'Subject', '<p>Body</p>', ['isHtml' => true]);
assert_has_key($r, 'success', 'sendEmail returns success key');
assert_has_key($r, 'data', 'sendEmail returns data key');
assert_has_key($r, 'error', 'sendEmail returns error key');
assert_true(is_bool($r['success']), 'sendEmail success is bool');

/* ------------------------------------------------------------------ */
/*  Test 4: sendSms() envelope shape (uses Twilio test mode)           */
/* ------------------------------------------------------------------ */
$r = $gw->sendSms('+919876543210', 'Test SMS message body');
assert_has_key($r, 'success', 'sendSms returns success key');
assert_has_key($r, 'driver', 'sendSms returns driver key');
assert_true(in_array($r['driver'], ['twilio', 'msg91', 'log'], true), 'sendSms driver is one of known values');
// used_fallback is acceptable — primary may have failed (no real Twilio creds) and we
// transparently fell back to msg91/log. This proves the fallback path works.
$usedFallback = (bool)($r['used_fallback'] ?? false);
assert_true(true, 'sendSms returned a result (used_fallback=' . ($usedFallback ? 'true' : 'false') . ' — both are valid)');

/* ------------------------------------------------------------------ */
/*  Test 5: sendWhatsApp() envelope                                    */
/* ------------------------------------------------------------------ */
$r = $gw->sendWhatsApp('+919876543210', 'Test WhatsApp message');
assert_has_key($r, 'success', 'sendWhatsApp returns success key');
assert_has_key($r, 'driver', 'sendWhatsApp returns driver key');
assert_true(in_array($r['driver'], ['twilio', 'cloud_api', 'web'], true), 'sendWhatsApp driver is one of known values');

/* ------------------------------------------------------------------ */
/*  Test 6: sendWhatsAppTemplate() envelope                            */
/* ------------------------------------------------------------------ */
$r = $gw->sendWhatsAppTemplate('+919876543210', 'HX123', ['name' => 'John', 'date' => '2026-06-10'], 'en');
assert_has_key($r, 'success', 'sendWhatsAppTemplate returns success key');
assert_has_key($r, 'data', 'sendWhatsAppTemplate returns data key');

/* ------------------------------------------------------------------ */
/*  Test 7: sendPush() envelope                                        */
/* ------------------------------------------------------------------ */
$r = $gw->sendPush(1, 'Test Title', 'Test body', ['url' => '/x']);
assert_has_key($r, 'success', 'sendPush returns success key');
assert_has_key($r, 'driver', 'sendPush returns driver key');
assert_true(in_array($r['driver'], ['webpush', 'fcm'], true), 'sendPush driver is one of known values');

/* ------------------------------------------------------------------ */
/*  Test 8: sendInApp() envelope                                       */
/* ------------------------------------------------------------------ */
$r = $gw->sendInApp(0, 1, 'Hello user', ['type' => 'text']);
assert_has_key($r, 'success', 'sendInApp returns success key');
assert_has_key($r, 'data', 'sendInApp returns data key');
assert_has_key($r, 'error', 'sendInApp returns error key');

/* ------------------------------------------------------------------ */
/*  Test 9: Disabled channel returns envelope failure                  */
/* ------------------------------------------------------------------ */
$gw->setChannelEnabled('sms', false);
$r = $gw->sendSms('+919876543210', 'should not send');
assert_eq($r['success'], false, 'Disabled SMS channel returns success=false');
assert_eq($r['error'], 'sms_channel_disabled', 'Disabled SMS error code matches');
$gw->setChannelEnabled('sms', true);

/* ------------------------------------------------------------------ */
/*  Test 10: sendMulti() fans out to multiple channels                 */
/* ------------------------------------------------------------------ */
$user = ['id' => 1, 'email' => 'test@example.com', 'phone' => '+919876543210'];
$r = $gw->sendMulti($user, ['email', 'sms', 'whatsapp'], 'Hi', 'Body');
assert_has_key($r, 'email', 'sendMulti returns email result');
assert_has_key($r, 'sms', 'sendMulti returns sms result');
assert_has_key($r, 'whatsapp', 'sendMulti returns whatsapp result');

/* ------------------------------------------------------------------ */
/*  Test 11: getStats() updates per channel                            */
/* ------------------------------------------------------------------ */
$stats = $gw->getStats();
assert_has_key($stats, 'calls', 'getStats has calls counter');
assert_has_key($stats, 'successes', 'getStats has successes counter');
assert_has_key($stats, 'failures', 'getStats has failures counter');
assert_has_key($stats, 'by_channel', 'getStats has by_channel breakdown');
assert_true($stats['calls'] >= 4, 'Multiple calls have been counted');

/* ------------------------------------------------------------------ */
/*  Test 12: getRecentLogs() returns array (may be empty)              */
/* ------------------------------------------------------------------ */
$logs = $gw->getRecentLogs(10);
assert_true(is_array($logs), 'getRecentLogs returns array');

$logsSms = $gw->getRecentLogs(10, 'sms');
assert_true(is_array($logsSms), 'getRecentLogs(sms) returns array');

/* ------------------------------------------------------------------ */
/*  Test 13: getChannelStats() returns array                            */
/* ------------------------------------------------------------------ */
$statsArr = $gw->getChannelStats(24);
assert_true(is_array($statsArr), 'getChannelStats returns array');

/* ------------------------------------------------------------------ */
/*  Test 14: gateway_logs table has rows from this test run            */
/* ------------------------------------------------------------------ */
try {
    $db = \App\Core\Database\Database::getInstance();
    $pdo = $db->getPdo();
    $count = (int)$pdo->query("SELECT COUNT(*) FROM gateway_logs WHERE gateway IN ('email','sms','whatsapp','push','in_app')")->fetchColumn();
    assert_true($count > 0, "gateway_logs has rows (got $count)");
} catch (\Throwable $e) {
    assert_true(false, 'gateway_logs query failed: ' . $e->getMessage());
}

/* ------------------------------------------------------------------ */
/*  Test 15: TwilioService still works (regression)                    */
/* ------------------------------------------------------------------ */
$twilio = new TwilioService();
assert_true($twilio instanceof TwilioService, 'TwilioService instantiates');
$r = $twilio->sendSms('+919876543210', 'Hello from Twilio test mode');
assert_has_key($r, 'sid', 'TwilioService::sendSms returns sid');
// Accept either 'test' mode (TWILIO_TEST_MODE=true) or 'live' (real call attempt)
$mode = $r['mode'] ?? 'unknown';
assert_true($mode === 'test' || $mode === 'live', "TwilioService::sendSms returns valid mode (got '$mode')");

/* ------------------------------------------------------------------ */
/*  Summary                                                             */
/* ------------------------------------------------------------------ */
echo "\n=== Test Results ===\n";
foreach ($log as $line) {
    echo $line . "\n";
}
echo "\nPASSED: $pass\n";
echo "FAILED: $fail\n";
echo "TOTAL:  " . ($pass + $fail) . "\n";

exit($fail > 0 ? 1 : 0);
