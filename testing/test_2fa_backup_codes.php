<?php
/**
 * E2E Test for 2FA Backup Recovery Code Flow
 *
 * Steps:
 *  1. Login as customer1@apsdreamhome.com / Test1234
 *  2. Reset 2FA state (force-disable) so we can test the full enable flow
 *  3. Visit /user/two-factor - should show QR + manual key
 *  4. Compute current OTP from the secret stored in DB
 *  5. POST to /user/two-factor/enable with the OTP
 *  6. Should redirect to /user/two-factor/backup-codes
 *  7. Extract one of the 8 backup codes from the page
 *  8. Logout (clear session)
 *  9. Login again with the same credentials
 * 10. Should be redirected to /user/two-factor/verify (2FA pending)
 * 11. Visit /user/two-factor/recovery - shows backup code form
 * 12. POST the backup code to /user/two-factor/recovery/verify
 * 13. Should redirect to /user/dashboard (200)
 * 14. Verify the used code cannot be reused (POST again -> redirect with error)
 * 15. Disable 2FA
 * 16. Visit /user/two-factor/disabled
 */

$BASE = 'http://localhost/apsdreamhome';
$EMAIL = 'customer1@apsdreamhome.com';
$PASS  = 'Aps@2026';

$jar = sys_get_temp_dir() . '/2fa_test_' . uniqid() . '.cookie';
$ch  = null;

function http($method, $url, $jar, $postBody = null, $csrf = null) {
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_COOKIEJAR => $jar,
        CURLOPT_COOKIEFILE => $jar,
        CURLOPT_HEADER => true,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_SSL_VERIFYPEER => false,
    ]);
    $headers = ['Accept: text/html,application/x-www-form-urlencoded'];
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if (is_array($postBody)) {
            $body = http_build_query($postBody);
            $headers[] = 'Content-Type: application/x-www-form-urlencoded';
        } else {
            $body = $postBody ?: '';
        }
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    }
    if ($csrf) $headers[] = 'X-CSRF-Token: ' . $csrf;
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $rawResp = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    curl_close($ch);
    $rawHeaders = substr($rawResp, 0, $headerSize);
    $body = substr($rawResp, $headerSize);
    $location = '';
    foreach (explode("\r\n", $rawHeaders) as $line) {
        if (stripos($line, 'Location:') === 0) { $location = trim(substr($line, 9)); break; }
    }
    return ['code' => $code, 'body' => $body, 'headers' => $rawHeaders, 'location' => $location];
}

function getCsrfFromHtml($html) {
    if (preg_match('/name="csrf_token"\s+value="([^"]+)"/', $html, $m)) return $m[1];
    if (preg_match('/name="csrf_token"\s+value=\'([^\']+)\'/', $html, $m)) return $m[1];
    if (preg_match('/<meta\s+name="csrf-token"\s+content="([^"]+)"/i', $html, $m)) return $m[1];
    return '';
}

function header_line($c) { echo "\n=== $c ===\n"; }
function ok($msg)   { echo "  [PASS] $msg\n"; }
function fail($msg) { echo "  [FAIL] $msg\n"; global $FAILS; $FAILS++; }
global $FAILS; $FAILS = 0;

// DB helper to seed/clear test state
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
]);

echo "=== 2FA E2E TEST ===\n";
echo "Base URL: $BASE\n";
echo "Test user: $EMAIL\n\n";

// ============================================================
// STEP 0: Reset 2FA state on test user (force-disable)
// ============================================================
header_line('STEP 0: Reset 2FA state');
$pdo->prepare("UPDATE users SET two_factor_enabled = 0, two_factor_secret = NULL, two_factor_backup_codes = NULL WHERE email = ?")
    ->execute([$EMAIL]);
$pdo->prepare("DELETE FROM two_factor_backup_codes_log WHERE user_id = (SELECT id FROM users WHERE email = ?)")
    ->execute([$EMAIL]);
