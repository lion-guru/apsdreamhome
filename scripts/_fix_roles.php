<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require __DIR__ . '/../config/bootstrap.php';
require __DIR__ . '/../app/Core/Database.php';
use App\Core\Database;
$db = Database::getInstance();
$pdo = $db->getConnection();

echo "=== FIXING ROLE COLUMN FOR DIRECTOR/MANAGER USERS ===\n\n";

$roleEmailMap = [
    'sales_director' => 'sales_director@apsdreamhome.com',
    'marketing_director' => 'marketing_director@apsdreamhome.com',
    'construction_director' => 'construction_director@apsdreamhome.com',
    'finance_director' => 'finance_director@apsdreamhome.com',
    'hr_director' => 'hr_director@apsdreamhome.com',
    'department_manager' => 'dept_manager@apsdreamhome.com',
    'project_manager' => 'project_manager@apsdreamhome.com',
    'sales_manager' => 'sales_manager@apsdreamhome.com',
    'hr_manager' => 'hr_manager@apsdreamhome.com',
    'marketing_manager' => 'marketing_manager@apsdreamhome.com',
    'finance_manager' => 'finance_manager@apsdreamhome.com',
    'property_manager' => 'property_manager@apsdreamhome.com',
    'it_manager' => 'it_manager@apsdreamhome.com',
    'operations_manager' => 'operations_manager@apsdreamhome.com',
    'team_lead' => 'team_lead@apsdreamhome.com',
    'telecalling_lead' => 'telecalling_lead@apsdreamhome.com',
    'sales_team_lead' => 'sales_team_lead@apsdreamhome.com',
    'support_lead' => 'support_lead@apsdreamhome.com',
    'senior_accountant' => 'senior_accountant@apsdreamhome.com',
    'senior_developer' => 'senior_developer@apsdreamhome.com',
    'legal_advisor' => 'legal_advisor@apsdreamhome.com',
    'chartered_accountant' => 'ca@apsdreamhome.com',
    'accountant' => 'accountant@apsdreamhome.com',
    'developer' => 'developer@apsdreamhome.com',
    'content_writer' => 'content_writer@apsdreamhome.com',
    'graphic_designer' => 'graphic_designer@apsdreamhome.com',
    'data_entry_operator' => 'data_entry@apsdreamhome.com',
    'backoffice_staff' => 'backoffice_staff@apsdreamhome.com',
    'telecalling_executive' => 'telecalling_exec@apsdreamhome.com',
    'support_executive' => 'support_exec@apsdreamhome.com',
    'senior_associate' => 'senior_associate@apsdreamhome.com',
    'associate_team_lead' => 'associate_team_lead@apsdreamhome.com',
    'senior_agent' => 'senior_agent@apsdreamhome.com',
    'franchise_owner' => 'franchise_owner@apsdreamhome.com',
];

$stmt = $pdo->prepare("UPDATE users SET role = ? WHERE email = ? AND (role IS NULL OR role = '' OR role = 'user')");
$fixed = 0;
$notFound = 0;

foreach ($roleEmailMap as $role => $email) {
    $result = $stmt->execute([$role, $email]);
    $count = $stmt->rowCount();
    if ($count > 0) {
        echo "  FIXED: $email => $role (updated $count rows)\n";
        $fixed++;
    } else {
        // Check if already correct
        $check = $pdo->prepare("SELECT role FROM users WHERE email = ?");
        $check->execute([$email]);
        $current = $check->fetch();
        if ($current && $current['role'] === $role) {
            echo "  OK: $email => $role (already correct)\n";
        } else {
            echo "  SKIP: $email (current role=" . ($current['role'] ?? 'NULL') . ")\n";
            $notFound++;
        }
    }
}

echo "\nFixed: $fixed, Already OK/NotFound: $notFound\n";

// Verify
echo "\n=== VERIFICATION ===\n";
$roles = $pdo->query("SELECT role, COUNT(*) as c FROM users WHERE role IN ('sales_director','finance_director','hr_director','it_manager','sales_manager','team_lead','accountant','ceo','cfo','cto') GROUP BY role ORDER BY role")->fetchAll(PDO::FETCH_ASSOC);
foreach ($roles as $r) {
    echo "  {$r['role']}: {$r['c']} users\n";
}
