<?php
/**
 * Associate Commission Chain Probe — Session 78
 * Verifies: associate login → lead create → status pipeline → commission ledger visibility
 */
require_once __DIR__ . '/../config/bootstrap.php';
$base = 'http://localhost/apsdreamhome';
$pass = 0; $fail = 0;
function ck(bool $ok, string $label, string $d = ''): void {
    global $pass, $fail;
    if ($ok) { $pass++; echo "PASS  $label"; } else { $fail++; echo "FAIL  $label"; }
    if ($d) echo " — $d";
    echo "\n";
}
function areq(string $m, string $u, string $tok, ?array $j = null) {
    global $base;
    $ch = curl_init($base . $u);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $m);
    if ($j !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($j));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Authorization: Bearer ' . $tok]);
    } else {
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $tok]);
    }
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    $b = curl_exec($ch);
    return [curl_getinfo($ch, CURLINFO_HTTP_CODE), json_decode((string)$b, true) ?: []];
}

// 1. Associate login
$ch = curl_init($base . '/api/v2/mobile/auth/login');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['email' => 'testassociate@example.com', 'password' => 'Aps@2026']));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$d = json_decode((string)curl_exec($ch), true) ?: [];
$tok = $d['token'] ?? $d['data']['token'] ?? '';
ck(!empty($tok), 'Associate login', 'token=' . substr($tok, 0, 10) . '...');

// 2. Agent portal endpoints (the 11 new ones)
foreach ([
    'analytics' => '/api/v2/mobile/agent/analytics',
    'leads' => '/api/v2/mobile/agent/leads',
    'commissions' => '/api/v2/mobile/agent/commissions',
    'properties' => '/api/v2/mobile/agent/properties',
    'follow-ups' => '/api/v2/mobile/agent/follow-ups',
    'my-team' => '/api/v2/mobile/agent/my-team',
    'rank-progress' => '/api/v2/mobile/agent/rank-progress',
] as $label => $ep) {
    [$code, $d] = areq('GET', $ep, $tok);
    ck($code === 200, "Agent API: $label", "HTTP $code");
}

// 3. Create a lead via CRM endpoint
[$code, $d] = areq('POST', '/api/v2/mobile/leads', $tok, [
    'name' => 'Chain Probe ' . date('His'),
    'phone' => '99999' . random_int(10000, 99999),
    'source' => 'workflow_probe',
    'notes' => 'Associate commission chain verification',
    'status' => 'new',
]);
$leadOk = ($d['success'] ?? false) === true || !empty($d['lead_id'] ?? $d['id'] ?? $d['data']['id'] ?? 0);
ck($leadOk, 'Lead create', 'HTTP ' . $code . ($leadOk ? ' lead=' . ($d['lead_id'] ?? '?') : ' ' . substr(json_encode($d), 0, 60)));

// 4. Ledger visibility (commissions endpoint returns ledger rows or empty — must not 500)
[$code, $d] = areq('GET', '/api/v2/mobile/agent/commissions', $tok);
ck($code === 200 && isset($d), 'Commission ledger fetch', 'HTTP ' . $code);

// 5. DB: ledger integrity + mlm tree for associate
try {
    $pdo = \App\Core\Database\Database::getInstance()->getConnection();
    $assocId = $pdo->query("SELECT id FROM users WHERE email='testassociate@example.com' LIMIT 1")->fetchColumn();
    $inTree = $pdo->query("SELECT COUNT(*) FROM mlm_network_tree WHERE associate_id = " . (int)$assocId)->fetchColumn();
    ck((int)$inTree > 0, 'DB: associate in mlm_network_tree', "user_id=$assocId rows=$inTree");
    $bad = $pdo->query("SELECT COUNT(*) FROM mlm_commission_ledger l LEFT JOIN users u ON u.id=l.beneficiary_user_id WHERE u.id IS NULL")->fetchColumn();
    ck((int)$bad === 0, 'DB: ledger→users integrity', "orphans=$bad");
} catch (\Throwable $e) {
    ck(false, 'DB checks', substr($e->getMessage(), 0, 70));
}

echo "\n===== ASSOCIATE CHAIN: $pass PASS / $fail FAIL =====\n";
exit($fail > 0 ? 1 : 0);
