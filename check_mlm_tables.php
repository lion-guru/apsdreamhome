<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '');

$tables = ['mlm_referrals', 'mlm_payouts', 'site_visits', 'bookings'];
foreach ($tables as $table) {
    $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
    $exists = $stmt->fetch() !== false;
    echo "$table: " . ($exists ? "EXISTS" : "MISSING") . "\n";
    if ($exists) {
        $stmt = $pdo->query("DESCRIBE $table");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "  {$row['Field']} {$row['Type']}\n";
        }
    }
    echo "\n";
}
