<?php
/**
 * Withdrawal Lifecycle Money Probe (self-cleaning)
 *
 * Full money path: synthetic customer → wallet seed → negative
 * (over-balance rejected, nothing moves) → request ₹1000 (deducted) →
 * admin reject (refunded) → full cleanup, zero leftovers.
 *
 * Usage: php testing/withdrawal_lifecycle_probe.php
 * Exit 0 = all assertions pass, 1 = failure (details printed).
 */
define('APS_ROOT', dirname(__DIR__));
require_once APS_ROOT . '/config/bootstrap.php';

$BASE = 'http://localhost/apsdreamhome';
$stamp = date('YmdHis');
$email = "probe_wd_{$stamp}@t.local";
$pass = 'Probe@1234';
$results = [];
$ok = function ($name, $cond, $detail = '') use (&$results) {
    $results[] = [$name, $cond];
    echo ($cond ? "  OK $name" : "  FAIL $name") . ($detail !== '' && !$cond ? " — $detail" : "") . "\n";
};

$db = \App\Core\Database\Database::getInstance()->getConnection();
$uid = $wid = $bid = $reqId = null;

function http($method, $url, $params, $jar) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $jar);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $jar);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 20);
    if ($method === 'POST') { curl_setopt($ch, CURLOPT_POST, true); curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params)); }
    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return [$code, $body];
}
function csrf($html) {
    if (preg_match('/name=["\']csrf_token["\']\s+value=["\']([^"\']+)/', $html, $m)) return $m[1];
    if (preg_match('/value=["\']([^"\']+)["\']\s+name=["\']csrf_token["\']/', $html, $m)) return $m[1];
    return null;
}

