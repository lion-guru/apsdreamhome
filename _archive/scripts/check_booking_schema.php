<?php
require_once __DIR__ . '/../app/Core/ConfigService.php';
require_once __DIR__ . '/../app/Core/Database/Database.php';
$db = \App\Core\Database\Database::getInstance();

echo "=== plot_bookings ===\n";
$cols = $db->fetchAll("SHOW COLUMNS FROM plot_bookings");
foreach ($cols as $c) {
    echo "  {$c['Field']}: {$c['Type']}\n";
}

echo "\n=== booking_payment_schedules ===\n";
$cols = $db->fetchAll("SHOW COLUMNS FROM booking_payment_schedules");
foreach ($cols as $c) {
    echo "  {$c['Field']}: {$c['Type']}\n";
}?>