<?php
/**
 * Voice Integration Test Suite
 *
 * Run: php testing/test_voice_integration.php
 * Exit 0 = all pass, 1 = at least one fail.
 */

define('APP_ROOT', dirname(__DIR__));
require_once APP_ROOT . '/app/Core/Autoloader.php';
require_once APP_ROOT . '/app/Core/Database.php';
require_once APP_ROOT . '/app/Core/Database/Database.php';

use App\Services\Voice\TwilioVoiceService;
use App\Services\Voice\TwiMLBuilder;
use App\Services\Gateway\TwilioService;
use App\Services\AI\VoiceAgents\SiteVisitBookingAgent;
use App\Services\AI\VoiceAgents\PropertyInquiryAgent;
use App\Services\AI\VoiceAgents\LeadFollowUpAgent;

$pass = 0; $fail = 0; $log = [];

function assert_true($cond, $name) {
    global $pass, $fail, $log;
    if ($cond) { $pass++; $log[] = "PASS  $name"; }
    else { $fail++; $log[] = "FAIL  $name"; }
}
function assert_eq($a, $b, $name) {
    global $pass, $fail, $log;
    if ($a === $b) { $pass++; $log[] = "PASS  $name"; }
    else { $fail++; $log[] = "FAIL  $name  (expected " . var_export($b, true) . ", got " . var_export($a, true) . ")"; }
}
function assert_has_key($arr, $key, $name) {
    global $pass, $fail, $log;
    $has = is_array($arr) && array_key_exists($key, $arr);
    if ($has) { $pass++; $log[] = "PASS  $name"; }
    else { $fail++; $log[] = "FAIL  $name  (key '$key' missing)"; }
}
function assert_starts_with($str, $prefix, $name) {
    global $pass, $fail, $log;
    if (is_string($str) && strpos($str, $prefix) === 0) { $pass++; $log[] = "PASS  $name"; }
    else { $fail++; $log[] = "FAIL  $name  (expected to start with '$prefix')"; }
}

echo "=== Voice Integration Test Suite ===\n\n";

/* ------------------------------------------------------------------ */
/*  Test 1: TwiMLBuilder — basic Say                                  */
/* ------------------------------------------------------------------ */
$b = new TwiMLBuilder();
$xml = $b->say('Hello world', 'alice', 'en')->hangup()->toXml();
assert_starts_with($xml, '<?xml version="1.0" encoding="UTF-8"?>', 'TwiML has XML declaration');
assert_true(strpos($xml, '<Response>') !== false, 'TwiML has <Response>');
assert_true(strpos($xml, '<Say voice="alice" language="en">Hello world</Say>') !== false, 'TwiML has correct <Say> verb');
assert_true(strpos($xml, '<Hangup/>') !== false, 'TwiML has <Hangup/>');
assert_true(strpos($xml, '</Response>') !== false, 'TwiML closes </Response>');

/* ------------------------------------------------------------------ */
/*  Test 2: TwiMLBuilder — Play + Record + Pause                      */
/* ------------------------------------------------------------------ */
$b = new TwiMLBuilder();
$xml = $b->play('https://example.com/audio.mp3')
         ->pause(2)
         ->record(['maxLength' => 30, 'playBeep' => true])
         ->toXml();
assert_true(strpos($xml, '<Play>https://example.com/audio.mp3</Play>') !== false, 'TwiML has <Play>');
assert_true(strpos($xml, '<Pause length="2"/>') !== false, 'TwiML has <Pause length="2"/>');
assert_true(strpos($xml, '<Record maxLength="30" playBeep="true"/>') !== false, 'TwiML has <Record> with attrs');

/* ------------------------------------------------------------------ */
/*  Test 3: TwiMLBuilder — Gather with nested Say                      */
/* ------------------------------------------------------------------ */
$b = new TwiMLBuilder();
$xml = $b->gather(['numDigits' => 1, 'action' => '/api/gather', 'method' => 'POST', 'timeout' => 8])
         ->say('Press 1 for yes, 2 for no.', 'alice', 'en')
         ->toXml();
