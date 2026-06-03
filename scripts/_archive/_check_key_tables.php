<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '');

$tables = ['properties', 'bookings', 'plots', 'leads', 'payments', 'invoices', 'commissions', 'mlm_commission_ledger', 'wallet_points', 'employees'];
foreach ($tables as $t) {
    echo "\n=== $t ===\n";
    $cols = $pdo->query("DESCRIBE `$t`")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($cols as $c) echo "  " . $c['Field'] . ' (' . $c['Type'] . ")\n";
}
