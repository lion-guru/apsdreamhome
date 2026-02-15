<?php
// scripts/migrate_legacy_users.php

require_once __DIR__ . '/../app/core/Database.php';

use App\Core\Database;

$db = Database::getInstance();
$conn = $db->getConnection();

echo "Starting legacy user migration...\n";

// Get all legacy users
$stmt = $conn->query("SELECT * FROM user");
$legacyUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($legacyUsers as $u) {
    echo "Processing user: {$u['uname']} ({$u['uemail']})...\n";
    
    // Check if user already exists in new table (by email or ID)
    $stmtCheck = $conn->prepare("SELECT id FROM users WHERE email = :email OR id = :id");
    $stmtCheck->execute(['email' => $u['uemail'], 'id' => $u['uid']]);
    $existing = $stmtCheck->fetch(PDO::FETCH_ASSOC);
    
    if ($existing) {
        echo "  User already exists (ID: {$existing['id']}). Skipping.\n";
        continue;
    }
    
    // Map columns
    // uid -> id
    // uname -> name
    // uemail -> email
    // uphone -> phone
    // upass -> password (if exists) or default?
    // utype -> role
    // status -> status
    // join_date -> created_at
    
    $password = $u['upass'] ?? null;
    if (empty($password)) {
        // If password is empty, maybe set a default or leave null
        // Ideally we should keep it null so they have to reset it?
        // Or if it's legacy plaintext? (Unlikely)
    }
    
    $role = $u['utype'] ?? 'user';
    // Map roles if necessary
    
    try {
        $stmtInsert = $conn->prepare("
            INSERT INTO users (id, name, email, phone, password, role, status, created_at, updated_at)
            VALUES (:id, :name, :email, :phone, :password, :role, :status, :created_at, NOW())
        ");
        
        $stmtInsert->execute([
            'id' => $u['uid'],
            'name' => $u['uname'],
            'email' => $u['uemail'],
            'phone' => $u['uphone'],
            'password' => $password,
            'role' => $role,
            'status' => $u['status'] ?? 'active',
            'created_at' => $u['join_date'] ?? date('Y-m-d H:i:s')
        ]);
        
        echo "  User migrated (ID: {$u['uid']}).\n";
        
    } catch (Exception $e) {
        echo "  Error migrating user: " . $e->getMessage() . "\n";
    }
}

echo "Legacy user migration complete.\n";
