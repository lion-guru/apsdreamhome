<?php
$db = new PDO('mysql:host=127.0.0.1;dbname=apsdreamhome', 'root', '');

echo "=== BOOKINGS ===\n";
foreach ($db->query("DESCRIBE bookings") as $r) {
    echo str_pad($r[0], 30) . str_pad($r[1], 25) . $r[2] . " " . ($r[4] ?? '') . " " . ($r[5] ?? '') . "\n";
}

echo "\n=== LEADS ===\n";
foreach ($db->query("DESCRIBE leads") as $r) {
    echo str_pad($r[0], 30) . str_pad($r[1], 25) . $r[2] . " " . ($r[4] ?? '') . " " . ($r[5] ?? '') . "\n";
}

echo "\n=== PLOT_BOOKINGS ===\n";
foreach ($db->query("DESCRIBE plot_bookings") as $r) {
    echo str_pad($r[0], 30) . str_pad($r[1], 25) . $r[2] . " " . ($r[4] ?? '') . " " . ($r[5] ?? '') . "\n";
}

echo "\n=== PAYMENTS ===\n";
foreach ($db->query("DESCRIBE payments") as $r) {
    echo str_pad($r[0], 30) . str_pad($r[1], 25) . $r[2] . " " . ($r[4] ?? '') . " " . ($r[5] ?? '') . "\n";
}

echo "\n=== USERS ===\n";
foreach ($db->query("DESCRIBE users") as $r) {
    echo str_pad($r[0], 30) . str_pad($r[1], 25) . $r[2] . " " . ($r[4] ?? '') . " " . ($r[5] ?? '') . "\n";
}