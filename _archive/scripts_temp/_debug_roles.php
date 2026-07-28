<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require __DIR__ . '/../config/bootstrap.php';
require __DIR__ . '/../app/Core/Database.php';
use App\Core\Database;
$db = Database::getInstance();
$pdo = $db->getConnection();

// Check if there's a trigger
$triggers = $pdo->query("SHOW TRIGGERS LIKE 'users'")->fetchAll(PDO::FETCH_ASSOC);
echo "Triggers on users table: " . count($triggers) . "\n";
foreach ($triggers as $t) {
    echo "  - {$t['Trigger']}: {$t['Event']}\n";
}

// Check column definition
$col = $pdo->query("SHOW COLUMNS FROM users LIKE 'role'")->fetch(PDO::FETCH_ASSOC);
echo "\nColumn 'role': " . json_encode($col) . "\n";

// Try a direct UPDATE and immediately SELECT
echo "\n=== DIRECT TEST ===\n";
$email = 'sales_director@apsdreamhome.com';
$pdo->prepare("UPDATE users SET role = 'sales_director' WHERE email = ?")->execute([$email]);
echo "After UPDATE: affected rows = " . $pdo->rowCount() . "\n";

$check = $pdo->prepare("SELECT role FROM users WHERE email = ?");
$check->execute([$email]);
echo "After UPDATE: role = '" . $check->fetch()['role'] . "'\n";

// Check if role column is in a different table (maybe view?)
$tables = $pdo->query("SHOW TABLES LIKE 'users'")->fetchAll(PDO::FETCH_NUM);
echo "\nUsers table exists: " . (count($tables) > 0 ? 'YES' : 'NO') . "\n";

// Check if there are multiple users tables or views
$allTables = $pdo->query("SHOW FULL TABLES WHERE Table_type = 'VIEW' AND Table_name LIKE '%user%'")->fetchAll(PDO::FETCH_NUM);
echo "User-related views: " . count($allTables) . "\n";
foreach ($allTables as $t) echo "  " . $t[0] . "\n";

// Try raw SQL
echo "\n=== RAW SQL TEST ===\n";
$pdo->exec("UPDATE users SET role = 'sales_director' WHERE email = 'sales_director@apsdreamhome.com'");
$rows = $pdo->exec("SELECT role FROM users WHERE email = 'sales_director@apsdreamhome.com'");
echo "Rows affected by exec: " . $rows . "\n";

$stmt = $pdo->query("SELECT role FROM users WHERE email = 'sales_director@apsdreamhome.com'");
echo "Role value: '" . $stmt->fetchColumn() . "'\n";

// Check if role is stored as enum or has special handling
$createTable = $pdo->query("SHOW CREATE TABLE users")->fetch(PDO::FETCH_ASSOC);
echo "\n=== CREATE TABLE (role portion) ===\n";
preg_match('/`role`[^,]+/', $createTable['Create Table'], $matches);
echo ($matches[0] ?? 'NOT FOUND') . "\n";
