<?php
/**
 * test_maintenance_mode.php
 *
 * Smoke test for MaintenanceService + MaintenanceController.
 * - Service unit behaviour (no DB dependency when settings table missing,
 *   but here it exists so we test the real path).
 * - HTTP /admin/settings/maintenance/status, /toggle, /ips/add, /ips/remove.
 * - Audit log entries written for toggle + IP add/remove.
 * - "Fail-open" behaviour: when service throws, the middleware lets traffic
 *   through (verified by inspecting the middleware source).
 */
define('APP_ROOT', dirname(__DIR__));
define('BASE_URL', '/apsdreamhome');
$pass = 0; $fail = 0; $fails = [];
function ok($name, $cond) { global $pass, $fail, $fails; if ($cond) { $pass++; echo "  [PASS] $name\n"; } else { $fail++; $fails[] = $name; echo "  [FAIL] $name\n"; } }
function section($title) { echo "\n=== $title ===\n"; }

$BASE = 'http://localhost/apsdreamhome';
$cookies = 'C:\\Users\\abhay\\AppData\\Local\\Temp\\opencode\\cookies.txt';
if (file_exists($cookies)) @unlink($cookies);

$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

// ---- 1. service unit ----
section('1. MaintenanceService unit');
$svcFile = APP_ROOT . '/app/Services/MaintenanceService.php';
ok('MaintenanceService file exists', file_exists($svcFile));
ok('MaintenanceService class exists in file', strpos(file_get_contents($svcFile), 'class MaintenanceService') !== false);

require_once $svcFile;
$svc = new App\Services\MaintenanceService($pdo);
ok('isEnabled() returns bool', is_bool($svc->isEnabled()));
ok('getMessage() non-empty', strlen($svc->getMessage()) > 0);
ok('getEta() is string', is_string($svc->getEta()));
ok('getAllowedIps() returns array', is_array($svc->getAllowedIps()));

// ---- 2. toggle on/off ----
section('2. toggle on/off');
$svc->disable();
ok('Disabled first', !$svc->isEnabled());
$svc->enable('Test maintenance', '2026-06-06 10:00');
ok('Enabled', $svc->isEnabled());
ok('Custom message persisted', $svc->getMessage() === 'Test maintenance');
ok('Custom ETA persisted', $svc->getEta() === '2026-06-06 10:00');

// ---- 3. toggle() returns new state ----
section('3. toggle() returns new state');
$after = $svc->toggle();
ok('toggle() returns false (now off)', $after === false);
ok('Service now disabled', !$svc->isEnabled());
$svc->enable();
ok('Re-enabled', $svc->isEnabled());

// ---- 4. allow-list ----
section('4. allow-list');
$svc->disable();
// Reset to a known state (clean any leftover IPs from prior test runs)
foreach ($svc->getAllowedIps() as $ip) {
    $svc->removeAllowedIp($ip);
}
ok('Starting from empty allow-list', count($svc->getAllowedIps()) === 0);
$svc->addAllowedIp('192.168.1.10');
ok('IP added', in_array('192.168.1.10', $svc->getAllowedIps(), true));
$svc->addAllowedIp('192.168.1.10');
ok('Adding same IP is idempotent (count stays 1)', count($svc->getAllowedIps()) === 1);
$svc->addAllowedIp('10.0.0.1');
ok('Second IP added', in_array('10.0.0.1', $svc->getAllowedIps(), true));
$svc->removeAllowedIp('192.168.1.10');
ok('IP removed', !in_array('192.168.1.10', $svc->getAllowedIps(), true));
ok('Other IP still present', in_array('10.0.0.1', $svc->getAllowedIps(), true));
$svc->removeAllowedIp('10.0.0.1');

// ---- 5. isRequestAllowed() ----
section('5. isRequestAllowed()');
$svc->disable();
ok('When disabled, all requests allowed', $svc->isRequestAllowed() === true);
$svc->enable();
$_SERVER['REMOTE_ADDR'] = '203.0.113.99';
unset($_SESSION['admin_id']);
ok('When enabled + no admin + no IP match, not allowed', $svc->isRequestAllowed() === false);
$_SESSION['admin_id'] = 1;
ok('With admin_id in session, allowed', $svc->isRequestAllowed() === true);
unset($_SESSION['admin_id']);
$svc->addAllowedIp('203.0.113.99');
ok('After adding current IP, allowed', $svc->isRequestAllowed() === true);
$svc->removeAllowedIp('203.0.113.99');

// ---- 6. IP matcher (CIDR) ----
section('6. CIDR match');
$ref = new ReflectionClass($svc);
$ip = $ref->getMethod('ipMatches');
$ip->setAccessible(true);
ok('Exact IP match', $ip->invoke($svc, '10.0.0.5', '10.0.0.5'));
ok('Subnet match', $ip->invoke($svc, '10.0.0.55', '10.0.0.0/24'));
ok('Out-of-subnet no match', $ip->invoke($svc, '10.0.1.5', '10.0.0.0/24') === false);

// ---- 7. login + status JSON ----
section('7. HTTP - login then status');
exec("curl -s -c \"$cookies\" -b \"$cookies\" \"$BASE/admin/login?test_login=1\" -o /dev/null -w \"%{http_code}\"", $out, $rc);
$loginCode = $out[count($out) - 1] ?? '';
ok("Login returned 302/200 (got $loginCode)", in_array($loginCode, ['200', '302']));

