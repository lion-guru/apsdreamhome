<?php
$root = dirname(__DIR__);
$config = require $root . '/config/database.php';

try {
    $pdo = new PDO(
        "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4",
        $config['username'],
        $config['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    echo "=== FIX COLLATION ===\n\n";

    // Check employees table collation
    $info = $pdo->query("SHOW CREATE TABLE employees")->fetch(PDO::FETCH_ASSOC);
    preg_match('/CHARSET=(\w+)/', $info['Create Table'], $m);
    $empCharset = $m[1] ?? 'unknown';
    echo "Employees table charset: $empCharset\n";

    // Fix employee_designation_roles to match
    $pdo->exec("ALTER TABLE employee_designation_roles CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "  Fixed employee_designation_roles -> utf8mb4_unicode_ci\n";

    // Also fix admin_role_menu_permissions
    $pdo->exec("ALTER TABLE admin_role_menu_permissions CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "  Fixed admin_role_menu_permissions -> utf8mb4_unicode_ci\n";

    // Verify employee mapping now works
    echo "\n[2] Current employee designation mapping:\n";
    $stmt = $pdo->query("
        SELECT u.id, u.name, e.designation, e.department, edr.sub_role
        FROM users u
        LEFT JOIN employees e ON e.user_id = u.id
        LEFT JOIN employee_designation_roles edr ON edr.designation = e.designation AND (edr.department = e.department OR edr.department IS NULL)
        WHERE u.role = 'employee'
        ORDER BY u.id
    ");
    while ($emp = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $subRole = $emp['sub_role'] ?? '(no mapping)';
        echo "  ID:{$emp['id']} | {$emp['name']} | {$emp['designation']}/{$emp['department']} -> $subRole\n";
    }

    echo "\n=== DONE ===\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}?>