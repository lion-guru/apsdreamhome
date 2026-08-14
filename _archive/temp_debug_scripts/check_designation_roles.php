<?php
$config = require __DIR__ . '/../config/database.php';
$pdo = new PDO('mysql:host=' . $config['host'] . ';port=' . $config['port'] . ';dbname=' . $config['database'], $config['username'], $config['password']);
$rows = $pdo->query('SELECT designation, department, sub_role, dashboard_view FROM employee_designation_roles ORDER BY id')->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) {
    echo $r['designation'] . '|' . ($r['department'] ?? 'NULL') . '|' . $r['sub_role'] . '|' . ($r['dashboard_view'] ?? 'NULL') . PHP_EOL;
}
echo "\nTotal: " . count($rows) . PHP_EOL;?>