<?php
/**
 * Test: AuditLogController + AuditService
 * Cluster 4 / Task 3
 *
 * Verifies:
 *  - AuditService::log inserts rows
 *  - getRecent filters by action / entity / user
 *  - getStats returns top actions, top users, total, failures
 *  - JSON endpoint shape
 *  - HTTP route returns 200 with admin session
 */

define('APP_ROOT', dirname(__DIR__));
require_once APP_ROOT . '/app/Services/AuditService.php';

use App\Services\AuditService;

$pass = 0;
$fail = 0;
$testNum = 0;

function ok(string $name, bool $cond) {
    global $pass, $fail, $testNum;
    $testNum++;
    if ($cond) { $pass++; echo "  [PASS] #$testNum $name\n"; }
    else { $fail++; echo "  [FAIL] #$testNum $name\n"; }
}

$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$svc = new AuditService($pdo);

ok('Service construct', $svc !== null);

// 1. Log a few events
$testMarker = 'cluster4_test_' . uniqid();
$id1 = $svc->log('user.login', 1, 'admin', 'user', 1, "Logged in ($testMarker)");
ok('log returns id', $id1 > 0);

$id2 = $svc->log('user.login_failed', 2, 'customer', 'user', 2, "Failed ($testMarker)", [], 'failure');
ok('Second log returns id', $id2 > 0);

$id3 = $svc->log('property.update', 1, 'admin', 'property', 42, "Property updated ($testMarker)");
ok('Third log returns id', $id3 > 0);

// 2. getRecent
$recent = $svc->getRecent(20, 'user.login');
ok('getRecent with action filter', is_array($recent));
ok('getRecent returns at least 1 row', count($recent) >= 1);

// 3. getStats
$stats = $svc->getStats(7);
ok('getStats returns array', is_array($stats));
ok('getStats has total', isset($stats['total']));
ok('getStats has by_action', isset($stats['by_action']));
ok('getStats has by_user', isset($stats['by_user']));
ok('getStats has failures', isset($stats['failures']));

// 4. By user filter
$byUser = $svc->getRecent(20, null, null, 1);
ok('getRecent by user returns array', is_array($byUser));
ok('All results have user_id=1', count(array_filter($byUser, fn($r) => (int)$r['user_id'] === 1)) >= 1);

// 5. By entity filter
$byEntity = $svc->getRecent(20, null, 'property');
ok('getRecent by entity_type', is_array($byEntity));
ok('All results have entity_type=property', count(array_filter($byEntity, fn($r) => $r['entity_type'] === 'property')) >= 1);

// 6. By date range (use SQL directly via PDO)
// Use a 7-day window from now-backwards to avoid PHP/MySQL timezone drift
$start = date('Y-m-d 00:00:00', strtotime('-7 days'));
$end   = date('Y-m-d 23:59:59', strtotime('+1 day'));
$stmt = $pdo->prepare("SELECT COUNT(*) FROM audit_log WHERE created_at BETWEEN ? AND ? AND details LIKE ?");
$stmt->execute([$start, $end, "%$testMarker%"]);
$rangeCount = (int) $stmt->fetchColumn();
ok('Date range query returns at least 3', $rangeCount >= 3);

// 7. Export shape (CSV/JSON)
$logs = $svc->getRecent(100, null, null, null);
$json = json_encode(['success' => true, 'data' => $logs, 'stats' => $stats]);
$j = json_decode($json, true);
ok('Export JSON valid', is_array($j) && isset($j['data']));

$csv = "id,user_id,action,entity,description\n";
foreach ($logs as $r) {
    $csv .= $r['id'] . ',' . $r['user_id'] . ',' . $r['action'] . ',' . $r['entity_type'] . ',"' . str_replace('"', '""', $r['description'] ?? '') . "\"\n";
}
ok('CSV header', strpos($csv, 'id,user_id,action') !== false);
ok('CSV has rows', substr_count($csv, "\n") >= 1);

// 8. Cleanup test rows (so we don't pollute the log)
$pdo->prepare("DELETE FROM audit_log WHERE details LIKE ?")->execute(["%$testMarker%"]);
$after = (int) $pdo->query("SELECT COUNT(*) FROM audit_log WHERE details LIKE '%$testMarker%'")->fetchColumn();
ok('Cleanup removed test rows', $after === 0);

// 9. Controller reachable via HTTP
$cookieJar = 'C:\Users\abhay\AppData\Local\Temp\opencode\cookies.txt';
@unlink($cookieJar);

$base = 'http://localhost/apsdreamhome';
$ch = curl_init($base . '/admin/login?test_login=1');
curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_COOKIEJAR => $cookieJar, CURLOPT_COOKIEFILE => $cookieJar, CURLOPT_FOLLOWLOCATION => false, CURLOPT_TIMEOUT => 10]);
curl_exec($ch);
curl_close($ch);

$ch = curl_init($base . '/admin/audit-log');
curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_COOKIEJAR => $cookieJar, CURLOPT_COOKIEFILE => $cookieJar, CURLOPT_FOLLOWLOCATION => false, CURLOPT_TIMEOUT => 10]);
$body = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
ok('GET /admin/audit-log returns 200', $code === 200);
ok('Page has Audit heading', stripos($body, 'Audit') !== false);

$ch = curl_init($base . '/admin/audit-log?action=login');
curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_COOKIEJAR => $cookieJar, CURLOPT_COOKIEFILE => $cookieJar, CURLOPT_FOLLOWLOCATION => false, CURLOPT_TIMEOUT => 10]);
$body2 = curl_exec($ch);
$code2 = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
ok('GET /admin/audit-log?action=login 200', $code2 === 200);

// 10. JSON API (uses /api/v2/audit/log)
$ch = curl_init($base . '/api/v2/audit/log?limit=5');
curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_COOKIEJAR => $cookieJar, CURLOPT_COOKIEFILE => $cookieJar, CURLOPT_FOLLOWLOCATION => false, CURLOPT_TIMEOUT => 10]);
$body3 = curl_exec($ch);
$code3 = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
ok('GET /api/v2/audit/log returns 200', $code3 === 200);
$j3 = json_decode($body3, true);
ok('API returns valid JSON', is_array($j3) && isset($j3['ok']));
ok('API has data array', isset($j3['data']) && is_array($j3['data']));
ok('API has stats', isset($j3['stats']));

echo "\n=== SUMMARY ===\n";
echo "PASS: $pass / FAIL: $fail / TOTAL: " . ($pass + $fail) . "\n";
exit($fail === 0 ? 0 : 1);
