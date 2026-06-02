<?php
/**
 * Drop 4 dead tables + 2 broken views
 * Verified safe: 0 rows, 0 FK references, 0 code references
 */
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

$before = $pdo->query('SHOW TABLES')->rowCount();
echo "Tables before: $before\n\n";

$targets = [
    ['type' => 'TABLE', 'name' => 'customers', 'reason' => '0 rows, 0 FKs, 0 code refs'],
    ['type' => 'TABLE', 'name' => 'admin_users', 'reason' => '0 rows, 0 FKs, 0 code refs'],
    ['type' => 'TABLE', 'name' => 'associates', 'reason' => '0 rows, 0 FKs, 0 code refs (data in mlm_profiles)'],
    ['type' => 'TABLE', 'name' => 'employees', 'reason' => '0 rows, 0 FKs, 0 code refs (data in users.role=employee)'],
    ['type' => 'VIEW', 'name' => 'booking_summary', 'reason' => 'broken view, references dead customers table'],
    ['type' => 'VIEW', 'name' => 'employee_performance', 'reason' => 'broken view, references dead employees table'],
];

foreach ($targets as $t) {
    try {
        $sql = "DROP {$t['type']} IF EXISTS `{$t['name']}`";
        $pdo->exec($sql);
        echo "✓ DROPPED {$t['type']} {$t['name']} -- {$t['reason']}\n";
    } catch (Exception $e) {
        echo "✗ FAILED to drop {$t['name']}: {$e->getMessage()}\n";
    }
}

$after = $pdo->query('SHOW TABLES')->rowCount();
echo "\nTables after: $after\n";
echo "Reduction: " . ($before - $after) . " tables\n";
