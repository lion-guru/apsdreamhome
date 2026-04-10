<?php
// Check Current User Roles in Database
$pdo = new PDO('mysql:host=localhost;port=3307;dbname=apsdreamhome', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

echo "Checking User Roles in Database...\n\n";

// Get unique roles from users table
$roles = $pdo->query("SELECT DISTINCT role, user_type, COUNT(*) as count FROM users GROUP BY role, user_type ORDER BY count DESC")->fetchAll();

echo "Current Roles in users table:\n";
echo str_pad("Role", 30) . str_pad("User Type", 20) . str_pad("Count", 10) . "\n";
echo str_repeat("-", 60) . "\n";

foreach ($roles as $role) {
    echo str_pad($role['role'], 30) . str_pad($role['user_type'], 20) . str_pad($role['count'], 10) . "\n";
}

echo "\nTotal unique roles: " . count($roles) . "\n";

// Check role column structure
echo "\nRole column structure:\n";
$roleInfo = $pdo->query("SHOW COLUMNS FROM users LIKE 'role'")->fetch();
echo "Type: " . $roleInfo['Type'] . "\n";
echo "Null: " . $roleInfo['Null'] . "\n";
echo "Default: " . ($roleInfo['Default'] ?? 'NULL') . "\n";

echo "\nDone.\n";
