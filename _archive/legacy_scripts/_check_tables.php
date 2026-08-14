<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '');
$count = $pdo->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = 'apsdreamhome'")->fetchColumn();
echo "Total tables: $count\n";

// Check the new tables exist
$newTables = ['incomplete_registrations', 'employee_advances', 'resell_properties', 'budgets', 'two_factor_tokens', 'agent_tasks', 'kpis', 'notification_templates', 'gst_returns', 'mlm_rank_rates'];
$found = 0;
foreach ($newTables as $t) {
    $r = $pdo->query("SHOW TABLES LIKE '$t'")->fetchAll();
    if ($r) { $found++; echo "  [OK] $t\n"; }
    else echo "  [MISSING] $t\n";
}
echo "Found: $found / " . count($newTables) . "\n";?>