ok("2FA reset (cleared secret, backup codes, and used-codes log)");

// ============================================================
// STEP 1: Login
// ============================================================
header_line('STEP 1: Login');
@unlink($jar);
$r = http('GET', "$BASE/login", $jar);
$csrf = getCsrfFromHtml($r['body']);
if (!$csrf) fail("Could not extract CSRF token from /login"); else ok("Got CSRF token");

$r = http('POST', "$BASE/login", $jar, ['csrf_token' => $csrf, 'identity' => $EMAIL, 'password' => $PASS]);
// Should be 302 to /user/dashboard (or /admin/dashboard for admin), or to /user/two-factor/verify
if ($r['code'] == 302 && (
    strpos(($r['body'] ?? ''), '/user/dashboard') !== false ||
    strpos($r['location'] ?? '', '/user/dashboard') !== false ||
    strpos($r['location'] ?? '', '/admin/dashboard') !== false ||
    strpos(($r['body'] ?? '') . ($r['location'] ?? ''), '/user/two-factor/verify') !== false
)) {
    ok("Login successful (HTTP 302)");
} else {
    // curl may not capture redirect URL in body if -L is off; just check 302
    if ($r['code'] == 302) ok("Login got 302 (redirect)");
    else {
        // show a snippet of body to debug
        $snippet = substr(strip_tags($r['body'] ?? ''), 0, 200);
        fail("Login failed (HTTP {$r['code']}): $snippet");
    }
}

// ============================================================
// STEP 2: Visit /user/two-factor and get the secret
// ============================================================
header_line('STEP 2: Visit /user/two-factor');
$r = http('GET', "$BASE/user/two-factor", $jar);
if ($r['code'] != 200) { fail("Expected 200, got {$r['code']}"); }
else ok("Got 200 from /user/two-factor");

// Use the cookie-based session ID directly — this is the only reliable way to
// find the actual current session (file-search via email/2fa_temp_secret picks
// up OLD sessions from previous test runs and produces CSRF mismatches).
$sessId = null;
$searchPaths = ['C:\\xampp\\tmp', sys_get_temp_dir()];
$cookieLine = @file_get_contents($jar);
if (preg_match('/PHPSESSID\s+(\S+)/', $cookieLine, $m)) {
    $sid = $m[1];
    foreach ($searchPaths as $p) {
        $candidate = $p . "/sess_$sid";
        if (file_exists($candidate)) { $sessId = $candidate; break; }
    }
}
if (!$sessId) {
    echo "  [WARN] Could not locate session file via cookie; falling back to glob search\n";
    foreach ($searchPaths as $p) {
        foreach (glob($p . '/sess_*') as $f) {
            $content = @file_get_contents($f);
            if ($content && strpos($content, $EMAIL) !== false && strpos($content, '2fa_temp_secret') !== false) {
                $sessId = $f;
                break 2;
            }
        }
    }
}

// Extract CSRF from session for later use
$csrf = '';
if ($sessId && file_exists($sessId)) {
    $sessContent = @file_get_contents($sessId);
    if (preg_match('/csrf_token\|s:\d+:"([^"]+)"/', $sessContent, $m)) {
        $csrf = $m[1];
    }
}

$secret = null;
if ($sessId && file_exists($sessId)) {
    $sess = @file_get_contents($sessId);
    if (preg_match('/2fa_temp_secret\|s:(\d+):"([^"]+)"/', $sess, $m)) {
        $secret = $m[2];
    }
}

if (!$secret) {
    // Fallback: read from DB
    $row = $pdo->query("SELECT two_factor_secret FROM users WHERE email = '$EMAIL'")->fetch();
    $secret = $row['two_factor_secret'] ?? null;
}

if (!$secret) fail("Could not find the 2FA temp secret");
else ok("Got 2FA temp secret: $secret");

