<?php
/**
 * Identify the 19 tables with no secondary indexes + 60 new tables
 * Check for orphan tables, missing critical indexes, industry gaps
 */
set_time_limit(120);
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome;charset=utf8mb4', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "=== Tables with only PRIMARY KEY (no secondary indexes) ===\n";
$allTables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
$noIdx = [];
foreach ($allTables as $t) {
    $idx = $pdo->query("SHOW INDEXES FROM `$t`")->fetchAll();
    if (count($idx) <= 1) $noIdx[] = $t;
}
foreach ($noIdx as $t) {
    $cnt = (int)$pdo->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
    echo sprintf("  %-40s  %6d rows\n", $t, $cnt);
}

echo "\n=== Tables added since 2026-05-25 backup (sample inspection) ===\n";
$backup = file_get_contents('C:\xampp\htdocs\apsdreamhome\database\backup_20260525.sql');
preg_match_all('/CREATE TABLE `(\w+)`/', $backup, $m);
$backupTables = array_flip($m[1]);
$added = [];
foreach ($allTables as $t) {
    if (!isset($backupTables[$t])) $added[] = $t;
}
echo "Total added: " . count($added) . "\n";
foreach ($added as $t) {
    $cnt = (int)$pdo->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
    echo sprintf("  + %-40s  %6d rows\n", $t, $cnt);
}

echo "\n=== Critical industry tables - cross-check ===\n";
// Real estate ERP MUST-HAVE tables (from industry research)
$must = [
    // Inventory/Properties
    'properties', 'plots', 'colonies', 'projects',
    'users', 'employees', 'associates', 'agents',  // people
    'leads', 'inquiries', 'bookings', 'payments',  // sales
    'commissions', 'payouts',                       // MLM
    'documents', 'kyc_requests', 'registries',     // compliance
    'invoices', 'expenses', 'tax_types',           // finance
    'audit_log', 'notifications', 'email_templates', // system
    'cities', 'states', 'districts', 'pincodes',   // geo
];
$present = [];
$missing = [];
foreach ($must as $t) {
    if (in_array($t, $allTables)) $present[] = $t;
    else $missing[] = $t;
}
echo "Industry must-have present (" . count($present) . "/" . count($must) . "):\n";
foreach ($present as $t) echo "  [OK]  $t\n";
echo "Industry must-have MISSING (" . count($missing) . "):\n";
foreach ($missing as $t) echo "  [--]  $t\n";

echo "\n=== Indexes missing on heavy queried tables (samples) ===\n";
// Common high-traffic queries that need indexes
$candidates = [
    'leads' => ['status', 'created_at', 'assigned_to', 'source', 'email'],
    'users' => ['email', 'phone', 'role', 'status', 'created_at'],
    'bookings' => ['user_id', 'plot_id', 'status', 'booking_date'],
    'payments' => ['user_id', 'status', 'created_at'],
    'notifications' => ['user_id', 'is_read', 'created_at'],
    'inquiries' => ['user_id', 'status', 'created_at'],
];
foreach ($candidates as $t => $cols) {
    if (!in_array($t, $allTables)) continue;
    $existing = $pdo->query("SHOW INDEXES FROM `$t`")->fetchAll(PDO::FETCH_COLUMN, 4); // column_name
    echo "$t columns with no index:\n";
    foreach ($cols as $c) {
        if (!in_array($c, $existing)) echo "  - missing index on $c\n";
    }
}

echo "\n=== Unused orphan tables (0 rows, 0 code refs) ===\n";
// scan controllers for table references
$tableRefs = [];
$dirs = ['app/Http/Controllers', 'app/Services', 'app/Models'];
foreach ($dirs as $d) {
    if (!is_dir($d)) continue;
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($d)) as $f) {
        if ($f->getExtension() !== 'php') continue;
        $content = file_get_contents($f);
        preg_match_all('/(?:FROM|INTO|UPDATE|JOIN)\s+`?(\w+)`?/i', $content, $m);
        foreach ($m[1] as $ref) $tableRefs[$ref] = ($tableRefs[$ref] ?? 0) + 1;
    }
}
$orphans = [];
foreach ($allTables as $t) {
    $refs = $tableRefs[$t] ?? 0;
    $cnt = (int)$pdo->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
    if ($refs === 0 && $cnt === 0) $orphans[] = $t;
}
echo "Orphan tables (0 refs + 0 rows): " . count($orphans) . "\n";
foreach (array_slice($orphans, 0, 30) as $t) echo "  - $t\n";
if (count($orphans) > 30) echo "  ... and " . (count($orphans) - 30) . " more\n";?>