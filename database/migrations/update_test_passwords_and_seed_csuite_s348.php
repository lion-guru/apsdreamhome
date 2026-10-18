<?php
/**
 * Migration: Standardize all user passwords to 'Aps@2026' & Seed C-Suite testing accounts
 * Session: 348
 * Idempotent: Re-run safe (skips existing, preserves IDs)
 */
require_once __DIR__ . '/../../config/bootstrap.php';

$pdo = \App\Core\Database\Database::getInstance()->getConnection();

$passwordPlain = 'Aps@2026';
$passwordHash = password_hash($passwordPlain, PASSWORD_BCRYPT);

echo "1. Standardizing passwords in users table...\n";
$stmtUpdateUsers = $pdo->prepare("UPDATE users SET password = ?, updated_at = NOW() WHERE status != 'deleted'");
$stmtUpdateUsers->execute([$passwordHash]);
$affected = $stmtUpdateUsers->rowCount();
echo "   Updated {$affected} user passwords to Aps@2026 hash.\n";

echo "2. Ensuring C-Suite test users exist in users table...\n";
$csuiteAccounts = [
    [
        'email'       => 'ceo@apsdreamhome.com',
        'name'        => 'CEO User',
        'role'        => 'ceo',
        'phone'       => '9876543220',
        'department'  => 'Executive',
        'designation' => 'CEO',
        'code'        => 'EMP0050',
    ],
    [
        'email'       => 'cto@apsdreamhome.com',
        'name'        => 'CTO User',
        'role'        => 'cto',
        'phone'       => '9876543221',
        'department'  => 'Technology',
        'designation' => 'CTO',
        'code'        => 'EMP0051',
    ],
    [
        'email'       => 'cfo@apsdreamhome.com',
        'name'        => 'CFO User',
        'role'        => 'cfo',
        'phone'       => '9876543222',
        'department'  => 'Finance',
        'designation' => 'CFO',
        'code'        => 'EMP0052',
    ],
    [
        'email'       => 'coo@apsdreamhome.com',
        'name'        => 'COO User',
        'role'        => 'coo',
        'phone'       => '9876543223',
        'department'  => 'Operations',
        'designation' => 'COO',
        'code'        => 'EMP0053',
    ],
    [
        'email'       => 'director@apsdreamhome.com',
        'name'        => 'Director User',
        'role'        => 'director',
        'phone'       => '9876543224',
        'department'  => 'Operations',
        'designation' => 'DIRECTOR',
        'code'        => 'EMP0054',
    ],
];

$stmtFindUser = $pdo->prepare("SELECT id, role, password FROM users WHERE email = ? LIMIT 1");
$stmtInsertUser = $pdo->prepare("INSERT INTO users (tenant_id, name, email, phone, role, password, status, registration_status, email_verified_at, created_at, updated_at) VALUES (1, ?, ?, ?, ?, ?, 'active', 'approved', NOW(), NOW(), NOW())");

$stmtFindEmp = $pdo->prepare("SELECT id FROM employees WHERE email = ? LIMIT 1");
$stmtUpdateEmpUserId = $pdo->prepare("UPDATE employees SET user_id = ?, updated_at = NOW() WHERE id = ?");
$stmtInsertEmp = $pdo->prepare("INSERT INTO employees (tenant_id, user_id, name, email, phone, role, department, designation, employee_code, joining_date, status, created_at, updated_at) VALUES (1, ?, ?, ?, ?, ?, ?, ?, ?, CURDATE(), 'active', NOW(), NOW())");

foreach ($csuiteAccounts as $acc) {
    $stmtFindUser->execute([$acc['email']]);
    $userRow = $stmtFindUser->fetch(PDO::FETCH_ASSOC);

    if ($userRow) {
        $userId = (int)$userRow['id'];
        echo "   User {$acc['email']} already exists (UID: {$userId}, Role: {$userRow['role']}) - verified.\n";
    } else {
        $stmtInsertUser->execute([
            $acc['name'],
            $acc['email'],
            $acc['phone'],
            $acc['role'],
            $passwordHash,
        ]);
        $userId = (int)$pdo->lastInsertId();
        echo "   INSERTED user {$acc['email']} (UID: {$userId}, Role: {$acc['role']}).\n";
    }

    // Link/Sync in employees table
    $stmtFindEmp->execute([$acc['email']]);
    $empRow = $stmtFindEmp->fetch(PDO::FETCH_ASSOC);

    if ($empRow) {
        $stmtUpdateEmpUserId->execute([$userId, (int)$empRow['id']]);
        echo "   Linked employee row ID {$empRow['id']} to user_id {$userId}.\n";
    } else {
        $stmtInsertEmp->execute([
            $userId,
            $acc['name'],
            $acc['email'],
            $acc['phone'],
            $acc['department'],
            $acc['department'],
            $acc['designation'],
            $acc['code'],
        ]);
        $empId = (int)$pdo->lastInsertId();
        echo "   INSERTED employee row ID {$empId} for {$acc['email']}.\n";
    }
}

echo "3. Password verification check...\n";
$stmtCheck = $pdo->prepare("SELECT id, name, email, role, password FROM users WHERE email IN ('admin@apsdreamhome.com', 'ceo@apsdreamhome.com', 'cto@apsdreamhome.com', 'cfo@apsdreamhome.com', 'employee@apsdreamhome.com', 'agent1@apsdreamhome.com', 'customer1@apsdreamhome.com')");
$stmtCheck->execute();
$sampleUsers = $stmtCheck->fetchAll(PDO::FETCH_ASSOC);

foreach ($sampleUsers as $su) {
    $matches = password_verify($passwordPlain, $su['password']);
    echo "   User {$su['email']} ({$su['role']}): Password verify " . ($matches ? "SUCCESS ✅" : "FAILED ❌") . "\n";
}

echo "Migration update_test_passwords_and_seed_csuite_s348 completed successfully!\n";
