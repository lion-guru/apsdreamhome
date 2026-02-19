<?php

/**
 * Database Consolidation Tool
 * Merges legacy 'user' table into 'users'
 * Merges legacy 'agents' table into 'associates' (if needed)
 * Reports on conflicts
 */

// Configuration
$host = 'localhost';
$dbname = 'apsdreamhome';
$username = 'root';
$password = '';

// Connect to DB
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connected to database.\n";
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage() . "\n");
}

echo "\n--- Analyzing User Consolidation ---\n";

// Check if tables exist
$tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
if (!in_array('user', $tables)) {
    die("Table 'user' does not exist. Consolidation might have already been run.\n");
}
if (!in_array('users', $tables)) {
    die("Table 'users' does not exist. Cannot consolidate.\n");
}

// Get all legacy users
$stmt = $pdo->query("SELECT * FROM user");
$legacyUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);

$migratedCount = 0;
$updatedCount = 0;
$skippedCount = 0;
$toMigrate = [];

foreach ($legacyUsers as $lUser) {
    $email = $lUser['uemail'];
    $phone = $lUser['uphone'];

    // Check if exists in users
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? OR phone = ?");
    $stmt->execute([$email, $phone]);
    $modernUser = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($modernUser) {
        echo "[EXISTS] User {$email} already exists in 'users' table (ID: {$modernUser['id']}).\n";
        $skippedCount++;
    } else {
        echo "[MISSING] User {$email} NOT found in 'users' table. Will migrate.\n";
        $toMigrate[] = $lUser;
        $migratedCount++;
    }
}

echo "\nSummary for Users:\n";
echo "Legacy Users: " . count($legacyUsers) . "\n";
echo "To Migrate: $migratedCount\n";
echo "Skipped (Already Exists): $skippedCount\n";

echo "\n--- Analyzing Agents vs Associates ---\n";
// Check agents table
if (in_array('agents', $tables)) {
    $stmt = $pdo->query("SELECT count(*) FROM agents");
    $agentsCount = $stmt->fetchColumn();
    echo "Legacy 'agents' count: $agentsCount\n";
} else {
    echo "Table 'agents' does not exist.\n";
}

if (in_array('associates', $tables)) {
    $stmt = $pdo->query("SELECT count(*) FROM associates");
    $associatesCount = $stmt->fetchColumn();
    echo "Modern 'associates' count: $associatesCount\n";
} else {
    echo "Table 'associates' does not exist.\n";
}

// Execution Logic
if ($migratedCount > 0) {
    echo "\nPerforming Migration of $migratedCount users...\n";
    foreach ($toMigrate as $lUser) {
        try {
            // Map fields: user -> users
            // user: uid, uname, uemail, uphone, upass, utype, uimage, address, city, state, pincode, status, created_at
            // users: name, email, phone, password, role, address, city, state, pincode, status, created_at

            $role = ($lUser['utype'] == 1) ? 'admin' : (($lUser['role'] == 'associate') ? 'associate' : 'user');

            $sql = "INSERT INTO users (name, email, phone, password, role, address, city, state, pincode, status, created_at) 
                    VALUES (:name, :email, :phone, :password, :role, :address, :city, :state, :pincode, :status, :created_at)";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':name' => $lUser['uname'],
                ':email' => $lUser['uemail'],
                ':phone' => $lUser['uphone'],
                ':password' => $lUser['upass'], // Assuming same hash
                ':role' => $role,
                ':address' => $lUser['address'],
                ':city' => $lUser['city'],
                ':state' => $lUser['state'],
                ':pincode' => $lUser['pincode'],
                ':status' => $lUser['status'] ?? 'active',
                ':created_at' => $lUser['join_date'] ?? date('Y-m-d H:i:s')
            ]);
            echo "Migrated: {$lUser['uemail']}\n";
        } catch (Exception $e) {
            echo "Failed to migrate {$lUser['uemail']}: " . $e->getMessage() . "\n";
        }
    }
}

// Rename/Drop legacy tables
if ($migratedCount == 0 && $skippedCount > 0) {
    echo "\nAll users accounted for. Dropping 'user' table.\n";
    $pdo->exec("DROP TABLE user");
    echo "Dropped 'user' table.\n";
}

if (isset($agentsCount) && $agentsCount < 5 && isset($associatesCount) && $associatesCount > 0) {
    echo "\n'agents' table seems redundant. Dropping.\n";
    $pdo->exec("DROP TABLE agents");
    echo "Dropped 'agents' table.\n";
}
