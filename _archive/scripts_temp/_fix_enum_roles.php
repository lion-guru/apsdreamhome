<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require __DIR__ . '/../config/bootstrap.php';
require __DIR__ . '/../app/Core/Database.php';
use App\Core\Database;
$db = Database::getInstance();
$pdo = $db->getConnection();

echo "=== FIXING ENUM ROLE COLUMN ===\n\n";

// Current ENUM values
$col = $pdo->query("SHOW COLUMNS FROM users LIKE 'role'")->fetch(PDO::FETCH_ASSOC);
echo "Current: " . $col['Type'] . "\n";

// All needed role values
$allRoles = [
    'admin','user','employee','associate','agent','builder','investor','super_admin',
    'ceo','cfo','coo','cto','cmo','chro','director','manager','customer','telecaller',
    // New roles to add:
    'sales_director','marketing_director','construction_director','finance_director','hr_director',
    'department_manager','project_manager','sales_manager','hr_manager','marketing_manager',
    'finance_manager','property_manager','it_manager','operations_manager',
    'team_lead','telecalling_lead','sales_team_lead','support_lead',
    'senior_accountant','senior_developer','legal_advisor','chartered_accountant',
    'accountant','developer','content_writer','graphic_designer',
    'data_entry_operator','backoffice_staff','telecalling_executive','support_executive',
    'senior_associate','associate_team_lead','senior_agent','franchise_owner',
];

// Build new ENUM
$enumValues = implode(',', array_map(function($v) { return "'$v'"; }, $allRoles));
$alterSql = "ALTER TABLE users MODIFY COLUMN role ENUM($enumValues) DEFAULT NULL";

echo "\nNew ENUM has " . count($allRoles) . " values\n";
echo "Executing ALTER TABLE...\n";

try {
    $pdo->exec($alterSql);
    echo "SUCCESS!\n";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}

// Verify
$col = $pdo->query("SHOW COLUMNS FROM users LIKE 'role'")->fetch(PDO::FETCH_ASSOC);
echo "\nVerified: " . $col['Type'] . "\n";

// Now update all the role users
echo "\n=== UPDATING USER ROLES ===\n";

$roleUpdates = [
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

$fixed = 0;
foreach ($roleUpdates as [$role, $email]) {
    $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE email = ?");
    $stmt->execute([$role, $email]);
    
    // Verify
    $check = $pdo->prepare("SELECT role FROM users WHERE email = ?");
    $check->execute([$email]);
    $current = $check->fetchColumn();
    
    if ($current === $role) {
        echo "  OK: $email => $role\n";
        $fixed++;
    } else {
        echo "  FAIL: $email => expected=$role, got=" . ($current ?: 'NULL') . "\n";
    }
}

echo "\nFixed: $fixed / " . count($roleUpdates) . "\n";
