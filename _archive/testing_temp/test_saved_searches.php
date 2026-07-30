<?php
/**
 * Standalone test for SavedSearchService & SavedSearchController.
 * Run from CLI:  C:\xampp\php\php.exe testing/test_saved_searches.php
 */
require_once __DIR__ . '/../app/Services/EmailService.php';
require_once __DIR__ . '/../app/Services/SavedSearchService.php';

use App\Services\SavedSearchService;

$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
]);

function test($name, $fn) {
    echo "[TEST] $name\n";
    try {
        $result = $fn();
        echo "  -> " . ($result === true || $result === null ? 'OK' : (is_string($result) ? $result : json_encode($result))) . "\n";
        return true;
    } catch (\Throwable $e) {
        echo "  -> FAIL: " . $e->getMessage() . "\n";
        return false;
    }
}

echo "=== Test 1: Schema check ===\n";
test('saved_searches has new columns', function() use ($pdo) {
    $cols = array_column($pdo->query('DESCRIBE saved_searches')->fetchAll(), 'Field');
    foreach (['email_alerts', 'result_count', 'last_run_at'] as $col) {
        if (!in_array($col, $cols)) throw new \Exception("Missing column: $col");
    }
    return 'All new columns present';
});
test('user_properties has new columns', function() use ($pdo) {
    $cols = array_column($pdo->query('DESCRIBE user_properties')->fetchAll(), 'Field');
    foreach (['bedrooms', 'bathrooms', 'furnished', 'year_built'] as $col) {
        if (!in_array($col, $cols)) throw new \Exception("Missing column: $col");
    }
    return 'All new columns present';
});
test('search_alert_log table exists', function() use ($pdo) {
    $n = $pdo->query("SHOW TABLES LIKE 'search_alert_log'")->rowCount();
    if (!$n) throw new \Exception('Table missing');
    return 'Table exists';
});

echo "\n=== Test 2: Service instantiation ===\n";
$svc = new SavedSearchService($pdo);
test('Service loads', fn() => 'OK');

echo "\n=== Test 3: Test customer (id=3) ===\n";
$user = $pdo->query('SELECT id,name,email,role FROM users WHERE id=3')->fetch();
echo "User: {$user['name']} <{$user['email']}> role={$user['role']}\n";
$userId = (int)$user['id'];

echo "\n=== Test 4: Save a search ===\n";
$filters = ['type' => 'plot', 'listing' => 'sell', 'min_price' => 500000, 'max_price' => 2000000, 'location' => 'Gorakhpur', 'bedrooms' => 2];
$id = $svc->saveSearch($userId, 'Plots in Gorakhpur under 20L', $filters, 'Test search', 1);
test("Save returns valid ID", function() use ($id) {
    if ($id <= 0) throw new \Exception("Bad id: $id");
    return "id=$id";
});

echo "\n=== Test 5: Get saved search ===\n";
$search = $svc->get($id);
test("Get returns search", function() use ($search, $id) {
    if ($search['id'] != $id) throw new \Exception("Wrong id");
    if ($search['name'] !== 'Plots in Gorakhpur under 20L') throw new \Exception("Wrong name");
    if ((int)$search['email_alerts'] !== 1) throw new \Exception("email_alerts not saved");
    return "name='{$search['name']}', email_alerts={$search['email_alerts']}";
});

echo "\n=== Test 6: List user searches ===\n";
$list = $svc->getUserSearches($userId);
test("List has at least 1", function() use ($list) {
    if (count($list) < 1) throw new \Exception("Empty list");
    return count($list) . ' searches';
});

echo "\n=== Test 7: matchProperties ===\n";
$matches = $svc->matchProperties($filters);
test("Match runs without error", function() use ($matches) {
    return count($matches) . ' matches';
});

echo "\n=== Test 8: countMatches ===\n";
$count = $svc->countMatches($filters);
test("Count runs", fn() => "$count properties");

echo "\n=== Test 9: toggleAlerts ===\n";
test("Turn off", fn() => $svc->toggleAlerts($id, $userId, false) ? 'OK' : 'FAIL');
test("Turn on",  fn() => $svc->toggleAlerts($id, $userId, true) ? 'OK' : 'FAIL');

echo "\n=== Test 10: logAlertSent ===\n";
test("Log alert", function() use ($svc, $id, $userId) {
    $ok = $svc->logAlertSent($id, $userId, 999, 'sent');
    if (!$ok) throw new \Exception("logAlertSent failed");
    return 'OK';
});

echo "\n=== Test 11: getAlertLog ===\n";
$log = $svc->getAlertLog($userId);
test("Get log", function() use ($log) {
    if (count($log) < 1) throw new \Exception("No log entries");
    return count($log) . ' entries';
});