// Verify QR code URL is generated (we can't render the image in curl, but the URL should be in the HTML)
if (strpos($r['body'], 'qrserver.com') !== false) ok("QR code URL present (uses api.qrserver.com)");
else fail("QR code URL not in page");

if (strpos($r['body'], 'Backup') !== false || strpos($r['body'], 'backup') !== false) ok("Backup codes section referenced in /user/two-factor");
else fail("No backup section in /user/two-factor page");

// Compute current OTP using the same algorithm as TotpService
function base32Decode($s) {
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    $s = strtoupper(rtrim($s, '='));
    $bin = '';
    foreach (str_split($s) as $c) $bin .= str_pad(decbin(strpos($chars, $c)), 5, '0', STR_PAD_LEFT);
    $out = '';
    foreach (str_split($bin, 8) as $b) { if (strlen($b) === 8) $out .= chr(bindec($b)); }
    return $out;
}
function genOtp($secret) {
    $slice = floor(time() / 30);
    $key = base32Decode($secret);
    $time = pack('N*', 0) . pack('N*', $slice);
    $hash = hash_hmac('sha1', $time, $key, true);
    $off = ord($hash[strlen($hash) - 1]) & 0xf;
    $code = (((ord($hash[$off]) & 0x7f) << 24) | ((ord($hash[$off + 1]) & 0xff) << 16) | ((ord($hash[$off + 2]) & 0xff) << 8) | (ord($hash[$off + 3]) & 0xff)) % 1000000;
    return str_pad((string)$code, 6, '0', STR_PAD_LEFT);
}
$otp = genOtp($secret);
ok("Computed current OTP: $otp");

// ============================================================
// STEP 3: POST to /user/two-factor/enable
// ============================================================
header_line('STEP 3: Enable 2FA');
$r = http('POST', "$BASE/user/two-factor/enable", $jar, ['csrf_token' => $csrf, 'code' => $otp]);
if ($r['code'] == 302) {
    ok("Got 302 after enable");
    echo "  DEBUG Location: " . ($r['location'] ?? '?') . "\n";
    echo "  DEBUG headers full: " . str_replace(["\r", "\n"], ' | ', $r['headers']) . "\n";
} else {
    fail("Expected 302, got {$r['code']}");
    echo "  DEBUG body: " . substr(strip_tags($r['body']), 0, 500) . "\n";
    echo "  DEBUG headers: " . str_replace(["\r", "\n"], ' | ', $r['headers']) . "\n";
}

// Follow the redirect: should be /user/two-factor/backup-codes
$r = http('GET', "$BASE/user/two-factor/backup-codes", $jar);
if ($r['code'] == 200) {
    ok("Got 200 from /user/two-factor/backup-codes");
} else {
    fail("Expected 200, got {$r['code']} location=" . ($r['location'] ?? '?'));
    echo "  DEBUG body: " . substr(strip_tags($r['body']), 0, 300) . "\n";
}

// Extract a backup code from the page (8-9 char alphanum in <code>)
$backupCode = null;
if (preg_match('/<code[^>]*>([A-Z0-9]{6,12})<\/code>/', $r['body'], $m)) {
    $backupCode = $m[1];
}
if (!$backupCode) fail("Could not extract backup code from page");
else ok("Extracted backup code: $backupCode");

// Check the page has Download / Print / Copy buttons
foreach (['Download as .txt', 'Print', 'Copy All', "I've saved my codes"] as $txt) {
    if (strpos($r['body'], $txt) !== false) ok("Page has button: $txt");
    else fail("Missing button: $txt");
}

// Check the warning message
if (strpos($r['body'], 'Save these codes') !== false || strpos($r['body'], 'save') !== false) ok("Warning message about saving codes");
else fail("Missing warning message");

