<?php
$config = require dirname(__DIR__) . '/config/database.php';
$pdo = new PDO(
    "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4",
    $config['username'],
    $config['password'],
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

// 1. Add 'telecaller' to users.role ENUM
$cols = $pdo->query("SHOW COLUMNS FROM users WHERE Field = 'role'")->fetch(PDO::FETCH_ASSOC);
$currentType = $cols['Type'];
echo "Current role ENUM: $currentType\n";

if (strpos($currentType, "'telecaller'") === false) {
    $newType = str_replace("customer')", "customer','telecaller')", $currentType);
    echo "  New ENUM: $newType\n";
    $pdo->exec("ALTER TABLE users MODIFY COLUMN role $newType");
    echo "  + Added 'telecaller' to users.role ENUM\n";
} else {
    echo "  = 'telecaller' already in role ENUM\n";
}

// 2. Add 'telecaller' to users.onboarding_track ENUM
$cols = $pdo->query("SHOW COLUMNS FROM users WHERE Field = 'onboarding_track'")->fetch(PDO::FETCH_ASSOC);
if ($cols) {
    $currentType = $cols['Type'];
    echo "Current onboarding_track ENUM: $currentType\n";
    if (strpos($currentType, "'telecaller'") === false) {
        $newType = str_replace("associate')", "associate','telecaller')", $currentType);
        echo "  New ENUM: $newType\n";
        $pdo->exec("ALTER TABLE users MODIFY COLUMN onboarding_track $newType");
        echo "  + Added 'telecaller' to onboarding_track ENUM\n";
    } else {
        echo "  = 'telecaller' already in onboarding_track ENUM\n";
    }
}

echo "\nDone!\n";
