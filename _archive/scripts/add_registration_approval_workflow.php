<?php
/**
 * Add Registration Approval Workflow
 * Adds registration_status, KYC columns, and user_kyc_documents table
 */

require_once __DIR__ . '/../app/Core/autoload.php';

$db = \App\Core\Database\Database::getInstance();

echo "=== Adding Registration Approval Workflow ===\n\n";

// 1. Add registration_status column to users table
echo "1. Adding registration_status column to users...\n";
try {
    $db->query("ALTER TABLE users ADD COLUMN registration_status ENUM('pending','approved','rejected') DEFAULT 'approved' AFTER status");
    echo "   ✅ registration_status column added\n";
} catch (Exception $e) {
    if (str_contains($e->getMessage(), 'Duplicate column')) {
        echo "   ⏭️  registration_status column already exists\n";
    } else {
        echo "   ❌ Error: " . $e->getMessage() . "\n";
    }
}

// 2. Add kyc_status column to users table
echo "\n2. Adding kyc_status column to users...\n";
try {
    $db->query("ALTER TABLE users ADD COLUMN kyc_status ENUM('none','pending','verified','rejected') DEFAULT 'none' AFTER registration_status");
    echo "   ✅ kyc_status column added\n";
} catch (Exception $e) {
    if (str_contains($e->getMessage(), 'Duplicate column')) {
        echo "   ⏭️  kyc_status column already exists\n";
    } else {
        echo "   ❌ Error: " . $e->getMessage() . "\n";
    }
}

// 3. Add rejection_reason column to users table
echo "\n3. Adding rejection_reason column to users...\n";
try {
    $db->query("ALTER TABLE users ADD COLUMN rejection_reason TEXT NULL AFTER kyc_status");
    echo "   ✅ rejection_reason column added\n";
} catch (Exception $e) {
    if (str_contains($e->getMessage(), 'Duplicate column')) {
        echo "   ⏭️  rejection_reason column already exists\n";
    } else {
        echo "   ❌ Error: " . $e->getMessage() . "\n";
    }
}

// 4. Add approved_by column to users table
echo "\n4. Adding approved_by column to users...\n";
try {
    $db->query("ALTER TABLE users ADD COLUMN approved_by INT NULL AFTER rejection_reason");
    echo "   ✅ approved_by column added\n";
} catch (Exception $e) {
    if (str_contains($e->getMessage(), 'Duplicate column')) {
        echo "   ⏭️  approved_by column already exists\n";
    } else {
        echo "   ❌ Error: " . $e->getMessage() . "\n";
    }
}

// 5. Add approved_at column to users table
echo "\n5. Adding approved_at column to users...\n";
try {
    $db->query("ALTER TABLE users ADD COLUMN approved_at DATETIME NULL AFTER approved_by");
    echo "   ✅ approved_at column added\n";
} catch (Exception $e) {
    if (str_contains($e->getMessage(), 'Duplicate column')) {
        echo "   ⏭️  approved_at column already exists\n";
    } else {
        echo "   ❌ Error: " . $e->getMessage() . "\n";
    }
}

// 6. Create user_kyc_documents table
echo "\n6. Creating user_kyc_documents table...\n";
try {
    $db->query("CREATE TABLE IF NOT EXISTS user_kyc_documents (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        document_type ENUM('pan','aadhaar','photo','address_proof','other') NOT NULL,
        document_path VARCHAR(500) NOT NULL,
        document_number VARCHAR(100) NULL,
        status ENUM('pending','verified','rejected') DEFAULT 'pending',
        verified_by INT NULL,
        verified_at DATETIME NULL,
        rejection_reason TEXT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_user_id (user_id),
        INDEX idx_status (status),
        INDEX idx_user_id2 (user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "   ✅ user_kyc_documents table created\n";
} catch (Exception $e) {
    if (str_contains($e->getMessage(), 'already exists')) {
        echo "   ⏭️  user_kyc_documents table already exists\n";
    } else {
        echo "   ❌ Error: " . $e->getMessage() . "\n";
    }
}

// 7. Set existing users to 'approved' status
echo "\n7. Setting existing active users to 'approved' status...\n";
try {
    $result = $db->query("UPDATE users SET registration_status = 'approved' WHERE status = 'active' AND registration_status = 'pending'");
    echo "   ✅ Updated existing users to approved\n";
} catch (Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
}

echo "\n=== Migration Complete! ===\n";
echo "\nNext steps:\n";
echo "1. Update registration controllers to set registration_status='pending'\n";
echo "2. Update login methods to check registration_status='approved'\n";
echo "3. Create admin approval queue view\n";
echo "4. Add KYC upload functionality\n";
