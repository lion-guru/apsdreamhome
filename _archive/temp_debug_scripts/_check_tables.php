<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '');
foreach (['expenses', 'commissions', 'booking_payments', 'activity_logs_unified', 'associates', 'agents', 'employees', 'performance_metrics', 'tasks'] as $t) {
    try {
        $count = $pdo->query("SELECT COUNT(*) FROM $t")->fetchColumn();
        echo "$t: EXISTS ($count rows)\n";
    } catch (Exception $e) {
        echo "$t: MISSING - " . $e->getMessage() . "\n";
    }
}?>