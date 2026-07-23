<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '');

echo "=== CHECK USERS TABLE ROLE FOR TEST EMPLOYEES ===\n";
$emps = $pdo->query("SELECT u.id, u.name, u.email, u.role, e.designation, e.department FROM users u INNER JOIN employees e ON u.id = e.user_id WHERE e.id IN (1,2,3,4,5,6) ORDER BY e.id")->fetchAll(PDO::FETCH_ASSOC);
foreach ($emps as $e) {
    echo "  user_id={$e['id']} name={$e['name']} role={$e['role']} designation={$e['designation']} dept={$e['department']}\n";
}

echo "\n=== ALL USERS WITH ROLE='employee' ===\n";
$all = $pdo->query("SELECT id, name, email, role FROM users WHERE role = 'employee' ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
foreach ($all as $u) {
    echo "  user_id={$u['id']} name={$u['name']} email={$u['email']}\n";
}
echo "Total: " . count($all) . "\n";

echo "\n=== test_login=4 would pick: ===\n";
$test = $pdo->query("SELECT * FROM users WHERE role = 'employee' ORDER BY id LIMIT 1")->fetch(PDO::FETCH_ASSOC);
if ($test) {
    echo "  user_id={$test['id']} name={$test['name']} email={$test['email']}\n";
    // Check if this user has an employee record
    $emp = $pdo->prepare("SELECT * FROM employees WHERE user_id = ?");
    $emp->execute([$test['id']]);
    $empRow = $emp->fetch(PDO::FETCH_ASSOC);
    if ($empRow) {
        echo "  employee_id={$empRow['id']} designation={$empRow['designation']} dept={$empRow['department']}\n";
    } else {
        echo "  NO EMPLOYEE RECORD!\n";
    }
} else {
    echo "  NO users with role='employee'!\n";
}
