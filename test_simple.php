<?php
$db = new PDO('mysql:host=127.0.0.1;dbname=apsdreamhome', 'root', '');
$db->setAttribute(PDO::ATTR_TIMEOUT, 10);

// Try simple queries
echo "Testing simple queries...\n";
try {
    $r = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
    echo "users count: $r\n";
} catch (\Throwable $e) {
    echo "users error: " . $e->getMessage() . "\n";
}

try {
    $r = $db->query("SHOW CREATE TABLE bookings")->fetch(PDO::FETCH_ASSOC);
    echo "bookings CREATE TABLE:\n" . $r['Create Table'] . "\n";
} catch (\Throwable $e) {
    echo "bookings error: " . $e->getMessage() . "\n";
}