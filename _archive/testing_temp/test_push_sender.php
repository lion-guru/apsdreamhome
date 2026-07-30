<?php
/**
 * Unit tests for PushSender VAPID + encryption primitives.
 * Run: php testing/test_push_sender.php
 */

if (!defined('APP_ROOT')) define('APP_ROOT', dirname(__DIR__));

require_once __DIR__ . '/../app/Core/Autoloader.php';
spl_autoload_register(function ($class) {
    // Fallback for classes outside the main autoloader's map
    $prefix = 'App\\';
    if (strpos($class, $prefix) !== 0) return;
    $relative = substr($class, strlen($prefix));
    $path = __DIR__ . '/../app/' . str_replace('\\', '/', $relative) . '.php';
    if (file_exists($path)) require_once $path;
});

require_once __DIR__ . '/../app/Services/Communication/PushSender.php';

// Load .env
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (!preg_match('/^([A-Z_][A-Z0-9_]*)\s*=\s*(.*)$/i', $line, $m)) continue;
        $key = $m[1]; $val = trim($m[2], " \t\"'");
        $_ENV[$key] = $val;
        putenv("$key=$val");
    }
}

$pass = 0; $fail = 0;
function ok($name, $cond, $extra = '') {
    global $pass, $fail;
    if ($cond) { $pass++; echo "  PASS  $name\n"; }
    else { $fail++; echo "  FAIL  $name $extra\n"; }
}
function section($title) { echo "\n=== $title ===\n"; }

use App\Services\Communication\PushSender;

// ---------------------------------------------------------------
section('Base64URL helpers');
$bin = "\x00\xff\x7f\x80\x01\xfe";
$b64u = PushSender::b64UrlEncode($bin);
$b64std = strtr(base64_encode($bin), '+/', '-_');
$b64std = rtrim($b64std, '=');
ok('b64UrlEncode matches base64url spec', $b64u === $b64std);
ok('b64UrlDecode round-trips', PushSender::b64UrlDecode($b64u) === $bin);

// ---------------------------------------------------------------
section('VAPID key decode');
$pub = 'BMRB-z3OMUm-VXzQqPsEiroXpGZ1SpP3rv_ySpgcdQTUasddqqn3wl0OWFKc-AZS1hNJ36f0CwmpBDa67P3VQLU';
$decoded = PushSender::b64UrlDecode($pub);
ok('Public key decodes to 65 bytes', strlen($decoded) === 65, '(got ' . strlen($decoded) . ')');
ok('Public key starts with 0x04', ord($decoded[0]) === 0x04);

$priv = 'YTiliiKyMyrQwO2cx7Wc_3Pbg-xbjUN0usT0LQnjKo4';
$dpriv = PushSender::b64UrlDecode($priv);
ok('Private key decodes to 32 bytes', strlen($dpriv) === 32);

// ---------------------------------------------------------------
section('PushSender instantiation');
$sender = new PushSender();
ok('Sender isConfigured (VAPID keys present)', $sender->isConfigured());
ok('Sender getVapidPublicKey returns base64url string', $sender->getVapidPublicKey() !== '');
ok('Sender getVapidPublicKey returns 87-char b64url', strlen($sender->getVapidPublicKey()) === 87);

// ---------------------------------------------------------------
section('VAPID JWT structure (ES256)');
// Use reflection to call the private method
$ref = new ReflectionClass($sender);
$m = $ref->getMethod('buildVapidJwt');
$m->setAccessible(true);
$audience = 'https://fcm.googleapis.com';
$jwt = $m->invoke($sender, $audience);
$parts = explode('.', $jwt);
ok('JWT has 3 parts', count($parts) === 3);
list($h, $c, $s) = $parts;
$hdr = json_decode(PushSender::b64UrlDecode($h), true);
$clm = json_decode(PushSender::b64UrlDecode($c), true);
$sig = PushSender::b64UrlDecode($s);
ok('Header typ=JWT', ($hdr['typ'] ?? null) === 'JWT');
ok('Header alg=ES256', ($hdr['alg'] ?? null) === 'ES256');
ok('Claim aud matches', ($clm['aud'] ?? null) === $audience);
ok('Claim sub present', !empty($clm['sub']));
ok('Claim exp is future', ($clm['exp'] ?? 0) > time());
ok('Signature is 64 bytes (raw R||S)', strlen($sig) === 64);

