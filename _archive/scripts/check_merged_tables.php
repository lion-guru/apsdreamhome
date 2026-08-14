<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome;charset=utf8mb4', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$tables = ['commissions', 'activity_logs_unified', 'booking_commissions', 'mlm_commission_ledger', 'users', 'associates', 'mlm_profiles'];
foreach ($tables as $t) {
    try {
        $stmt = $pdo->query("SHOW TABLES LIKE '$t'");
        $exists = $stmt->rowCount() > 0;
        echo $t . ': ' . ($exists ? 'EXISTS' : 'MISSING') . "\n";
        if ($exists) {
            $cols = $pdo->query("DESCRIBE $t")->fetchAll(PDO::FETCH_COLUMN);
            echo '  Columns: ' . implode(', ', $cols) . "\n";
        }
    } catch (Exception $e) {
        echo $t . ': ERROR - ' . $e->getMessage() . "\n";
    }
}?>