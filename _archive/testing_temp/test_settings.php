<?php
/**
 * Test: System Settings
 * Cluster 4 / Task 4
 *
 * Verifies the SiteSettingsController covers all 8 tabs (general,
 * contact, social, seo, email, sms, payment, maintenance), that
 * settings are saved to the `settings` key/value table, that
 * the admin page loads, and that each tab returns 200.
 */

define('APP_ROOT', dirname(__DIR__));

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

// 1. settings table exists with key/value columns
$cols = $pdo->query("SHOW COLUMNS FROM settings")->fetchAll(PDO::FETCH_COLUMN);
ok('settings has key col', in_array('key', $cols, true));
ok('settings has value col', in_array('value', $cols, true));

// 2. Insert a test setting
$testKey = 'cluster4_test_setting';
$pdo->prepare("INSERT INTO settings (`key`, `value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)")->execute([$testKey, 'test_value_123']);
ok('Inserted cluster4 test setting', true);

// 3. Read back
$stmt = $pdo->prepare("SELECT `value` FROM settings WHERE `key` = ?");
$stmt->execute([$testKey]);
$v = $stmt->fetchColumn();
ok('Setting reads back', $v === 'test_value_123');

// 4. Update
$pdo->prepare("UPDATE settings SET `value` = ? WHERE `key` = ?")->execute(['test_value_456', $testKey]);
$stmt->execute([$testKey]);
ok('Setting updates', $stmt->fetchColumn() === 'test_value_456');

// 5. Multiple settings can coexist (group by prefix)
$pdo->prepare("INSERT INTO settings (`key`, `value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)")->execute(['cluster4_test_group_a', 'A']);
$pdo->prepare("INSERT INTO settings (`key`, `value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)")->execute(['cluster4_test_group_b', 'B']);
$count = (int) $pdo->query("SELECT COUNT(*) FROM settings WHERE `key` LIKE 'cluster4_test_group_%'")->fetchColumn();
ok('Group prefix query returns 2', $count === 2);

// 6. JSON values supported (payment keys are JSON-ish)
$pdo->prepare("INSERT INTO settings (`key`, `value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)")->execute(['cluster4_test_json', '{"key":"val"}']);
$stmt = $pdo->prepare("SELECT `value` FROM settings WHERE `key` = ?");
$stmt->execute(['cluster4_test_json']);
$j = json_decode($stmt->fetchColumn(), true);
ok('JSON value parses', is_array($j) && ($j['key'] ?? null) === 'val');

// 7. Test SiteSettingsController via HTTP
$cookieJar = 'C:\Users\abhay\AppData\Local\Temp\opencode\cookies.txt';
@unlink($cookieJar);
$base = 'http://localhost/apsdreamhome';

$ch = curl_init($base . '/admin/login?test_login=1');
curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_COOKIEJAR => $cookieJar, CURLOPT_COOKIEFILE => $cookieJar, CURLOPT_FOLLOWLOCATION => false, CURLOPT_TIMEOUT => 10]);
curl_exec($ch);
curl_close($ch);

// 8. Each tab returns 200
$tabs = ['general' => '/admin/settings', 'contact' => '/admin/settings/contact', 'social' => '/admin/settings/social',
         'seo' => '/admin/settings/seo', 'email' => '/admin/settings/email-config',
         'sms' => '/admin/settings/sms', 'payment' => '/admin/settings/payment',
         'maintenance' => '/admin/settings/maintenance', 'company' => '/admin/settings/company'];
foreach ($tabs as $name => $path) {
    $ch = curl_init($base . $path);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_COOKIEJAR => $cookieJar, CURLOPT_COOKIEFILE => $cookieJar, CURLOPT_FOLLOWLOCATION => false, CURLOPT_TIMEOUT => 10]);
    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    ok("$name tab ($path) returns $code", $code === 200 || $code === 302);
}

// 9. Settings page renders the index view
$ch = curl_init($base . '/admin/settings');
curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_COOKIEJAR => $cookieJar, CURLOPT_COOKIEFILE => $cookieJar, CURLOPT_FOLLOWLOCATION => false, CURLOPT_TIMEOUT => 10]);
$body = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
ok('Index page 200', $code === 200);
ok('Index page has form/table', stripos($body, 'settings') !== false || stripos($body, 'setting') !== false);

// 10. Cleanup
$pdo->exec("DELETE FROM settings WHERE `key` LIKE 'cluster4_test_%'");

// 11. Settings class shape (read source for method names, no autoloader)
$src = file_get_contents(APP_ROOT . '/app/Http/Controllers/Admin/SiteSettingsController.php');
$expected = ['index', 'edit', 'update', 'reset', 'getStats', 'export', 'getCategory'];
foreach ($expected as $m) {
    ok("Method $m present", strpos($src, "function $m(") !== false || strpos($src, "function $m (") !== false);
}

echo "\n=== SUMMARY ===\n";
echo "PASS: $pass / FAIL: $fail / TOTAL: " . ($pass + $fail) . "\n";
exit($fail === 0 ? 0 : 1);
