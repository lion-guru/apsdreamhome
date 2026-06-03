<?php
/**
 * User Consolidation Script
 * Migrate customers, admin_users, agents, associates to unified users table
 * 
 * Run AFTER safe_cleanup.php
 */

$pdo = new PDO("mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome", "root", "");

echo "=== USER CONSOLIDATION ===\n\n";

echo "Step 1: Adding new columns to users table...\n";

$alterQueries = [
    "ALTER TABLE users ADD COLUMN IF NOT EXISTS user_type ENUM('customer', 'associate', 'agent', 'employee', 'admin', 'super_admin') DEFAULT 'customer' AFTER role",
    "ALTER TABLE users ADD COLUMN IF NOT EXISTS first_name VARCHAR(100) AFTER name",
    "ALTER TABLE users ADD COLUMN IF NOT EXISTS last_name VARCHAR(100) AFTER first_name",
    "ALTER TABLE users ADD COLUMN IF NOT EXISTS date_of_birth DATE AFTER last_name",
    "ALTER TABLE users ADD COLUMN IF NOT EXISTS gender ENUM('male', 'female', 'other') AFTER date_of_birth",
    "ALTER TABLE users ADD COLUMN IF NOT EXISTS profile_image VARCHAR(500) AFTER gender",
    "ALTER TABLE users ADD COLUMN IF NOT EXISTS kyc_status ENUM('pending', 'partial', 'verified', 'rejected') DEFAULT 'pending' AFTER profile_image",
    "ALTER TABLE users ADD COLUMN IF NOT EXISTS referral_code VARCHAR(20) UNIQUE AFTER kyc_status",
    "ALTER TABLE users ADD COLUMN IF NOT EXISTS referred_by BIGINT UNSIGNED AFTER referral_code",
    "ALTER TABLE users ADD COLUMN IF NOT EXISTS mlm_position ENUM('left', 'right', 'none') DEFAULT 'none' AFTER referred_by",
    "ALTER TABLE users ADD COLUMN IF NOT EXISTS sponsor_id BIGINT UNSIGNED AFTER mlm_position",
    "ALTER TABLE users ADD COLUMN IF NOT EXISTS last_login_at TIMESTAMP NULL AFTER sponsor_id",
    "ALTER TABLE users ADD COLUMN IF NOT EXISTS login_attempts INT DEFAULT 0 AFTER last_login_at",
    "ALTER TABLE users ADD COLUMN IF NOT EXISTS locked_until TIMESTAMP NULL AFTER login_attempts",
    "ALTER TABLE users ADD COLUMN IF NOT EXISTS metadata JSON AFTER locked_until",
];

foreach ($alterQueries as $sql) {
    try {
        $pdo->exec($sql);
        echo "  ✓ " . strtok($sql, ' ') . " - Done\n";
    } catch (Exception $e) {
        // Column might already exist
        echo "  - " . strtok($sql, ' ') . " - Already exists or error: " . substr($e->getMessage(), 0, 50) . "\n";
    }
}

echo "\nStep 2: Creating supporting tables...\n";

