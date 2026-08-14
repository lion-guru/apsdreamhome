<?php
require_once __DIR__ . '/../app/Core/autoload.php';

$db = \App\Core\Database\Database::getInstance();

// Check the latest booking
$booking = $db->fetch("SELECT * FROM plot_bookings ORDER BY id DESC LIMIT 1");
print_r($booking);

// Check associates table
echo "\n=== Associates for user 2 and 6 ===\n";
$assoc = $db->fetchAll("SELECT id, user_id, referral_code FROM associates WHERE user_id IN (2, 6)");
print_r($assoc);

// Check user 2's associate record
echo "\n=== User 2 associate record ===\n";
$u2 = $db->fetch("SELECT * FROM associates WHERE user_id = 2");
print_r($u2);?>