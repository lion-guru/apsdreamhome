<?php
$config = require dirname(__DIR__) . '/config/database.php';
$pdo = new PDO(
    'mysql:host=' . $config['host'] . ';port=' . $config['port'] . ';dbname=' . $config['database'],
    $config['username'],
    $config['password'],
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

// user_notification_preferences schema
echo "--- user_notification_preferences schema ---\n";
$r = $pdo->query("SHOW CREATE TABLE user_notification_preferences");
$row = $r->fetch();
echo $row['Create Table'] . "\n\n";

// notification_settings sample data
echo "--- notification_settings (first 10) ---\n";
$r = $pdo->query("SELECT * FROM notification_settings LIMIT 10");
while ($row = $r->fetch(PDO::FETCH_ASSOC)) {
    echo "  user_id={$row['user_id']} channel={$row['channel']} event={$row['event_type']} enabled={$row['is_enabled']}\n";
}

// Count distinct event_types
echo "\n--- distinct event_types in notification_settings ---\n";
$r = $pdo->query("SELECT DISTINCT event_type FROM notification_settings ORDER BY event_type");
while ($row = $r->fetch()) echo "  {$row['event_type']}\n";

// Count distinct channels
echo "\n--- distinct channels ---\n";
$r = $pdo->query("SELECT DISTINCT channel FROM notification_settings ORDER BY channel");
while ($row = $r->fetch()) echo "  {$row['channel']}\n";

// Check UserController has notification-related methods
echo "\n--- UserController notification methods ---\n";
$code = file_get_contents(dirname(__DIR__) . '/app/Http/Controllers/Front/UserController.php');
if (preg_match_all('/function\s+(\w*[Nn]otif\w*)\s*\(/', $code, $m)) {
    foreach ($m[1] as $fn) echo "  $fn()\n";
} else {
    echo "  (none found)\n";
}

// Check if there's already a notification preferences view
echo "\n--- notification preference views ---\n";
$views = glob(dirname(__DIR__) . '/app/views/**/*notif*');
foreach ($views as $v) echo "  " . str_replace(dirname(__DIR__) . '/app/views/', '', $v) . "\n";?>