<?php
$db = new PDO('mysql:host=127.0.0.1;dbname=apsdreamhome', 'root', '');
foreach (['associate_profiles', 'employee_profiles', 'agent_profiles', 'customer_profiles'] as $table) {
    echo "=== $table ===\n";
    try {
        $result = $db->query("SHOW TABLE STATUS LIKE '$table'");
        $row = $result->fetch(PDO::FETCH_ASSOC);
        foreach ($row as $k => $v) {
            if (in_array($k, ['Name', 'Engine', 'Comment', 'Auto_increment', 'Create_options'])) {
                echo "  $k: $v\n";
            }
        }
    } catch (\Throwable $e) {
        echo "  Error: " . $e->getMessage() . "\n";
    }
}