<?php
$db = new PDO('mysql:host=127.0.0.1;dbname=apsdreamhome', 'root', '');
foreach (['associate_profiles', 'employee_profiles', 'agent_profiles', 'customer_profiles'] as $table) {
    echo "=== $table ===\n";
    $cols = $db->query("DESCRIBE $table")->fetchAll(PDO::FETCH_COLUMN);
    foreach ($cols as $c) echo "  $c\n";
    echo "\n";
}