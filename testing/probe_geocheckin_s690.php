<?php
// S690 self-improvement probe. Sacrificial rows + full cleanup.
require_once __DIR__ . '/../config/bootstrap.php';
use App\Services\HR\GeoCheckinService;
use App\Core\Database\Database;

$svc = new GeoCheckinService();
$pdo = Database::getInstance()->getConnection();
$TID = 1;

echo "=== GeoCheckinService probe ===\n";

$emp = $pdo->fetch("SELECT id, name, user_id FROM employees WHERE status='active' ORDER BY id ASC LIMIT 1");
$employeeId = $emp ? (int) $emp['id'] : 0;
$userId = $emp ? (int) $emp['user_id'] : 0;
echo "Using employee_id={$employeeId} user_id={$userId} ('" . ($emp['name'] ?? '?') . "')\n";
if (!$employeeId || !$userId) { echo "NO EMPLOYEE - skip\n"; exit(0); }

$existing = $pdo->fetch("SELECT id FROM employee_checkins WHERE tenant_id = ? AND employee_id = ? AND work_date = CURDATE()", [$TID, $employeeId]);
if ($existing) { echo "TODAY ROW EXISTS id={$existing['id']} - skipping probe\n"; exit(0); }

echo "\n-- zones --\n";
foreach ($svc->getZones($userId) as $z) { echo "  {$z['name']} ({$z['type']}, r{$z['radius_m']}m)\n"; }

echo "\n-- check-in inside office --\n";
$r = $svc->checkIn($userId, ['latitude' => 26.8402340, 'longitude' => 83.3012250, 'accuracy_m' => 50, 'speed_kmh' => 0, 'is_mock_gps' => false]);
print_r($r);
$cid = $r['checkin_id'] ?? 0;

echo "\n-- re-punch updates same row --\n";
print_r($svc->checkIn($userId, ['latitude' => 26.8402340, 'longitude' => 83.3012250, 'accuracy_m' => 120, 'is_mock_gps' => false]));

echo "\n-- pings --\n";
print_r($svc->recordPing($userId, ['latitude' => 26.8402340, 'longitude' => 83.3012250, 'accuracy_m' => 60, 'speed_kmh' => 0]));

echo "\n-- day report --\n";
$rep = $svc->dayReport($employeeId);
echo "pings=", $rep['pings_count'] ?? 0, " distance=", $rep['distance_km'] ?? 0, "km anomalies=[", implode(',', array_map(fn($a) => $a['code'], $rep['anomalies'] ?? [])), "]\n";
echo "status: ", $pdo->fetchColumn("SELECT status FROM employee_checkins WHERE id = ?", [$cid]), "\n";

echo "\n-- mock GPS flag --\n";
print_r($svc->checkIn($userId, ['latitude' => 26.8402340, 'longitude' => 83.3012250, 'accuracy_m' => 40, 'is_mock_gps' => true]));
echo "status after mock: ", $pdo->fetchColumn("SELECT status FROM employee_checkins WHERE id = ?", [$cid]), "\n";

echo "\n-- check-out with log --\n";
print_r($svc->checkOut($userId, ['latitude' => 26.8402340, 'longitude' => 83.3012250], 'probe work log - deleted after'));

// ── Cleanup (sacrificial, zero leftovers) ──
$pdo->exec("DELETE FROM employee_pings WHERE tenant_id = 1 AND employee_id = {$employeeId} AND work_date = CURDATE()");
$pdo->exec("DELETE FROM employee_checkins WHERE id = {$cid}");
echo "\ncleanup: checkins=", $pdo->fetchColumn("SELECT COUNT(*) FROM employee_checkins WHERE id = {$cid}"),
     " pings=", $pdo->fetchColumn("SELECT COUNT(*) FROM employee_pings WHERE employee_id = {$employeeId} AND work_date = CURDATE()"), "\n";
echo "=== probe done ===\n";