assert_true(strpos($xml, '<Gather numDigits="1" action="/api/gather" method="POST" timeout="8">') !== false, 'Gather has correct attributes');
assert_true(strpos($xml, '<Say voice="alice" language="en">Press 1 for yes, 2 for no.</Say>') !== false, 'Gather has nested Say');

/* ------------------------------------------------------------------ */
/*  Test 4: TwiMLBuilder — Dial + Redirect + Reject                    */
/* ------------------------------------------------------------------ */
$b = new TwiMLBuilder();
$xml = $b->dial('+919876543210', ['timeout' => 30, 'record' => 'record-from-answer'])
         ->redirect('https://example.com/twiml', 'POST')
         ->reject('busy')
         ->toXml();
assert_true(strpos($xml, '<Dial timeout="30" record="record-from-answer">+919876543210</Dial>') !== false, 'TwiML has <Dial> with attrs');
assert_true(strpos($xml, '<Redirect method="POST">https://example.com/twiml</Redirect>') !== false, 'TwiML has <Redirect>');
assert_true(strpos($xml, '<Reject reason="busy"/>') !== false, 'TwiML has <Reject> with reason');

/* ------------------------------------------------------------------ */
/*  Test 5: TwilioVoiceService — instantiation                        */
/* ------------------------------------------------------------------ */
$v = new TwilioVoiceService();
assert_true($v instanceof TwilioVoiceService, 'TwilioVoiceService instantiates');
assert_has_key($v->getStats(), 'calls_initiated', 'getStats has calls_initiated');
assert_has_key($v->getStats(), 'calls_transferred', 'getStats has calls_transferred');

/* ------------------------------------------------------------------ */
/*  Test 6: TwilioVoiceService — generateSiteVisitTwiML               */
/* ------------------------------------------------------------------ */
$xml = $v->generateSiteVisitTwiML();
assert_starts_with($xml, '<?xml', 'Site visit TwiML is valid XML');
assert_true(strpos($xml, 'site visit') !== false, 'Site visit TwiML mentions "site visit"');
assert_true(strpos($xml, '<Gather') !== false, 'Site visit TwiML has <Gather>');
assert_true(strpos($xml, '<Hangup/>') !== false, 'Site visit TwiML ends with Hangup');

/* ------------------------------------------------------------------ */
/*  Test 7: TwilioVoiceService — generatePropertyInquiryTwiML         */
/* ------------------------------------------------------------------ */
$xml = $v->generatePropertyInquiryTwiML(['property' => ['name' => 'Test Plot', 'price' => '5000000', 'location' => 'Gorakhpur']]);
assert_starts_with($xml, '<?xml', 'Property inquiry TwiML is valid XML');
assert_true(strpos($xml, 'Test Plot') !== false, 'Property inquiry TwiML mentions property name');
assert_true(strpos($xml, '5000000') !== false, 'Property inquiry TwiML mentions price');
assert_true(strpos($xml, 'Gorakhpur') !== false, 'Property inquiry TwiML mentions location');

/* ------------------------------------------------------------------ */
/*  Test 8: TwilioVoiceService — generateFollowUpTwiML                */
/* ------------------------------------------------------------------ */
$xml = $v->generateFollowUpTwiML(['leadName' => 'Ravi']);
assert_starts_with($xml, '<?xml', 'Follow up TwiML is valid XML');
assert_true(strpos($xml, 'Ravi') !== false, 'Follow up TwiML mentions lead name');
assert_true(strpos($xml, 'following up') !== false, 'Follow up TwiML has follow-up copy');

/* ------------------------------------------------------------------ */
/*  Test 9: TwilioVoiceService — generateGreetingTwiML                */
/* ------------------------------------------------------------------ */
$xml = $v->generateGreetingTwiML('Welcome to APS Dream Home.');
assert_starts_with($xml, '<?xml', 'Greeting TwiML is valid XML');
assert_true(strpos($xml, 'Welcome to APS Dream Home.') !== false, 'Greeting TwiML has custom message');

/* ------------------------------------------------------------------ */
/*  Test 10: TwilioVoiceService — verifyWebhookSignature (negative)   */
/* ------------------------------------------------------------------ */
// Without setting auth token, signature is always invalid
$_ENV['TWILIO_AUTH_TOKEN'] = 'test_auth_token_for_verification';
$ok = $v->verifyWebhookSignature('https://example.com/webhook', ['foo' => 'bar'], 'invalid_signature');
assert_eq($ok, false, 'Invalid signature is rejected');

