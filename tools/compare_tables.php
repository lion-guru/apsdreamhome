<?php
// require_once __DIR__ . '/../app/Core/UnifiedModel.php';

// Quick DB connection
$host = 'localhost';
$db   = 'apsdreamhome';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

echo "--- User vs Users ---\n";
$users = $pdo->query("SELECT * FROM users")->fetchAll();
$user_tbl = $pdo->query("SELECT * FROM user")->fetchAll();

echo "Count 'users': " . count($users) . "\n";
echo "Count 'user': " . count($user_tbl) . "\n";

echo "\nSample 'users' row:\n";
print_r($users[0] ?? 'Empty');

echo "\nSample 'user' row:\n";
print_r($user_tbl[0] ?? 'Empty');

echo "\n--- Agents vs Associates ---\n";
// Check if tables exist first to avoid errors
$tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

if (in_array('agents', $tables)) {
    $agents = $pdo->query("SELECT * FROM agents LIMIT 1")->fetch();
    echo "\nSample 'agents' row:\n";
    print_r($agents);
} else {
    echo "\n'agents' table does not exist.\n";
}

if (in_array('associates', $tables)) {
    $associates = $pdo->query("SELECT * FROM associates LIMIT 1")->fetch();
    echo "\nSample 'associates' row:\n";
    print_r($associates);
} else {
    echo "\n'associates' table does not exist.\n";
}

echo "\n--- Expenses Table ---\n";
if (in_array('expenses', $tables)) {
    $columns = $pdo->query("DESCRIBE expenses")->fetchAll(PDO::FETCH_COLUMN);
    echo "Columns: " . implode(', ', $columns) . "\n";
} else {
    echo "'expenses' table does not exist.\n";
}
