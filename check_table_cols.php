<?php
$db = new PDO('mysql:host=127.0.0.1;dbname=apsdreamhome', 'root', '');
foreach (['associate_profiles', 'employee_profiles', 'agent_profiles', 'customer_profiles'] as $table) {
    echo "=== $table ===\n";
    foreach ($db->query("DESCRIBE $table") as $row) {
        echo "  $row[0] ($row[1])\n";
    }
    echo "\n";
}