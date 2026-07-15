<?php
/**
 * Populate dashboard_view column in employee_designation_roles
 * Maps each department/designation to its specific dashboard view
 */
$config = require __DIR__ . '/../config/database.php';
$pdo = new PDO(
    'mysql:host=' . $config['host'] . ';port=' . $config['port'] . ';dbname=' . $config['database'],
    $config['username'],
    $config['password'],
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

// Dashboard view mapping by department
$mapping = [
    'HR'          => 'employee/hr_dashboard',
    'Land'        => 'employee/land_manager_dashboard',
    'Legal'       => 'employee/legal_dashboard',
    'Finance'     => 'employee/finance_dashboard',
    'Marketing'   => 'employee/marketing_dashboard',
    'IT'          => 'employee/it_dashboard',
    'Operations'  => 'employee/ops_dashboard',
    'Sales'       => 'employee/sales_dashboard',
];

// Telecaller (department=NULL)
$telecallerView = 'employee/telecalling_dashboard';
// General (* designation)
$generalView = 'employee/dashboard';

$totalUpdated = 0;

// Update by department
foreach ($mapping as $dept => $view) {
    $stmt = $pdo->prepare("UPDATE employee_designation_roles SET dashboard_view = ? WHERE department = ?");
    $stmt->execute([$view, $dept]);
    $count = $stmt->rowCount();
    $totalUpdated += $count;
    echo "  {$dept}: {$count} rows → {$view}" . PHP_EOL;
}

// Update telecaller (department IS NULL, sub_role LIKE 'employee_telecaller%')
$stmt = $pdo->prepare("UPDATE employee_designation_roles SET dashboard_view = ? WHERE sub_role LIKE 'employee_telecaller%' AND department IS NULL");
$stmt->execute([$telecallerView]);
$count = $stmt->rowCount();
$totalUpdated += $count;
echo "  Telecaller: {$count} rows → {$telecallerView}" . PHP_EOL;

// Update general (* designation)
$stmt = $pdo->prepare("UPDATE employee_designation_roles SET dashboard_view = ? WHERE designation = '*'");
$stmt->execute([$generalView]);
$count = $stmt->rowCount();
$totalUpdated += $count;
echo "  General (*): {$count} rows → {$generalView}" . PHP_EOL;

echo PHP_EOL . "Total updated: {$totalUpdated} rows" . PHP_EOL;

// Verify
$remaining = $pdo->query("SELECT COUNT(*) FROM employee_designation_roles WHERE dashboard_view IS NULL")->fetchColumn();
echo "Remaining NULL: {$remaining} rows" . PHP_EOL;
