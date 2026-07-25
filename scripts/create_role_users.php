<?php
require_once __DIR__ . '/../config/bootstrap.php';

use App\Core\Database\Database;

$db = Database::getInstance();

// All roles with test users
$roles = [
    // Executive/Director Level
    ['sales_director', 'sales_director@apsdreamhome.com', 'Sales Director'],
    ['marketing_director', 'marketing_director@apsdreamhome.com', 'Marketing Director'],
    ['construction_director', 'construction_director@apsdreamhome.com', 'Construction Director'],
    ['finance_director', 'finance_director@apsdreamhome.com', 'Finance Director'],
    ['hr_director', 'hr_director@apsdreamhome.com', 'HR Director'],
    
    // Department Managers
    ['department_manager', 'dept_manager@apsdreamhome.com', 'Department Manager'],
    ['project_manager', 'project_manager@apsdreamhome.com', 'Project Manager'],
    ['sales_manager', 'sales_manager@apsdreamhome.com', 'Sales Manager'],
    ['hr_manager', 'hr_manager@apsdreamhome.com', 'HR Manager'],
    ['marketing_manager', 'marketing_manager@apsdreamhome.com', 'Marketing Manager'],
    ['finance_manager', 'finance_manager@apsdreamhome.com', 'Finance Manager'],
    ['property_manager', 'property_manager@apsdreamhome.com', 'Property Manager'],
    ['it_manager', 'it_manager@apsdreamhome.com', 'IT Manager'],
    ['operations_manager', 'operations_manager@apsdreamhome.com', 'Operations Manager'],
    
    // Team Leads
    ['team_lead', 'team_lead@apsdreamhome.com', 'Team Lead'],
    ['telecalling_lead', 'telecalling_lead@apsdreamhome.com', 'Telecalling Lead'],
    ['sales_team_lead', 'sales_team_lead@apsdreamhome.com', 'Sales Team Lead'],
    ['support_lead', 'support_lead@apsdreamhome.com', 'Support Lead'],
    
    // Senior Professional
    ['senior_accountant', 'senior_accountant@apsdreamhome.com', 'Senior Accountant'],
    ['senior_developer', 'senior_developer@apsdreamhome.com', 'Senior Developer'],
    ['legal_advisor', 'legal_advisor@apsdreamhome.com', 'Legal Advisor'],
    ['chartered_accountant', 'ca@apsdreamhome.com', 'Chartered Accountant'],
    
    // Professional
    ['accountant', 'accountant@apsdreamhome.com', 'Accountant'],
    ['developer', 'developer@apsdreamhome.com', 'Developer'],
    ['content_writer', 'content_writer@apsdreamhome.com', 'Content Writer'],
    ['graphic_designer', 'graphic_designer@apsdreamhome.com', 'Graphic Designer'],
    ['data_entry_operator', 'data_entry@apsdreamhome.com', 'Data Entry Operator'],
    ['backoffice_staff', 'backoffice_staff@apsdreamhome.com', 'Backoffice Staff'],
    
    // Telecalling/Support
    ['telecalling_executive', 'telecalling_exec@apsdreamhome.com', 'Telecalling Executive'],
    ['support_executive', 'support_exec@apsdreamhome.com', 'Support Executive'],
    
    // Associate/Agent
    ['senior_associate', 'senior_associate@apsdreamhome.com', 'Senior Associate'],
    ['associate_team_lead', 'associate_team_lead@apsdreamhome.com', 'Associate Team Lead'],
    ['senior_agent', 'senior_agent@apsdreamhome.com', 'Senior Agent'],
    ['franchise_owner', 'franchise_owner@apsdreamhome.com', 'Franchise Owner'],
];

$password = password_hash('Aps@2026', PASSWORD_BCRYPT);

foreach ($roles as $role) {
    list($roleName, $email, $name) = $role;
    
    // Check if exists
    $existing = $db->fetchOne("SELECT id FROM users WHERE email = ?", [$email]);
    
    if ($existing) {
        echo "Exists: $email ($roleName)\n";
        continue;
    }
    
    try {
        $db->insert('users', [
            'email' => $email,
            'password' => $password,
            'name' => $name,
            'role' => $roleName,
            'is_active' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        echo "Created: $email ($roleName)\n";
    } catch (Exception $e) {
        echo "Error for $email: " . $e->getMessage() . "\n";
    }
}

echo "\nDone! All users have password: Aps@2026\n";