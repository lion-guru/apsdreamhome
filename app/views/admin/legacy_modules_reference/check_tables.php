<?php
require_once __DIR__ . '/core/init.php';

$db = \App\Core\App::database();
$tables = ['mlm_levels', 'mlm_commissions', 'associates', 'mlm_settings', 'salary_plans', 'commission_rates'];

foreach ($tables as $table) {
    echo "<h3>Table: $table</h3>";
    try {
        $results = $db->fetchAll("DESCRIBE $table");
        if (!empty($results)) {
            echo "<table border='1'><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
            foreach ($results as $row) {
                echo "<tr><td>" . implode("</td><td>", $row) . "</td></tr>";
            }
            echo "</table>";
        } else {
            echo "No description available for $table";
        }
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }
}
