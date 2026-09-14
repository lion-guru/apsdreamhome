<?php
require_once 'C:/xampp/htdocs/apsdreamhome/config/bootstrap.php';
$pdo = \App\Core\Database\Database::getInstance()->getConnection();

// Check bookings table
$stmt = $pdo->query("DESCRIBE bookings");
echo "=== bookings ===\n";
while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) echo $r['Field'] . "\n";

// Check plot_bookings table
$stmt2 = $pdo->query("DESCRIBE plot_bookings");
echo "\n=== plot_bookings ===\n";
while ($r = $stmt2->fetch(PDO::FETCH_ASSOC)) echo $r['Field'] . "\n";

// Check user_properties table
$stmt3 = $pdo->query("DESCRIBE user_properties");
echo "\n=== user_properties ===\n";
while ($r = $stmt3->fetch(PDO::FETCH_ASSOC)) echo $r['Field'] . "\n";
