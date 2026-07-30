<?php
$db = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '');
$tables = $db->query("SELECT TABLE_NAME FROM information_schema.COLUMNS WHERE COLUMN_NAME='tenant_id' AND TABLE_SCHEMA='apsdreamhome'")->fetchAll(PDO::FETCH_COLUMN);
$indexed = 0;
$missing = [];
foreach ($tables as $t) {
    $indexes = $db->query("SHOW INDEX FROM `$t` WHERE Column_name='tenant_id'")->fetchAll();
    if (count($indexes) > 0) { $indexed++; }
    else { $missing[] = $t; }
}
echo "Tables with tenant_id: " . count($tables) . "\n";
echo "Indexed on tenant_id: $indexed\n";
echo "Missing index: " . count($missing) . "\n";
if (count($missing) > 0) {
    echo "First 30: " . implode(', ', array_slice($missing, 0, 30)) . "\n";
}