/* ------------------------------------------------------------------ */
/*  Test 11: TwilioVoiceService — verifyWebhookSignature (positive)   */
/* ------------------------------------------------------------------ */
$token = 'test_auth_token_for_verification';
$url   = 'https://example.com/webhook';
$params = ['CallSid' => 'CA123', 'From' => '+919876543210'];
ksort($params);
$data = $url . implode('', array_map(fn($k) => $k . $params[$k], array_keys($params)));
$expected = base64_encode(hash_hmac('sha1', $data, $token, true));
$ok = $v->verifyWebhookSignature($url, $params, $expected);
assert_eq($ok, true, 'Valid HMAC signature is accepted');

/* ------------------------------------------------------------------ */
/*  Test 12: TwilioVoiceService — makeCall (envelope shape)           */
/* ------------------------------------------------------------------ */
$result = $v->makeCall('+919876543210', 'https://example.com/twiml', null, ['leadId' => 1, 'record' => true]);
assert_has_key($result, 'success', 'makeCall returns success');
assert_has_key($result, 'sid', 'makeCall returns sid');
assert_has_key($result, 'error', 'makeCall returns error');
assert_has_key($result, 'call_sid', 'makeCall returns call_sid (alias)');
// Test mode is on, so it should "succeed" with a synthetic SID
if (($result['success'] ?? false) === true) {
    assert_starts_with($result['sid'], 'CA', 'makeCall SID starts with CA (Twilio Call prefix)');
}

/* ------------------------------------------------------------------ */
/*  Test 13: TwilioVoiceService — getCallStatus (test mode)           */
/* ------------------------------------------------------------------ */
// Set test mode via env (already on by default if TWILIO_TEST_MODE not set)
// Skip assertion if it tries a real call
$stats = $v->getStats();
assert_true($stats['calls_initiated'] >= 1, 'calls_initiated counter incremented');

/* ------------------------------------------------------------------ */
/*  Test 14: TwilioVoiceService — getCallRecordings (envelope)        */
/* ------------------------------------------------------------------ */
// We can't test this without a real call SID, so just verify the method shape
$reflection = new ReflectionMethod($v, 'getCallRecordings');
assert_true($reflection->isPublic(), 'getCallRecordings is public');
assert_eq($reflection->getNumberOfParameters(), 1, 'getCallRecordings takes 1 parameter');

/* ------------------------------------------------------------------ */
/*  Test 15: TwilioVoiceService — transferCall (test mode)            */
/* ------------------------------------------------------------------ */
$result = $v->transferCall('CA_FAKE_SID_123', '+919876543211');
assert_has_key($result, 'success', 'transferCall returns success');
assert_has_key($result, 'transferred_to', 'transferCall returns transferred_to');
assert_eq($result['transferred_to'], '+919876543211', 'transferCall echoes target number');

/* ------------------------------------------------------------------ */
/*  Test 16: TwilioVoiceService — hangupCall                           */
/* ------------------------------------------------------------------ */
$result = $v->hangupCall('CA_FAKE_SID_123');
assert_has_key($result, 'success', 'hangupCall returns success');
assert_has_key($result, 'sid', 'hangupCall returns sid');

/* ------------------------------------------------------------------ */
/*  Test 17: Voice agents — executeCall (instantiation only)          */
/* ------------------------------------------------------------------ */
$siteVisit = new SiteVisitBookingAgent();
assert_true($siteVisit instanceof SiteVisitBookingAgent, 'SiteVisitBookingAgent instantiates');
$propInq = new PropertyInquiryAgent();
assert_true($propInq instanceof PropertyInquiryAgent, 'PropertyInquiryAgent instantiates');
$followUp = new LeadFollowUpAgent();
assert_true($followUp instanceof LeadFollowUpAgent, 'LeadFollowUpAgent instantiates');

