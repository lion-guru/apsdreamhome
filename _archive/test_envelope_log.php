<?php
/**
 * Unit tests for App\Services\Envelope and App\Services\Log.
 *
 * No database / no framework bootstrap needed — these are pure helpers.
 *
 * Run:  php testing/test_envelope_log.php
 */

require_once __DIR__ . '/../app/Services/Envelope.php';
require_once __DIR__ . '/../app/Services/Log.php';

use App\Services\Envelope;
use App\Services\Log;

$pass = 0;
$fail = 0;
$failures = [];

function ok($label, $cond) {
    global $pass, $fail, $failures;
    if ($cond) {
        $pass++;
        echo "  [PASS] $label\n";
    } else {
        $fail++;
        $failures[] = $label;
        echo "  [FAIL] $label\n";
    }
}

function hasKey($arr, $key) {
    return is_array($arr) && array_key_exists($key, $arr);
}

echo "\n=== Envelope ===\n";

$e = Envelope::ok(['id' => 1, 'name' => 'Test']);
ok('ok() returns success=true', $e->success === true);
ok('ok() returns data', $e->data === ['id' => 1, 'name' => 'Test']);
ok('ok() error is null', $e->error === null);

$arr = $e->toArray();
ok('toArray has success key', hasKey($arr, 'success'));
ok('toArray has data key', hasKey($arr, 'data'));
ok('toArray has error key', hasKey($arr, 'error'));
ok('toArray omits empty meta', !hasKey($arr, 'meta'));

$e2 = Envelope::ok(['x'], ['count' => 5, 'page' => 1]);
ok('with meta exposes meta key', hasKey($e2->toArray(), 'meta'));
ok('meta contains count', $e2->meta['count'] === 5);

$ef = Envelope::fail('oops');
ok('fail() success=false', $ef->success === false);
ok('fail() has error message', $ef->error === 'oops');

$enf = Envelope::notFound('User');
ok('notFound() error contains class name', str_contains($enf->error, 'User'));

$efbd = Envelope::forbidden('Not your resource');
ok('forbidden() has access denied error', $efbd->success === false && str_contains($efbd->error, 'Not your'));

$eu = Envelope::unauthorized();
ok('unauthorized() has auth error', $eu->success === false && str_contains($eu->error, 'authenticated'));

$ev = Envelope::validation(['email' => 'invalid']);
ok('validation() includes errors in meta', isset($ev->meta['errors']));
ok('validation() success=false', $ev->success === false);

$ew = $e2->withMeta(['extra' => 1]);
ok('withMeta() merges', $ew->meta['count'] === 5 && $ew->meta['extra'] === 1);

$json = $e->toJson();
ok('toJson is valid JSON', json_decode($json) !== null);
ok('toJson contains success', str_contains($json, '"success":true'));

echo "\n=== Log ===\n";

putenv('DEBUG_MODE=true');
$id = Log::setRequestId('req_test_abc123');
ok('setRequestId returns id', $id === 'req_test_abc123');
ok('getRequestId returns same id', Log::getRequestId() === 'req_test_abc123');

putenv('DEBUG_MODE=true');
$dir = (new ReflectionClass(Log::class))->getProperty('logDir');
$dir->setAccessible(true);
$dir->setValue(null, sys_get_temp_dir() . '/aps_log_test_' . getmypid());
@mkdir($dir->getValue(), 0755, true);

$before = file_exists($dir->getValue() . '/app-' . date('Y-m-d') . '.log')
    ? filesize($dir->getValue() . '/app-' . date('Y-m-d') . '.log')
    : 0;

Log::info('user logged in', ['user_id' => 42]);
Log::error('payment failed', ['order_id' => 7]);
Log::warning('retrying', ['attempt' => 1]);

$after = file_exists($dir->getValue() . '/app-' . date('Y-m-d') . '.log')
    ? filesize($dir->getValue() . '/app-' . date('Y-m-d') . '.log')
    : 0;
ok('log write grows file', $after > $before);

$lines = file($dir->getValue() . '/app-' . date('Y-m-d') . '.log', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
ok('log file has at least 3 lines', count($lines) >= 3);
$last = json_decode(end($lines), true);
ok('log line is JSON', $last !== null);
ok('log line has timestamp', isset($last['ts']));
ok('log line has level', isset($last['level']));
ok('log line has request_id', ($last['request_id'] ?? null) === 'req_test_abc123');
ok('log line has message', isset($last['message']));

Log::info('login attempt', ['password' => 'secret123', 'email' => 'a@b.com']);
$lines2 = file($dir->getValue() . '/app-' . date('Y-m-d') . '.log', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$redact = json_decode(end($lines2), true);
ok('password is redacted', ($redact['context']['password'] ?? '') === '***REDACTED***');
ok('non-sensitive email is preserved', ($redact['context']['email'] ?? '') === 'a@b.com');

foreach (['password', 'pwd', 'secret', 'token', 'api_key', 'authorization', 'credit_card', 'cvv'] as $field) {
    Log::info('redact test', [$field => 'value-' . $field]);
}
$lines3 = file($dir->getValue() . '/app-' . date('Y-m-d') . '.log', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$last8 = array_slice($lines3, -8);
$decoded = array_map(fn($l) => json_decode($l, true), $last8);
$allRedacted = true;
foreach ($decoded as $entry) {
    foreach (['password', 'pwd', 'secret', 'token', 'api_key', 'authorization', 'credit_card', 'cvv'] as $f) {
        if (hasKey($entry['context'] ?? [], $f) && $entry['context'][$f] !== '***REDACTED***') {
            $allRedacted = false;
            break 2;
        }
    }
}
ok('multiple sensitive fields all redacted', $allRedacted);

putenv('DEBUG_MODE=false');
Log::debug('should be suppressed', ['x' => 1]);
$suppressed = file($dir->getValue() . '/app-' . date('Y-m-d') . '.log', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$debugLast = json_decode(end($suppressed), true);
ok('debug suppressed when DEBUG_MODE=false', ($debugLast['message'] ?? '') !== 'should be suppressed');

@unlink($dir->getValue() . '/app-' . date('Y-m-d') . '.log');
@rmdir($dir->getValue());

echo "\n=== Summary ===\n";
echo "PASS: $pass\n";
echo "FAIL: $fail\n";
if ($fail > 0) {
    echo "\nFailed tests:\n";
    foreach ($failures as $f) {
        echo "  - $f\n";
    }
    exit(1);
}
echo "All tests passed.\n";
exit(0);