// ============================================================
// STEP 4: Verify backup codes are stored persistently in DB
// ============================================================
header_line('STEP 4: Verify backup codes in DB');
$row = $pdo->prepare("SELECT two_factor_backup_codes FROM users WHERE email = ?");
$row->execute([$EMAIL]);
$codesJson = $row->fetchColumn();
$codesArr = json_decode($codesJson, true);
if (is_array($codesArr) && count($codesArr) === 8) ok("DB has 8 backup codes stored");
else fail("DB backup codes count = " . (is_array($codesArr) ? count($codesArr) : 'invalid'));

// ============================================================
// STEP 5: Logout
// ============================================================
header_line('STEP 5: Logout');
$r = http('GET', "$BASE/user/logout", $jar);
if ($r['code'] == 302 || $r['code'] == 200) ok("Logout completed (HTTP {$r['code']})");
else fail("Logout got HTTP {$r['code']}");

// ============================================================
// STEP 6: Login again -> 2FA pending
// ============================================================
header_line('STEP 6: Login again (2FA should be required)');
@unlink($jar);
$r = http('GET', "$BASE/login", $jar);
$csrf = getCsrfFromHtml($r['body']);
$r = http('POST', "$BASE/login", $jar, ['csrf_token' => $csrf, 'identity' => $EMAIL, 'password' => $PASS]);
if ($r['code'] != 302) { fail("Login redirect expected, got {$r['code']}"); }
else {
    $loc = $r['location'] ?? '';
    if (strpos($loc, '/user/two-factor/verify') !== false) {
        ok("Login redirected to 2FA verify page: $loc");
    } else {
        fail("Expected redirect to /user/two-factor/verify, got: $loc");
    }
}

// ============================================================
// STEP 7: Visit /user/two-factor/recovery
// ============================================================
header_line('STEP 7: Visit /user/two-factor/recovery');
$r = http('GET', "$BASE/user/two-factor/recovery", $jar);
if ($r['code'] == 200) ok("Got 200 from /user/two-factor/recovery");
else fail("Expected 200, got {$r['code']}");

if (strpos($r['body'], 'Use Backup Code') !== false || strpos($r['body'], 'Backup Code') !== false) ok("Recovery page rendered");
else fail("Recovery page content not found");

if (strpos($r['body'], 'recovery/verify') !== false) ok("Form posts to recovery/verify");
else fail("Form action missing - body had: " . substr(strip_tags($r['body']), 0, 300));

// Extract CSRF for the recovery POST
$csrfRecovery = getCsrfFromHtml($r['body']);

// ============================================================
// STEP 8: POST backup code
// ============================================================
header_line('STEP 8: POST backup code');
$r = http('POST', "$BASE/user/two-factor/recovery/verify", $jar, ['csrf_token' => $csrfRecovery, 'code' => $backupCode]);
if ($r['code'] == 302) {
    ok("Got 302 after backup code verify");
    $loc = $r['location'] ?? '';
    if (strpos($loc, '/user/dashboard') !== false) ok("Redirected to /user/dashboard: $loc");
    else fail("Expected /user/dashboard, got: $loc");
} else {
    fail("Expected 302, got {$r['code']}");
}

// Verify the user is actually logged in by hitting /user/dashboard
$r = http('GET', "$BASE/user/dashboard", $jar);
if ($r['code'] == 200) ok("/user/dashboard returns 200 (logged in)");
else fail("/user/dashboard got HTTP {$r['code']}");

// ============================================================
// STEP 9: Verify the used code cannot be reused
// ============================================================
header_line('STEP 9: Verify used code cannot be reused');
// Logout and login again
$r = http('GET', "$BASE/user/logout", $jar);
@unlink($jar);
$r = http('GET', "$BASE/login", $jar);
$csrf = getCsrfFromHtml($r['body']);
$r = http('POST', "$BASE/login", $jar, ['csrf_token' => $csrf, 'identity' => $EMAIL, 'password' => $PASS]);
$r = http('GET', "$BASE/user/two-factor/recovery", $jar);
$csrfReuse = getCsrfFromHtml($r['body']);

