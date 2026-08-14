<?php
require_once __DIR__ . '/../app/Core/autoload.php';
$db = \App\Core\Database\Database::getInstance();

// Check customer login test user
$users = $db->fetchAll("SELECT id, email, role, status, registration_status FROM users WHERE role = 'customer' LIMIT 5");
echo "Sample customer users:\n";
foreach ($users as $u) {
    echo "  ID={$u['id']} | {$u['email']} | status={$u['status']} | reg_status={$u['registration_status']}\n";
}

// Count customers with wrong registration_status
$wrongCount = $db->fetchOne("SELECT COUNT(*) as cnt FROM users WHERE role = 'customer' AND registration_status != 'approved'");
echo "\nCustomers NOT approved: " . ($wrongCount['cnt'] ?? 0) . "\n";

// Fix: set all active customers to approved
$db->query("UPDATE users SET registration_status = 'approved' WHERE role = 'customer' AND status = 'active' AND registration_status != 'approved'");
echo "Fixed all active customers to approved\n";?>