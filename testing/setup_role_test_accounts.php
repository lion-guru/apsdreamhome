<?php
require_once __DIR__ . '/../config/bootstrap.php';
use App\Core\Database\Database;

$db = Database::getInstance();

$testUsers = [
    'admin' => ['admin@apsdreamhome.com', 'Aps@2026', 'admin'],
    'customer' => ['customer_test@apsdreamhome.test', 'Aps@2026', 'customer'],
    'associate' => ['rajesh.associate@apsdreamhome.test', 'Aps@2026', 'associate'],
    'freelancer_agent' => ['sanjay.freelancer@apsdreamhome.test', 'Aps@2026', 'agent'],
    'employee_agent' => ['pooja.employee@apsdreamhome.test', 'Aps@2026', 'agent'],
    'employee' => ['emp_test@apsdreamhome.test', 'Aps@2026', 'employee'],
];

echo "=== ENSURING TEST ACCOUNTS WITH KNOWN PASSWORDS EXIST ===\n";

foreach ($testUsers as $label => [$email, $password, $role]) {
    try {
        $existing = $db->fetchOne("SELECT id, name, email, role FROM users WHERE email = ?", [$email]);
        if (!$existing) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $phone = '98' . rand(10000000, 99999999);
            $db->execute(
                "INSERT INTO users (name, email, phone, password, role, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, 'active', NOW(), NOW())",
                [ucfirst(str_replace('_', ' ', $label)), $email, $phone, $hash, $role]
            );
            $id = $db->lastInsertId();
            echo "[CREATED] $label -> $email (ID: $id, Role: $role)\n";
        } else {
            // Ensure password is set to known
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $db->execute("UPDATE users SET password = ?, status = 'active', role = ? WHERE id = ?", [$hash, $role, $existing['id']]);
            echo "[READY] $label -> $email (ID: {$existing['id']}, Role: $role)\n";
        }
    } catch (\Throwable $e) {
        echo "[ERROR] $label: " . $e->getMessage() . "\n";
    }
}
