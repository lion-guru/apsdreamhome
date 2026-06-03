<?php

$host = '127.0.0.1';
$port = 3307;
$dbname = 'apsdreamhome';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    echo "Connected to database.\n";

    $pdo->exec("ALTER TABLE users ADD COLUMN unified_role VARCHAR(50) DEFAULT 'customer' AFTER user_role");
    echo "Added unified_role column.\n";

    $stmt = $pdo->query("SELECT id, user_type, role, user_role FROM users");
    $updateStmt = $pdo->prepare("UPDATE users SET unified_role = ? WHERE id = ?");
    $count = 0;

    $mapping = [
        'user' => 'customer',
        'manager' => 'employee',
        'director' => 'admin',
        'ceo' => 'admin',
        'cfo' => 'admin',
        'coo' => 'admin',
        'cto' => 'admin',
        'cmo' => 'admin',
        'chro' => 'admin',
        'builder' => 'customer',
        'investor' => 'customer',
    ];

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $id = $row['id'];
        $userType = $row['user_type'] ?? '';
        $roleVal = $row['role'] ?? '';
        $userRole = $row['user_role'] ?? '';

        // Priority: user_type > role > user_role
        // But if user_type is 'customer' (the old default), treat it as unset
        $source = $userType;
        if (empty($source) || $source === '' || $source === 'customer') {
            $source = $roleVal;
        }
        if (empty($source)) {
            $source = $userRole;
        }
        if (empty($source)) {
            $source = 'customer';
        }

        $unified = $mapping[$source] ?? $source;

        $valid = ['customer', 'associate', 'agent', 'employee', 'admin', 'super_admin'];
        if (!in_array($unified, $valid)) {
            $unified = 'customer';
        }

        $updateStmt->execute([$unified, $id]);
        $count++;
    }

    echo "Migrated $count user records.\n";

    $verify = $pdo->query("SELECT unified_role, COUNT(*) as cnt FROM users GROUP BY unified_role ORDER BY unified_role");
    while ($v = $verify->fetch(PDO::FETCH_ASSOC)) {
        echo "  {$v['unified_role']}: {$v['cnt']} users\n";
    }

    echo "Dropping old columns...\n";
    $pdo->exec("ALTER TABLE users DROP COLUMN user_type");
    $pdo->exec("ALTER TABLE users DROP COLUMN user_role");
    $pdo->exec("ALTER TABLE users DROP COLUMN role");
    $pdo->exec("ALTER TABLE users CHANGE COLUMN unified_role role VARCHAR(50) DEFAULT 'customer'");

    echo "Done!\n";

    $final = $pdo->query("SELECT role, COUNT(*) as cnt FROM users GROUP BY role ORDER BY role");
    while ($f = $final->fetch(PDO::FETCH_ASSOC)) {
        echo "  {$f['role']}: {$f['cnt']} users\n";
    }

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
