<?php
/**
 * Salary consolidation: salary_structures (1 row, 12 refs) -> employee_salary_structure (3 rows, 14 refs)
 * Both have same purpose. Merge salary_structures into employee_salary_structure.
 */
$config = require __DIR__ . '/../config/database.php';
$pdo = new PDO("mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4", $config['username'], $config['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

echo "=== SALARY CONSOLIDATION ===\n\n";

// Backup
$pdo->exec("DROP TABLE IF EXISTS salary_structures_backup_20260603");
$pdo->exec("CREATE TABLE salary_structures_backup_20260603 AS SELECT * FROM salary_structures");
echo "Backed up salary_structures\n";

// Migrate the 1 row from salary_structures to employee_salary_structure
$row = $pdo->query("SELECT * FROM salary_structures LIMIT 1")->fetch(PDO::FETCH_ASSOC);
if ($row) {
    $pdo->prepare("INSERT INTO employee_salary_structure (employee_id, basic_salary, hra, medical_allowance, gross_salary, net_salary, effective_from, effective_to, is_active, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())")
        ->execute([
            $row['employee_id'],
            $row['basic_salary'],
            $row['house_rent_allowance'],
            $row['medical_allowance'],
            $row['gross_salary'],
            $row['net_salary'],
            $row['effective_from'],
            $row['effective_to'],
            $row['is_active']
        ]);
    echo "Migrated 1 row from salary_structures -> employee_salary_structure\n";
}

$pdo->exec("DROP TABLE salary_structures");
echo "Dropped salary_structures\n";

$after = $pdo->query('SHOW TABLES')->rowCount();
echo "Tables: $after\n";?>