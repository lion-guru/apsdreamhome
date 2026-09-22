<?php
require_once __DIR__ . '/../config/bootstrap.php';
use App\Core\Database\Database;

$db = Database::getInstance();

echo "=== CHECKING PASSWORDS OF TEST/STANDARD USERS ===\n";
$emails = [
    'admin@apsdreamhome.com',
    'agent1@apsdreamhome.com',
    'customer1@apsdreamhome.com',
    'rajesh.associate@apsdreamhome.test',
    'sanjay.freelancer@apsdreamhome.test',
    'pooja.employee@apsdreamhome.test'
];

foreach ($emails as $email) {
    $u = $db->fetchOne("SELECT id, name, email, role, password, status FROM users WHERE email = ?", [$email]);
    if ($u) {
        $pwMatchesAdmin123 = password_verify('admin123', $u['password']);
        $pwMatchesPass123 = password_verify('Password@123', $u['password']);
        $pwMatchesCustomer123 = password_verify('customer123', $u['password']);
        $pwMatchesSecret = password_verify('secret', $u['password']);
        $pwMatchesPassword = password_verify('password', $u['password']);
        
        $matched = 'unknown';
        if ($pwMatchesAdmin123) $matched = 'admin123';
        elseif ($pwMatchesPass123) $matched = 'Password@123';
        elseif ($pwMatchesCustomer123) $matched = 'customer123';
        elseif ($pwMatchesSecret) $matched = 'secret';
        elseif ($pwMatchesPassword) $matched = 'password';
        
        echo sprintf("[%s] ID: %d | Role: %-12s | Pwd: %s\n", $email, $u['id'], $u['role'], $matched);
    } else {
        echo sprintf("[%s] NOT FOUND\n", $email);
    }
}

// Check employees
echo "\n=== EMPLOYEES IN DB ===\n";
$emps = $db->fetchAll("SELECT u.id, u.name, u.email, u.role, u.password FROM users u WHERE u.role IN ('employee', 'telecaller', 'staff', 'manager') LIMIT 5");
foreach ($emps as $emp) {
    $matched = 'unknown';
    if (password_verify('admin123', $emp['password'])) $matched = 'admin123';
    elseif (password_verify('Password@123', $emp['password'])) $matched = 'Password@123';
    elseif (password_verify('password', $emp['password'])) $matched = 'password';
    echo sprintf("[%s] ID: %d | Role: %-12s | Pwd: %s\n", $emp['email'], $emp['id'], $emp['role'], $matched);
}
