<?php
/**
 * Test: Push Notification Service (FCM v1 API) + Tables
 *
 * Run: php testing/test_push_notifications.php
 */

$root = dirname(__DIR__);
define('APP_ROOT', $root);
require_once $root . '/app/Core/Autoloader.php';

$pass = 0;
$fail = 0;

function assert_test(string $label, bool $cond, string $detail = ''): void
{
    global $pass, $fail;
    if ($cond) {
        echo "  [PASS] $label\n";
        $pass++;
    } else {
        echo "  [FAIL] $label" . ($detail ? " — $detail" : '') . "\n";
        $fail++;
    }
}

echo "=== Push Notification Service Tests ===\n\n";

// -------------------------------------------------------
// 1. Table existence
// -------------------------------------------------------
echo "1. Database Tables\n";
try {
    $pdo = \App\Core\Database\Database::getInstance()->getConnection();

    $r = $pdo->query("SHOW TABLES LIKE 'mobile_devices'")->fetch();
    assert_test('mobile_devices table exists', $r !== false);

    $r = $pdo->query("SHOW TABLES LIKE 'notification_logs'")->fetch();
    assert_test('notification_logs table exists', $r !== false);

    // Verify columns
    $cols = $pdo->query("SHOW COLUMNS FROM mobile_devices")->fetchAll(PDO::FETCH_COLUMN);
    assert_test('mobile_devices has user_id column', in_array('user_id', $cols));
    assert_test('mobile_devices has device_token column', in_array('device_token', $cols));
    assert_test('mobile_devices has platform column', in_array('platform', $cols));

    $cols = $pdo->query("SHOW COLUMNS FROM notification_logs")->fetchAll(PDO::FETCH_COLUMN);
    assert_test('notification_logs has status column', in_array('status', $cols));
    assert_test('notification_logs has payload column', in_array('payload', $cols));
    assert_test('notification_logs has recipient_token column', in_array('recipient_token', $cols));
} catch (\Throwable $e) {
    assert_test('DB connection', false, $e->getMessage());
}

// -------------------------------------------------------
// 2. PushNotificationService instantiation
// -------------------------------------------------------
echo "\n2. PushNotificationService Instantiation\n";
try {
    $svc = new \App\Services\Communication\PushNotificationService();
    assert_test('Service instantiates without error', true);

    // FCM not configured in dev — expect graceful failure, no crash
    $result = $svc->sendToDevice('fake-token-123', [
        'title' => 'Test',
        'body'  => 'Hello from test',
    ]);
    assert_test('sendToDevice returns array without crash', is_array($result));
    assert_test('sendToDevice returns success=false in dev (no FCM creds)', $result['success'] === false);
} catch (\Throwable $e) {
    assert_test('Service instantiation', false, $e->getMessage());
}

// -------------------------------------------------------
// 3. Device registration
// -------------------------------------------------------
echo "\n3. Device Registration\n";
try {
    $svc = new \App\Services\Communication\PushNotificationService();
    $result = $svc->registerDevice(1, 'test-device-token-dev-001', 'android');
    assert_test('registerDevice returns array', is_array($result));
    assert_test('registerDevice success key exists', array_key_exists('success', $result));

    // Verify row was inserted (or updated)
    $row = $pdo->prepare("SELECT * FROM mobile_devices WHERE device_token = ?");
    $row->execute(['test-device-token-dev-001']);
    $device = $row->fetch(PDO::FETCH_ASSOC);
    assert_test('Device row inserted in mobile_devices', $device !== false);
    if ($device) {
        assert_test('Device user_id = 1', (int)$device['user_id'] === 1);
        assert_test('Device platform = android', $device['platform'] === 'android');
    }

    // Cleanup test row
    $pdo->prepare("DELETE FROM mobile_devices WHERE device_token = ?")->execute(['test-device-token-dev-001']);
} catch (\Throwable $e) {
    assert_test('Device registration', false, $e->getMessage());
}

// -------------------------------------------------------
// 4. Notification logging (direct insert)
// -------------------------------------------------------
echo "\n4. Notification Logging\n";
try {
    $pdo->prepare("
        INSERT INTO notification_logs (type, recipient_token, title, body, payload, response, status, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
    ")->execute([
        'push',
        'test-token-log',
        'Test Title',
        'Test body content',
        json_encode(['test' => true]),
        json_encode(['name' => 'projects/test/messages/123']),
        'sent',
    ]);

    $logRow = $pdo->prepare("SELECT * FROM notification_logs WHERE recipient_token = ?");
    $logRow->execute(['test-token-log']);
    $log = $logRow->fetch(PDO::FETCH_ASSOC);
    assert_test('Notification log row inserted', $log !== false);
    if ($log) {
        assert_test('Log type = push', $log['type'] === 'push');
        assert_test('Log status = sent', $log['status'] === 'sent');
        assert_test('Log title preserved', $log['title'] === 'Test Title');
    }

    // Cleanup
    $pdo->prepare("DELETE FROM notification_logs WHERE recipient_token = ?")->execute(['test-token-log']);
} catch (\Throwable $e) {
    assert_test('Notification logging', false, $e->getMessage());
}

// -------------------------------------------------------
// 5. Unregister device
// -------------------------------------------------------
echo "\n5. Unregister Device\n";
try {
    $svc = new \App\Services\Communication\PushNotificationService();
    // Insert then unregister
    $pdo->prepare("INSERT INTO mobile_devices (user_id, device_token, platform, is_active, created_at) VALUES (?, ?, ?, 1, NOW())")
        ->execute([2, 'test-unregister-token', 'ios']);

    $result = $svc->unregisterDevice('test-unregister-token');
    assert_test('unregisterDevice returns bool or null', is_bool($result) || $result === null);

    $row = $pdo->prepare("SELECT is_active FROM mobile_devices WHERE device_token = ?");
    $row->execute(['test-unregister-token']);
    $device = $row->fetch(PDO::FETCH_ASSOC);
    assert_test('Device is_active set to 0', $device && (int)$device['is_active'] === 0);

    // Cleanup
    $pdo->prepare("DELETE FROM mobile_devices WHERE device_token = ?")->execute(['test-unregister-token']);
} catch (\Throwable $e) {
    assert_test('Unregister device', false, $e->getMessage());
}

// -------------------------------------------------------
// Summary
// -------------------------------------------------------
echo "\n=== Results: $pass passed, $fail failed ===\n";
exit($fail > 0 ? 1 : 0);
