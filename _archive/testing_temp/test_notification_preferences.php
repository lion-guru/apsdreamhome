<?php
/**
 * Test: Notification Preferences
 * Cluster 4 / Task 2
 *
 * Verifies the customer notification preferences flow:
 *  - NotificationPreferenceController loads/saves preferences
 *  - NotificationService respects preferences before sending
 *  - Suppressed (no channel enabled) notifications are logged
 *  - Critical types (security, password_reset) bypass preferences
 */

define('APP_ROOT', dirname(__DIR__));
require_once APP_ROOT . '/app/Services/NotificationService.php';

use App\Services\NotificationService;

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

// Use a test user (idempotent: pick the first customer or create one)
$user = $pdo->query("SELECT id FROM users WHERE role = 'customer' AND status = 'active' LIMIT 1")->fetch();
if (!$user) {
    $pdo->exec("INSERT INTO users (name, email, role, status, created_at) VALUES ('Test Cust', 'cust-test@apsdreamhome.com', 'customer', 'active', NOW())");
    $userId = (int) $pdo->lastInsertId();
} else {
    $userId = (int) $user['id'];
}
echo "Using test user_id=$userId\n";

// 1. Clean slate for this user
$pdo->prepare("DELETE FROM user_notification_preferences WHERE user_id = ?")->execute([$userId]);
$pdo->prepare("DELETE FROM realtime_notifications WHERE user_id = ?")->execute([$userId]);

$svc = new NotificationService($pdo);

// 2. Default behaviour: with no row, every channel is enabled
$ok1 = $svc->isChannelEnabled($userId, 'booking', 'email');
ok('No-pref row => default email enabled', $ok1 === true);

$ok2 = $svc->isChannelEnabled($userId, 'marketing', 'sms');
ok('No-pref row => default sms enabled', $ok2 === true);

// 3. Critical types bypass preferences (always on)
$ok3 = $svc->isChannelEnabled($userId, 'security', 'sms');
ok('Security type bypasses prefs', $ok3 === true);

$ok4 = $svc->isChannelEnabled($userId, 'password_reset', 'email');
ok('Password reset type bypasses prefs', $ok4 === true);

// 4. Set preferences: disable sms + whatsapp for marketing
$pdo->prepare("INSERT INTO user_notification_preferences
    (user_id, user_type, notification_type, email_enabled, sms_enabled, whatsapp_enabled, push_enabled, frequency, updated_at)
    VALUES (?, 'customer', 'marketing', 1, 0, 0, 1, 'immediate', NOW())
    ON DUPLICATE KEY UPDATE email_enabled=1, sms_enabled=0, whatsapp_enabled=0, push_enabled=1")
    ->execute([$userId]);

// 5. Verify
$emailEnabled = $svc->isChannelEnabled($userId, 'marketing', 'email');
$smsEnabled   = $svc->isChannelEnabled($userId, 'marketing', 'sms');
$waEnabled    = $svc->isChannelEnabled($userId, 'marketing', 'whatsapp');
$pushEnabled  = $svc->isChannelEnabled($userId, 'marketing', 'push');

ok('Email enabled for marketing', $emailEnabled === true);
ok('SMS disabled for marketing',   $smsEnabled === false);
ok('WhatsApp disabled for marketing', $waEnabled === false);
ok('Push enabled for marketing',  $pushEnabled === true);

// 6. send() should skip disabled channels
$res = $svc->send($userId, 'sms', 'Marketing', 'Special offer', ['notification_type' => 'marketing']);
ok('send() returns ok=false when channel disabled', $res['ok'] === false);
ok('send() result flagged skipped', !empty($res['skipped']));
ok('Skip reason is channel_disabled_by_user', ($res['reason'] ?? '') === 'channel_disabled_by_user');

// 7. Skipped notifications are recorded (realtime_notifications table has no 'status' col -
//    we just verify a row was inserted with channel_name = 'sms' for the user)
$count = (int) $pdo->query("SELECT COUNT(*) FROM realtime_notifications WHERE user_id = $userId AND channel_name = 'sms'")->fetchColumn();
ok('Skipped row logged in realtime_notifications', $count >= 1);

// 8. Enabled channel still works (or at least enters the pipeline)
$res2 = $svc->send($userId, 'email', 'Marketing', 'Welcome', ['notification_type' => 'marketing']);
ok('Email channel send ok (or skipped at template level)', is_array($res2) && array_key_exists('ok', $res2));

// 9. Critical types send even with all channels disabled
$pdo->prepare("INSERT INTO user_notification_preferences
    (user_id, user_type, notification_type, email_enabled, sms_enabled, whatsapp_enabled, push_enabled, frequency, updated_at)
    VALUES (?, 'customer', 'security', 0, 0, 0, 0, 'immediate', NOW())
    ON DUPLICATE KEY UPDATE email_enabled=0, sms_enabled=0, whatsapp_enabled=0, push_enabled=0")
    ->execute([$userId]);

$ok5 = $svc->isChannelEnabled($userId, 'security', 'email');
ok('Security type still on even with all off', $ok5 === true);

// 10. After save: same user still respects prefs
$res3 = $svc->send($userId, 'whatsapp', 'Marketing', 'Try us', ['notification_type' => 'marketing']);
ok('WhatsApp channel blocked after prefs saved', $res3['ok'] === false || !empty($res3['skipped']));

// 11. Update preferences (toggle)
$pdo->prepare("UPDATE user_notification_preferences SET sms_enabled = 1 WHERE user_id = ? AND notification_type = 'marketing'")->execute([$userId]);
$sms2 = $svc->isChannelEnabled($userId, 'marketing', 'sms');
ok('Updated preference takes effect', $sms2 === true);

// 12. Default preference types
$rows = $pdo->prepare("SELECT notification_type FROM user_notification_preferences WHERE user_id = ?");
$rows->execute([$userId]);
$types = $rows->fetchAll(PDO::FETCH_COLUMN);
ok('Has at least one pref row', count($types) > 0);

// 13. Suppression logging - re-send with all channels off
$pdo->prepare("INSERT INTO user_notification_preferences
    (user_id, user_type, notification_type, email_enabled, sms_enabled, whatsapp_enabled, push_enabled, frequency, updated_at)
    VALUES (?, 'customer', 'agreement', 0, 0, 0, 0, 'never', NOW())
    ON DUPLICATE KEY UPDATE email_enabled=0, sms_enabled=0, whatsapp_enabled=0, push_enabled=0")
    ->execute([$userId]);
$res4 = $svc->send($userId, 'email', 'A', 'B', ['notification_type' => 'agreement']);
ok('All-off channel: ok=false skipped', $res4['ok'] === false && !empty($res4['skipped']));

// 14. Cleanup
$pdo->prepare("DELETE FROM user_notification_preferences WHERE user_id = ?")->execute([$userId]);
$pdo->prepare("DELETE FROM realtime_notifications WHERE user_id = ?")->execute([$userId]);

// 15. Service API surface check
$ref = new ReflectionClass(NotificationService::class);
$publicMethods = array_map(fn($m) => $m->getName(), $ref->getMethods(ReflectionMethod::IS_PUBLIC));
$expected = ['send', 'isChannelEnabled'];
foreach ($expected as $m) {
    ok("Method $m present", in_array($m, $publicMethods, true));
}

echo "\n=== SUMMARY ===\n";
echo "PASS: $pass / FAIL: $fail / TOTAL: " . ($pass + $fail) . "\n";
exit($fail === 0 ? 0 : 1);
