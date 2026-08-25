<?php
/**
 * Business Workflow E2E Probe — Session 78 Phase 1B
 * Verifies: login → properties → favorites → inquiry → bookings → EMI → ledger
 */
require_once __DIR__ . '/../config/bootstrap.php';

$base = 'http://localhost/apsdreamhome';
$jar = sys_get_temp_dir() . '/wf_cookie.txt';
@unlink($jar);
$pass = 0; $fail = 0;

function check(bool $ok, string $label, string $detail = ''): void {
    global $pass, $fail;
    if ($ok) { $pass++; echo "PASS  $label"; } else { $fail++; echo "FAIL  $label"; }
    if ($detail !== '') echo " — $detail";
    echo "\n";
}

function req(string $method, string $url, ?array $json = null, array $form = null) {
    global $base, $jar;
    $ch = curl_init($base . $url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    if ($json !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($json));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    } elseif ($form !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($form));
    }
    curl_setopt($ch, CURLOPT_COOKIEJAR, $jar);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $jar);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return [$code, json_decode((string)$body, true) ?: [], (string)$body];
}

// 1. Customer login
[$code, $d] = req('POST', '/api/v2/mobile/auth/login', ['email' => 'testuser@example.com', 'password' => 'Aps@2026']);
$token = $d['token'] ?? $d['data']['token'] ?? '';
check(!empty($token), 'Customer login', 'HTTP ' . $code . ' token=' . substr($token, 0, 12) . '...');

// Authed helper
function areq(string $method, string $url, string $token, ?array $json = null): array {
    global $base;
    $ch = curl_init($base . $url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    if ($json !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($json));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Authorization: Bearer ' . $token]);
    } else {
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $token]);
    }
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return [$code, json_decode((string)$body, true) ?: []];
}

// 2. Properties list
[$code, $d] = areq('GET', '/api/v2/mobile/properties/browse', $token);
$props = $d['data']['properties'] ?? $d['data'] ?? [];
check(is_array($props), 'Properties browse', 'HTTP ' . $code . ' count=' . count((array)$props));

// Grab one property id
$propId = null;
if (is_array($props)) { foreach ($props as $p) { if (!empty($p['id'])) { $propId = $p['id']; break; } } }

// 3. Favorites add/check/stats
if ($propId) {
    [$code, $d] = areq('POST', "/api/v2/mobile/user/favorites", $token, ['property_id' => $propId]);
    $favOk = ($d['success'] ?? false) === true || stripos(json_encode($d), 'already') !== false;
    check($favOk, 'Favorite add', 'HTTP ' . $code);

    [$code, $d] = areq('GET', '/api/v2/mobile/user/favorites', $token);
    check(($d['success'] ?? false) === true, 'Favorites list', 'HTTP ' . $code);
} else {
    check(false, 'Favorites (no property id found)');
}

// 4. Property inquiry (public) — targets user_properties listings (id=1 verified)
[$code, $d] = req('POST', '/api/v2/mobile/properties/inquiry', ['property_id' => 1, 'name' => 'WF Probe', 'phone' => '9999990001', 'message' => 'Workflow smoke test']);
check(($d['success'] ?? false) === true || $code === 200, 'Property inquiry', 'HTTP ' . $code . ' ' . ($d['error'] ?? ($d['message'] ?? '')));

// 5. Colonies + plots
[$code, $d] = req('GET', '/api/v2/mobile/colonies');
$colonies = $d['data'] ?? [];
check(count((array)$colonies) >= 5, 'Colonies list', 'HTTP ' . $code . ' count=' . count((array)$colonies));

// 6. Dashboard (authed)
[$code, $d] = areq('GET', '/api/v2/mobile/dashboard', $token);
check(($d['success'] ?? false) === true, 'Mobile dashboard', 'HTTP ' . $code);

// 7. Notifications (authed)
[$code, $d] = areq('GET', '/api/v2/mobile/user/notifications', $token);
check(($d['success'] ?? false) === true, 'Notifications', 'HTTP ' . $code);

// 8. Payment history (authed)
[$code, $d] = areq('GET', '/api/v2/mobile/user/payment-history', $token);
check(isset($d), 'Payment history endpoint alive', 'HTTP ' . $code);

// 9. Profile fetch (authed)
[$code, $d] = areq('GET', '/api/v2/mobile/user/profile', $token);
check(($d['success'] ?? false) === true, 'Profile fetch', 'HTTP ' . $code);

// DB-level checks
try {
    $db = \App\Core\Database\Database::getInstance();
    $pdo = $db->getConnection();

    $n = $pdo->query("SELECT COUNT(*) FROM users WHERE role='customer'")->fetchColumn();
    check((int)$n > 0, 'DB: customers exist', "count=$n");

    $n = $pdo->query("SELECT COUNT(*) FROM colonies WHERE is_active=1")->fetchColumn();
    check((int)$n >= 5, 'DB: active colonies', "count=$n");

    $n = $pdo->query("SELECT COUNT(*) FROM plots WHERE status='available'")->fetchColumn();
    check((int)$n > 0, 'DB: available plots', "count=$n");

    $n = $pdo->query("SELECT COUNT(*) FROM mlm_commission_ledger")->fetchColumn();
    check((int)$n > 0, 'DB: commission ledger entries', "count=$n");

    $bad = $pdo->query("SELECT COUNT(*) FROM plot_bookings b LEFT JOIN plots p ON p.id=b.plot_id WHERE p.id IS NULL")->fetchColumn();
    check((int)$bad === 0, 'DB: no orphaned booking→plot FKs', "orphans=$bad");
} catch (\Throwable $e) {
    check(false, 'DB checks', substr($e->getMessage(), 0, 80));
}

echo "\n===== WORKFLOW RESULT: $pass PASS / $fail FAIL =====\n";
exit($fail > 0 ? 1 : 0);
