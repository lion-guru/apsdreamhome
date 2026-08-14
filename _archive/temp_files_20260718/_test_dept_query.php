<?php
$config = include 'config/database.php';
$dsn = 'mysql:host=' . $config['host'] . ';port=' . $config['port'] . ';dbname=' . $config['database'];
$pdo = new PDO($dsn, $config['username'], $config['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

// Test the exact query from DepartmentService
$sql = "SELECT d.*, u.name AS head_name,
               (SELECT COUNT(*) FROM designations des WHERE des.department_id = d.id) AS designation_count,
               (SELECT COUNT(*) FROM employees e WHERE e.department = d.code) AS employee_count
        FROM departments d
        LEFT JOIN users u ON d.head_user_id = u.id
        ORDER BY d.code ASC";
$r = $pdo->query($sql);
$rows = $r->fetchAll(PDO::FETCH_ASSOC);
echo "Query returned " . count($rows) . " rows" . PHP_EOL;
foreach ($rows as $row) {
    echo $row['code'] . ': ' . $row['name'] . ' (desigs: ' . $row['designation_count'] . ', emps: ' . $row['employee_count'] . ')' . PHP_EOL;
}

// Test stats query
echo PHP_EOL . "--- Stats ---" . PHP_EOL;
$r = $pdo->query("SELECT COUNT(*) FROM departments");
echo "Total: " . $r->fetchColumn() . PHP_EOL;
$r = $pdo->query("SELECT COUNT(*) FROM departments WHERE status='active'");
echo "Active: " . $r->fetchColumn() . PHP_EOL;
$r = $pdo->query("SELECT COUNT(*) FROM designations");
echo "Designations: " . $r->fetchColumn() . PHP_EOL;
$r = $pdo->query("SELECT COUNT(*) FROM employees");
echo "Employees: " . $r->fetchColumn() . PHP_EOL;?>