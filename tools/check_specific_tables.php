<?php
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
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

$stmt = $pdo->query("SHOW TABLES");
$tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

$targets = ['agents', 'associates', 'users', 'user', 'agent_users'];

echo "Checking for tables: " . implode(', ', $targets) . "\n\n";

foreach ($targets as $target) {
    if (in_array($target, $tables)) {
        $count = $pdo->query("SELECT COUNT(*) FROM `$target`")->fetchColumn();
        echo "✅ Table '$target' exists with $count rows.\n";
    } else {
        echo "❌ Table '$target' does NOT exist.\n";
    }
}
