<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

echo "=== DEAD TABLE ANALYSIS ===\n\n";

// 1. customers (0 rows, no FKs pointing to it)
$customersRefs = $pdo->query("
    SELECT TABLE_NAME FROM information_schema.KEY_COLUMN_USAGE
    WHERE REFERENCED_TABLE_NAME = 'customers' AND TABLE_SCHEMA = 'apsdreamhome'
")->fetchAll();
echo "customers: 0 rows, " . count($customersRefs) . " FKs referencing it\n";
if (count($customersRefs) > 0) {
    foreach ($customersRefs as $r) echo "  - {$r['TABLE_NAME']}\n";
}

// 2. admin_users
$adminRefs = $pdo->query("
    SELECT TABLE_NAME FROM information_schema.KEY_COLUMN_USAGE
    WHERE REFERENCED_TABLE_NAME = 'admin_users' AND TABLE_SCHEMA = 'apsdreamhome'
")->fetchAll();
echo "\nadmin_users: 0 rows, " . count($adminRefs) . " FKs referencing it\n";
if (count($adminRefs) > 0) {
    foreach ($adminRefs as $r) echo "  - {$r['TABLE_NAME']}\n";
}

// 3. associates
$assocRefs = $pdo->query("
    SELECT TABLE_NAME FROM information_schema.KEY_COLUMN_USAGE
    WHERE REFERENCED_TABLE_NAME = 'associates' AND TABLE_SCHEMA = 'apsdreamhome'
")->fetchAll();
echo "\nassociates: 0 rows, " . count($assocRefs) . " FKs referencing it\n";
if (count($assocRefs) > 0) {
    foreach ($assocRefs as $r) echo "  - {$r['TABLE_NAME']}\n";
}

// 4. employees
$empRefs = $pdo->query("
    SELECT TABLE_NAME FROM information_schema.KEY_COLUMN_USAGE
    WHERE REFERENCED_TABLE_NAME = 'employees' AND TABLE_SCHEMA = 'apsdreamhome'
")->fetchAll();
echo "\nemployees: 0 rows, " . count($empRefs) . " FKs referencing it\n";
if (count($empRefs) > 0) {
    foreach ($empRefs as $r) echo "  - {$r['TABLE_NAME']}\n";
}

// 5. Check for code references (controllers, models, views)
echo "\n=== CODE REFERENCES ===\n\n";
$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('app'));
$found = ['customers' => 0, 'admin_users' => 0, 'associates' => 0, 'employees' => 0];
foreach ($files as $f) {
    if ($f->isFile() && $f->getExtension() === 'php') {
        $content = file_get_contents($f->getPathname());
        foreach (array_keys($found) as $table) {
            // Only count SQL references (FROM, JOIN, UPDATE, etc)
            if (preg_match("/\b(FROM|JOIN|INTO|UPDATE|TABLE)\s+`?$table`?/i", $content)) {
                $found[$table]++;
            }
        }
    }
}
foreach ($found as $table => $count) {
    echo "$table: $count SQL references in app/ code\n";
}

echo "\n=== VIEW DEFINITIONS (broken views) ===\n\n";
try {
    $v1 = $pdo->query("SHOW CREATE VIEW booking_summary")->fetch();
    echo "booking_summary:\n{$v1['Create View']}\n\n";
} catch (Exception $e) { echo "booking_summary: {$e->getMessage()}\n"; }

try {
    $v2 = $pdo->query("SHOW CREATE VIEW employee_performance")->fetch();
    echo "employee_performance:\n{$v2['Create View']}\n\n";
} catch (Exception $e) { echo "employee_performance: {$e->getMessage()}\n"; }