// ---------------------------------------------------------------
section('Endpoint audience extraction');
$m2 = $ref->getMethod('endpointAudience');
$m2->setAccessible(true);
ok('https://fcm.googleapis.com/foo → https://fcm.googleapis.com',
    $m2->invoke($sender, 'https://fcm.googleapis.com/foo/bar') === 'https://fcm.googleapis.com');
ok('https://push.example.com:8443/x → https://push.example.com:8443',
    $m2->invoke($sender, 'https://push.example.com:8443/x') === 'https://push.example.com:8443');
ok('http://localhost:8080/x → http://localhost:8080',
    $m2->invoke($sender, 'http://localhost:8080/x') === 'http://localhost:8080');

// ---------------------------------------------------------------
section('HKDF');
$hkdfM = $ref->getMethod('hkdf');
$hkdfM->setAccessible(true);

// RFC 5869 Test Case 3 (SHA-256, non-zero salt/info)
//   IKM=0x0b*22, salt=0x00..0x0c, info=0xf0..0xf9, L=42
//   Expected OKM:
//   3cb25f25faacd57a90434f64d0362f2a2d2d0a90cf1a5a4c5db02d56ecc4c5bf34007208d5b887185865
$salt = hex2bin('000102030405060708090a0b0c');
$ikm  = hex2bin('0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b0b');
$info = hex2bin('f0f1f2f3f4f5f6f7f8f9');
$expected = hex2bin('3cb25f25faacd57a90434f64d0362f2a2d2d0a90cf1a5a4c5db02d56ecc4c5bf34007208d5b887185865');
$got = $hkdfM->invoke($sender, $salt, $ikm, $info, 42);
ok('HKDF matches RFC 5869 vector 3 (42 bytes OKM)',
    $got === $expected, '(got ' . bin2hex($got) . ')');

// ---------------------------------------------------------------
section('Subscribe / Unsubscribe (DB round-trip)');
$testUserId = 3;  // Use an existing user ID to respect database FK constraints
$endpoint = 'https://fcm.googleapis.com/fcm/send/test-' . uniqid();
$p256dh = 'BNcRdreAa-bmFRm7lmsXK6cQOgmNIhv3nXg5k2xY8bR1N8Q-fake-p256dh-key-aaaaaaaaaaaaaa';
$auth   = 'dGVzdC1hdXRoLXNlY3JldC1iCg';
$id = $sender->subscribe($testUserId, $endpoint, $p256dh, $auth);
ok('Subscribe inserts a row', $id > 0);

// Update path (same endpoint)
$id2 = $sender->subscribe($testUserId, $endpoint, $p256dh, $auth);
ok('Subscribe is idempotent (returns same id)', $id2 === $id);

$ok_unsub = $sender->unsubscribe($testUserId, $endpoint);
ok('Unsubscribe deletes the row', $ok_unsub === true);

// ---------------------------------------------------------------
section('sendToUser (without real push endpoint — no subscriptions)');
$res = $sender->sendToUser($testUserId, 'Hello', 'Body', '/test');
ok('sendToUser returns success/failed counts', isset($res['success'], $res['failed']));
ok('sendToUser shows 0/0 for user with no subs', $res['success'] === 0 && $res['failed'] === 0);

// Cleanup test row if any survived
$db = \App\Core\Database\Database::getInstance();
$db->prepare("DELETE FROM push_subscriptions WHERE user_id = ?")->execute([$testUserId]);

// ---------------------------------------------------------------
echo "\n========================================\n";
echo "Results: $pass passed, $fail failed\n";
echo "========================================\n";
exit($fail > 0 ? 1 : 0);