try {
    // ---- Setup: synthetic customer + wallet + bank ----
    $stmt = $db->prepare("INSERT INTO users (tenant_id, name, email, phone, password, role, status) VALUES (1, 'Probe WD', ?, ?, ?, 'customer', 'active')");
    $stmt->execute([$email, '90000' . substr($stamp, -5), password_hash($pass, PASSWORD_BCRYPT)]);
    $uid = (int)$db->lastInsertId();
    $db->prepare("INSERT INTO wallet_points (tenant_id, user_id, points_balance, total_earned, status) VALUES (1, ?, 5000.00, 5000.00, 'active')")->execute([$uid]);
    $db->prepare("INSERT INTO user_bank_accounts (user_id, bank_name, account_holder_name, account_number, ifsc_code, status) VALUES (?, 'HDFC Bank', 'Probe WD', '50100223344554', 'HDFC0001234', 'active')")->execute([$uid]);
    $bid = (int)$db->lastInsertId();
    $ok('setup synthetic customer+wallet+bank', $uid > 0 && $bid > 0);

    // ---- Customer login ----
    $jar = sys_get_temp_dir() . '/probe_wd_' . $stamp . '.jar';
    [$c, $loginPage] = http('GET', "$BASE/login", [], $jar);
    $tok = csrf($loginPage);
    $ok('login page serves csrf', !empty($tok));
    [$c, $b] = http('POST', "$BASE/login", ['email' => $email, 'password' => $pass, 'csrf_token' => $tok], $jar);
    $ok('customer login (302 away from /login)', $c >= 300 && $c < 400, "code=$c");

    // ---- Withdrawal page + token ----
    [$c, $wpage] = http('GET', "$BASE/wallet/withdrawal", [], $jar);
    $wtok = csrf($wpage);
    $ok('withdrawal page 200 + csrf', $c === 200 && !empty($wtok), "code=$c");

    // ---- Negative: over-balance ----
    [$c, $b] = http('POST', "$BASE/wallet/withdrawal/process", ['bank_account_id' => $bid, 'amount' => 99999, 'csrf_token' => $wtok], $jar);
    $j = json_decode($b, true);
    $bal = $db->query("SELECT points_balance FROM wallet_points WHERE user_id = $uid")->fetchColumn();
    $reqs = $db->query("SELECT COUNT(*) FROM withdrawal_requests WHERE user_id = $uid")->fetchColumn();
    $ok('over-balance rejected, nothing moves', ($j['success'] ?? true) === false && (float)$bal === 5000.00 && (int)$reqs === 0, substr($b, 0, 120));

    // ---- Positive: request 1000 ----
    [$c, $wpage2] = http('GET', "$BASE/wallet/withdrawal", [], $jar);
    $wtok2 = csrf($wpage2) ?: $wtok;
    [$c, $b] = http('POST', "$BASE/wallet/withdrawal/process", ['bank_account_id' => $bid, 'amount' => 1000, 'csrf_token' => $wtok2], $jar);
    $j = json_decode($b, true);
    $bal = $db->query("SELECT points_balance, total_used FROM wallet_points WHERE user_id = $uid")->fetch(PDO::FETCH_ASSOC);
    $req = $db->query("SELECT * FROM withdrawal_requests WHERE user_id = $uid ORDER BY id DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    $led = $req ? $db->query("SELECT * FROM wallet_transactions WHERE reference_id = " . (int)$req['id'] . " AND transaction_category = 'withdrawal'")->fetch(PDO::FETCH_ASSOC) : false;
    $reqId = $req ? (int)$req['id'] : null;
    $ok(
        'request deducts + pending + ledger',
        ($j['success'] ?? false) === true && (float)$bal['points_balance'] === 4000.00 && ($req['status'] ?? '') === 'pending' && $led && (float)$led['amount'] === 1000.00,
        substr($b, 0, 160)
    );

    // ---- Admin reject → refund (only if request was created) ----
    if ($reqId) {
    $ajar = sys_get_temp_dir() . '/probe_wd_admin_' . $stamp . '.jar';
    http('GET', "$BASE/admin/login?test_login=1", [], $ajar);
    [$c, $apage] = http('GET', "$BASE/admin/mlm/withdrawals", [], $ajar);
    $atok = csrf($apage);
    $ok('admin session + csrf', $c === 200 && !empty($atok), "code=$c");
    http('POST', "$BASE/admin/mlm-rewards/withdrawals/$reqId/status", ['status' => 'rejected', 'admin_notes' => 'probe reject', 'csrf_token' => $atok], $ajar);
    $bal2 = $db->query("SELECT points_balance FROM wallet_points WHERE user_id = $uid")->fetchColumn();
    $st2 = $db->query("SELECT status FROM withdrawal_requests WHERE id = $reqId")->fetchColumn();
    $ok('reject refunds in full', (float)$bal2 === 5000.00 && $st2 === 'rejected', "bal=$bal2 status=$st2");
    @unlink($ajar);
    } else {
        $ok('admin reject phase (skipped, no request)', false, 'request step failed');
    }
    @unlink($jar);
} catch (\Throwable $e) {
    echo "  EXCEPTION: " . $e->getMessage() . "\n";
    $results[] = ['no-exception', false];
}

$p = count(array_filter($results, fn($r) => $r[1]));
$f = count($results) - $p;
echo "Withdrawal lifecycle: $p passed, $f failed (" . count($results) . " checks)\n";

// ---- Cleanup (always): zero leftovers ----
try {
    if ($reqId) $db->exec("DELETE FROM wallet_transactions WHERE reference_id = $reqId AND transaction_category = 'withdrawal'");
    if ($reqId) $db->exec("DELETE FROM withdrawal_requests WHERE id = $reqId");
    if ($bid) $db->exec("DELETE FROM user_bank_accounts WHERE id = $bid");
    if ($uid) $db->exec("DELETE FROM wallet_points WHERE user_id = $uid");
    if ($uid) $db->exec("DELETE FROM users WHERE id = $uid");
    $left = $db->query("SELECT COUNT(*) FROM users WHERE email = '$email'")->fetchColumn();
    echo "Cleanup leftovers: $left (must be 0)\n";
    if ((int)$left !== 0) $f++;
} catch (\Throwable $e) {
    echo "Cleanup error: " . $e->getMessage() . "\n";
    $f++;
}
exit($f > 0 ? 1 : 0);