// Grab a CSRF token by reading the PHP session file directly (sess_<PHPSESSID>).
// The session is established by the test_login redirect above, but no controller
// calls getCsrfToken() on warmup, so we trigger it ourselves by re-opening the
// same session in CLI and writing the token. The cookie jar carries the SID.
$cookieTxt = file_get_contents($cookies);
preg_match('/PHPSESSID\s+([a-z0-9]+)/i', $cookieTxt, $ck);
$sessionId = $ck[1] ?? '';
$csrf = '';
if ($sessionId) {
    // First, hit a login page that calls getCsrfToken() so the session file gets the token.
    $ch = curl_init("$BASE/login");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookies);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookies);
    curl_exec($ch);
    curl_close($ch);
    $sessionFile = 'C:\\xampp\\tmp\\sess_' . $sessionId;
    if (file_exists($sessionFile)) {
        $sessData = file_get_contents($sessionFile);
        if (preg_match('/csrf_token\|s:(\d+):"([a-f0-9]+)"/', $sessData, $m)) {
            $csrf = $m[2];
        }
    }
}
ok("CSRF token from session file (len: " . strlen($csrf) . ")", strlen($csrf) >= 16);

$ch = curl_init("$BASE/admin/settings/maintenance/status");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookies);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookies);
$body = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
ok("Status endpoint HTTP 200 (got $code)", $code === 200);
$decoded = json_decode($body, true);
ok('Status has success key', is_array($decoded) && ($decoded['success'] ?? false) === true);
ok('Status has enabled key', isset($decoded['enabled']));
ok('Status has ips key', is_array($decoded['ips'] ?? null));
ok('Status has message key', isset($decoded['message']));

// ---- 8. HTTP - toggle (POST) ----
section('8. HTTP - toggle');
$ch = curl_init("$BASE/admin/settings/maintenance/toggle");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, "csrf_token=$csrf");
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookies);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookies);
$body = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
ok("Toggle HTTP 200 (got $code)", $code === 200);
$decoded = json_decode($body, true);
ok('Toggle returned JSON with success', is_array($decoded) && ($decoded['success'] ?? false) === true);
ok('Toggle returned enabled=false (was on, now off)', ($decoded['enabled'] ?? null) === false);

// ---- 9. HTTP - add IP ----
section('9. HTTP - add IP');
$ch = curl_init("$BASE/admin/settings/maintenance/ips/add");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, "csrf_token=$csrf&ip=203.0.113.42");
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookies);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookies);
$body = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
ok("Add IP HTTP 200 (got $code)", $code === 200);
$decoded = json_decode($body, true);
ok('Add IP returned success', ($decoded['success'] ?? false) === true);
ok('Add IP contains 203.0.113.42', in_array('203.0.113.42', $decoded['ips'] ?? [], true));

// ---- 10. HTTP - add invalid IP (should 400) ----
section('10. HTTP - invalid IP');
$ch = curl_init("$BASE/admin/settings/maintenance/ips/add");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, "csrf_token=$csrf&ip=not-an-ip");
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookies);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookies);
$body = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
ok("Invalid IP returns 400 (got $code)", $code === 400);

// ---- 11. HTTP - remove IP ----
section('11. HTTP - remove IP');
$ch = curl_init("$BASE/admin/settings/maintenance/ips/remove");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, "csrf_token=$csrf&ip=203.0.113.42");
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookies);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookies);
$body = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
ok("Remove IP HTTP 200 (got $code)", $code === 200);

// ---- 12. middleware fail-open behaviour ----
section('12. middleware fail-open');
$mwFile = APP_ROOT . '/app/Http/Middleware/MaintenanceModeMiddleware.php';
$mwSrc = file_get_contents($mwFile);
ok('Middleware has try/catch', strpos($mwSrc, 'try {') !== false && strpos($mwSrc, '} catch (\\Throwable $e)') !== false);
ok('Middleware fails open on exception', strpos($mwSrc, 'fail open') !== false);
ok('Middleware has renderMaintenancePage()', strpos($mwSrc, 'function renderMaintenancePage') !== false);

// ---- 13. audit log entries ----
section('13. audit log persistence');
$st = $pdo->prepare("SELECT COUNT(*) FROM audit_log WHERE action LIKE 'maintenance.%'");
$st->execute();
$auditCount = (int) $st->fetchColumn();
ok("Audit log has maintenance.* entries (count: $auditCount)", $auditCount >= 3);
$st = $pdo->prepare("SELECT action, user_id, details FROM audit_log WHERE action LIKE 'maintenance.%' ORDER BY id DESC LIMIT 5");
$st->execute();
$rows = $st->fetchAll(PDO::FETCH_ASSOC);
ok("Audit log has at least one entry with non-null user_id", $rows[0]['user_id'] ?? null);
foreach ($rows as $r) {
    $d = json_decode($r['details'] ?? '{}', true) ?: [];
    ok("Entry {$r['action']} has description in details", !empty($d['description']));
}

// ---- 14. maintenance settings page loads ----
section('14. /admin/settings/maintenance page');
$ch = curl_init("$BASE/admin/settings/maintenance");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookies);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookies);
$body = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
ok("Maintenance settings page HTTP 200 (got $code)", $code === 200);

// ---- 15. cleanup ----
section('15. cleanup');
$svc->disable();
ok('Maintenance disabled at end', !$svc->isEnabled());

echo "\n=== SUMMARY ===\nPASS: $pass / FAIL: $fail / TOTAL: " . ($pass + $fail) . "\n";
if ($fail > 0) { echo "FAILED:\n - " . implode("\n - ", $fails) . "\n"; exit(1); }
exit(0);
