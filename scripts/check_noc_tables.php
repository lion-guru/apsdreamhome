<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome;charset=utf8mb4', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$tables = ['noc_requests', 'registry_appointments', 'plot_bookings', 'booking_payment_schedules', 'mlm_commission_ledger', 'users', 'plots', 'colonies'];
foreach ($tables as $t) {
    try {
        $stmt = $pdo->query("SHOW TABLES LIKE '$t'");
        $exists = $stmt->rowCount() > 0;
        echo $t . ': ' . ($exists ? 'EXISTS' : 'MISSING') . "\n";
    } catch (Exception $e) {
        echo $t . ': ERROR - ' . $e->getMessage() . "\n";
    }
}