// Try to use the already-used code
$r = http('POST', "$BASE/user/two-factor/recovery/verify", $jar, ['csrf_token' => $csrfReuse, 'code' => $backupCode]);
if ($r['code'] == 302) {
    $loc = $r['location'] ?? '';
    if (strpos($loc, '/user/dashboard') !== false) {
        fail("Used backup code was accepted! SECURITY BUG. Redirected to: $loc");
    } else {
        ok("Used code was rejected (redirected back to: $loc)");
    }
} else {
    fail("Expected 302, got {$r['code']}");
}

// ============================================================
// STEP 10: Verify a fresh backup code works
// ============================================================
header_line('STEP 10: Use a fresh backup code');
$row = $pdo->prepare("SELECT two_factor_backup_codes FROM users WHERE email = ?");
$row->execute([$EMAIL]);
$codesJson = $row->fetchColumn();
$codesArr = json_decode($codesJson, true);
if (!is_array($codesArr) || count($codesArr) === 0) {
    fail("No backup codes left in DB");
} else {
    $freshCode = $codesArr[0];
    $r = http('GET', "$BASE/user/two-factor/recovery", $jar);
    $csrfFresh = getCsrfFromHtml($r['body']);
    $r = http('POST', "$BASE/user/two-factor/recovery/verify", $jar, ['csrf_token' => $csrfFresh, 'code' => $freshCode]);
    if ($r['code'] == 302) {
        $loc = $r['location'] ?? '';
        if (strpos($loc, '/user/dashboard') !== false) ok("Fresh backup code works (redirected to: $loc)");
        else fail("Fresh code didn't redirect to dashboard: $loc");
    } else {
        fail("Expected 302, got {$r['code']}");
    }
}

// ============================================================
// STEP 11: Disable 2FA + visit /user/two-factor/disabled
// ============================================================
header_line('STEP 11: Disable 2FA + visit disabled page');
$r = http('GET', "$BASE/user/dashboard", $jar);
$csrfDisable = getCsrfFromHtml($r['body']);
if (!$csrfDisable) {
    // Fallback: get from session file
    $sessContent = @file_get_contents($sessId ?? '');
    if ($sessContent && preg_match('/csrf_token\|s:\d+:"([^"]+)"/', $sessContent, $m)) {
        $csrfDisable = $m[1];
    }
}
$r = http('POST', "$BASE/user/two-factor/disable", $jar, ['csrf_token' => $csrfDisable]);
if ($r['code'] == 302) {
    ok("Disable 2FA got 302 (location=" . ($r['location'] ?? '?') . ")");
} else {
    fail("Disable 2FA got HTTP {$r['code']}");
}

$r = http('GET', "$BASE/user/two-factor/disabled", $jar);
if ($r['code'] == 200) ok("Got 200 from /user/two-factor/disabled");
else fail("Disabled page got HTTP {$r['code']}");

if (strpos($r['body'], '2FA Has Been Disabled') !== false) ok("Disabled page content rendered");
else fail("Disabled page content not found");

if (strpos($r['body'], 'Re-enable 2FA') !== false) ok("Re-enable link present");
else fail("Re-enable link missing");

// ============================================================
// STEP 12: Verify 2FA actually disabled in DB
// ============================================================
header_line('STEP 12: Verify 2FA disabled in DB');
$row = $pdo->prepare("SELECT two_factor_enabled FROM users WHERE email = ?");
$row->execute([$EMAIL]);
$enabled = $row->fetchColumn();
if (empty($enabled)) ok("DB confirms 2FA disabled (two_factor_enabled = 0)");
else fail("DB still shows 2FA enabled");

// ============================================================
// Final summary
// ============================================================
echo "\n=== TEST SUMMARY ===\n";
if ($FAILS === 0) {
    echo "ALL CHECKS PASSED ✅\n";
    @unlink($jar);
    exit(0);
} else {
    echo "FAILED: $FAILS checks ❌\n";
    @unlink($jar);
    exit(1);
}
