<?php
require_once dirname(__DIR__) . '/config/bootstrap.php';
$db = \App\Core\Database::getInstance();

echo "=== EMPLOYEE USERS ===\n";
$rows = $db->fetchAll("SELECT u.id, u.name, u.role, u.user_type FROM users u WHERE u.role = 'employee' ORDER BY u.id");
foreach ($rows as $r) {
    echo "ID:{$r['id']} | Name:{$r['name']} | role:{$r['role']} | user_type:{$r['user_type']}\n";
}

echo "\n=== EMPLOYEES TABLE ===\n";
$rows2 = $db->fetchAll("SELECT * FROM employees ORDER BY id");
foreach ($rows2 as $r) {
    echo "ID:{$r['id']} | user_id:{$r['user_id']} | name:{$r['name']} | designation:{$r['designation']} | department:{$r['department']}\n";
}

echo "\n=== ALL UNIQUE ROLES ===\n";
$rows3 = $db->fetchAll("SELECT role, COUNT(*) as c FROM users GROUP BY role ORDER BY c DESC");
foreach ($rows3 as $r) {
    echo "{$r['role']}: {$r['c']}\n";
}

echo "\n=== ALL UNIQUE DESIGNATIONS ===\n";
$rows4 = $db->fetchAll("SELECT designation, COUNT(*) as c FROM employees WHERE designation IS NOT NULL AND designation != '' GROUP BY designation ORDER BY c DESC");
foreach ($rows4 as $r) {
    echo "{$r['designation']}: {$r['c']}\n";
}

echo "\n=== ALL UNIQUE DEPARTMENTS ===\n";
$rows5 = $db->fetchAll("SELECT department, COUNT(*) as c FROM employees WHERE department IS NOT NULL AND department != '' GROUP BY department ORDER BY c DESC");
foreach ($rows5 as $r) {
    echo "{$r['department']}: {$r['c']}\n";
}

echo "\n=== USER_TYPES ===\n";
$rows6 = $db->fetchAll("SELECT user_type, role, COUNT(*) as c FROM users GROUP BY user_type, role ORDER BY user_type");
foreach ($rows6 as $r) {
    echo "user_type:{$r['user_type']} | role:{$r['role']} | count:{$r['c']}\n";
}?>