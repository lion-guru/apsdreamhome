<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require __DIR__ . '/../config/bootstrap.php';
require __DIR__ . '/../app/Core/Database.php';
use App\Core\Database;
$db = Database::getInstance();
$pdo = $db->getConnection();

// Direct fix - force update all role users
$fixes = [
    ['sales_director', 'sales_director@apsdreamhome.com'],
    ['marketing_director', 'marketing_director@apsdreamhome.com'],
    ['construction_director', 'construction_director@apsdreamhome.com'],
    ['finance_director', 'finance_director@apsdreamhome.com'],
    ['hr_director', 'hr_director@apsdreamhome.com'],
    ['department_manager', 'dept_manager@apsdreamhome.com'],
    ['project_manager', 'project_manager@apsdreamhome.com'],
    ['sales_manager', 'sales_manager@apsdreamhome.com'],
    ['hr_manager', 'hr_manager@apsdreamhome.com'],
    ['marketing_manager', 'marketing_manager@apsdreamhome.com'],
    ['finance_manager', 'finance_manager@apsdreamhome.com'],
    ['property_manager', 'property_manager@apsdreamhome.com'],
    ['it_manager', 'it_manager@apsdreamhome.com'],
    ['operations_manager', 'operations_manager@apsdreamhome.com'],
    ['team_lead', 'team_lead@apsdreamhome.com'],
    ['telecalling_lead', 'telecalling_lead@apsdreamhome.com'],
    ['sales_team_lead', 'sales_team_lead@apsdreamhome.com'],
    ['support_lead', 'support_lead@apsdreamhome.com'],
    ['senior_accountant', 'senior_accountant@apsdreamhome.com'],
    ['senior_developer', 'senior_developer@apsdreamhome.com'],
    ['legal_advisor', 'legal_advisor@apsdreamhome.com'],
    ['chartered_accountant', 'ca@apsdreamhome.com'],
    ['accountant', 'accountant@apsdreamhome.com'],
    ['developer', 'developer@apsdreamhome.com'],
    ['content_writer', 'content_writer@apsdreamhome.com'],
    ['graphic_designer', 'graphic_designer@apsdreamhome.com'],
    ['data_entry_operator', 'data_entry@apsdreamhome.com'],
    ['backoffice_staff', 'backoffice_staff@apsdreamhome.com'],
    ['telecalling_executive', 'telecalling_exec@apsdreamhome.com'],
    ['support_executive', 'support_exec@apsdreamhome.com'],
    ['senior_associate', 'senior_associate@apsdreamhome.com'],
    ['associate_team_lead', 'associate_team_lead@apsdreamhome.com'],
    ['senior_agent', 'senior_agent@apsdreamhome.com'],
    ['franchise_owner', 'franchise_owner@apsdreamhome.com'],
];

foreach ($fixes as [$role, $email]) {
    $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE email = ?");
    $stmt->execute([$role, $email]);
    $count = $stmt->rowCount();
    if ($count > 0) {
        echo "FIXED: $email => $role\n";
    } else {
        // Check what's there
        $check = $pdo->prepare("SELECT id, role FROM users WHERE email = ?");
        $check->execute([$email]);
        $row = $check->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            echo "EXIST: $email (id={$row['id']}, role='{$row['role']}')\n";
            // Force update
            $pdo->prepare("UPDATE users SET role = ? WHERE id = ?")->execute([$role, $row['id']]);
            echo "  => FORCE UPDATED to $role\n";
        } else {
            echo "MISSING: $email\n";
        }
    }
}

echo "\n=== FINAL VERIFICATION ===\n";
$all = $pdo->query("SELECT email, role FROM users WHERE email LIKE '%@apsdreamhome.com' ORDER BY role, email")->fetchAll(PDO::FETCH_ASSOC);
foreach ($all as $u) {
    $status = empty($u['role']) ? 'EMPTY!' : 'OK';
    echo "  [$status] {$u['role']}: {$u['email']}\n";
}