echo "\n=== Test 12: recordRun ===\n";
test("Record run", function() use ($svc, $id) {
    $svc->recordRun($id, 5);
    $s = $svc->get($id);
    if ((int)$s['result_count'] !== 5) throw new \Exception("Result count not updated: {$s['result_count']}");
    return "result_count=5, last_run_at={$s['last_run_at']}";
});

echo "\n=== Test 13: sendAlerts (cron) ===\n";
$stats = $svc->sendAlerts();
test("sendAlerts runs", function() use ($stats) {
    if (!isset($stats['searches_processed'])) throw new \Exception("No stats");
    return "searches_processed={$stats['searches_processed']}, alerts_sent={$stats['alerts_sent']}, failed={$stats['alerts_failed']}";
});

echo "\n=== Test 14: update ===\n";
test("Rename", function() use ($svc, $id, $userId) {
    $role = $svc->resolveUserRole($userId);
    $ok = $svc->update($id, $userId, $role, ['name' => 'Renamed Search']);
    if (!$ok) throw new \Exception("Update failed");
    $s = $svc->get($id);
    if ($s['name'] !== 'Renamed Search') throw new \Exception("Name not updated");
    return 'OK';
});

echo "\n=== Test 15: cleanup ===\n";
test("Cleanup", function() use ($svc) {
    $n = $svc->cleanup(90);
    return "$n stale searches removed";
});

echo "\n=== Test 16: Delete ===\n";
test("Delete", function() use ($svc, $id, $userId) {
    $role = $svc->resolveUserRole($userId);
    $ok = $svc->delete($id, $userId, $role);
    if (!$ok) throw new \Exception("Delete failed");
    return 'OK';
});

echo "\n=== Test 17: Controller loading ===\n";
test("Controller loads", function() {
    require_once __DIR__ . '/../app/Http/Controllers/BaseController.php';
    require_once __DIR__ . '/../app/Http/Controllers/Front/SavedSearchController.php';
    $reflection = new ReflectionClass('App\\Http\\Controllers\\Front\\SavedSearchController');
    $expected = ['index', 'store', 'update', 'destroy', 'execute', 'toggleAlerts', 'manageAlerts', 'autocomplete', 'cronAlerts'];
    foreach ($expected as $m) {
        if (!$reflection->hasMethod($m)) throw new \Exception("Missing method: $m");
    }
    return count($expected) . ' methods found';
});

echo "\n=== Test 18: Properties page with no filter ===\n";
test("matchProperties no filter", function() use ($svc) {
    $m = $svc->matchProperties([]);
    if (count($m) < 1) throw new \Exception("No approved properties");
    return count($m) . ' properties (status=approved)';
});

echo "\n=== Test 19: Match filters that should work ===\n";
test("Gorakhpur only", function() use ($svc) {
    $m = $svc->matchProperties(['location' => 'Gorakhpur']);
    return count($m) . ' properties in Gorakhpur';
});
test("3BHK+ only", function() use ($svc) {
    $m = $svc->matchProperties(['bedrooms' => 3]);
    return count($m) . ' properties with 3+ BHK';
});
test("Price range 1-10L", function() use ($svc) {
    $m = $svc->matchProperties(['min_price' => 100000, 'max_price' => 1000000]);
    return count($m) . ' properties between 1L-10L';
});
test("Sort price_low", function() use ($svc) {
    $m = $svc->matchProperties(['sort' => 'price_low'], 5, 0);
    if (count($m) >= 2) {
        $p1 = (float)$m[0]['price'];
        $p2 = (float)$m[1]['price'];
        if ($p1 > $p2) throw new \Exception("Sort wrong: {$m[0]['price']} > {$m[1]['price']}");
    }
    return 'First prices: ' . implode(', ', array_map(fn($p) => (float)$p['price'], array_slice($m, 0, 3)));
});
test("Sort price_high", function() use ($svc) {
    $m = $svc->matchProperties(['sort' => 'price_high'], 5, 0);
    if (count($m) >= 2) {
        $p1 = (float)$m[0]['price'];
        $p2 = (float)$m[1]['price'];
        if ($p1 < $p2) throw new \Exception("Sort wrong: {$m[0]['price']} < {$m[1]['price']}");
    }
    return 'First prices: ' . implode(', ', array_map(fn($p) => (float)$p['price'], array_slice($m, 0, 3)));
});
test("Sort area_large", function() use ($svc) {
    $m = $svc->matchProperties(['sort' => 'area_large'], 5, 0);
    if (count($m) >= 2) {
        $a1 = (int)$m[0]['area_sqft'];
        $a2 = (int)$m[1]['area_sqft'];
        if ($a1 < $a2) throw new \Exception("Sort wrong: {$m[0]['area_sqft']} < {$m[1]['area_sqft']}");
    }
    return 'First areas: ' . implode(', ', array_map(fn($p) => (int)$p['area_sqft'], array_slice($m, 0, 3)));
});

echo "\n=== ALL TESTS COMPLETE ===\n";