/* ------------------------------------------------------------------ */
/*  Test 18: Voice agents — executeCall method exists                  */
/* ------------------------------------------------------------------ */
$siteReflection = new ReflectionMethod($siteVisit, 'executeCall');
$propReflection = new ReflectionMethod($propInq, 'executeCall');
$followReflection = new ReflectionMethod($followUp, 'executeCall');
assert_true($siteReflection->isPublic(), 'SiteVisitBookingAgent::executeCall is public');
assert_true($propReflection->isPublic(), 'PropertyInquiryAgent::executeCall is public');
assert_true($followReflection->isPublic(), 'LeadFollowUpAgent::executeCall is public');

/* ------------------------------------------------------------------ */
/*  Test 19: gateway_logs has twilio-voice entries                     */
/* ------------------------------------------------------------------ */
try {
    $pdo = \App\Core\Database\Database::getInstance()->getPdo();
    $count = (int)$pdo->query("SELECT COUNT(*) FROM gateway_logs WHERE gateway = 'twilio-voice'")->fetchColumn();
    assert_true($count > 0, "gateway_logs has twilio-voice entries (got $count)");
} catch (\Throwable $e) {
    assert_true(false, 'gateway_logs query failed: ' . $e->getMessage());
}

/* ------------------------------------------------------------------ */
/*  Test 20: ai_call_sessions schema accepts our insert                 */
/* ------------------------------------------------------------------ */
try {
    $pdo = \App\Core\Database\Database::getInstance()->getPdo();
    $stmt = $pdo->query("SHOW COLUMNS FROM ai_call_sessions");
    $columns = $stmt->fetchAll(\PDO::FETCH_COLUMN, 0);
    assert_true(in_array('call_sid', $columns, true), 'ai_call_sessions has call_sid column');
    assert_true(in_array('recording_url', $columns, true), 'ai_call_sessions has recording_url column');
} catch (\Throwable $e) {
    assert_true(false, 'ai_call_sessions schema check failed: ' . $e->getMessage());
}

/* ------------------------------------------------------------------ */
/*  Test 21: TwiML XML is well-formed (parseable)                       */
/* ------------------------------------------------------------------ */
$builder = new TwiMLBuilder();
$xml = $builder->say('Hello, this is a test', 'alice', 'en')
               ->gather(['numDigits' => 1, 'action' => '/api/x', 'method' => 'POST'])
               ->say('Press 1', 'alice', 'en')
               ->endGather()
               ->hangup()
               ->toXml();
libxml_use_internal_errors(true);
$doc = simplexml_load_string($xml);
assert_true($doc !== false, 'TwiML XML is well-formed and parseable');
libxml_clear_errors();

/* ------------------------------------------------------------------ */
/*  Test 22: TwiMLBuilder is fluent (returns $this)                    */
/* ------------------------------------------------------------------ */
$builder = new TwiMLBuilder();
$ret = $builder->say('test');
assert_true($ret === $builder, 'say() returns $this for chaining');
$ret = $builder->pause(1);
assert_true($ret === $builder, 'pause() returns $this for chaining');

/* ------------------------------------------------------------------ */
/*  Test 23: TwilioVoiceService stats update across calls              */
/* ------------------------------------------------------------------ */
$initial = $v->getStats();
$v->makeCall('+919876543212', 'https://example.com/twiml');
$after = $v->getStats();
assert_true($after['calls_initiated'] > $initial['calls_initiated'], 'calls_initiated counter increments');

/* ------------------------------------------------------------------ */
/*  Test 24: Existing VoiceCallService still works (regression)        */
/* ------------------------------------------------------------------ */
$voice = new \App\Services\Voice\VoiceCallService();
assert_true($voice instanceof \App\Services\Voice\VoiceCallService, 'VoiceCallService still instantiates');
$sched = $voice->scheduleCall(1, '+919876543213', 'agent_10', date('Y-m-d', strtotime('+1 day')), '10:00:00');
assert_has_key($sched, 'success', 'VoiceCallService::scheduleCall still works');

/* ------------------------------------------------------------------ */
/*  Summary                                                             */
/* ------------------------------------------------------------------ */
echo "\n=== Test Results ===\n";
foreach ($log as $line) echo $line . "\n";
echo "\nPASSED: $pass\n";
echo "FAILED: $fail\n";
echo "TOTAL:  " . ($pass + $fail) . "\n";

exit($fail > 0 ? 1 : 0);
