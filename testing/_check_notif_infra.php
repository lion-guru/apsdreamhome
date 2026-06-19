<?php
$config = require dirname(__DIR__) . '/config/database.php';
$pdo = new PDO(
    'mysql:host=' . $config['host'] . ';port=' . $config['port'] . ';dbname=' . $config['database'],
    $config['username'],
    $config['password'],
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

$tables = ['notification_settings', 'notification_preferences', 'user_notification_preferences', 'notification_templates', 'notification_queue', 'realtime_notifications'];
foreach ($tables as $t) {
    $r = $pdo->query("SHOW TABLES LIKE '$t'");
    echo "$t: " . ($r->rowCount() > 0 ? 'EXISTS' : 'MISSING') . "\n";
}

// Check for notification-related columns in users
echo "\n--- users notification columns ---\n";
try {
    $r = $pdo->query("SHOW COLUMNS FROM users LIKE '%notif%'");
    while ($row = $r->fetch()) echo "  users." . $row['Field'] . "\n";
} catch (Exception $e) {
    echo "  Error: " . $e->getMessage() . "\n";
}

// Check notification_settings schema
echo "\n--- notification_settings schema ---\n";
try {
    $r = $pdo->query("SHOW CREATE TABLE notification_settings");
    $row = $r->fetch();
    echo $row['Create Table'] . "\n";
} catch (Exception $e) {
    echo "  Error: " . $e->getMessage() . "\n";
}

// Check realtime_notifications schema
echo "\n--- realtime_notifications schema ---\n";
try {
    $r = $pdo->query("SHOW CREATE TABLE realtime_notifications");
    $row = $r->fetch();
    echo $row['Create Table'] . "\n";
} catch (Exception $e) {
    echo "  Error: " . $e->getMessage() . "\n";
}

// Check what notification-related menu items exist
echo "\n--- notification menu items ---\n";
$r = $pdo->query("SELECT id, name, url, section FROM admin_menu_items WHERE name LIKE '%notif%' OR url LIKE '%notif%'");
while ($row = $r->fetch()) {
    echo "  Menu: {$row['name']} → {$row['url']} (section={$row['section']})\n";
}

// Check what notification services exist
echo "\n--- notification service files ---\n";
$serviceDir = dirname(__DIR__) . '/app/Services';
$files = glob($serviceDir . '/**/*otif*');
foreach ($files as $f) echo "  " . basename($f) . "\n";
