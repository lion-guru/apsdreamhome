<?php
// require_once __DIR__ . '/../config/database.php';

function getDBConnection()
{
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
        return new PDO($dsn, $user, $pass, $options);
    } catch (\PDOException $e) {
        throw new \PDOException($e->getMessage(), (int)$e->getCode());
    }
}

$pdo = getDBConnection();

echo "--- Customers vs Users Check ---\n";

$customers = $pdo->query("SELECT * FROM customers")->fetchAll();
echo "Found " . count($customers) . " customers.\n";

foreach ($customers as $c) {
    echo "Customer: {$c['email']} (ID: {$c['id']})\n";

    $u = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $u->execute([$c['email']]);
    $user = $u->fetch();

    if ($user) {
        echo "  ✅ Found in users table (ID: {$user['id']}, Role: {$user['role']})\n";
    } else {
        echo "  ❌ NOT found in users table.\n";
    }
}

echo "\n--- Settings Check ---\n";
$sys = $pdo->query("SELECT * FROM system_settings")->fetchAll();
$site = $pdo->query("SELECT * FROM site_settings")->fetchAll();

echo "System Settings (" . count($sys) . "):\n";
foreach ($sys as $s) echo "  - {$s['setting_key']}: {$s['setting_value']}\n";

echo "Site Settings (" . count($site) . "):\n";
foreach ($site as $s) echo "  - {$s['setting_name']}: {$s['value']}\n";
