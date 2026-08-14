<?php
$config = require __DIR__ . '/../config/database.php';
$pdo = new PDO("mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4", $config['username'], $config['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

$linked = $pdo->query("SELECT COUNT(*) FROM salary_payments WHERE salary_structure_id IS NOT NULL")->fetchColumn();
echo "salary_payments with salary_structure_id: $linked\n";

$ids = $pdo->query("SELECT id FROM salary_structures")->fetchAll(PDO::FETCH_COLUMN);
echo "salary_structures IDs: " . implode(', ', $ids) . "\n";

// Update salary_payments to reference employee_salary_structure
// First, find the employee_salary_structure.id that matches
$pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
foreach ($ids as $oldId) {
    $ss = $pdo->query("SELECT employee_id FROM salary_structures WHERE id = $oldId")->fetch(PDO::FETCH_ASSOC);
    if ($ss) {
        $newId = $pdo->query("SELECT id FROM employee_salary_structure WHERE employee_id = {$ss['employee_id']} ORDER BY id DESC LIMIT 1")->fetchColumn();
        echo "Old salary_structures.id=$oldId (emp_id={$ss['employee_id']}) -> new employee_salary_structure.id=$newId\n";
        if ($newId) {
            $pdo->exec("UPDATE salary_payments SET salary_structure_id = $newId WHERE salary_structure_id = $oldId");
            echo "  Updated salary_payments\n";
        }
    }
}
$pdo->exec("DROP TABLE salary_structures");
$pdo->exec("DROP TABLE salary_structures_backup_20260603");
$pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
echo "Dropped salary_structures + backup\n";

$after = $pdo->query('SHOW TABLES')->rowCount();
echo "Tables: $after\n";?>