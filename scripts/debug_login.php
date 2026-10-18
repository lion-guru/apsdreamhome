<?php
require_once __DIR__ . '/../config/bootstrap.php';

$BASE = 'http://localhost/apsdreamhome';
$stamp = date('YmdHis');
$email = "probe_debug_{$stamp}@t.local";
$pass = 'Probe@1234';

$db = \App\Core\Database\Database::getInstance()->getConnection();

// Setup: synthetic customer + wallet + bank
$stmt = $db->prepare("INSERT INTO users (tenant_id, name, email, phone, password, role, status) VALUES (1, 'Probe Debug', ?, ?, ?, 'customer', 'active')");
$stmt->execute([$email, '90000' . substr($stamp, -5), password_hash($pass, PASSWORD_BCRYPT)]);
$uid = (int)$db->lastInsertId();
$db->prepare("INSERT INTO wallet_points (tenant_id, user_id, points_balance, total_earned, status) VALUES (1, ?, 5000.00, 5000.00, 'active')")->execute([$uid]);
$db->prepare("INSERT INTO user_bank_accounts (user_id, bank_name, account_holder_name, account_number, ifsc_code, status) VALUES (?, 'HDFC Bank', 'Probe Debug', '50100223344554', 'HDFC0001234', 'active')")->execute([$uid]);
$bid = (int)$db->lastInsertId();
echo "Setup: uid=$uid, bid=$bid\n";

function http($method, $url, $params, $jar) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $jar);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $jar);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 20);
    curl_setopt($ch, CURLOPT_VERBOSE, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    if ($method === 'POST') { 
        curl_setopt($ch, CURLOPT_POST, true); 
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params)); 
    }
    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $header = substr($body, 0, $headerSize);
    $responseBody = substr($body, $headerSize);
    curl_close($ch);
    return [$code, $header, $responseBody];
}
function csrf($html) {
    if (preg_match('/name=["\']csrf_token["\']\s+value=["\']([^"\']+)/', $html, $m)) return $m[1];
    if (preg_match('/value=["\']([^"\']+)["\']\s+name=["\']csrf_token["\']/', $html, $m)) return $m[1];
    return null;
}

$jar = 'C:/temp/probe_debug_' . $stamp . '.jar';
echo "Using jar: $jar\n\n";

// ---- Customer login ----
[$c, $h, $loginPage] = http('GET', "$BASE/login", [], $jar);
echo "GET /login: $c\n";
$tok = csrf($loginPage);
echo "CSRF token: " . ($tok ?: 'NOT FOUND') . "\n\n";

[$c, $h, $b] = http('POST', "$BASE/login", ['email' => $email, 'password' => $pass, 'csrf_token' => $tok], $jar);
echo "POST /login: $c\n";
echo "Response headers:\n$h\n\n";

// Check if session cookie was set
if (preg_match('/Set-Cookie:\s*([^;]+)/i', $h, $m)) {
    echo "Set-Cookie: {$m[1]}\n";
}

// ---- Check wallet page ----
[$c, $h, $wpage] = http('GET', "$BASE/wallet/withdrawal", [], $jar);
echo "\nGET /wallet/withdrawal: $c\n";
echo "Headers:\n$h\n\n";

@unlink($jar);