$createTables = [
    "CREATE TABLE IF NOT EXISTS user_addresses (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id BIGINT UNSIGNED NOT NULL,
        address_type ENUM('permanent', 'current', 'billing', 'shipping', 'office') DEFAULT 'permanent',
        address_line1 VARCHAR(255) NOT NULL,
        address_line2 VARCHAR(255),
        landmark VARCHAR(100),
        city VARCHAR(100),
        district VARCHAR(100),
        state VARCHAR(100),
        country VARCHAR(100) DEFAULT 'India',
        pincode VARCHAR(10),
        latitude DECIMAL(10, 8),
        longitude DECIMAL(11, 8),
        is_primary TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_user_id (user_id),
        INDEX idx_address_type (address_type)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
    
    "CREATE TABLE IF NOT EXISTS user_bank_accounts (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id BIGINT UNSIGNED NOT NULL,
        account_type ENUM('savings', 'current', 'joint') DEFAULT 'savings',
        account_holder_name VARCHAR(200) NOT NULL,
        account_number VARCHAR(30) NOT NULL,
        bank_name VARCHAR(200) NOT NULL,
        branch_name VARCHAR(200),
        ifsc_code VARCHAR(20) NOT NULL,
        micr_code VARCHAR(20),
        is_primary TINYINT(1) DEFAULT 0,
        is_verified TINYINT(1) DEFAULT 0,
        verified_at TIMESTAMP,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_user_id (user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
    
    "CREATE TABLE IF NOT EXISTS user_documents (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id BIGINT UNSIGNED NOT NULL,
        document_type ENUM('aadhar', 'pan', 'passport', 'voter_id', 'driving_license', 'bank_passbook', 'cheque', 'photo', 'other') NOT NULL,
        document_number VARCHAR(50),
        file_path VARCHAR(500) NOT NULL,
        file_type VARCHAR(50),
        file_size INT,
        verification_status ENUM('pending', 'verified', 'rejected') DEFAULT 'pending',
        rejection_reason TEXT,
        verified_by BIGINT UNSIGNED,
        verified_at TIMESTAMP,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_user_id (user_id),
        INDEX idx_verification (verification_status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
];

foreach ($createTables as $sql) {
    $tableName = 'user_' . preg_match('/user_(addresses|bank_accounts|documents)/', $sql, $m) ? $m[1] : 'unknown';
    try {
        $pdo->exec($sql);
        echo "  ✓ user_$tableName - Created\n";
    } catch (Exception $e) {
        echo "  - user_$tableName - Already exists or error: " . substr($e->getMessage(), 0, 50) . "\n";
    }
}

echo "\nStep 3: Generating referral codes for existing users...\n";

// Generate referral codes
$users = $pdo->query("SELECT id, name, email FROM users WHERE referral_code IS NULL")->fetchAll(PDO::FETCH_ASSOC);
foreach ($users as $user) {
    $code = strtoupper(substr($user['name'], 0, 3)) . $user['id'] . rand(100, 999);
    $pdo->prepare("UPDATE users SET referral_code = ? WHERE id = ?")->execute([$code, $user['id']]);
}
echo "  ✓ Generated " . count($users) . " referral codes\n";

echo "\nStep 4: Migrating data from customers table...\n";

$customers = $pdo->query("SELECT * FROM customers")->fetchAll(PDO::FETCH_ASSOC);
foreach ($customers as $cust) {
    // Check if user already exists by email
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$cust['email']]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$existing) {
        // Generate referral code
        $refCode = 'CUS' . strtoupper(substr($cust['first_name'], 0, 2)) . rand(1000, 9999);
        
        $sql = "INSERT INTO users (name, email, phone, password, role, user_type, first_name, last_name, 
                kyc_status, referral_code, created_at) VALUES (?, ?, ?, ?, 'customer', 'customer', ?, ?, 'partial', ?, NOW())";
        
        $name = trim(($cust['first_name'] ?? '') . ' ' . ($cust['last_name'] ?? ''));
        if (empty($name)) $name = $cust['email'] ?? 'Customer';
        
        $pdo->prepare($sql)->execute([
            $name,
            $cust['email'],
            $cust['phone'],
            $cust['password'],
            $cust['first_name'] ?? '',
            $cust['last_name'] ?? '',
            $refCode
        ]);
        
        $newUserId = $pdo->lastInsertId();
        
        // Add address
        if (!empty($cust['current_address'])) {
            $pdo->prepare("INSERT INTO user_addresses (user_id, address_type, address_line1, city, state, pincode, is_primary) 
                          VALUES (?, 'current', ?, ?, ?, ?, 1)")
                ->execute([$newUserId, $cust['current_address'], $cust['city'] ?? '', $cust['state'] ?? '', $cust['pincode'] ?? '']);
        }
        
        echo "  ✓ Migrated customer: {$cust['email']}\n";
    }
}

echo "\nStep 5: Migrating data from admin_users table...\n";

$admins = $pdo->query("SELECT * FROM admin_users")->fetchAll(PDO::FETCH_ASSOC);
foreach ($admins as $admin) {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$admin['email']]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$existing) {
        $userType = $admin['role'] === 'super_admin' ? 'super_admin' : 'admin';
        
        $sql = "INSERT INTO users (name, email, password, role, user_type, created_at) 
                VALUES (?, ?, ?, ?, ?, NOW())";
        
        $pdo->prepare($sql)->execute([
            $admin['full_name'] ?? 'Admin',
            $admin['email'],
            $admin['password_hash'],
            $admin['role'],
            $userType
        ]);
        
        echo "  ✓ Migrated admin: {$admin['email']}\n";
    }
}

echo "\nStep 6: Setting user_type for existing users...\n";

$roleMapping = [
    'super_admin' => 'super_admin',
    'admin' => 'admin',
    'associate' => 'associate',
    'customer' => 'customer',
];

foreach ($roleMapping as $role => $userType) {
    $count = $pdo->prepare("UPDATE users SET user_type = ? WHERE role = ? AND user_type IS NULL")
        ->execute([$userType, $role]);
    echo "  ✓ Updated " . $pdo->query("SELECT ROW_COUNT()")->fetchColumn() . " users with role=$role\n";
}

echo "\n=== USER CONSOLIDATION COMPLETE ===\n\n";

// Summary
$stats = [
    'Total Users' => $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(),
    'Customers' => $pdo->query("SELECT COUNT(*) FROM users WHERE user_type = 'customer'")->fetchColumn(),
    'Associates' => $pdo->query("SELECT COUNT(*) FROM users WHERE user_type = 'associate'")->fetchColumn(),
    'Admins' => $pdo->query("SELECT COUNT(*) FROM users WHERE user_type IN ('admin', 'super_admin')")->fetchColumn(),
    'With Referral Code' => $pdo->query("SELECT COUNT(*) FROM users WHERE referral_code IS NOT NULL")->fetchColumn(),
    'Addresses' => $pdo->query("SELECT COUNT(*) FROM user_addresses")->fetchColumn(),
    'Bank Accounts' => $pdo->query("SELECT COUNT(*) FROM user_bank_accounts")->fetchColumn(),
    'Documents' => $pdo->query("SELECT COUNT(*) FROM user_documents")->fetchColumn(),
];

echo "Summary:\n";
foreach ($stats as $label => $value) {
    echo "  • $label: $value\n";
}
