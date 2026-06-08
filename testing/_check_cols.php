<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '');
foreach (['expenses', 'commissions', 'booking_payments', 'activity_logs_unified', 'associates', 'agents', 'employees', 'performance_metrics', 'tasks'] as $t) {
    echo "=== $t ===\n";
    $cols = $pdo->query("DESCRIBE $t")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($cols as $c) {
        echo "  {$c['Field']} ({$c['Type']})\n";
    }
    echo "\n";
}
